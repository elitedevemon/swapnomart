#!/usr/bin/env python3
"""Debian/Ubuntu sandbox testing only. Not required by Laragon/cPanel.
--install: install a disposable preview runtime (sudo required).
--run: one foreground Supervisor manages MySQL and PHP (local preview only).
External runtime/data do NOT survive replacement of the sandbox. If lost,
installation seeds fictional demo data; it cannot recover lost customer data.
Never points at the production .env or imports into a nonempty database.
"""
import argparse, datetime, json, os, pathlib, re, shutil, signal, socket, subprocess, sys, time
ROOT = pathlib.Path(__file__).resolve().parents[1]
BASE = pathlib.Path('/opt/storefront-testing')
DATA = pathlib.Path('/var/lib/storefront-testing')
RUN = pathlib.Path('/run/storefront-testing')
LOG = pathlib.Path('/var/log/storefront-testing')
PRIVATE = DATA / 'private'
CONTROL = BASE / 'control'
ENV = PRIVATE / 'preview.env'
os.umask(0o077)

def audit(message):
    stamp = datetime.datetime.now(datetime.timezone(datetime.timedelta(hours=6))).isoformat(timespec='seconds')
    with (ROOT / 'working-flow.md').open('a') as f:
        f.write(f'- **Preview recovery {stamp}** {message}\n')

def write(path, text):
    path.parent.mkdir(parents=True, exist_ok=True)
    action = 'MODIFY' if path.exists() else 'CREATE'
    path.write_text(text)
    path.chmod(0o600)
    audit(f'{action} `{path}` — private runtime/configuration; no secrets copied into project.')

def install():
    # Do not reconfigure a running instance.
    if (RUN / 'supervisor.sock').exists():
        raise RuntimeError('Existing manager socket found. Inspect/stop that manager before reinstalling.')
    subprocess.run(['sudo','apt-get','update','-qq'], check=True)
    subprocess.run(['sudo','env','DEBIAN_FRONTEND=noninteractive','apt-get','install','-y','-qq',
                    'php-cli','php-mysql','php-mbstring','php-curl','php-gd','supervisor',
                    'libaio1t64','libnuma1','libncurses6','libtinfo6','curl','xz-utils'], check=True)
    # Do not start a second default Supervisor daemon.
    subprocess.run(['sudo','systemctl','disable','--now','supervisor.service'],check=True)
    for p in [BASE,DATA,RUN,LOG]:
        subprocess.run(['sudo','install','-d','-o',str(os.getuid()),'-g',str(os.getgid()),'-m','700',str(p)],check=True)
    for p in [PRIVATE, PRIVATE/'storage', CONTROL, BASE/'mysql-lib']:
        p.mkdir(parents=True,exist_ok=True)
    if not (BASE/'mysql/bin/mysqld').exists():
        archive = BASE/'mysql-install.tar.xz'
        subprocess.run(['curl','-fSL','--retry','2','--max-time','120',
                        'https://cdn.mysql.com/archives/mysql-8.4/mysql-8.4.6-linux-glibc2.28-x86_64-minimal.tar.xz','-o',str(archive)],check=True)
        subprocess.run(['tar','-xf',str(archive),'-C',str(BASE)],check=True)
        (BASE/'mysql-8.4.6-linux-glibc2.28-x86_64-minimal').rename(BASE/'mysql')
        archive.unlink()
        audit('INSTALL MySQL8.4.6 binaries outside workspace; DELETE temporary download archive.')
    lib = BASE/'mysql-lib/libaio.so.1'
    if not lib.exists(): lib.symlink_to('/usr/lib/x86_64-linux-gnu/libaio.so.1t64')
    if not (DATA/'mysql/mysql').is_dir():
        if (DATA/'mysql').exists() and any((DATA/'mysql').iterdir()):
            raise RuntimeError('Refusing to overwrite unrecognized nonempty MySQL data.')
        (DATA/'mysql').mkdir(exist_ok=True)
        subprocess.run([str(BASE/'mysql/bin/mysqld'),'--no-defaults','--initialize-insecure',
                        '--basedir='+str(BASE/'mysql'),'--datadir='+str(DATA/'mysql'),
                        '--innodb-redo-log-capacity=8388608'],check=True,
                       env={**os.environ,'LD_LIBRARY_PATH':str(BASE/'mysql-lib')})
        write(PRIVATE/'fresh-mysql','yes\n')
    if not ENV.exists():
        password = os.urandom(24).hex()
        write(ENV, f'APP_NAME="Lifestyle Store"\nAPP_ENV=local\nAPP_URL="http://localhost:8081"\n'
              f'APP_REQUIRE_HTTPS=false\nTRUST_PROXY_HEADERS=true\ndemo=true\n'
              f'DB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=storefront_preview\n'
              f'DB_USERNAME=storefront_preview\nDB_PASSWORD="{password}"\nAPP_STORAGE="{PRIVATE}/storage"\n')
    if not (PRIVATE/'root-password').exists():
        if not (PRIVATE/'fresh-mysql').exists():
            raise RuntimeError('Root credential missing for existing data: refusing unsafe recovery.')
        write(PRIVATE/'root-password',os.urandom(24).hex())
    write(CONTROL/'setup-db.php', '''<?php
if(PHP_SAPI!=='cli')exit(1);
$base='/var/lib/storefront-testing/private/';
$e=parse_ini_file($base.'preview.env',false,INI_SCANNER_RAW);
if($e['DB_DATABASE']!=='storefront_preview')throw new RuntimeException('Not the disposable preview database.');
$password=trim(file_get_contents($base.'root-password'));
$dsn='mysql:unix_socket=/run/storefront-testing/mysql.sock;charset=utf8mb4';
try{$db=new PDO($dsn,'root',$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}
catch(PDOException $error){if(!is_file($base.'fresh-mysql'))throw $error;$db=new PDO($dsn,'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}
$db->exec('CREATE DATABASE IF NOT EXISTS storefront_preview CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
foreach(['localhost','127.0.0.1']as $host){$who=$db->quote($e['DB_USERNAME']).'@'.$db->quote($host);$db->exec('CREATE USER IF NOT EXISTS '.$who.' IDENTIFIED BY '.$db->quote($e['DB_PASSWORD']));$db->exec('GRANT ALL ON storefront_preview.* TO '.$who);}
$db->exec('ALTER USER root@localhost IDENTIFIED BY '.$db->quote($password));
if(is_file($base.'fresh-mysql'))unlink($base.'fresh-mysql');
$count=(int)$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='storefront_preview'")->fetchColumn();
echo $count===0?'EMPTY':'EXISTING';
''')
    header = f'''[unix_http_server]
file={RUN}/supervisor.sock
chmod=0600
[supervisord]
nodaemon=true
logfile={LOG}/manager.log
logfile_maxbytes=1MB
logfile_backups=1
pidfile={RUN}/supervisor.pid
childlogdir={LOG}
umask=0077
[rpcinterface:supervisor]
supervisor.rpcinterface_factory=supervisor.rpcinterface:make_main_rpcinterface
[supervisorctl]
serverurl=unix://{RUN}/supervisor.sock
'''
    commands = {
        'mysql': f'{BASE}/mysql/bin/mysqld --no-defaults --basedir={BASE}/mysql --datadir={DATA}/mysql --socket={RUN}/mysql.sock --pid-file={RUN}/mysql.pid --bind-address=127.0.0.1 --port=3306 --mysqlx=OFF --innodb-redo-log-capacity=8388608 --skip-log-bin',
        'storefront': f'{sys.executable} -u {ROOT}/scripts/preview-recover.py --storefront',
    }
    for i,(name,command) in enumerate(commands.items()):
        header += f'''\n[program:{name}]
command={command}
directory={ROOT}
environment=LD_LIBRARY_PATH="{BASE}/mysql-lib",APP_ENV_FILE="{ENV}",APP_STORAGE="{PRIVATE}/storage"
priority={10+i*10}
autostart=true
autorestart=true
startsecs=5
startretries=10
stopasgroup=true
killasgroup=true
stopwaitsecs=30
redirect_stderr=true
stdout_logfile={LOG}/{name}.log
stdout_logfile_maxbytes=1MB
stdout_logfile_backups=1
'''
    write(CONTROL/'supervisord.conf',header)
    print('Installed. Run: python3 scripts/preview-recover.py --run',flush=True)

def storefront():
    # Bounded dependency readiness inside the managed service, not another server.
    ready = None
    for attempt in range(40):
        ready = subprocess.run(['php',str(CONTROL/'setup-db.php')],capture_output=True,text=True)
        if ready.returncode == 0: break
        time.sleep(1)
    if ready is None or ready.returncode:
        raise RuntimeError('Database setup failed; check private runtime logs/configuration.')
    if ready.stdout == 'EMPTY':
        subprocess.run(['php',str(ROOT/'scripts/install.php'),'--demo'],check=True)
        audit('SEED empty disposable preview DB with bundled fictional demo data; no prior live data recovered.')
    env=os.environ.copy();env.pop('PHP_CLI_SERVER_WORKERS',None)
    os.chdir(ROOT)
    os.execvpe('php',['php','-S','0.0.0.0:8081','-t','public','public/router.php'],env)

def public_link():
    import urllib.request
    for attempt in range(60):
        try:
            with urllib.request.urlopen('http://127.0.0.1:8081/api?action=catalog',timeout=3) as r:
                assert r.status==200
            break
        except Exception: time.sleep(1)
    else: raise RuntimeError('Storefront not ready for public tunnel.')
    state=CONTROL/'public-url.json'
    if state.exists():state.unlink()
    # Native quick tunnel; no replay server, extra HTTP credential endpoint or stale hostname reuse.
    child=subprocess.Popen([str(BASE/'cloudflared'),'tunnel','--url','http://127.0.0.1:8081',
                            '--protocol','http2','--edge-ip-version','4','--no-autoupdate'],
                           stdout=subprocess.PIPE,stderr=subprocess.STDOUT,text=True)
    def stop(signum,frame):
        child.terminate()
    signal.signal(signal.SIGTERM,stop)
    for line in child.stdout:
        print(line.rstrip(),flush=True)
        m=re.search(r'https://[a-z0-9-]+\.trycloudflare\.com',line)
        if m:
            url=m.group(0)
            text=re.sub(r'^APP_URL=.*$',f'APP_URL="{url}"',ENV.read_text(),flags=re.M)
            tmp=ENV.with_suffix('.new');tmp.write_text(text);tmp.chmod(0o600);tmp.replace(ENV)
            write(state,json.dumps({'url':url,'allocated_at':datetime.datetime.now(datetime.timezone.utc).isoformat()}))
            audit('ALLOCATE public testing URL '+url+'; update external APP_URL. Allocation alone is not a health check.')
    sys.exit(child.wait())

if __name__=='__main__':
    p=argparse.ArgumentParser(description=__doc__)
    g=p.add_mutually_exclusive_group(required=True)
    for option in ['install','run','storefront','public-link']: g.add_argument('--'+option,action='store_true')
    a=p.parse_args()
    if a.install:install()
    elif a.run:
        if (RUN/'supervisor.sock').exists():raise SystemExit('Manager socket already exists; inspect existing manager instead of starting duplicates.')
        os.execvp('supervisord',['supervisord','-n','-c',str(CONTROL/'supervisord.conf')])
    elif a.storefront:storefront()
    else:public_link()
