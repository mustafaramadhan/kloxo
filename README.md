# Mfklixo (Modern Kloxo-MR)

![Kloxo-MR logo](kloxo-mr_big.png)

> **Mfklixo** is an upgraded, modernized, and bug-fixed edition of **Kloxo-MR** (a feature-rich fork of Kloxo 6.1.12+). This edition brings full compatibility with **modern PHP 8.x**, adds support for **Ubuntu & systemd**, modernizes the web UI, and introduces selectable versions for **MariaDB (up to 11.4 LTS)** and **PHP (up to 8.5)**.

---

## 🚀 Key Improvements & Modern Enhancements

### 1. Modern PHP 8.x Full Compatibility
- Resolved **all 49+ syntax and runtime fatal errors** caused by legacy PHP 5.x constructs.
- Removed deprecated `__autoload()` in favor of standard `spl_autoload_register`.
- Replaced deprecated curly brace string/array indexing (`$str{0}` ➔ `$str[0]`).
- Fixed invalid dynamic property syntax and references (`$this->__sortby_{$class}` ➔ `$this->$__tvar`).
- Removed deprecated reference assignments (`=& new` ➔ `= new`).
- Updated PEAR / WebDAV magic method signatures for strict PHP 8 compatibility (`PEAR_Autoloader::__call($method, $args)`).

### 2. Multi-OS Support & Ubuntu / Debian Integration
- Added automatic OS detection via standard `/etc/os-release` (supporting **Ubuntu 20.04 / 22.04 / 24.04 / 26.04+** and **Debian** alongside RHEL/CentOS).
- Integrated native **`systemctl`** service management for modern systemd-based Linux distributions.
- Added dedicated driver configurations for Debian/Ubuntu (`file/driver/ubuntu.inc`).
- Supported both `apache2` (Debian/Ubuntu) and `httpd` (RHEL/CentOS) web service names seamlessly.

### 3. Web Servers & Reverse Proxy Drivers
- **Supported Web Servers:**
  - **Nginx** (standalone high-performance engine)
  - **Apache (httpd / apache2)**
  - **Apache + Nginx Dual Proxy (`apache_nginx`)**: Nginx as reverse proxy front-end handling SSL & static assets with Apache as back-end.
  - **Lighttpd**, **Hiawatha**, and proxy variants.
- Configurable directly from the panel or via command line.

### 4. Database: MariaDB & MySQL Version Selector
- Added support for selectable MariaDB versions:
  - **MariaDB 10.4, 10.5, 10.6, 10.11 (LTS), and 11.4 (LTS)**.
  - MySQL 5.7, 8.0, and 8.4 LTS.
- Included unified upgrade/switch script:
  ```bash
  sh /script/mariadb-upgrade 10.11
  # or
  sh /script/mariadb-upgrade 11.4
  ```

### 5. Multi-PHP Branch Selection
- Support for modern PHP branches from PHP 5.6 up to **PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, and 8.5**.
- Switch PHP branches with:
  ```bash
  sh /script/set-php-branch php82
  ```

### 6. Modernized Web UI
- Redesigned login and authentication pages using **CSS Custom Properties (CSS variables)**, **Flexbox responsive layout**, and modern dark-mode aesthetic.
- Enhanced accessibility with standard form labels, autocomplete attributes, and client-side validation.

---

## 🛠️ Requirements & Supported Platforms

| Component | Supported Targets |
| :--- | :--- |
| **Operating Systems** | Ubuntu 20.04+, Debian 11+, AlmaLinux / Rocky Linux / CentOS 7/8/9 |
| **PHP Runtime** | PHP 7.4 to PHP 8.5+ |
| **Init System** | systemd (preferred), SysVinit / update-rc.d |
| **Database** | MariaDB 10.4+ / 11.x LTS, MySQL 5.7 / 8.x |

---

## 📦 Getting Started & Commands

### Switch Web Server
```bash
# Switch to Apache + Nginx reverse proxy
sh /script/switchserver --class=web --driver=apache_nginx

# Switch to Nginx standalone
sh /script/switchserver --class=web --driver=nginx

# Switch to Apache standalone
sh /script/switchserver --class=web --driver=apache
```

### Upgrade / Select MariaDB Version
```bash
sh /script/mariadb-upgrade 10.11
```

### Fix & Restart Web Services
```bash
sh /script/fixweb
sh /script/restart-web -y
```

---

## 📄 License

This project is licensed under the **GNU Affero General Public License v3.0 (AGPLv3)**. See [LICENSE.txt](LICENSE.txt) for details.
