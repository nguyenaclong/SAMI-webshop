<?php
/**
 * Standalone WordPress Database Search & Replace Tool
 *
 * Safely replaces URLs/strings in WordPress databases, correctly preserving
 * PHP serialized data structures without corrupting options, postmeta, or widgets.
 *
 * Usage:
 *   php search-replace.php --search="https://old-domain.com" --replace="https://new-domain.com"
 *
 * Optional arguments:
 *   --host=localhost
 *   --port=3306
 *   --db=sami_webshop
 *   --user=sami_user
 *   --pass=password
 *   --dry-run
 */

// Parse CLI options
$options = getopt('', [
    'search:',
    'replace:',
    'host::',
    'port::',
    'db::',
    'user::',
    'pass::',
    'dry-run'
]);

if (empty($options['search']) || empty($options['replace'])) {
    echo "Usage: php search-replace.php --search=<old_url> --replace=<new_url> [options]\n";
    echo "Options:\n";
    echo "  --search=<str>    String/URL to search for (Required)\n";
    echo "  --replace=<str>   String/URL to replace with (Required)\n";
    echo "  --host=<host>     MySQL host (default: DB_HOST or localhost)\n";
    echo "  --port=<port>     MySQL port (default: DB_PORT or 3306)\n";
    echo "  --db=<dbname>     Database name (default: DB_NAME)\n";
    echo "  --user=<user>     Database user (default: DB_USER)\n";
    echo "  --pass=<pass>     Database password (default: DB_PASSWORD)\n";
    echo "  --dry-run         Simulate replacements without modifying the database\n";
    exit(1);
}

$search = $options['search'];
$replace = $options['replace'];
$dry_run = isset($options['dry-run']);

// Auto-load .env if available
$env_file = dirname(__DIR__) . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $k = trim($parts[0]);
                $v = trim($parts[1]);
                if ((str_starts_with($v, '"') && str_ends_with($v, '"')) ||
                    (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
                    $v = substr($v, 1, -1);
                }
                if (!getenv($k)) putenv("{$k}={$v}");
            }
        }
    }
}

$db_host = $options['host'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$db_port = $options['port'] ?? getenv('DB_PORT') ?: '3306';
$db_name = $options['db'] ?? getenv('DB_NAME') ?: 'sami_webshop';
$db_user = $options['user'] ?? getenv('DB_USER') ?: 'root';
$db_pass = $options['pass'] ?? getenv('DB_PASSWORD') ?: '';

echo "\n=== WordPress Safe Database Search & Replace ===\n";
echo "Search:  {$search}\n";
echo "Replace: {$replace}\n";
echo "Target:  {$db_user}@{$db_host}:{$db_port}/{$db_name}\n";
if ($dry_run) {
    echo "Mode:    [DRY RUN - No changes will be written]\n";
}
echo "=================================================\n\n";

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Database connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

/**
 * Recursive search and replace handling strings, arrays, objects, and serialized data.
 */
function recursive_search_replace($data, $search, $replace, &$count = 0) {
    if (is_string($data)) {
        // Check if string is serialized PHP
        if (is_serialized_string($data)) {
            $unserialized = @unserialize($data);
            if ($unserialized !== false || $data === 'b:0;') {
                $replaced = recursive_search_replace($unserialized, $search, $replace, $count);
                return serialize($replaced);
            }
        }

        // Check if string is JSON
        if ((str_starts_with(trim($data), '{') || str_starts_with(trim($data), '[')) && is_array($json = json_decode($data, true))) {
            $replaced = recursive_search_replace($json, $search, $replace, $count);
            return json_encode($replaced, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        // Plain string replacement
        $occurrences = substr_count($data, $search);
        if ($occurrences > 0) {
            $count += $occurrences;
            return str_replace($search, $replace, $data);
        }
        return $data;
    }

    if (is_array($data)) {
        $result = [];
        foreach ($data as $key => $value) {
            $new_key = recursive_search_replace($key, $search, $replace, $count);
            $result[$new_key] = recursive_search_replace($value, $search, $replace, $count);
        }
        return $result;
    }

    if (is_object($data)) {
        $clone = clone $data;
        foreach (get_object_vars($data) as $property => $value) {
            $clone->$property = recursive_search_replace($value, $search, $replace, $count);
        }
        return $clone;
    }

    return $data;
}

function is_serialized_string($data) {
    if (!is_string($data)) return false;
    $data = trim($data);
    if ('N;' === $data) return true;
    if (strlen($data) < 4) return false;
    if (':' !== $data[1]) return false;
    $last_char = substr($data, -1);
    if (';' !== $last_char && '}' !== $last_char) return false;
    $token = $data[0];
    switch ($token) {
        case 's':
            if ('"' !== substr($data, -2, 1)) return false;
        case 'a':
        case 'O':
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b':
        case 'i':
        case 'd':
            return (bool) preg_match("/^{$token}:[0-9.E-]+;\$/si", $data);
    }
    return false;
}

// Fetch all tables
$tables_stmt = $pdo->query("SHOW TABLES");
$tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);

$total_tables = count($tables);
$total_updates = 0;
$total_replacements = 0;

echo "Found {$total_tables} tables in database.\n";

foreach ($tables as $table) {
    // Get primary key / unique column if possible
    $cols_stmt = $pdo->query("DESCRIBE `{$table}`");
    $columns = $cols_stmt->fetchAll();
    
    $primary_keys = [];
    $text_columns = [];

    foreach ($columns as $col) {
        $field = $col['Field'];
        $type = strtolower($col['Type']);
        if ($col['Key'] === 'PRI') {
            $primary_keys[] = $field;
        }
        if (str_contains($type, 'char') || str_contains($type, 'text') || str_contains($type, 'blob')) {
            $text_columns[] = $field;
        }
    }

    if (empty($text_columns)) {
        continue;
    }

    // Process table in batches
    $select_cols = empty($primary_keys) ? '*' : implode(', ', array_map(fn($k) => "`{$k}`", array_unique(array_merge($primary_keys, $text_columns))));
    $rows_stmt = $pdo->query("SELECT {$select_cols} FROM `{$table}`");
    
    $table_replacements = 0;
    $table_updates = 0;

    while ($row = $rows_stmt->fetch()) {
        $needs_update = false;
        $updated_data = [];

        foreach ($text_columns as $col) {
            $original_val = $row[$col];
            if (is_string($original_val) && str_contains($original_val, $search)) {
                $count = 0;
                $new_val = recursive_search_replace($original_val, $search, $replace, $count);
                if ($count > 0 && $new_val !== $original_val) {
                    $needs_update = true;
                    $updated_data[$col] = $new_val;
                    $table_replacements += $count;
                }
            }
        }

        if ($needs_update && !$dry_run) {
            if (!empty($primary_keys)) {
                $set_clause = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($updated_data)));
                $where_clause = implode(' AND ', array_map(fn($pk) => "`{$pk}` = ?", $primary_keys));
                $params = array_values($updated_data);
                foreach ($primary_keys as $pk) {
                    $params[] = $row[$pk];
                }

                $update_stmt = $pdo->prepare("UPDATE `{$table}` SET {$set_clause} WHERE {$where_clause}");
                $update_stmt->execute($params);
                $table_updates++;
            }
        }
    }

    if ($table_replacements > 0) {
        echo " - Table `{$table}`: {$table_replacements} replacements ({$table_updates} rows modified)\n";
    }

    $total_replacements += $table_replacements;
    $total_updates += $table_updates;
}

echo "\n=================================================\n";
if ($dry_run) {
    echo "Summary (Dry Run): Would perform {$total_replacements} replacements across {$total_updates} rows.\n";
} else {
    echo "Summary: Completed {$total_replacements} replacements across {$total_updates} rows.\n";
}
echo "=================================================\n\n";

