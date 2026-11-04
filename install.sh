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
echo -e "${YELLOW}[1/4] Installing necessary dependencies...${NC}"
if [[ "$OS_NAME" == "ubuntu" ]] || [[ "$OS_LIKE" =~ "debian" ]]; then
    OS_FAMILY="debian"
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -y
    apt-get install -y git curl wget zip unzip tar systemd \
                       php-cli php-fpm php-mysql php-mbstring php-xml php-curl \
                       mariadb-server nginx apache2
elif [[ "$OS_NAME" =~ "centos" ]] || [[ "$OS_NAME" =~ "rhel" ]] || [[ "$OS_NAME" =~ "almalinux" ]] || [[ "$OS_NAME" =~ "rocky" ]] || [[ "$OS_LIKE" =~ "rhel" ]]; then
    OS_FAMILY="rhel"
    yum update -y
    yum install -y epel-release yum-utils
    yum install -y git curl wget zip unzip tar systemd \
                       mariadb-server nginx httpd
else
    echo -e "${RED}[ERROR] Unsupported distribution: ${OS_NAME}${NC}"
    exit 1
fi

# 2. Setup Directory and Clone Mfklixo
INSTALL_DIR="/usr/local/lxlabs/kloxo"
echo -e "${YELLOW}[2/4] Setting up Mfklixo in ${INSTALL_DIR}...${NC}"
mkdir -p /usr/local/lxlabs

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

# 3. Configure Database & Web Services
echo -e "${YELLOW}[3/4] Initializing Database and Web Services...${NC}"

# Start & Enable MariaDB
systemctl daemon-reload
systemctl enable mariadb || systemctl enable mysql || true
systemctl restart mariadb || systemctl restart mysql || true

# Setup default Apache + Nginx dual reverse proxy
echo -e "${GREEN}[*] Configuring Apache + Nginx dual web server...${NC}"
if [ -f "$INSTALL_DIR/pscript/switchserver" ]; then
    bash "$INSTALL_DIR/pscript/switchserver" --class=web --driver=apache_nginx || true
fi

# Generate configurations
if [ -f "$INSTALL_DIR/bin/fix/fixweb.php" ]; then
    php "$INSTALL_DIR/bin/fix/fixweb.php" --target=defaults || true
fi

# Enable & restart services
if [ -f "$INSTALL_DIR/pscript/restart-web" ]; then
    bash "$INSTALL_DIR/pscript/restart-web" -y || true
fi

# 4. Final summary
echo -e "${BLUE}===============================================================================${NC}"
echo -e "${GREEN}[SUCCESS] Mfklixo installation completed successfully!${NC}"
echo -e "${BLUE}===============================================================================${NC}"

SERVER_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
if [ -z "$SERVER_IP" ]; then
    SERVER_IP="<YOUR_SERVER_IP>"
fi

echo -e "You can access your control panel at:"
echo -e "  HTTP:  ${GREEN}http://${SERVER_IP}:7778/${NC}"
echo -e "  HTTPS: ${GREEN}https://${SERVER_IP}:7777/${NC}"
echo
echo -e "Default Admin Credentials:"
echo -e "  Username: ${YELLOW}admin${NC}"
echo -e "  Password: ${YELLOW}admin${NC} (or lxlabs depending on installation stage)"
echo
echo -e "Useful Commands:"
echo -e "  Select MariaDB version: ${BLUE}sh /usr/local/lxlabs/kloxo/pscript/mariadb-upgrade 10.11${NC}"
echo -e "  Switch PHP branch:      ${BLUE}sh /usr/local/lxlabs/kloxo/pscript/set-php-branch php82${NC}"
echo -e "  Restart Web:            ${BLUE}sh /usr/local/lxlabs/kloxo/pscript/restart-web -y${NC}"
echo -e "${BLUE}===============================================================================${NC}"
