<?php
/**
 * Script de mise à jour de la base de données
 */

require_once __DIR__ . '/app/config/database.php';

$pdo = getDatabase();

echo "Mise à jour de la base de données...\n\n";

// Créer la table regions si elle n'existe pas
$pdo->exec("CREATE TABLE IF NOT EXISTS regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL UNIQUE
)");

echo "Table 'regions' créée.\n";

// Vérifier si la colonne region_id existe dans villes
$stmt = $pdo->query("DESCRIBE villes");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('region_id', $columns)) {
    // Ajouter la colonne region_id
    // D'abord, ajouter une région par défaut
    $pdo->exec("INSERT IGNORE INTO regions (nom) VALUES ('Analamanga')");
    
    // Ajouter la colonne region_id
    $pdo->exec("ALTER TABLE villes ADD COLUMN region_id INT NOT NULL DEFAULT 1");
    $pdo->exec("ALTER TABLE villes ADD FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE RESTRICT");
    
    echo "Colonne 'region_id' ajoutée à la table 'villes'.\n";
} else {
    echo "La colonne 'region_id' existe déjà.\n";
}

// Insérer les régions si elles n'existent pas
$regions = [
    'Analamanga', 'Vakinankaratra', 'Itasy', 'Bongolava', 'Sofia',
    'Boeny', 'Melaky', 'Alaotra-Mangoro', 'Analanjirofo', 'Atsinanana',
    'Atsimo-Atsinana', 'Ihorombe', 'Haute Matsiatra', 'Vatovavy-Fitovinany',
    'Atsimo-Andrefana', 'Androy', 'Anosy', 'Menabe', 'Diana', 'Sava'
];

foreach ($regions as $region) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO regions (nom) VALUES (?)");
        $stmt->execute([$region]);
    } catch (Exception $e) {
        // Ignore duplicate errors
    }
}

echo "Régions insérées.\n";

// Mettre à jour les villes existantes avec une région
$stmt = $pdo->query("SELECT id, nom FROM villes WHERE region_id IS NULL OR region_id = 0");
$villes = $stmt->fetchAll();

foreach ($villes as $ville) {
    // Mapper les villes existantes aux régions
    $regionMapping = [
        'Antananarivo' => 'Analamanga',
        'Antsirabe' => 'Vakinankaratra',
        'Toamasina' => 'Atsinanana',
        'Mahajanga' => 'Boeny'
    ];
    
    $regionNom = $regionMapping[$ville['nom']] ?? 'Analamanga';
    
    $stmt = $pdo->prepare("UPDATE villes v 
                           JOIN regions r ON r.nom = ? 
                           SET v.region_id = r.id 
                           WHERE v.id = ?");
    $stmt->execute([$regionNom, $ville['id']]);
}

echo "Villes mises à jour avec les régions.\n\n";
echo "Mise à jour terminée avec succès!\n";
