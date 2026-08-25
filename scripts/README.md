# SAMI-webshop Deployment & Backup Tooling

This directory contains automated tooling to deploy, backup, and restore the **SAMI-webshop** WordPress site on any Linux server environment.

---

## Quick Reference

| Action | Command | Description |
|---|---|---|
| **Deploy Stack** | `./deploy.sh` (or `./scripts/deploy.sh`) | Initializes `.env`, configures permissions, launches stack |
| **Backup Everything** | `./backup.sh` (or `./scripts/backup.sh`) | Exports DB + `wp-content/` into a timestamped `.tar.gz` bundle |
| **Restore / Migrate** | `./restore.sh` (or `./scripts/restore.sh`) | Restores DB + files and safely updates site URLs |
| **Safe URL Migration** | `php scripts/search-replace.php` | Safe serialized PHP string replacement |

---

## 1. Deploying to a New Linux Server

### Prerequisites
- **Docker Compose Mode (Recommended)**: Any Linux distribution with Docker & Docker Compose installed.
- **Native Mode**: Linux server with PHP 8.1+ (with `mysqli`, `curl`, `gd`, `zip`, `mbstring`), MySQL/MariaDB, and Nginx/Apache.

### Steps:
1. **Clone the repository on the server**:
   ```bash
   git clone https://github.com/your-org/SAMI-webshop.git /var/www/sami-webshop
   cd /var/www/sami-webshop
   ```

2. **Configure Environment Variables**:
   ```bash
   cp .env.example .env
   nano .env
   ```
   Set your domain name (`WP_HOME=https://your-domain.com`), database passwords, and ports.

3. **Run Deployment**:
   ```bash
   ./deploy.sh --docker
   ```
   *(Or for native hosting: `./deploy.sh --native`)*

---

## 2. Backing Up the Project

The backup script automatically captures:
- The complete WordPress database (dumped and compressed to `database.sql.gz`)
- All uploads, themes, plugins, and custom assets (`wp-content/`)
- A `manifest.json` metadata file recording source URL, database prefix, and timestamp
- Compressed into a single archive in `./backups/sami_backup_<type>_<timestamp>.tar.gz`

### Usage:
```bash
# Backup both database and files
./backup.sh

# Database only
./backup.sh --db-only

# Files only (wp-content)
./backup.sh --files-only

# Custom destination directory
./backup.sh --output /var/backups/sami
```

### Automating Daily Backups via Cron:
Add a cron job on your Linux server:
```bash
crontab -e
```
Add the following line to run daily at 2:00 AM:
```cron
0 2 * * * cd /var/www/sami-webshop && ./backup.sh --output /var/backups/sami-webshop >> /var/log/sami_backup.log 2>&1
```

---

## 3. Restoring or Migrating to Another Server

### Scenario: Migrating from Staging/Local to Production

1. **Create backup on the source server**:
   ```bash
   ./backup.sh
   # Produces: backups/sami_backup_all_20260825_150000.tar.gz
   ```

2. **Transfer archive to the target server**:
   ```bash
   scp backups/sami_backup_all_20260825_150000.tar.gz user@target-server:/var/www/sami-webshop/backups/
   ```

3. **On target server, run restore with the new domain URL**:
   ```bash
   ./restore.sh --file backups/sami_backup_all_20260825_150000.tar.gz --new-url https://84kitchen-kempten.de
   ```

The restore script automatically:
- Extracts `wp-content/uploads`, plugins, and themes
- Imports `database.sql.gz` into MySQL/MariaDB
- Safely replaces URLs in database tables, preserving PHP serialized arrays and WooCommerce data structures
- Corrects Linux file ownership and permissions

---

## 4. Script Details & Internals

### `common.sh`
Provides unified helper functions:
- Color-coded logging (`log_info`, `log_success`, `log_warning`, `log_error`)
- Automatic environment detection (DDEV, Docker Compose, Native)
- Automatic generation of 64-character WordPress salts & authentication keys
- Standard WordPress permission enforcement (`755` for directories, `644` for files, `600` for `.env` and `640` for `wp-config.php`)

### `search-replace.php`
A standalone, memory-efficient PHP script that recursively handles:
- Strings
- Serialized PHP objects (`a:x:{...}`, `s:x:"..."`)
- JSON-encoded strings
- Multi-dimensional arrays
Ensures that string length bytes in serialized PHP data are accurately recalculated, preventing corrupted widgets, menus, and plugin settings.

