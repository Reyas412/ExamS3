<?php
/**
 * Script de configuration de la base de données
 * Exécuter ce fichier pour initialiser la base de données avec la structure correcte
 */

// Configuration DB - XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'gnbrc');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Connexion sans la base de données pour la créer
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    echo "Création de la base de données...\n";
    
    // Créer la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS gnbrc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Utiliser la base de données
    $pdo->exec("USE gnbrc");
    
    echo "Création des tables...\n";
    
    // Table regions
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS regions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(255) NOT NULL UNIQUE
        )
    ");
    echo "✓ Table 'regions' créée\n";
    
    // Table villes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS villes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(255) NOT NULL UNIQUE,
            idregion INT,
            FOREIGN KEY (idregion) REFERENCES regions(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Table 'villes' créée\n";
    
    // Table besoins
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS besoins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ville_id INT NOT NULL,
            type ENUM('nature', 'matériaux', 'argent') NOT NULL,
            designation VARCHAR(255) NOT NULL,
            quantite DECIMAL(10,2) NOT NULL,
            prix_unitaire DECIMAL(10,2) NOT NULL,
            date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ville_id) REFERENCES villes(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Table 'besoins' créée\n";
    
    // Table dons
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('nature', 'matériaux', 'argent') NOT NULL,
            designation VARCHAR(255) NOT NULL,
            quantite DECIMAL(10,2) NOT NULL,
            date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Table 'dons' créée\n";
    
    // Table dispatch
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dispatch (
            id INT AUTO_INCREMENT PRIMARY KEY,
            don_id INT NOT NULL,
            besoin_id INT NOT NULL,
            quantite_attribuee DECIMAL(10,2) NOT NULL,
            date_dispatch DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (don_id) REFERENCES dons(id) ON DELETE CASCADE,
            FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Table 'dispatch' créée\n";
    
    echo "\n✅ Base de données initialisée avec succès!\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la configuration de la base de données: " . $e->getMessage() . "\n";
    exit(1);
}
?>
