<?php
/**
 * Script de mise à jour V2 - Achats avec dons argent
 */

require_once __DIR__ . '/app/config/database.php';

$pdo = getDatabase();

echo "Mise à jour V2 - Achats avec dons argent\n\n";

// Créer la table config si elle n'existe pas
$pdo->exec("
    CREATE TABLE IF NOT EXISTS config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cle VARCHAR(50) NOT NULL UNIQUE,
        valeur VARCHAR(255) NOT NULL
    )
");

echo "Table 'config' créée.\n";

// Insérer les paramètres de configuration
$configs = [
    ['cle' => 'frais_achat', 'valeur' => '10'],
    ['cle' => 'devise', 'valeur' => 'Ariary']
];

foreach ($configs as $config) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO config (cle, valeur) VALUES (?, ?)");
    $stmt->execute([$config['cle'], $config['valeur']]);
}

echo "Paramètres de configuration insérés.\n";

// Créer la table achats
$pdo->exec("
    CREATE TABLE IF NOT EXISTS achats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        besoin_id INT NOT NULL,
        don_id INT,
        montant_utilise DECIMAL(10,2) NOT NULL,
        frais DECIMAL(5,2) NOT NULL,
        montant_total DECIMAL(10,2) NOT NULL,
        quantiteAchetee DECIMAL(10,2) NOT NULL,
        date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE,
        FOREIGN KEY (don_id) REFERENCES dons(id) ON DELETE SET NULL
    )
");

echo "Table 'achats' créée.\n";

// Ajouter le type 'achat' à la table dispatch pour标识购买
$pdo->exec("ALTER TABLE dispatch ADD COLUMN type_dispatch ENUM('don', 'achat') DEFAULT 'don'");
$pdo->exec("UPDATE dispatch SET type_dispatch = 'don' WHERE type_dispatch IS NULL");

echo "Colonne 'type_dispatch' ajoutée à la table 'dispatch'.\n\n";

echo "Mise à jour V2 terminée avec succès!\n";
