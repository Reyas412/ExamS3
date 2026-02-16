<?php
/**
 * Script de mise à jour pour le système d'achats
 * Ajoute les tables et achats
 */

require_once __DIR__ . '/app/config/database.php';

$pdo = getDatabase();

echo "=== Migration: Système d'achats ===\n\n";

// 1. Ajouter les colonnes manquantes à la table besoins
echo "1. Modification de la table besoins...\n";

try {
    // Ajouter quantite_satisfaite si pas exists
    $pdo->exec("ALTER TABLE besoins ADD COLUMN IF NOT EXISTS quantite_satisfaite DECIMAL(10,2) DEFAULT 0");
    echo "   - colonne quantite_satisfaite ajoutée\n";
} catch (Exception $e) {
    echo "   - quantite_satisfaite: " . $e->getMessage() . "\n";
}

try {
    // Ajouter montant_restant si pas exists
    $pdo->exec("ALTER TABLE besoins ADD COLUMN IF NOT EXISTS montant_restant DECIMAL(10,2) DEFAULT 0");
    echo "   - colonne montant_restant ajoutée\n";
} catch (Exception $e) {
    echo "   - montant_restant: " . $e->getMessage() . "\n";
}

// 2. Ajouter colonne montant_restant à la table dons
echo "\n2. Modification de la table dons...\n";

try {
    $pdo->exec("ALTER TABLE dons ADD COLUMN IF NOT EXISTS montant_restant DECIMAL(10,2) DEFAULT 0");
    echo "   - colonne montant_restant ajoutée\n";
} catch (Exception $e) {
    echo "   - montant_restant: " . $e->getMessage() . "\n";
}

// 3. Créer la table config (utilisant config_key au lieu de 'key')
echo "\n3. Création de la table config...\n";

try {
    $pdo->exec("DROP TABLE IF EXISTS config");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS config (
            config_key VARCHAR(255) PRIMARY KEY,
            value TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   - table config créée\n";
} catch (Exception $e) {
    echo "   - table config: " . $e->getMessage() . "\n";
}

// Insérer la valeur par défaut des frais d'achat
$stmt = $pdo->prepare("INSERT IGNORE INTO config (config_key, value) VALUES ('purchase_fee_percent', '10')");
$stmt->execute();
echo "   - valeur par défaut purchase_fee_percent = 10%\n";

// 4. Créer la table achats
echo "\n4. Création de la table achats...\n";

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS achat_dispatch");
    $pdo->exec("DROP TABLE IF EXISTS achats");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS achats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            besoin_id INT NOT NULL,
            ville_id INT NOT NULL,
            quantite DECIMAL(10,2) NOT NULL,
            montant_base DECIMAL(10,2) NOT NULL,
            frais_percent DECIMAL(5,2) DEFAULT 0,
            montant_total DECIMAL(10,2) NOT NULL,
            status ENUM('simule', 'valide') DEFAULT 'simule',
            created_by VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE,
            FOREIGN KEY (ville_id) REFERENCES villes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   - table achats créée\n";
} catch (Exception $e) {
    echo "   - table achats: " . $e->getMessage() . "\n";
}

// 5. Créer la table achat_dispatch
echo "\n5. Création de la table achat_dispatch...\n";

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS achat_dispatch (
            id INT AUTO_INCREMENT PRIMARY KEY,
            achat_id INT NOT NULL,
            don_id INT NOT NULL,
            montant_utilise DECIMAL(10,2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (achat_id) REFERENCES achats(id) ON DELETE CASCADE,
            FOREIGN KEY (don_id) REFERENCES dons(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   - table achat_dispatch créée\n";
} catch (Exception $e) {
    echo "   - table achat_dispatch: " . $e->getMessage() . "\n";
}

// 6. Mettre à jour les montants restants existants
echo "\n6. Mise à jour des montants restants...\n";

// Mettre à jour montant_restant dans besoins (basé sur prix_unitaire et quantite - quantite_satisfaite)
$stmt = $pdo->query("SELECT id, quantite, quantite_satisfaite, prix_unitaire FROM besoins");
$besoins = $stmt->fetchAll();
foreach ($besoins as $b) {
    $quantite_restante = floatval($b['quantite']) - floatval($b['quantite_satisfaite']);
    $montant_restant = $quantite_restante * floatval($b['prix_unitaire']);
    $pdo->prepare("UPDATE besoins SET montant_restant = ? WHERE id = ?")
        ->execute([round($montant_restant, 2), $b['id']]);
}
echo "   - " . count($besoins) . " besoins mis à jour\n";

// Mettre à jour montant_restant dans dons
$stmt = $pdo->query("SELECT id, quantite FROM dons");
$dons = $stmt->fetchAll();
foreach ($dons as $d) {
    $pdo->prepare("UPDATE dons SET montant_restant = quantite WHERE id = ?")
        ->execute([$d['id']]);
}
echo "   - " . count($dons) . " dons mis à jour\n";

echo "\n=== Migration terminée avec succès! ===\n";
