#!/usr/bin/env bash
# ==============================================================================
# SAMI-webshop Turnkey Deployment Utility
# Deploys the project to any Linux server (Docker Compose, Native LAMP/LEMP, or DDEV)
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=scripts/common.sh
source "${SCRIPT_DIR}/common.sh"

DEPLOY_MODE="auto"
RESTORE_FILE=""
NEW_URL=""
PULL_GIT=false
GEN_SALTS=false

show_help() {
    cat << EOF
SAMI-webshop Deployment Utility

Usage:
  ./scripts/deploy.sh [OPTIONS]
  ./deploy.sh [OPTIONS]

Options:
  --docker            Deploy using Docker Compose (Recommended for isolated stack)
  --native            Deploy on native host web server (Nginx/Apache + PHP + MySQL)
  --ddev              Deploy using DDEV (for development servers)
  --restore <file>    Restore a backup bundle during deployment
  --new-url <url>     Configure site URL (e.g. https://84kitchen-kempten.de)
  --generate-salts    Regenerate security salts in .env
  --pull              Pull latest code from git before deployment
  -h, --help          Show this help message

Examples:
  # Quick deploy with Docker Compose
  ./scripts/deploy.sh --docker

  # Deploy and restore database + uploads from a backup
  ./scripts/deploy.sh --docker --restore backups/sami_backup.tar.gz --new-url https://example.com

  # Pull latest code and update deployment
  ./scripts/deploy.sh --pull
EOF
}

# Parse options
while [[ $# -gt 0 ]]; do
    case "$1" in
        --docker)
            DEPLOY_MODE="docker"
            shift
            ;;
        --native)
            DEPLOY_MODE="native"
            shift
            ;;
        --ddev)
            DEPLOY_MODE="ddev"
            shift
            ;;
        --restore)
            RESTORE_FILE="$2"
            shift 2
            ;;
        --new-url)
            NEW_URL="$2"
            shift 2
            ;;
        --generate-salts)
            GEN_SALTS=true
            shift
            ;;
        --pull)
            PULL_GIT=true
            shift
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

log_header "SAMI-webshop Server Deployment"

# Optional Git Pull
if [[ "${PULL_GIT}" = true ]]; then
    if has_command git && [[ -d "${PROJECT_ROOT}/.git" ]]; then
        log_info "Pulling latest changes from git repository..."
        git -C "${PROJECT_ROOT}" pull --ff-only || log_warning "Git pull failed or has merge conflicts. Continuing..."
    fi
fi

# ------------------------------------------------------------------------------
# 1. Environment Configuration Setup (.env)
# ------------------------------------------------------------------------------
ENV_FILE="${PROJECT_ROOT}/.env"
ENV_EXAMPLE="${PROJECT_ROOT}/.env.example"

if [[ ! -f "${ENV_FILE}" ]]; then
    if [[ -f "${ENV_EXAMPLE}" ]]; then
        log_info "No .env found. Initializing from .env.example..."
        cp "${ENV_EXAMPLE}" "${ENV_FILE}"
        generate_salts
    else
        log_fatal "Neither .env nor .env.example was found in project root."
    fi
elif [[ "${GEN_SALTS}" = true ]]; then
    generate_salts
fi

# Update URL in .env if passed via CLI
if [[ -n "${NEW_URL}" ]]; then
    log_info "Updating WP_HOME and WP_SITEURL in .env to: ${NEW_URL}..."
    sed -i "s|^WP_HOME=.*|WP_HOME=${NEW_URL}|" "${ENV_FILE}"
    sed -i "s|^WP_SITEURL=.*|WP_SITEURL=${NEW_URL}|" "${ENV_FILE}"
fi

load_env

# ------------------------------------------------------------------------------
# 2. Determine Deployment Mode
# ------------------------------------------------------------------------------
if [[ "${DEPLOY_MODE}" == "auto" ]]; then
    if has_command docker; then
        DEPLOY_MODE="docker"
    elif has_command ddev && [[ -d "${PROJECT_ROOT}/.ddev" ]]; then
        DEPLOY_MODE="ddev"
    else
        DEPLOY_MODE="native"
    fi
fi

log_info "Deployment mode: ${BOLD}${CYAN}${DEPLOY_MODE}${NC}"

# ------------------------------------------------------------------------------
# 3. Stack Initialization
# ------------------------------------------------------------------------------
case "${DEPLOY_MODE}" in
    docker)
        if ! has_command docker; then
            log_fatal "Docker is not installed on this system. Install Docker or use --native."
        fi

        log_info "Building and launching Docker containers..."
        (cd "${PROJECT_ROOT}" && docker compose build)
        (cd "${PROJECT_ROOT}" && docker compose up -d)
        
        log_info "Waiting 10s for database service initialization..."
        sleep 10
        ;;

    ddev)
        if ! has_command ddev; then
            log_fatal "DDEV is not installed. Install DDEV or use --docker / --native."
        fi
        log_info "Starting DDEV project..."
        (cd "${PROJECT_ROOT}" && ddev start)
        ;;

    native)
        log_info "Verifying native environment requirements..."
        if ! has_command php; then
            log_warning "PHP CLI not found in PATH. Ensure PHP 8.1+ and PHP-FPM are installed."
        else
            PHP_VER="$(php -r 'echo PHP_VERSION;' 2>/dev/null || true)"
            log_info "Detected PHP version: ${PHP_VER}"
        fi
        ;;
esac

# ------------------------------------------------------------------------------
# 4. Optional Backup Restore
# ------------------------------------------------------------------------------
if [[ -n "${RESTORE_FILE}" ]]; then
    log_info "Restoring backup bundle: ${RESTORE_FILE}..."
    RESTORE_ARGS=("--file" "${RESTORE_FILE}" "--env" "${DEPLOY_MODE}")
    if [[ -n "${NEW_URL}" ]]; then
        RESTORE_ARGS+=("--new-url" "${NEW_URL}")
    fi
    "${PROJECT_ROOT}/scripts/restore.sh" "${RESTORE_ARGS[@]}"
fi

# ------------------------------------------------------------------------------
# 5. Fix Permissions
# ------------------------------------------------------------------------------
fix_permissions

# ------------------------------------------------------------------------------
# 6. Final Status & Summary
# ------------------------------------------------------------------------------
FINAL_URL="${NEW_URL:-${WP_HOME:-http://localhost}}"

log_header "Deployment Finished Successfully!"
echo -e "Website URL : ${BOLD}${GREEN}${FINAL_URL}${NC}"
echo -e "Deploy Mode : ${BOLD}${DEPLOY_MODE}${NC}"
echo ""

if [[ "${DEPLOY_MODE}" == "docker" ]]; then
    echo -e "Container Status:"
    docker compose ps
    echo ""
    echo -e "Useful Docker Commands:"
    echo -e "  - View logs:           ${CYAN}docker compose logs -f${NC}"
    echo -e "  - Stop containers:     ${CYAN}docker compose down${NC}"
    echo -e "  - Restart containers:  ${CYAN}docker compose restart${NC}"
    echo -e "  - WP-CLI command:      ${CYAN}docker compose run --rm wpcli <command>${NC}"
fi

echo ""

