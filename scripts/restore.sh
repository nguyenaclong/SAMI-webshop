#!/usr/bin/env bash
# ==============================================================================
# SAMI-webshop Restore & Migration Utility
# Restores a backup bundle (database + files) and updates site URLs safely
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=scripts/common.sh
source "${SCRIPT_DIR}/common.sh"

ARCHIVE_FILE=""
NEW_URL=""
OLD_URL=""
SKIP_DB=false
SKIP_FILES=false
TARGET_ENV=""

show_help() {
    cat << EOF
SAMI-webshop Restore Utility

Usage:
  ./scripts/restore.sh [OPTIONS]
  ./restore.sh [OPTIONS]

Options:
  --file <archive>   Path to .tar.gz backup archive (Default: latest file in ./backups)
  --new-url <url>    New domain / site URL (e.g. https://84kitchen-kempten.de)
  --old-url <url>    Old domain / site URL (Overrides manifest detected URL)
  --skip-db          Do not restore database (files only)
  --skip-files       Do not restore files (database only)
  --env <type>       Force target environment: 'docker', 'ddev', or 'native'
  -h, --help         Show this help message

Examples:
  ./scripts/restore.sh --file backups/sami_backup_all_20260825.tar.gz --new-url https://example.com
  ./scripts/restore.sh --new-url http://localhost:8080
EOF
}

# Parse arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        --file)
            ARCHIVE_FILE="$2"
            shift 2
            ;;
        --new-url)
            NEW_URL="$2"
            shift 2
            ;;
        --old-url)
            OLD_URL="$2"
            shift 2
            ;;
        --skip-db)
            SKIP_DB=true
            shift
            ;;
        --skip-files)
            SKIP_FILES=true
            shift
            ;;
        --env)
            TARGET_ENV="$2"
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

log_header "SAMI-webshop Restore & Migration Utility"

# If no archive specified, find latest in backups directory
if [[ -z "${ARCHIVE_FILE}" ]]; then
    ARCHIVE_FILE="$(find "${PROJECT_ROOT}/backups" -name "*.tar.gz" -type f 2>/dev/null | sort -r | head -n 1 || true)"
    if [[ -z "${ARCHIVE_FILE}" ]]; then
        log_fatal "No backup archive found in ${PROJECT_ROOT}/backups. Please specify --file <path>."
    fi
    log_info "Auto-detected latest backup: ${BOLD}${ARCHIVE_FILE}${NC}"
fi

if [[ ! -f "${ARCHIVE_FILE}" ]]; then
    log_fatal "Archive file not found: ${ARCHIVE_FILE}"
fi

# Detect target environment if not forced
if [[ -z "${TARGET_ENV}" ]]; then
    TARGET_ENV="$(detect_environment)"
fi

log_info "Target Environment: ${BOLD}${TARGET_ENV}${NC}"

# Extract archive to temporary staging folder
TEMP_DIR="$(mktemp -d /tmp/sami_restore_XXXXXX)"
cleanup() {
    rm -rf "${TEMP_DIR}"
}
trap cleanup EXIT

log_info "Extracting backup archive..."
tar -xzf "${ARCHIVE_FILE}" -C "${TEMP_DIR}"

# Read manifest metadata if available
MANIFEST_FILE="${TEMP_DIR}/manifest.json"
MANIFEST_OLD_URL=""
if [[ -f "${MANIFEST_FILE}" ]]; then
    MANIFEST_OLD_URL="$(grep -o '"site_url": "[^"]*' "${MANIFEST_FILE}" | cut -d'"' -f4 || true)"
fi

# Determine source and target URLs for search-and-replace
if [[ -z "${OLD_URL}" ]]; then
    OLD_URL="${MANIFEST_OLD_URL}"
fi

# If new URL was not passed via CLI, fallback to WP_HOME from .env
if [[ -z "${NEW_URL}" && -n "${WP_HOME}" ]]; then
    NEW_URL="${WP_HOME}"
fi

# ------------------------------------------------------------------------------
# 1. Restore Files (wp-content)
# ------------------------------------------------------------------------------
if [[ "${SKIP_FILES}" = false && -d "${TEMP_DIR}/files/wp-content" ]]; then
    log_info "Restoring wp-content (plugins, themes, uploads, languages)..."
    mkdir -p "${PROJECT_ROOT}/wp-content"
    rsync -av "${TEMP_DIR}/files/wp-content/" "${PROJECT_ROOT}/wp-content/"
    log_success "Files restored successfully."
fi

# ------------------------------------------------------------------------------
# 2. Restore Database
# ------------------------------------------------------------------------------
SQL_GZ="${TEMP_DIR}/database.sql.gz"
SQL_RAW="${TEMP_DIR}/database.sql"

if [[ "${SKIP_DB}" = false ]]; then
    if [[ -f "${SQL_GZ}" || -f "${SQL_RAW}" ]]; then
        log_info "Importing database..."
        
        # Decompress if needed
        if [[ -f "${SQL_GZ}" && ! -f "${SQL_RAW}" ]]; then
            gunzip -c "${SQL_GZ}" > "${SQL_RAW}"
        fi

        case "${TARGET_ENV}" in
            ddev)
                log_info "Importing into DDEV database..."
                (cd "${PROJECT_ROOT}" && ddev import-db --file="${SQL_RAW}")
                ;;
            docker)
                log_info "Importing into Docker database container..."
                if ! docker compose ps | grep -q "sami_webshop_db"; then
                    log_info "Starting database container..."
                    docker compose up -d db
                    log_info "Waiting 10s for database readiness..."
                    sleep 10
                fi
                docker compose exec -T db mariadb \
                    -u"${DB_USER:-sami_user}" \
                    -p"${DB_PASSWORD:-change_this_password}" \
                    "${DB_NAME:-sami_webshop}" < "${SQL_RAW}" 2>/dev/null || \
                docker compose exec -T db mysql \
                    -u"${DB_USER:-sami_user}" \
                    -p"${DB_PASSWORD:-change_this_password}" \
                    "${DB_NAME:-sami_webshop}" < "${SQL_RAW}"
                ;;
            native)
                log_info "Importing into native MySQL database..."
                MYSQL_CMD="mysql"
                has_command mariadb && MYSQL_CMD="mariadb"
                ${MYSQL_CMD} -h "${DB_HOST:-localhost}" \
                    -P "${DB_PORT:-3306}" \
                    -u "${DB_USER:-root}" \
                    -p"${DB_PASSWORD}" \
                    "${DB_NAME:-sami_webshop}" < "${SQL_RAW}"
                ;;
        esac
        log_success "Database import completed."
    else
        log_warning "No database dump found in backup archive."
    fi
fi

# ------------------------------------------------------------------------------
# 3. WordPress Database URL Search & Replace
# ------------------------------------------------------------------------------
if [[ -n "${OLD_URL}" && -n "${NEW_URL}" && "${OLD_URL}" != "${NEW_URL}" ]]; then
    log_info "Migrating site URLs from ${BOLD}${OLD_URL}${NC} -> ${BOLD}${GREEN}${NEW_URL}${NC}..."

    case "${TARGET_ENV}" in
        ddev)
            log_info "Running search-replace with DDEV WP-CLI..."
            ddev wp search-replace "${OLD_URL}" "${NEW_URL}" --all-tables --precise || true
            ;;
        docker)
            log_info "Running search-replace with Docker WP-CLI..."
            docker compose run --rm wpcli search-replace "${OLD_URL}" "${NEW_URL}" --all-tables --precise 2>/dev/null || \
            php "${PROJECT_ROOT}/scripts/search-replace.php" \
                --search="${OLD_URL}" \
                --replace="${NEW_URL}" \
                --host="${DB_HOST:-127.0.0.1}" \
                --port="${DB_PORT:-3306}" \
                --db="${DB_NAME:-sami_webshop}" \
                --user="${DB_USER:-sami_user}" \
                --pass="${DB_PASSWORD:-change_this_password}" || true
            ;;
        native)
            if has_command wp && wp core is-installed --path="${PROJECT_ROOT}" 2>/dev/null; then
                log_info "Running search-replace with native WP-CLI..."
                wp search-replace "${OLD_URL}" "${NEW_URL}" --all-tables --precise --path="${PROJECT_ROOT}"
            else
                log_info "Running standalone PHP search-replace tool..."
                php "${PROJECT_ROOT}/scripts/search-replace.php" \
                    --search="${OLD_URL}" \
                    --replace="${NEW_URL}" \
                    --host="${DB_HOST:-127.0.0.1}" \
                    --port="${DB_PORT:-3306}" \
                    --db="${DB_NAME:-sami_webshop}" \
                    --user="${DB_USER:-sami_user}" \
                    --pass="${DB_PASSWORD}"
            fi
            ;;
    esac
    log_success "Database URLs updated safely."
fi

# ------------------------------------------------------------------------------
# 4. Permissions & Cleanup
# ------------------------------------------------------------------------------
fix_permissions

log_header "Restore & Migration Completed Successfully!"
echo -e "Website URL : ${BOLD}${GREEN}${NEW_URL:-${WP_HOME:-http://localhost}}${NC}"
echo -e "Environment : ${BOLD}${TARGET_ENV}${NC}"
echo ""

