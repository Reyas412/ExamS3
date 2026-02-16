<?php
/**
 * Configuration de la base de données
 */

// Configuration DB - XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'gnbrc');
define('DB_USER', 'root');
define('DB_PASS', '');

// Pour XAMPP, utiliser le socket Unix
define('DB_SOCKET', '/opt/lampp/var/mysql/mysql.sock');

/**
 * Connexion à la base de données
 */
function getDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = DB_HOST;
        $database = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        $charset = 'utf8mb4';
        
        // Utiliser le socket XAMPP pour la connexion
        $dsn = "mysql:host={$host};dbname={$database};charset={$charset};unix_socket=" . DB_SOCKET;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed', 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    return $pdo;
}