# SAMI-webshop

WordPress & WooCommerce webshop and reservation system for [84kitchen-kempten.de](https://84kitchen-kempten.de).

Includes custom plugins (`2-step-webshop`, `an-table-reservation-manager`), theme customizations, and universal deployment/backup automation for any Linux server environment.

---

## 🚀 Quick Start Deployment

### Deploy with Docker Compose (Recommended)
Works seamlessly on any Linux distribution (Ubuntu, Debian, AlmaLinux, CentOS, Arch, etc.):

```bash
# 1. Clone repository
git clone https://github.com/your-org/SAMI-webshop.git
cd SAMI-webshop

# 2. Copy and customize environment variables
cp .env.example .env
nano .env

# 3. Deploy
./deploy.sh --docker
```

The stack includes:
- **Nginx**: High-performance web server with WordPress rewrite rules and security headers.
- **PHP 8.4-FPM**: Pre-configured with all required extensions (`mysqli`, `gd`, `zip`, `opcache`, `intl`, `exif`, `bcmath`, `imagick`) and WP-CLI.
- **MariaDB 11.4**: Optimized database with persistent volume.
- **phpMyAdmin** *(optional)*: Database admin interface (run with `--profile tools`).

---

## 📦 Backup & Migration

### 1. Create a Backup
Captures the complete database dump and all `wp-content/` files into a timestamped compressed archive:

```bash
# Full backup (Database + Files)
./backup.sh

# Database only
./backup.sh --db-only

# Files only
./backup.sh --files-only
```
Backups are saved to `./backups/sami_backup_<type>_<timestamp>.tar.gz`.

### 2. Restore or Migrate to Another Server
```bash
# Transfer archive to destination server
scp backups/sami_backup_all_*.tar.gz user@destination:/path/to/SAMI-webshop/backups/

# Run restore on destination server with your target domain URL
./restore.sh --new-url https://84kitchen-kempten.de
```

The restore script automatically:
1. Restores `wp-content/uploads`, themes, and plugins.
2. Imports the database dump into MariaDB/MySQL.
3. Performs safe URL search-and-replace across all database tables (handling PHP serialized data).
4. Configures correct Linux file permissions.

---

## 🛠 Project Structure

```
├── .env.example             # Environment template (Database, Ports, URLs, Salts)
├── docker-compose.yml       # Production Docker stack (Nginx, PHP 8.4, MariaDB)
├── docker/
│   ├── nginx/default.conf   # Optimized Nginx config for WordPress
│   └── php/                 # PHP 8.4 Dockerfile & custom.ini
├── scripts/
│   ├── backup.sh            # Universal backup utility
│   ├── deploy.sh            # Universal deployment script
│   ├── restore.sh           # Restore & URL migration utility
│   ├── search-replace.php   # Safe serialized PHP search-replace tool
│   └── common.sh            # Shared helper functions & environment detector
├── backup.sh                # Root convenience wrapper
├── deploy.sh                # Root convenience wrapper
├── restore.sh               # Root convenience wrapper
├── wp-config.php            # Environment-aware WordPress configuration
└── wp-content/
    ├── plugins/
    │   ├── 2-step-webshop/  # Custom 2-step checkout & shop plugin
    │   └── ...
    └── themes/
```

---

## 🔐 Environment Configuration (`.env`)

Key settings in `.env`:

| Variable | Description | Example |
|---|---|---|
| `DB_NAME` | MySQL database name | `sami_webshop` |
| `DB_USER` | MySQL database user | `sami_user` |
| `DB_PASSWORD` | MySQL database password | `secure_password` |
| `DB_HOST` | Database host | `db` (Docker) or `localhost` (Native) |
| `WP_HOME` | Site URL with protocol | `https://84kitchen-kempten.de` |
| `WP_SITEURL` | WordPress URL | `https://84kitchen-kempten.de` |
| `HTTP_PORT` | Host HTTP port | `80` (or `8080`) |
| `HTTPS_PORT` | Host HTTPS port | `443` (or `8443`) |
| `WP_DEBUG` | Enable debug logging | `false` |

To generate fresh security keys and salts automatically:
```bash
./scripts/deploy.sh --generate-salts
```

---

## 💻 Development with DDEV

For local development using DDEV:
```bash
ddev start
```
`wp-config.php` automatically recognizes DDEV environments.

---

## 📄 License
GPL-2.0 or later.
