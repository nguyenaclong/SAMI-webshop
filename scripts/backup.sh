#!/usr/bin/env bash
# ==============================================================================
# SAMI-webshop Universal Backup Utility
# Creates a portable, self-contained backup bundle of the database & files
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=scripts/common.sh
source "${SCRIPT_DIR}/common.sh"

# Default configuration
BACKUP_TYPE="all"
BACKUP_DIR="${PROJECT_ROOT}/backups"
CUSTOM_NAME=""
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"

show_help() {
    cat << EOF
SAMI-webshop Backup Utility

Usage:
  ./scripts/backup.sh [OPTIONS]
  ./backup.sh [OPTIONS]

Options:
  --all            Backup both database and wp-content files (Default)
  --db-only        Backup database only
  --files-only     Backup wp-content files only (themes, plugins, uploads)
  --output <dir>   Custom output directory (Default: ./backups)
  --name <name>    Custom prefix/name for the backup file
  -h, --help       Show this help message

Examples:
  ./scripts/backup.sh
  ./scripts/backup.sh --db-only
  ./scripts/backup.sh --output /var/backups/sami
EOF
}

# Parse CLI arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        --all)
            BACKUP_TYPE="all"
            shift
            ;;
        --db-only)
            BACKUP_TYPE="db"
            shift
            ;;
        --files-only)
            BACKUP_TYPE="files"
            shift
            ;;
        --output)
            BACKUP_DIR="$2"
            shift 2
            ;;
        --name)
            CUSTOM_NAME="$2"
            shift 2
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            log_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

load_env

log_header "SAMI-webshop Backup Utility"

# Create output backup directory
mkdir -p "${BACKUP_DIR}"

# Determine backup archive filename
ARCHIVE_PREFIX="${CUSTOM_NAME:-sami_backup_${BACKUP_TYPE}_${TIMESTAMP}}"
ARCHIVE_PATH="${BACKUP_DIR}/${ARCHIVE_PREFIX}.tar.gz"
TEMP_DIR="$(mktemp -d /tmp/sami_backup_XXXXXX)"

cleanup() {
    rm -rf "${TEMP_DIR}"
}
trap cleanup EXIT

ENV_TYPE="$(detect_environment)"
log_info "Detected environment: ${BOLD}${ENV_TYPE}${NC}"
log_info "Backup mode: ${BOLD}${BACKUP_TYPE}${NC}"

# ------------------------------------------------------------------------------
# 1. Database Backup
# ------------------------------------------------------------------------------
if [[ "${BACKUP_TYPE}" == "all" || "${BACKUP_TYPE}" == "db" ]]; then
    log_info "Exporting WordPress database..."
    SQL_FILE="${TEMP_DIR}/database.sql"
    
    case "${ENV_TYPE}" in
        ddev)
            log_info "Using DDEV database exporter..."
            (cd "${PROJECT_ROOT}" && ddev export-db --file="${SQL_FILE}" --gzip=false)
            ;;
        docker)
            log_info "Exporting from Docker database container..."
            if docker compose ps | grep -q "sami_webshop_db"; then
                docker compose exec -T db mariadb-dump \
                    -u"${DB_USER:-sami_user}" \
                    -p"${DB_PASSWORD:-change_this_password}" \
                    "${DB_NAME:-sami_webshop}" > "${SQL_FILE}" 2>/dev/null || \
                docker compose exec -T db mysqldump \
                    -u"${DB_USER:-sami_user}" \
                    -p"${DB_PASSWORD:-change_this_password}" \
                    "${DB_NAME:-sami_webshop}" > "${SQL_FILE}"
            else
                log_fatal "Docker is selected, but 'sami_webshop_db' container is not running. Run 'docker compose up -d' first."
            fi
            ;;
        native)
            if has_command wp && wp core is-installed --path="${PROJECT_ROOT}" 2>/dev/null; then
                log_info "Exporting via WP-CLI..."
                wp db export "${SQL_FILE}" --path="${PROJECT_ROOT}"
            elif has_command mysqldump || has_command mariadb-dump; then
                log_info "Exporting via native mysqldump..."
                DUMP_CMD="mysqldump"
                has_command mariadb-dump && DUMP_CMD="mariadb-dump"
                ${DUMP_CMD} -h "${DB_HOST:-localhost}" \
                    -P "${DB_PORT:-3306}" \
                    -u "${DB_USER:-root}" \
                    -p"${DB_PASSWORD}" \
                    "${DB_NAME:-sami_webshop}" > "${SQL_FILE}"
            else
                log_fatal "No database dump tool found (DDEV, Docker, WP-CLI, or mysqldump)."
            fi
            ;;
    esac

    if [[ -s "${SQL_FILE}" ]]; then
        gzip -c "${SQL_FILE}" > "${TEMP_DIR}/database.sql.gz"
        rm -f "${SQL_FILE}"
        log_success "Database export completed successfully ($(du -h "${TEMP_DIR}/database.sql.gz" | cut -f1))."
    else
        log_fatal "Database dump produced an empty file. Check database credentials and connection."
    fi
fi

# ------------------------------------------------------------------------------
# 2. Project Files Backup
# ------------------------------------------------------------------------------
if [[ "${BACKUP_TYPE}" == "all" || "${BACKUP_TYPE}" == "files" ]]; then
    log_info "Packaging wp-content files (themes, plugins, uploads, languages)..."
    
    mkdir -p "${TEMP_DIR}/files"
    
    # Copy wp-content with sensible exclusions
    rsync -a \
        --exclude='cache/' \
        --exclude='updraft/' \
        --exclude='upgrade/' \
        --exclude='upgrade-temp-backup/' \
        --exclude='backup-db/' \
        --exclude='litespeed/' \
        --exclude='*.log' \
        --exclude='.DS_Store' \
        "${PROJECT_ROOT}/wp-content" "${TEMP_DIR}/files/"
    
    # If wp-config.php or .htaccess exist, include them for reference
    [[ -f "${PROJECT_ROOT}/.htaccess" ]] && cp "${PROJECT_ROOT}/.htaccess" "${TEMP_DIR}/files/"
    
    log_success "Project files packaged successfully."
fi

# ------------------------------------------------------------------------------
# 3. Create Manifest Metadata
# ------------------------------------------------------------------------------
log_info "Generating backup manifest..."
CURRENT_SITE_URL="${WP_HOME:-https://84kitchen-kempten.de}"
cat << EOF > "${TEMP_DIR}/manifest.json"
{
  "backup_name": "${ARCHIVE_PREFIX}",
  "created_at": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "backup_type": "${BACKUP_TYPE}",
  "source_environment": "${ENV_TYPE}",
  "site_url": "${CURRENT_SITE_URL}",
  "db_name": "${DB_NAME:-sami_webshop}",
  "db_prefix": "${DB_PREFIX:-wp_}",
  "version": "1.0.0"
}
EOF

# ------------------------------------------------------------------------------
# 4. Create Final Compressed Tarball Bundle
# ------------------------------------------------------------------------------
log_info "Creating final archive at ${ARCHIVE_PATH}..."
tar -czf "${ARCHIVE_PATH}" -C "${TEMP_DIR}" .

ARCHIVE_SIZE="$(du -h "${ARCHIVE_PATH}" | cut -f1)"

log_header "Backup Completed Successfully!"
echo -e "Archive File : ${BOLD}${GREEN}${ARCHIVE_PATH}${NC}"
echo -e "Archive Size : ${BOLD}${ARCHIVE_SIZE}${NC}"
echo -e "Backup Type  : ${BOLD}${BACKUP_TYPE}${NC}"
echo -e "Environment  : ${BOLD}${ENV_TYPE}${NC}"
echo ""
echo -e "To deploy or restore this backup on another Linux server:"
echo -e "  1. Transfer archive: ${CYAN}scp ${ARCHIVE_PATH} user@new-server:/path/to/SAMI-webshop/backups/${NC}"
echo -e "  2. Run restore:     ${CYAN}./scripts/restore.sh --file ${ARCHIVE_PATH} --new-url https://new-domain.com${NC}"
echo ""

