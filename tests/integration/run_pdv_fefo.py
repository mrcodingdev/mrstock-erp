"""Execute com Python no Windows e PHP/MariaDB do XAMPP em C:/xampp."""

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


WORKSPACE = Path(__file__).resolve().parents[2]
MYSQL_BIN = Path("C:/xampp/mysql/bin")
PHP = Path("C:/xampp/php/php.exe")
LOT_ORDER = b"ORDER BY data_validade ASC, id ASC"
VARIANTS = (
    ("fefo_base", LOT_ORDER, 0, {"10": 5, "90": 0, "91": 3}),
    ("fefo_validade", b"ORDER BY data_validade DESC, id ASC", 1, {"10": 2, "90": 2, "91": 4}),
    ("fefo_empate", b"ORDER BY data_validade ASC, id DESC", 1, {"10": 5, "90": 2, "91": 1}),
)


def run(command, *, label, expected=0, timeout=30, **options):
    try:
        result = subprocess.run(
            command, capture_output=True, text=True, encoding="utf-8",
            errors="replace", timeout=timeout, creationflags=subprocess.CREATE_NO_WINDOW,
            **options,
        )
    except subprocess.TimeoutExpired:
        raise RuntimeError(f"{label} excedeu {timeout} segundos.") from None
    if expected is not None and result.returncode != expected:
        raise RuntimeError(f"{label} retornou {result.returncode}, esperado {expected}. {result.stderr.strip()}")
    return result


def wait_for_server(server, client, data, port, env, log_path):
    deadline = time.monotonic() + 20
    while time.monotonic() < deadline:
        if server.poll() is not None:
            logs = log_path.read_text(errors="replace")[-2000:] if log_path.exists() else ""
            raise RuntimeError(f"O MariaDB descartável encerrou durante a inicialização. {logs}")
        probe = run(
            [*client, "-N", "--raw", "-e", "SELECT @@port, @@datadir, VERSION()"],
            label="Confirmar instância", expected=None, timeout=5, env=env,
        )
        if probe.returncode == 0:
            actual_port, actual_directory, version = probe.stdout.strip().split("\t")
            if actual_port != str(port) or not Path(actual_directory).samefile(data):
                raise RuntimeError("A conexão não pertence à instância descartável.")
            return version
        time.sleep(0.2)
    raise RuntimeError("O MariaDB descartável não respondeu.")


def run_variants(temp, ddl, client, env, port):
    for relative in ("vendas/functions.php", "inc/auth.php", "inc/functions.php"):
        target = temp / "app" / relative
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(WORKSPACE / relative, target)
    (temp / "app/inc/database.php").write_text("<?php // PDO da fixture.\n", encoding="utf-8")
    runner = temp / "pdv_fefo_innodb_test.php"
    shutil.copyfile(Path(__file__).with_name(runner.name), runner)
    controller = temp / "app/vendas/functions.php"
    source = controller.read_bytes()
    if source.count(LOT_ORDER) != 1:
        raise RuntimeError("Ponto de mutação FEFO ausente ou ambíguo. Revise a prova.")

    reports = []
    for schema, order, expected_code, expected_lots in VARIANTS:
        sql = f"CREATE DATABASE `{schema}`; USE `{schema}`; SET FOREIGN_KEY_CHECKS=0;\n"
        run(client, label=f"Criar {schema}", input=sql + ddl + "\nSET FOREIGN_KEY_CHECKS=1;", env=env)
        controller.write_bytes(source.replace(LOT_ORDER, order, 1))
        for stage in ("seed", "sale"):
            execution = run([PHP, runner, stage, str(port), schema], label=f"{schema}/{stage}", env=env)
            if execution.stderr.strip() or execution.stdout.strip():
                raise RuntimeError(f"Saída inesperada em {schema}/{stage}: {execution.stderr.strip()}")
        result = run(
            [PHP, runner, "assert", str(port), schema], label=f"{schema}/assert",
            expected=expected_code, env=env,
        )
        report = json.loads(result.stdout)
        if report["rule"] != "saldo_por_lote" or report["actual"] != expected_lots:
            raise RuntimeError(f"{schema} divergiu do saldo previsto na verificação independente.")
        reports.append({"scenario": schema, "assertion_exit": result.returncode, **report})
    return reports


def main():
    # Importa somente os CREATE TABLE do schema estrutural.
    schema = (WORKSPACE / "database/schema_only.sql").read_text(encoding="utf-8-sig")
    statements = re.findall(
        r"^CREATE TABLE IF NOT EXISTS `\w+` \(.*?^\) ENGINE=InnoDB[^;]*;",
        schema, re.MULTILINE | re.DOTALL,
    )
    if len(statements) != 15 or any(statement.count(";") != 1 for statement in statements):
        raise RuntimeError("Esperava 15 CREATE TABLE InnoDB em database/schema_only.sql.")

    with tempfile.TemporaryDirectory(prefix="mrstock-fefo-") as directory:
        temp = Path(directory)
        data = temp / "data"
        log_path = temp / "mariadb.log"
        with socket.socket() as probe:
            probe.bind(("127.0.0.1", 0))
            port = probe.getsockname()[1]
        password = secrets.token_hex(24)
        env = {**os.environ, "MYSQL_PWD": password, "PDV_FEFO_TEST_PASSWORD": password}
        run(
            [MYSQL_BIN / "mysql_install_db.exe", f"--datadir={data}", f"--password={password}"],
            label="Inicializar MariaDB descartável", timeout=45,
        )
        server = subprocess.Popen(
            [MYSQL_BIN / "mysqld.exe", "--no-defaults", f"--basedir={MYSQL_BIN.parent}",
             f"--datadir={data}", "--bind-address=127.0.0.1", f"--port={port}",
             f"--pid-file={temp / 'server.pid'}", f"--log-error={log_path}"],
            cwd=temp, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
            creationflags=subprocess.CREATE_NO_WINDOW,
        )
        try:
            client = [MYSQL_BIN / "mysql.exe", "--no-defaults", "--protocol=TCP", "-h", "127.0.0.1",
                      "-P", str(port), "-u", "root"]
            version = wait_for_server(server, client, data, port, env, log_path)
            reports = run_variants(temp, "\n".join(statements), client, env, port)
        finally:
            if server.poll() is None:
                server.terminate()
            server.wait(timeout=10)
    print(json.dumps({"mariadb": version, "scenarios": reports}, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
