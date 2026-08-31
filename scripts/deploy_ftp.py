import os
import ftplib
import sys

FTP_HOST = "ftpupload.net"
FTP_USER = "root"
FTP_PASS = ""
LOCAL_DIR = r"C:\xampp\htdocs\MrStock"

IGNORE_DIRS = {
    '.git', '.vscode', '.idea', 'node_modules', 'tests', '__pycache__', 
    'scripts', '.agents', '.gemini', 'docs', 'docs_pdf', 'QA_Logs'
}
IGNORE_EXTS = {'.log', '.tmp', '.bak', '.py', '.md', '.zip', '.pdf'}
IGNORE_FILES = {'upload_to_profreehost.py', 'deploy_ftp.py', '.env', 'composer.json', 'composer.lock'}

def sync_folder(ftp, local_dir, remote_rel_path=""):
    items = sorted(os.listdir(local_dir))
    
    # 1. Envia primeiro todos os arquivos deste diretório
    for item in items:
        if item in IGNORE_DIRS or item in IGNORE_FILES:
            continue
        
        local_path = os.path.join(local_dir, item)
        if os.path.isfile(local_path):
            _, ext = os.path.splitext(item)
            if ext.lower() in IGNORE_EXTS and item != '.htaccess':
                continue
            
            print(f"  [UPLOAD] {os.path.join(remote_rel_path, item)}...", end="", flush=True)
            try:
                with open(local_path, 'rb') as f:
                    ftp.storbinary(f"STOR {item}", f)
                print(" [OK]")
            except Exception as e:
                print(f" [ERRO: {e}]")
    
    # 2. Processa recursivamente os subdiretórios
    for item in items:
        if item in IGNORE_DIRS or item in IGNORE_FILES:
            continue
        
        local_path = os.path.join(local_dir, item)
        if os.path.isdir(local_path):
            new_remote_rel = f"{remote_rel_path}/{item}".strip('/')
            
            # Tenta entrar ou criar o diretório remoto
            try:
                ftp.cwd(item)
            except ftplib.error_perm:
                try:
                    ftp.mkd(item)
                    print(f"  [MKDIR] Criado diretorio: {new_remote_rel}")
                    ftp.cwd(item)
                except Exception as e:
                    print(f"  [AVISO] Erro ao entrar/criar {item}: {e}")
                    continue
            
            sync_folder(ftp, local_path, new_remote_rel)
            ftp.cwd("..")

def main():
    print("=" * 60)
    print("MRSTOCK ERP - DEPLOY AUTOMATICO PARA PROFEEHOST (UNAUX)")
    print("=" * 60)
    print(f"Conectando a {FTP_HOST} com usuario {FTP_USER}...")
    
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=45)
        ftp.login(FTP_USER, FTP_PASS)
        print("[OK] Conexao FTP autenticada com sucesso!")
        
        ftp.cwd("htdocs")
        print("[OK] Diretorio base remoto: /htdocs/")
        
        sync_folder(ftp, LOCAL_DIR, "")
        
        ftp.quit()
        print("\n" + "=" * 60)
        print("DEPLOY CONCLUIDO COM SUCESSO NO MRSTOCK.UNAUX.COM!")
        print("=" * 60)
    except Exception as e:
        print(f"\n[ERRO] Falha durante deploy FTP: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()
