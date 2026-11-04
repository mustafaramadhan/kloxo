#!/bin/bash
# =============================================================================
# Mfklixo (Modern Kloxo-MR) Automated Installer
# Supports: Ubuntu, Debian, CentOS, RHEL, AlmaLinux, Rocky Linux
# =============================================================================

set -e

# ANSI Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}===============================================================================${NC}"
echo -e "${GREEN}                       Mfklixo Automated Installer                             ${NC}"
echo -e "${BLUE}===============================================================================${NC}"

# Check root privilege
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] Please run this installer as root (e.g. sudo bash install.sh)${NC}"
    exit 1
fi

# Detect Operating System
OS_FAMILY=""
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_NAME=$ID
    OS_LIKE=${ID_LIKE:-$ID}
else
    echo -e "${RED}[ERROR] Cannot detect operating system (/etc/os-release missing).${NC}"
    exit 1
fi

echo -e "${YELLOW}[*] Detected OS: ${PRETTY_NAME}${NC}"

# 1. Install Dependencies
echo -e "${YELLOW}[1/5] Installing necessary dependencies...${NC}"
if [[ "$OS_NAME" == "ubuntu" ]] || [[ "$OS_LIKE" =~ "debian" ]]; then
    OS_FAMILY="debian"
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -y
    apt-get install -y git curl wget zip unzip tar systemd openssl \
                       php-cli php-fpm php-mysql php-mbstring php-xml php-curl \
                       mariadb-server nginx apache2 nodejs npm
elif [[ "$OS_NAME" =~ "centos" ]] || [[ "$OS_NAME" =~ "rhel" ]] || [[ "$OS_NAME" =~ "almalinux" ]] || [[ "$OS_NAME" =~ "rocky" ]] || [[ "$OS_LIKE" =~ "rhel" ]]; then
    OS_FAMILY="rhel"
    yum update -y
    yum install -y epel-release yum-utils
    yum install -y git curl wget zip unzip tar systemd openssl \
                       php-cli php-fpm php-mysqlnd php-mbstring php-xml \
                       mariadb-server nginx httpd
else
    echo -e "${RED}[ERROR] Unsupported distribution: ${OS_NAME}${NC}"
    exit 1
fi

# 2. Setup System Users and Directories
INSTALL_DIR="/usr/local/lxlabs/kloxo"
echo -e "${YELLOW}[2/5] Setting up system user, symlinks, and directories...${NC}"

if ! id -u lxlabs >/dev/null 2>&1; then
    groupadd -r lxlabs 2>/dev/null || true
    useradd -r -g lxlabs -d "$INSTALL_DIR" -s /bin/false lxlabs 2>/dev/null || true
fi

mkdir -p /usr/local/lxlabs \
         "$INSTALL_DIR/pid" \
         "$INSTALL_DIR/log" \
         "$INSTALL_DIR/session" \
         "$INSTALL_DIR/etc/conf" \
         "$INSTALL_DIR/etc/slavedb" \
         "/home/kloxo/httpd" \
         "/var/cache/kloxo"

# Essential binary symlinks
ln -sf /usr/bin/php /usr/bin/lxphp.exe
ln -sf /usr/bin/php /usr/bin/lphp.exe

# 3. Setup Directory and Clone / Update Mfklixo
echo -e "${YELLOW}[3/5] Setting up Mfklixo in ${INSTALL_DIR}...${NC}"

# Allow git operations regardless of directory ownership
git config --global --add safe.directory "$INSTALL_DIR" 2>/dev/null || true
git config --global --add safe.directory "*" 2>/dev/null || true

if [ -d "$INSTALL_DIR/.git" ]; then
    echo -e "${GREEN}[*] Existing installation found, pulling latest changes...${NC}"
    cd "$INSTALL_DIR"
    git fetch origin modernize-kloxo
    git reset --hard origin/modernize-kloxo
else
    if [ -d "$INSTALL_DIR" ]; then
        mv "$INSTALL_DIR" "${INSTALL_DIR}.bak_$(date +%s)"
    fi
    git clone -b modernize-kloxo https://github.com/feizezadeh/Mfklixo.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
fi

# Synchronize the inner kloxo/ source tree into $INSTALL_DIR root
if [ -d "$INSTALL_DIR/kloxo" ]; then
    echo -e "${GREEN}[*] Syncing kloxo source files to installation directory...${NC}"
    cp -a "$INSTALL_DIR/kloxo/." "$INSTALL_DIR/"
fi

# Link /script to pscript
ln -sf "$INSTALL_DIR/pscript" /script

# 4. Configure Database & SSL & PHP-FPM
echo -e "${YELLOW}[4/5] Initializing Database, SSL, and Web Services...${NC}"

# Start & Enable MariaDB
systemctl daemon-reload
systemctl enable mariadb || systemctl enable mysql || true
systemctl restart mariadb || systemctl restart mysql || true

# Start & Enable PHP-FPM
PHP_FPM_SVC=$(systemctl list-unit-files | grep -E 'php[0-9.]*-fpm\.service|php-fpm\.service' | awk '{print $1}' | head -n 1)
if [ -n "$PHP_FPM_SVC" ]; then
    systemctl enable "$PHP_FPM_SVC" || true
    systemctl restart "$PHP_FPM_SVC" || true
fi

# Symlink PHP-FPM socket (match actual socket file, avoid circular symlink)
PHP_SOCK=$(find /run/php /var/run/php-fpm /run/php-fpm -maxdepth 1 -name "php*fpm*.sock" -type s 2>/dev/null | head -n 1)
if [ -z "$PHP_SOCK" ]; then
    PHP_SOCK=$(ls -1 /run/php/php*.sock /var/run/php-fpm/php-fpm.sock /run/php-fpm/www.sock 2>/dev/null | grep -v 'php-fpm.sock$' | head -n 1)
fi
if [ -n "$PHP_SOCK" ]; then
    mkdir -p "$INSTALL_DIR/init"
    ln -sf "$PHP_SOCK" "$INSTALL_DIR/init/kloxo-phpcgi.sock"
    if [ "$PHP_SOCK" != "/run/php/php-fpm.sock" ]; then
        ln -sf "$PHP_SOCK" /run/php/php-fpm.sock 2>/dev/null || true
    fi
fi

# Adjust Nginx user for OS family
if [[ "$OS_FAMILY" == "rhel" ]]; then
    sed -i 's/user www-data;/user nginx;/' "$INSTALL_DIR/init/kloxo-panel-nginx.conf" 2>/dev/null || true
fi

# Generate SSL certificate for panel if not present
if [ ! -f "$INSTALL_DIR/etc/program.pem" ]; then
    echo -e "${GREEN}[*] Generating SSL certificate for panel...${NC}"
    openssl req -new -x509 -days 3650 -nodes \
        -out "$INSTALL_DIR/etc/program.pem" \
        -keyout "$INSTALL_DIR/etc/program.pem" \
        -subj "/C=US/ST=State/L=City/O=Mfklixo/OU=Admin/CN=$(hostname)" 2>/dev/null || true
    chmod 600 "$INSTALL_DIR/etc/program.pem"
fi

# Setup internal Kloxo database credentials
if [ ! -f "$INSTALL_DIR/etc/conf/kloxo.pass" ]; then
    DB_PASS=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 16)
    echo "$DB_PASS" > "$INSTALL_DIR/etc/conf/kloxo.pass"
    chmod 644 "$INSTALL_DIR/etc/conf/kloxo.pass"
else
    DB_PASS=$(cat "$INSTALL_DIR/etc/conf/kloxo.pass")
    chmod 644 "$INSTALL_DIR/etc/conf/kloxo.pass"
fi

chmod 755 "$INSTALL_DIR/etc" "$INSTALL_DIR/etc/conf" "$INSTALL_DIR/etc/slavedb" 2>/dev/null || true

echo 'O:6:"Remote":1:{s:4:"data";a:1:{s:5:"mysql";a:1:{s:10:"dbpassword";s:0:"";}}}' > "$INSTALL_DIR/etc/slavedb/dbadmin"
echo 'O:6:"Remote":1:{s:4:"data";a:3:{s:3:"web";s:12:"apache_nginx";s:4:"spam";s:10:"bogofilter";s:3:"dns";s:4:"bind";s:4:"pop3";s:7:"courier";s:4:"smtp";s:5:"qmail";}}' > "$INSTALL_DIR/etc/slavedb/driver"

# Create database and grant access
mariadb -u root -e "
CREATE DATABASE IF NOT EXISTS kloxo;
CREATE USER IF NOT EXISTS 'kloxo'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER 'kloxo'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON kloxo.* TO 'kloxo'@'localhost';
FLUSH PRIVILEGES;
" 2>/dev/null || mysql -u root -e "
CREATE DATABASE IF NOT EXISTS kloxo;
CREATE USER IF NOT EXISTS 'kloxo'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER 'kloxo'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON kloxo.* TO 'kloxo'@'localhost';
FLUSH PRIVILEGES;
" 2>/dev/null || true

# Disable innodb strict mode temporarily to allow wide table structures
mariadb -u root -e "SET GLOBAL innodb_strict_mode=0;" 2>/dev/null || mysql -u root -e "SET GLOBAL innodb_strict_mode=0;" 2>/dev/null || true

# Import database structure if empty
TABLE_COUNT=$(mariadb -u root -N -e "SELECT count(*) FROM information_schema.tables WHERE table_schema='kloxo';" 2>/dev/null || mysql -u root -N -e "SELECT count(*) FROM information_schema.tables WHERE table_schema='kloxo';" 2>/dev/null || echo 0)
if [ "$TABLE_COUNT" -lt 5 ]; then
    echo -e "${GREEN}[*] Importing Kloxo schema and default admin data...${NC}"
    mariadb -u root kloxo < "$INSTALL_DIR/file/sql/db-structure-base.sql" 2>/dev/null || mysql -u root kloxo < "$INSTALL_DIR/file/sql/db-structure-base.sql" 2>/dev/null || true
    mariadb -u root kloxo < "$INSTALL_DIR/file/sql/db-structure-update.sql" 2>/dev/null || mysql -u root kloxo < "$INSTALL_DIR/file/sql/db-structure-update.sql" 2>/dev/null || true
    mariadb -u root kloxo < "$INSTALL_DIR/file/sql/admin.sql" 2>/dev/null || mysql -u root kloxo < "$INSTALL_DIR/file/sql/admin.sql" 2>/dev/null || true
    mariadb -u root kloxo < "$INSTALL_DIR/file/sql/pserver.sql" 2>/dev/null || mysql -u root kloxo < "$INSTALL_DIR/file/sql/pserver.sql" 2>/dev/null || true
    mariadb -u root kloxo < "$INSTALL_DIR/file/sql/mysql.servers.sql" 2>/dev/null || mysql -u root kloxo < "$INSTALL_DIR/file/sql/mysql.servers.sql" 2>/dev/null || true
fi

# Fix permissions
chown -R lxlabs:lxlabs "$INSTALL_DIR"
chown -R www-data:www-data "$INSTALL_DIR/httpdocs" "$INSTALL_DIR/log" "$INSTALL_DIR/session" "$INSTALL_DIR/pid" 2>/dev/null || chown -R nginx:nginx "$INSTALL_DIR/httpdocs" "$INSTALL_DIR/log" "$INSTALL_DIR/session" "$INSTALL_DIR/pid" 2>/dev/null || true
chmod -R 775 "$INSTALL_DIR/session" "$INSTALL_DIR/log" "$INSTALL_DIR/pid"

# Initialize system drivers and IP addresses
if [ -f "$INSTALL_DIR/bin/common/driverload.php" ]; then
    php -f "$INSTALL_DIR/bin/common/driverload.php" 2>/dev/null || true
fi
if [ -f "$INSTALL_DIR/bin/fixIpAddress.php" ]; then
    php -f "$INSTALL_DIR/bin/fixIpAddress.php" 2>/dev/null || true
fi

# 5. Setup Kloxo Services & Web Server
echo -e "${YELLOW}[5/5] Activating Control Panel and Web Services...${NC}"

# Install systemd service unit files
cp -f "$INSTALL_DIR/init/kloxo-web.service" /etc/systemd/system/kloxo-web.service
cp -f "$INSTALL_DIR/init/kloxo-php.service" /etc/systemd/system/kloxo-php.service
cp -f "$INSTALL_DIR/init/kloxo-wrap.service" /etc/systemd/system/kloxo-wrap.service
if [ -f "$INSTALL_DIR/init/kloxo-dashboard.service" ]; then
    cp -f "$INSTALL_DIR/init/kloxo-dashboard.service" /etc/systemd/system/kloxo-dashboard.service
fi
chmod 644 /etc/systemd/system/kloxo-*.service

# Build and enable Next.js Modern Dashboard
if [ -d "$INSTALL_DIR/frontend" ] && command -v npm >/dev/null 2>&1; then
    echo -e "${GREEN}[*] Building Next.js Modern Dashboard...${NC}"
    cd "$INSTALL_DIR/frontend"
    npm install --production=false
    npm run build
    systemctl daemon-reload
    systemctl enable kloxo-dashboard.service || true
    systemctl restart kloxo-dashboard.service || true
fi

systemctl daemon-reload
systemctl enable kloxo-php.service kloxo-web.service kloxo-wrap.service || true
systemctl restart kloxo-php.service || true
systemctl restart kloxo-web.service || true
systemctl restart kloxo-wrap.service || true

# Setup Apache + Nginx dual reverse proxy for hosted domains
if [ -f "$INSTALL_DIR/pscript/switchserver" ]; then
    bash "$INSTALL_DIR/pscript/switchserver" --class=web --driver=apache_nginx || true
fi

if [ -f "$INSTALL_DIR/bin/fix/fixweb.php" ]; then
    php "$INSTALL_DIR/bin/fix/fixweb.php" --target=defaults || true
fi

if [ -f "$INSTALL_DIR/pscript/restart-web" ]; then
    bash "$INSTALL_DIR/pscript/restart-web" -y || true
fi

# Detect Server IP (Prefer Public IP if on AWS/Cloud)
SERVER_IP=$(curl -s -m 3 https://ifconfig.me 2>/dev/null || curl -s -m 3 https://api.ipify.org 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}')
if [ -z "$SERVER_IP" ]; then
    SERVER_IP="<YOUR_SERVER_IP>"
fi

echo -e "${BLUE}===============================================================================${NC}"
echo -e "${GREEN}[SUCCESS] Mfklixo installation completed successfully!${NC}"
echo -e "${BLUE}===============================================================================${NC}"
echo -e "You can access your control panel at:"
echo -e "  HTTP:  ${GREEN}http://${SERVER_IP}:7778/${NC}"
echo -e "  HTTPS: ${GREEN}https://${SERVER_IP}:7777/${NC}"
echo
echo -e "Default Admin Credentials:"
echo -e "  Username: ${YELLOW}admin${NC}"
echo -e "  Password: ${YELLOW}admin${NC}"
echo
echo -e "Useful Commands:"
echo -e "  Service Status:         ${BLUE}systemctl status kloxo-web${NC}"
echo -e "  Select MariaDB version: ${BLUE}sh /usr/local/lxlabs/kloxo/pscript/mariadb-upgrade 10.11${NC}"
echo -e "  Switch PHP branch:      ${BLUE}sh /usr/local/lxlabs/kloxo/pscript/set-php-branch php82${NC}"
echo -e "  Restart Web:            ${BLUE}sh /usr/local/lxlabs/kloxo/pscript/restart-web -y${NC}"
echo -e "${BLUE}===============================================================================${NC}"
