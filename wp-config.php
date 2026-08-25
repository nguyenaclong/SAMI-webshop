<?php
/**
 * WordPress Base Configuration
 *
 * This configuration file dynamically loads settings from environment variables
 * or a local .env file, while maintaining full compatibility with DDEV and Docker.
 *
 * @package SAMI-webshop
 */

// -----------------------------------------------------------------------------
// 1. Lightweight .env File Parser (No external dependencies required)
// -----------------------------------------------------------------------------
(function() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file) || !is_readable($env_file)) {
        return;
    }

    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);

            // Strip enclosing quotes if present
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }

            if (!getenv($key)) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
})();

// -----------------------------------------------------------------------------
// 2. Reverse Proxy & SSL Termination Handling
// -----------------------------------------------------------------------------
if (
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
    (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false) ||
    (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
) {
    $_SERVER['HTTPS'] = 'on';
}

// -----------------------------------------------------------------------------
// 3. Local Environment Overrides (DDEV / Local file)
// -----------------------------------------------------------------------------
$ddev_settings = __DIR__ . '/wp-config-ddev.php';
if (!defined('DB_USER') && getenv('IS_DDEV_PROJECT') === 'true' && is_readable($ddev_settings)) {
    require_once $ddev_settings;
}

$local_settings = __DIR__ . '/wp-config-local.php';
if (is_readable($local_settings)) {
    require_once $local_settings;
}

// -----------------------------------------------------------------------------
// 4. Database Settings
// -----------------------------------------------------------------------------
defined('DB_NAME') || define('DB_NAME', getenv('DB_NAME') ?: 'sami_webshop');
defined('DB_USER') || define('DB_USER', getenv('DB_USER') ?: 'sami_user');
defined('DB_PASSWORD') || define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
defined('DB_HOST') || define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
defined('DB_CHARSET') || define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
defined('DB_COLLATE') || define('DB_COLLATE', getenv('DB_COLLATE') ?: '');

/** WordPress Database Table prefix */
if (empty($table_prefix)) {
    $table_prefix = getenv('DB_PREFIX') ?: 'wp_';
}

// -----------------------------------------------------------------------------
// 5. Site URLs & Dynamic Host Handling
// -----------------------------------------------------------------------------
if (getenv('WP_HOME')) {
    defined('WP_HOME') || define('WP_HOME', getenv('WP_HOME'));
}
if (getenv('WP_SITEURL')) {
    defined('WP_SITEURL') || define('WP_SITEURL', getenv('WP_SITEURL'));
} elseif (defined('WP_HOME')) {
    defined('WP_SITEURL') || define('WP_SITEURL', WP_HOME);
}

// -----------------------------------------------------------------------------
// 6. Authentication Unique Keys and Salts
// -----------------------------------------------------------------------------
defined('AUTH_KEY') || define('AUTH_KEY', getenv('AUTH_KEY') ?: 'AHHYGKghwSKySHoSTiaxvftpoxrGdNOHJrmJrlgwXrySgFfCftIejDVxjCIRyjcO');
defined('SECURE_AUTH_KEY') || define('SECURE_AUTH_KEY', getenv('SECURE_AUTH_KEY') ?: 'yoeaKvOwysdIuChTfWeckBklbbbkTUMdTrTZDkcfCHrWHdvGmivHAtoyYGukiEbb');
defined('LOGGED_IN_KEY') || define('LOGGED_IN_KEY', getenv('LOGGED_IN_KEY') ?: 'ijKvfdUowVePVNXiaYQJsHLfisbkxuPAGvnIGxtTrPgxYceVBcOrpKqhucIhYzdl');
defined('NONCE_KEY') || define('NONCE_KEY', getenv('NONCE_KEY') ?: 'RHzTOBhvfLviItKAKpFXQUIEjLwyUeEtUBmBJfYKeTLXoWzheBzlcISIjbnyCALH');
defined('AUTH_SALT') || define('AUTH_SALT', getenv('AUTH_SALT') ?: 'SITkUQYektrqppRDWzYINCYuniybbEVoZkacKfAxqnJnIjZKgythYCTFTzBQaMWA');
defined('SECURE_AUTH_SALT') || define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT') ?: 'VKWQoETUCAhioRcFsYceIDVpTeNgLohwVSGXRYFSRgHZKyoxPhDkcOeBoGCFdIvG');
defined('LOGGED_IN_SALT') || define('LOGGED_IN_SALT', getenv('LOGGED_IN_SALT') ?: 'pNZAEAXfDofgCZoBMePVTdMqnlgNDSgTTHwtgCHuvEtyCzFqyBeygYPzmZvAJNBa');
defined('NONCE_SALT') || define('NONCE_SALT', getenv('NONCE_SALT') ?: 'XyUmUmjecPKdrkUtcygzjYAeBhkLVZzbuikLGYYaNMxSbIxWjfyFTVfgaFMDtDWZ');

// -----------------------------------------------------------------------------
// 7. Environment, Debugging & Memory Limits
// -----------------------------------------------------------------------------
if (getenv('WP_ENVIRONMENT_TYPE')) {
    defined('WP_ENVIRONMENT_TYPE') || define('WP_ENVIRONMENT_TYPE', getenv('WP_ENVIRONMENT_TYPE'));
}

$is_debug = getenv('WP_DEBUG') === 'true' || getenv('WP_DEBUG') === '1';
defined('WP_DEBUG') || define('WP_DEBUG', $is_debug);
defined('WP_DEBUG_LOG') || define('WP_DEBUG_LOG', getenv('WP_DEBUG_LOG') === 'true' || getenv('WP_DEBUG_LOG') === '1');
defined('WP_DEBUG_DISPLAY') || define('WP_DEBUG_DISPLAY', getenv('WP_DEBUG_DISPLAY') === 'true' || getenv('WP_DEBUG_DISPLAY') === '1');
defined('SCRIPT_DEBUG') || define('SCRIPT_DEBUG', getenv('SCRIPT_DEBUG') === 'true' || getenv('SCRIPT_DEBUG') === '1');

if (getenv('WP_MEMORY_LIMIT')) {
    defined('WP_MEMORY_LIMIT') || define('WP_MEMORY_LIMIT', getenv('WP_MEMORY_LIMIT'));
}
if (getenv('WP_MAX_MEMORY_LIMIT')) {
    defined('WP_MAX_MEMORY_LIMIT') || define('WP_MAX_MEMORY_LIMIT', getenv('WP_MAX_MEMORY_LIMIT'));
}

// -----------------------------------------------------------------------------
// 8. Bootstrap WordPress
// -----------------------------------------------------------------------------
defined('ABSPATH') || define('ABSPATH', __DIR__ . '/');

if (file_exists(ABSPATH . '/wp-settings.php')) {
    require_once ABSPATH . '/wp-settings.php';
}
