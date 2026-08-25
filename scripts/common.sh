#!/usr/bin/env bash
# ==============================================================================
# SAMI-webshop Common Helper Utilities & Environment Detection
# ==============================================================================

# Strict error handling
set -eo pipefail

# ANSI Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Determine Project Root Directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# Logging helpers
log_header() {
    echo -e "\n${BOLD}${CYAN}=== $1 ===${NC}\n"
}

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

log_fatal() {
    echo -e "${RED}[FATAL]${NC} $1" >&2
    exit 1
}

# Check if a command is installed
has_command() {
    command -v "$1" >/dev/null 2>&1
}

# Load .env file into environment
load_env() {
    local env_file="${PROJECT_ROOT}/.env"
    if [[ -f "${env_file}" ]]; then
        log_info "Loading environment settings from .env..."
        # Export non-comment lines
        set -a
        # shellcheck disable=SC1090
        source <(sed -E 's/^[[:space:]]*#.*$//; /^[[:space:]]*$/d' "${env_file}")
        set +a
    else
        log_warning "No .env file found at ${env_file}."
    fi
}

# Detect active runtime environment
detect_environment() {
    # 1. Check if DDEV is active for this project
    if has_command ddev && [[ -d "${PROJECT_ROOT}/.ddev" ]]; then
        if ddev status >/dev/null 2>&1; then
            echo "ddev"
            return
        fi
    fi

    # 2. Check if Docker Compose is running
    if has_command docker; then
        if docker compose ps 2>/dev/null | grep -q "sami_webshop"; then
            echo "docker"
            return
        fi
        # If docker-compose.yml exists and docker is available
        if [[ -f "${PROJECT_ROOT}/docker-compose.yml" ]]; then
            echo "docker"
            return
        fi
    fi

    # 3. Fallback to native host environment
    echo "native"
}

# Generate 64-char secure random string
generate_secret_key() {
    if has_command openssl; then
        openssl rand -base64 48 | tr -dc 'a-zA-Z0-9!@#$%^&*()-_=+' | head -c 64
    else
        head /dev/urandom | tr -dc 'a-zA-Z0-9!@#$%^&*()-_=+' | head -c 64
    fi
}

# Generate WordPress security salts and write to .env if needed
generate_salts() {
    local env_file="${PROJECT_ROOT}/.env"
    if [[ ! -f "${env_file}" ]]; then
        log_error "Cannot generate salts: .env file does not exist."
        return 1
    fi

    log_info "Generating fresh secure authentication keys and salts..."
    
    local keys=("AUTH_KEY" "SECURE_AUTH_KEY" "LOGGED_IN_KEY" "NONCE_KEY" "AUTH_SALT" "SECURE_AUTH_SALT" "LOGGED_IN_SALT" "NONCE_SALT")
    
    for key in "${keys[@]}"; do
        local secret
        secret=$(generate_secret_key)
        # Escape special sed characters in replacement
        local escaped_secret
        escaped_secret=$(printf '%s\n' "$secret" | sed -e 's/[\/&]/\\&/g')
        
        if grep -q "^${key}=" "${env_file}"; then
            sed -i "s|^${key}=.*|${key}='${escaped_secret}'|" "${env_file}"
        else
            echo "${key}='${escaped_secret}'" >> "${env_file}"
        fi
    done
    
    log_success "WordPress keys and salts generated successfully."
}

# Set proper file permissions for WordPress
fix_permissions() {
    log_info "Adjusting file and directory permissions..."
    
    local target_dir="${PROJECT_ROOT}"
    
    # 755 for directories, 644 for files
    find "${target_dir}" -type d -not -path "*/.git*" -not -path "*/.ddev*" -exec chmod 755 {} + 2>/dev/null || true
    find "${target_dir}" -type f -not -path "*/.git*" -not -path "*/.ddev*" -not -name "*.sh" -exec chmod 644 {} + 2>/dev/null || true
    
    # Ensure scripts are executable
    chmod +x "${PROJECT_ROOT}"/scripts/*.sh 2>/dev/null || true
    chmod +x "${PROJECT_ROOT}"/*.sh 2>/dev/null || true
    
    # Protect config files
    if [[ -f "${PROJECT_ROOT}/wp-config.php" ]]; then
        chmod 640 "${PROJECT_ROOT}/wp-config.php" 2>/dev/null || true
    fi
    if [[ -f "${PROJECT_ROOT}/.env" ]]; then
        chmod 600 "${PROJECT_ROOT}/.env" 2>/dev/null || true
    fi
    
    log_success "Permissions configured safely."
}

