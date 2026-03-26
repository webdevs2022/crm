<?php
// ============================================================
// DATABASE CONFIGURATION
// ============================================================
// UPDATE THESE VALUES WITH YOUR ACTUAL HOSTING CREDENTIALS
// You can find them in:  cPanel → MySQL Databases
// ============================================================

define('DB_HOST',    'localhost');          // Almost always 'localhost' on shared hosting
define('DB_NAME',    'crm_db');     // Your actual database name (e.g. u123456_crm)
define('DB_USER',    'root'); // Your actual database username
define('DB_PASS',    '');   // Your actual database password
define('DB_CHARSET', 'utf8mb4');

// App Settings
define('APP_NAME',   'Enterprise CRM');
define('BASE_URL',   'https://yourdomain.com/crm'); // Change to your actual URL

class Database {
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // On production, never expose the real error message
            error_log('CRM DB Connection Error: ' . $e->getMessage());
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please check config/database.php'
            ]));
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
