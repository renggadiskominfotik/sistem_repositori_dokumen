<?php

if (session_status() === PHP_SESSION_NONE) {
    if (is_dir('/tmp') && is_writable('/tmp')) {
        @session_save_path('/tmp');
    }
    @session_start();
}

function get_db_env($key, $default = '') {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    return $default;
}

$host     = get_db_env('DB_HOST', get_db_env('MYSQLHOST', 'localhost'));
$user     = get_db_env('DB_USER', get_db_env('MYSQLUSER', 'root'));
$password = get_db_env('DB_PASSWORD', get_db_env('MYSQLPASSWORD', ''));
$database = get_db_env('DB_NAME', get_db_env('MYSQLDATABASE', 'db_repository_dokumen'));
$port     = get_db_env('DB_PORT', get_db_env('MYSQLPORT', '3306'));

$is_postgres = ($port == 5432 || $port == 6543 || strpos($host, 'supabase') !== false || strpos($host, 'postgres') !== false);

$koneksi = null;
$pdo = null;

if ($is_postgres) {
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e2) {
            die("Koneksi Supabase PostgreSQL Gagal: " . $e2->getMessage());
        }
    }
} else {
    $koneksi = @mysqli_connect($host, $user, $password, $database, (int)$port);
    if (!$koneksi) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $user, $password);
        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . mysqli_connect_error());
        }
    }
}

class DBResult {
    public $rows = [];
    public $currentIndex = 0;
    public $numRows = 0;

    public function __construct($stmt) {
        if ($stmt instanceof PDOStatement) {
            try {
                if ($stmt->columnCount() > 0) {
                    $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->numRows = count($this->rows);
                } else {
                    $this->rows = [];
                    $this->numRows = $stmt->rowCount();
                }
            } catch (Throwable $e) {
                $this->rows = [];
                $this->numRows = $stmt->rowCount();
            }
        }
    }
}

function db_query($conn_or_sql, $sql = null) {
    global $pdo, $koneksi, $is_postgres;
    $queryStr = $sql !== null ? $sql : $conn_or_sql;

    if ($pdo) {
        try {
            if ($is_postgres) {
                $queryStr = str_replace('`', '"', $queryStr);
                $queryStr = str_replace(' LIKE ', ' ILIKE ', $queryStr);
            }
            $stmt = $pdo->query($queryStr);
            return new DBResult($stmt);
        } catch (PDOException $e) {
            error_log("DB Query Error: " . $e->getMessage() . " | SQL: " . $queryStr);
            return false;
        }
    } else if ($koneksi) {
        return mysqli_query($koneksi, $queryStr);
    }
    return false;
}

function db_fetch_assoc($result) {
    global $koneksi;
    if ($result instanceof DBResult) {
        if ($result->currentIndex < $result->numRows) {
            return $result->rows[$result->currentIndex++];
        }
        return false;
    } else if ($result && $koneksi) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

function db_num_rows($result) {
    global $koneksi;
    if ($result instanceof DBResult) {
        return $result->numRows;
    } else if ($result && $koneksi) {
        return mysqli_num_rows($result);
    }
    return 0;
}

function db_real_escape_string($conn_or_str, $str = null) {
    global $pdo, $koneksi;
    $target = $str !== null ? $str : $conn_or_str;
    if ($target === null) return '';

    if ($pdo) {
        $quoted = $pdo->quote($target);
        if (substr($quoted, 0, 1) === "'" && substr($quoted, -1) === "'") {
            return substr($quoted, 1, -1);
        }
        return $quoted;
    } else if ($koneksi) {
        $conn = is_object($conn_or_str) ? $conn_or_str : $koneksi;
        return mysqli_real_escape_string($conn, $target);
    }
    return addslashes($target);
}

function is_admin_logged_in() {
    if (isset($_SESSION['id_admin']) && !empty($_SESSION['id_admin'])) {
        return true;
    }
    if (isset($_COOKIE['admin_id']) && !empty($_COOKIE['admin_id'])) {
        $_SESSION['id_admin'] = $_COOKIE['admin_id'];
        $_SESSION['nama']     = isset($_COOKIE['admin_nama']) ? $_COOKIE['admin_nama'] : 'Administrator';
        $_SESSION['username'] = isset($_COOKIE['admin_user']) ? $_COOKIE['admin_user'] : 'Admin';
        return true;
    }
    return false;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header("Location: /admin/login.php");
        exit;
    }
}