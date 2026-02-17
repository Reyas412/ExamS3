<?php
/**
 * Configuration de la base de données
 */

// Configuration DB - XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'gnbrc');
define('DB_USER', 'root');
define('DB_PASS', '');

// Socket XAMPP
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
        
        // Utiliser le socket XAMPP
        $dsn = "mysql:host={$host};dbname={$database};charset={$charset};unix_socket=" . DB_SOCKET;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Ajouter du debug
            $errorMsg = $e->getMessage();
            
            // Essayer aussi sans unix_socket
            try {
                $dsn_alt = "mysql:host=localhost;dbname={$database};charset={$charset};port=3306";
                $pdo_alt = new PDO($dsn_alt, $user, $pass, $options);
                // Si ça marche, utiliser cette connexion
                $pdo = $pdo_alt;
            } catch (PDOException $e2) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Database connection failed', 
                    'message' => $errorMsg,
                    'dsn' => $dsn,
                    'alt_dsn' => $dsn_alt,
                    'alt_error' => $e2->getMessage()
                ]);
                exit;
            }
        }
    }
    
    return $pdo;
}