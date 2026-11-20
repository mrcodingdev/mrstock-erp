"""Valida idempotência por HTTP sobre o schema limpo e a migration 001."""
from concurrent.futures import ThreadPoolExecutor
from contextlib import ExitStack, contextmanager
import json
from pathlib import Path
import re
import shutil
import socket
import subprocess
import time

import run_pdv_margem as fixture


@contextmanager
def worker(nome):
    sessao = APP.parent / nome
    sessao.mkdir()
    for arquivo in APP.parent.glob("sess_*"):
        shutil.copyfile(arquivo, sessao / arquivo.name)
    porta = fixture.porta_livre()
    processo = subprocess.Popen(
        [fixture.PHP, "-d", f"session.save_path={sessao}", "-d", "session.save_handler=files",
         "-S", f"127.0.0.1:{porta}", "-t", APP],
        cwd=APP.parent, env=AMBIENTE, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
        creationflags=subprocess.CREATE_NO_WINDOW,
    )
    try:
        for _ in range(30):
            try:
                with socket.create_connection(("127.0.0.1", porta), timeout=0.3):
                    break
            except OSError:
                time.sleep(0.1)
        else:
            raise RuntimeError("Worker PHP da fixture indisponível.")
        yield porta
    finally:
        if processo.poll() is None:
            processo.terminate()
        processo.wait(timeout=10)


def paralelo(payload, cookie, rodada, verificar_margem=False):
    (APP.parent / "rodada").write_text(rodada, encoding="ascii")
    with ExitStack() as stack:
        portas = [stack.enter_context(worker(f"sessao-{rodada}-{i}")) for i in range(2)]
        with ThreadPoolExecutor(max_workers=2) as threads:
            respostas = list(threads.map(lambda p: enviar(p, cookie, payload), portas))
    assert len(list(APP.parent.glob(f"{rodada}-*"))) == 2, "Os dois workers não chegaram à transação."
    assert (APP.parent / f"duplicada-{rodada}").read_text(encoding="ascii").splitlines() == ["1062"]
    assert sorted(codigo for codigo, _ in respostas) == ([200, 409] if verificar_margem else [200, 200]), respostas
    destinos = {resposta["redirect"] for _, resposta in respostas}
    assert len(destinos) == 1 and next(iter(destinos)).startswith("/vendas/cupom.php?venda_id=")
    if verificar_margem:
        assert {resposta["status"] for _, resposta in respostas} == {"ok", "margem_ja_confirmada"}
    print(f"{rodada}: dois workers após consulta inicial e erro 1062 no banco; HTTP {sorted(c for c, _ in respostas)}; mesmo cupom.")


def enviar(porta, cookie, payload):
    codigo, _, corpo = fixture.requisitar(porta, "/vendas/functions.php?tipo=venda", cookie, payload)
    return codigo, json.loads(corpo)


def conferir(porta, sql):
    def abrir_sessao():
        codigo, headers, csrf = fixture.requisitar(porta, "/sessao.php")
        assert codigo == 200 and len(csrf) == 64
        return headers["Set-Cookie"].split(";", 1)[0], csrf

    def nova_chave(cookie):
        codigo, _, html = fixture.requisitar(porta, "/vendas/pdv.php", cookie)
        assert codigo == 200
        return fixture.chave_pdv(html)

    def estado():
        return sql("""
SELECT (SELECT COUNT(*) FROM vendas WHERE chave_envio IS NOT NULL),
       (SELECT COUNT(*) FROM vendas_itens),
       (SELECT quantidade FROM produtos WHERE id=1),
       (SELECT quantidade FROM lotes WHERE id=1),
       (SELECT COUNT(*) FROM movimentacoes WHERE tipo='saida_venda'),
       (SELECT COALESCE(SUM(quantidade),0) FROM movimentacoes WHERE tipo='saida_venda'),
       (SELECT COUNT(*) FROM cupons_fiscais);
""").split("\t")

    def conferir_estado(label, vendas, saldo):
        assert list(map(int, estado())) == [vendas, vendas, saldo, saldo, vendas, 11 - saldo, vendas], estado()
        print(f"{label}: vendas/itens/kardex/cupons={vendas}/{vendas}/{vendas}/{vendas}, produto/lote={saldo}/{saldo}.")

    # O INSERT legado omite a coluna e continua válido para mais de uma venda.
    sql("UPDATE produtos SET quantidade=11 WHERE id=1; UPDATE lotes SET quantidade=11 WHERE id=1;")
    sql("INSERT INTO vendas (total, forma_pagamento) VALUES (1, 'PIX'), (2, 'PIX');")
    assert sql("SELECT COUNT(*) FROM vendas WHERE chave_envio IS NULL;") == "2"
    assert sql("SELECT NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() "
               "AND TABLE_NAME='vendas' AND INDEX_NAME='uq_vendas_chave_envio';") == "0"
    print("Migration 001: índice UNIQUE e dois INSERTs legados com NULL.")

    cookie, csrf = abrir_sessao()
    outro_cookie, outro_csrf = abrir_sessao()
    chave1, chave2, chave3 = (nova_chave(cookie) for _ in range(3))
    assert len({chave1, chave2, chave3}) == 3
    base = {"csrf_token": csrf, "acao": "venda_completa", "forma_pagamento": "PIX",
            "cart_data": '[{"id":1,"quantidade":2}]', "desconto": 0}
    for sessao, tentativa in ((cookie, base), (cookie, {**base, "chave_envio": "f" * 32}),
                               (outro_cookie, {**base, "csrf_token": outro_csrf, "chave_envio": chave1})):
        codigo, resposta = enviar(porta, sessao, tentativa)
        assert codigo == 422 and resposta["erro"] == "chave_envio_invalida"
    conferir_estado("ausente/desconhecida/outra sessão", 0, 11)

    sql("UPDATE produtos SET preco_compra=5 WHERE id=1;")
    paralelo({**base, "chave_envio": chave1}, cookie, "comum")
    conferir_estado("mesma chave", 1, 9)

    primeira = enviar(porta, cookie, {**base, "chave_envio": chave2})
    replay = enviar(porta, cookie, {**base, "chave_envio": chave2})
    assert primeira == replay and primeira[0] == 200
    assert sql("SELECT COUNT(DISTINCT chave_envio) FROM vendas WHERE chave_envio IS NOT NULL;") == "2"
    conferir_estado("segunda aba e reenvio após consumo", 2, 7)

    tentativa = {**base, "chave_envio": chave3}
    codigo, resposta = enviar(porta, cookie, {**tentativa, "cart_data": '[{"id":1,"quantidade":8}]'})
    assert codigo == 422 and resposta["erro"] == "estoque"
    conferir_estado("ruptura", 2, 7)
    assert enviar(porta, cookie, tentativa)[0] == 200
    conferir_estado("ruptura corrigida com mesma chave", 3, 5)

    sql("UPDATE produtos SET preco_compra=11 WHERE id=1;")
    margem = {**base, "chave_envio": nova_chave(cookie), "cart_data": '[{"id":1,"quantidade":1}]'}
    codigo, aviso = enviar(porta, cookie, margem)
    assert codigo == 409 and set(aviso) == {"status", "token"}
    outra_margem = {**margem, "chave_envio": nova_chave(cookie)}
    codigo, outro_aviso = enviar(porta, cookie, outra_margem)
    assert codigo == 409 and outro_aviso["token"] != aviso["token"]
    conferir_estado("dois avisos em abas distintas", 3, 5)
    paralelo({**margem, "confirmacao_margem": aviso["token"]}, cookie, "margem", verificar_margem=True)
    conferir_estado("confirmação simultânea de margem", 4, 4)
    assert sql("SELECT COUNT(*) FROM logs WHERE acao='MARGEM_CONFIRMADA_PDV';") == "1"
    codigo, resultado = enviar(porta, cookie, {**margem, "confirmacao_margem": aviso["token"]})
    assert codigo == 409 and resultado["status"] == "margem_ja_confirmada"
    assert enviar(porta, cookie, {**outra_margem, "confirmacao_margem": outro_aviso["token"]})[0] == 200
    conferir_estado("confirmação da outra aba preservada", 5, 3)

    sql("UPDATE produtos SET preco_venda=1.20, preco_compra=1.10 WHERE id=1;")
    for vendas, modo in enumerate(("bloquear", "aviso"), start=6):
        sql(f"UPDATE configuracoes SET valor='{modo}' WHERE chave='pdv_trava_margem';")
        chave = nova_chave(cookie)
        codigo, resposta = enviar(porta, cookie, {**margem, "chave_envio": chave, "desconto": "0.10"})
        assert codigo == 200 and resposta["status"] == "ok"
        assert sql("SELECT total FROM vendas ORDER BY id DESC LIMIT 1;") == "1.10"
        conferir_estado(f"igualdade {modo} em R$ 1,10", vendas, 8 - vendas)

    _, _, chave = fixture.requisitar(porta, "/rapida.php", cookie)
    assert re.fullmatch(r"[a-f0-9]{32}", chave)
    rapida = {**base, "acao": "venda_rapida", "chave_envio": chave, "produto_id": 1, "quantidade": 1}
    rota = "/vendas/functions.php?tipo=venda"
    codigo, headers, _ = fixture.requisitar(porta, rota, cookie, rapida)
    assert codigo == 302 and headers["Location"] == "/dashboard.php?msg=sucesso"
    codigo, headers, _ = fixture.requisitar(porta, rota, cookie, rapida)
    assert codigo == 302 and headers["Location"].startswith("/vendas/cupom.php?venda_id=")
    conferir_estado("venda rápida e reenvio", 8, 0)


def preparar(pasta, porta, datadir):
    global APP
    APP = preparar_original(pasta, porta, datadir)
    (APP / "rapida.php").write_text("""<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/venda_envio.php';
echo emitir_chave_envio_venda('venda_rapida');
""", encoding="utf-8")
    db = APP / "inc/database.php"
    codigo = db.read_text(encoding="utf-8").replace("$pdo = new PDO(", """
// Na fixture, cada worker está em um armazenamento de sessão separado.
// A barreira começa somente após a consulta inicial de idempotência.
final class PdoDaFixture extends PDO {
    public function beginTransaction(): bool {
        $diretorio = dirname(__DIR__, 2);
        $arquivo = $diretorio . '/rodada';
        if (is_file($arquivo) && str_contains(session_save_path(), 'sessao-')) {
            $rodada = file_get_contents($arquivo);
            file_put_contents($diretorio . '/' . $rodada . '-' . getmypid(), '1');
            $limite = microtime(true) + 5;
            while (count(array_filter(glob($diretorio . '/' . $rodada . '-*'), 'is_file')) < 2) {
                if (microtime(true) >= $limite) {
                    throw new RuntimeException('Segundo worker não chegou à barreira.');
                }
                usleep(1000);
            }
        }
        return parent::beginTransaction();
    }
}
$pdo = new PdoDaFixture(""")
    codigo += """
final class ComandoDaFixture extends PDOStatement {
    protected function __construct() {}

    public function execute(?array $params = null): bool {
        try {
            return parent::execute($params);
        } catch (PDOException $erro) {
            if ((int)($erro->errorInfo[1] ?? 0) === 1062 && str_starts_with($this->queryString, 'INSERT INTO vendas ')) {
                $diretorio = dirname(__DIR__, 2);
                $rodada = file_get_contents($diretorio . '/rodada');
                file_put_contents($diretorio . '/duplicada-' . $rodada, "1062\\n", FILE_APPEND | LOCK_EX);
            }
            throw $erro;
        }
    }
}
$pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [ComandoDaFixture::class]);
"""
    db.write_text(codigo, encoding="utf-8")
    return APP


def aguardar_banco(servidor, cliente, dados, porta, ambiente, log):
    global AMBIENTE
    AMBIENTE = ambiente
    return aguardar_original(servidor, cliente, dados, porta, ambiente, log)


if __name__ == "__main__":
    preparar_original = fixture.preparar_app
    aguardar_original = fixture.wait_for_server
    fixture.preparar_app = preparar
    fixture.wait_for_server = aguardar_banco
    fixture.conferir_fluxo = conferir
    fixture.main()
