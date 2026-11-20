"""Exercita o PDV por HTTP com MariaDB descartável no XAMPP."""

from http.client import HTTPConnection
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import socket
import subprocess
import tempfile
import time
from urllib.parse import urlencode

from run_pdv_fefo import MYSQL_BIN, PHP, WORKSPACE, run, wait_for_server


def porta_livre():
    with socket.socket() as probe:
        probe.bind(("127.0.0.1", 0))
        return probe.getsockname()[1]


def requisitar(porta, caminho, cookie="", post=None, aceita_json=True):
    conn = HTTPConnection("127.0.0.1", porta, timeout=10)
    headers = {"Cookie": cookie} if cookie else {}
    if post is not None:
        headers["Content-Type"] = "application/x-www-form-urlencoded"
        if aceita_json:
            headers["Accept"] = "application/json"
    try:
        conn.request("POST" if post is not None else "GET", caminho,
                     urlencode(post) if post is not None else None, headers)
        response = conn.getresponse()
        return response.status, response.headers, response.read().decode("utf-8")
    finally:
        conn.close()


def chave_pdv(html):
    return re.search(r'name="chave_envio" value="([a-f0-9]{32})"', html).group(1)


def preparar_app(pasta, porta, datadir):
    app = pasta / "app"
    for arquivo in (
        "inc/auth.php", "inc/functions.php", "inc/header.php", "inc/footer.php", "inc/cookie_banner.php", "inc/venda_envio.php",
        "vendas/functions.php", "vendas/pdv.php",
    ):
        destino = app / arquivo
        destino.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(WORKSPACE / arquivo, destino)
    diretorio = str(datadir).replace("\\", "/")
    (app / "inc/database.php").write_text(f"""<?php
define('BASE_URL', '');
require_once __DIR__ . '/functions.php';
$pdo = new PDO('mysql:host=127.0.0.1;port={porta};dbname=pdv_margem;charset=utf8mb4',
    'root', getenv('PDV_MARGEM_TEST_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
$identidade = $pdo->query('SELECT @@datadir, @@port, DATABASE()')->fetch(PDO::FETCH_NUM);
if (realpath($identidade[0]) !== realpath('{diretorio}') || (int)$identidade[1] !== {porta}
    || $identidade[2] !== 'pdv_margem') {{
    throw new RuntimeException('A conexão não pertence ao banco descartável.');
}}
""", encoding="utf-8")
    (app / "sessao.php").write_text("""<?php
require __DIR__ . '/inc/functions.php';
$_SESSION = ['user_id' => 1, 'user_perfil' => 'caixa'];
echo csrf_token();
""", encoding="utf-8")
    return app


def conferir_fluxo(porta, sql):
    status, headers, csrf = requisitar(porta, "/sessao.php")
    assert status == 200 and re.fullmatch(r"[a-f0-9]{64}", csrf)
    cookie = headers["Set-Cookie"].split(";", 1)[0]
    rota = "/vendas/functions.php?tipo=venda"
    payload = {"csrf_token": csrf, "acao": "venda_completa", "forma_pagamento": "PIX",
               "cart_data": '[{"id":1,"quantidade":2}]', "desconto": 0}
    estado = (
        "SELECT (SELECT COUNT(*) FROM vendas), (SELECT COUNT(*) FROM vendas_itens),"
        " (SELECT COUNT(*) FROM movimentacoes), (SELECT COUNT(*) FROM cupons_fiscais),"
        " (SELECT quantidade FROM produtos WHERE id=1), (SELECT quantidade FROM lotes WHERE id=1),"
        " (SELECT COUNT(*) FROM logs WHERE acao='MARGEM_CONFIRMADA_PDV');"
    )
    antes = "0\t0\t0\t0\t10\t10\t0"
    depois = "1\t1\t1\t1\t8\t8\t1"
    status, _, html = requisitar(porta, "/vendas/pdv.php?erro=estoque&produto=" +
                                  urlencode({"": "<MARCADOR-FIXTURE>"}).split("=", 1)[1], cookie)
    assert status == 200 and "&lt;MARCADOR-FIXTURE&gt;" in html and "<MARCADOR-FIXTURE>" not in html
    payload["chave_envio"] = chave_pdv(html)
    catalogo = json.loads(re.search(r"^const catalogoProdutos = (.+);$", html, re.M).group(1))
    assert len(catalogo) == 1 and "preco_compra" not in catalogo[0] and float(catalogo[0]["preco"]) == 10
    status, _, _ = requisitar(porta, rota, cookie, {**payload, "csrf_token": ""})
    assert status == 403 and sql(estado) == antes

    sql("UPDATE configuracoes SET valor='bloquear' WHERE chave='pdv_trava_margem';")
    status, headers, mensagem = requisitar(porta, rota, cookie, payload)
    assert status == 422 and not headers.get("Location")
    assert json.loads(mensagem) == {"status": "erro", "erro": "margem_negativa"} and sql(estado) == antes
    status, headers, _ = requisitar(porta, rota, cookie, payload, aceita_json=False)
    assert status == 302 and headers["Location"] == "/vendas/pdv.php?erro=margem_negativa"
    assert sql(estado) == antes
    sql("UPDATE configuracoes SET valor='aviso' WHERE chave='pdv_trava_margem';")

    def pedir_aviso(post):
        status, headers, resposta = requisitar(porta, rota, cookie, post)
        aviso = json.loads(resposta)
        assert status == 409 and not headers.get("Location")
        assert set(aviso) == {"status", "token"} and aviso["status"] == "margem_aviso"
        assert re.fullmatch(r"[a-f0-9]{64}", aviso["token"])
        return aviso["token"]

    token = pedir_aviso(payload)
    assert sql(estado) == antes
    sql("UPDATE produtos SET preco_compra=12.00 WHERE id=1;")
    novo_token = pedir_aviso({**payload, "confirmacao_margem": token})
    assert novo_token != token and sql(estado) == antes
    status, _, mensagem = requisitar(porta, rota, cookie, {**payload, "confirmacao_margem": novo_token})
    assert status == 200 and json.loads(mensagem) == {"status": "ok", "redirect": "/vendas/cupom.php?venda_id=1"}
    assert sql(estado) == depois
    assert sql("SELECT COUNT(*) FROM logs WHERE acao='MARGEM_CONFIRMADA_PDV' AND descricao LIKE '%24.00%20.00%';") == "1"
    status, _, repeticao = requisitar(porta, rota, cookie, {**payload, "confirmacao_margem": novo_token})
    assert status == 409 and json.loads(repeticao) == {
        "status": "margem_ja_confirmada", "redirect": "/vendas/cupom.php?venda_id=1",
    }
    assert sql(estado) == depois
    print("PDV: catálogo sem custo, texto escapado, CSRF e bloqueio sem gravação.")
    print("Margem: aviso sem baixa, custo alterado exige novo aceite, confirmação baixa produto e lote uma vez.")
    print("Auditoria gravada, redirecionamento ao cupom e repetição sem efeito comercial.")
    sql("UPDATE produtos SET preco_venda=1.20, preco_compra=1.10 WHERE id=1;")
    _, _, outra_tela = requisitar(porta, "/vendas/pdv.php", cookie)
    venda_sem_margem_negativa = {**payload, "cart_data": '[{"id":1,"quantidade":1}]', "desconto": "0.10",
                                "chave_envio": chave_pdv(outra_tela)}
    status, _, resposta = requisitar(porta, rota, cookie, venda_sem_margem_negativa)
    assert status == 200 and json.loads(resposta)["status"] == "ok", "Igualdade em centavos pediu confirmação."
    assert sql("SELECT total FROM vendas WHERE id=2;") == "1.10"
    assert sql(estado) == "2\t2\t2\t2\t7\t7\t1"
    print("R$ 1,20 menos R$ 0,10 igual a custo de R$ 1,10: venda gravada sem aviso indevido.")


def main():
    ddl = (WORKSPACE / "database/schema_only.sql").read_text(encoding="utf-8-sig")
    tabelas = re.findall(r"^CREATE TABLE IF NOT EXISTS `\w+` \(.*?^\) ENGINE=InnoDB[^;]*;",
                         ddl, re.M | re.S)
    assert len(tabelas) == 15
    migration = (WORKSPACE / "database/migrations/001_vendas_chave_envio.sql").read_text(encoding="utf-8")
    with tempfile.TemporaryDirectory(prefix="mrstock-margem-") as temporario:
        pasta = Path(temporario)
        datadir = pasta / "data"
        log = pasta / "mariadb.log"
        porta = porta_livre()
        senha = secrets.token_hex(24)
        env = {**os.environ, "MYSQL_PWD": senha, "PDV_MARGEM_TEST_PASSWORD": senha}
        run([MYSQL_BIN / "mysql_install_db.exe", f"--datadir={datadir}", f"--password={senha}"],
            label="Criar banco descartável", timeout=45)
        servidor = subprocess.Popen(
            [MYSQL_BIN / "mysqld.exe", "--no-defaults", f"--basedir={MYSQL_BIN.parent}",
             f"--datadir={datadir}", "--bind-address=127.0.0.1", f"--port={porta}",
             f"--pid-file={pasta / 'server.pid'}", f"--log-error={log}"],
            cwd=pasta, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
        )
        try:
            cliente = [MYSQL_BIN / "mysql.exe", "--no-defaults", "--protocol=TCP", "-h", "127.0.0.1",
                       "-P", str(porta), "-u", "root", "-N", "--raw"]
            versao = wait_for_server(servidor, cliente, datadir, porta, env, log)
            fixture = (
                "CREATE DATABASE pdv_margem; USE pdv_margem; SET FOREIGN_KEY_CHECKS=0;\n"
                + "\n".join(tabelas)
                + "\nSET FOREIGN_KEY_CHECKS=1;"
                + "\n" + migration + "\n"
                + "INSERT INTO usuarios (id,username,password,perfil) VALUES (1,'caixa_fixture','sem-login','caixa');"
                + "INSERT INTO produtos (id,nome,quantidade,preco_venda,preco_compra) VALUES (1,'Caneta fictícia',10,10,11);"
                + "INSERT INTO lotes (id,produto_id,numero_lote,data_validade,quantidade)"
                  " VALUES (1,1,'L1',DATE_ADD(CURDATE(), INTERVAL 10 DAY),10);"
                + "INSERT INTO configuracoes (chave,valor) VALUES ('pdv_trava_margem','aviso');"
            )
            run(cliente, input=fixture, env=env, label="Importar schema e fixture")

            def sql(comando):
                return run([*cliente, "pdv_margem"], input=comando, env=env, label="Conferir estado").stdout.strip()

            app = preparar_app(pasta, porta, datadir)
            porta_http = porta_livre()
            php = subprocess.Popen(
                [PHP, "-d", f"session.save_path={pasta}", "-S", f"127.0.0.1:{porta_http}", "-t", app],
                cwd=pasta, env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
                creationflags=subprocess.CREATE_NO_WINDOW,
            )
            try:
                for _ in range(20):
                    try:
                        with socket.create_connection(("127.0.0.1", porta_http), timeout=0.5):
                            break
                    except OSError:
                        time.sleep(0.15)
                else:
                    raise RuntimeError("O servidor PHP da fixture não abriu a porta local.")
                print(f"Ambiente: {versao}, InnoDB descartável e PHP HTTP em loopback.")
                conferir_fluxo(porta_http, sql)
            finally:
                php.terminate()
                php.wait(timeout=10)
        finally:
            servidor.terminate()
            servidor.wait(timeout=10)


if __name__ == "__main__":
    main()
