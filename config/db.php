<?php
/**
 * Database connection (PDO) for ServiceConnect.
 *
 * Reads credentials from environment variables so the same code works
 * unmodified on XAMPP (via a local .env file) and on cloud hosts like
 * Render / Railway (which inject real env vars at runtime — no .env
 * file needed there).
 *
 * Supports two shapes of config, in this priority order:
 *   1. A single DATABASE_URL (Railway/Render style):
 *        mysql://user:password@host:port/dbname
 *   2. Individual DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS vars
 */

// ---- Load .env for LOCAL development only (XAMPP). -----------------
// On Render/Railway, real environment variables are already set by the
// platform, so this loader is skipped automatically if vendor/autoload
// isn't present or .env doesn't exist — it never overrides real env vars.
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad(); // safeLoad = won't error if .env is missing
    }
}

/** Read an env var from $_ENV, $_SERVER, or getenv() — whichever is set. */
function env(string $key, ?string $default = null): ?string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($value === false || $value === null || $value === '') ? $default : $value;
}

// ---- Resolve connection details -------------------------------------
$databaseUrl = env('DATABASE_URL'); // e.g. mysql://user:pass@host:3306/dbname

if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    $dbHost = $parts['host'] ?? 'localhost';
    $dbPort = $parts['port'] ?? '3306';
    $dbName = isset($parts['path']) ? ltrim($parts['path'], '/') : 'serviceconnect';
    $dbUser = $parts['user'] ?? 'root';
    $dbPass = $parts['pass'] ?? '';
} else {
    $dbHost = env('DB_HOST', 'localhost');
    $dbPort = env('DB_PORT', '3306');
    $dbName = env('DB_NAME', 'serviceconnect');
    $dbUser = env('DB_USER', 'root');
    $dbPass = env('DB_PASS', ''); // empty default matches stock XAMPP MySQL
}

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // In production, don't leak connection details in the error message.
    $isProd = env('APP_ENV', 'local') === 'production';
    die($isProd ? 'Database connection failed. Please try again later.' : 'Database connection failed: ' . $e->getMessage());
}
