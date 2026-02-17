<?php
/**
 * Migration - Ajouter la table achats
 * Cette table stocke les achats de marchandises pour couvrir les besoins restants
 */

require_once __DIR__ . '/app/config/database.php';

try {
    $pdo = getDatabase();
    
    echo "Création de la table achats...\n";
    
    // Vérifier si la table existe déjà
    $stmt = $pdo->query("SHOW TABLES LIKE 'achats'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "✓ Table 'achats' existe déjà.\n";
    } else {
        // Créer la table achats
        $pdo->exec("
            CREATE TABLE achats (
                id INT AUTO_INCREMENT PRIMARY KEY,
                besoin_id INT NOT NULL,
                quantite_achetee DECIMAL(10,2) NOT NULL,
                montant_base DECIMAL(12,2) NOT NULL,
                idconfig INT NOT NULL,
                montant_frais DECIMAL(12,2) NOT NULL,
                montant_total DECIMAL(12,2) NOT NULL,
                date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE,
                FOREIGN KEY (idconfig) REFERENCES config(id) ON DELETE CASCADE
            )
        ");
        echo "✓ Table 'achats' créée avec succès.\n";
    }
    
    // Vérifier/Créer la table config pour stocker les frais d'achat
    $stmt = $pdo->query("SHOW TABLES LIKE 'config'");
    $configExists = $stmt->rowCount() > 0;
    
    if (!$configExists) {
        $pdo->exec("
            CREATE TABLE config (
                id INT PRIMARY KEY,
                frais_achat DECIMAL(5,2) NOT NULL
            )
        ");

        // Insérer les frais d'achat par défaut (10%)
        $pdo->exec("INSERT INTO config (id, frais_achat) VALUES (1, 10)");
        echo "✓ Table 'config' créée.\n";
    } else {
        // Vérifier si la config par défaut existe
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM config WHERE id = 1");
        $result = $stmt->fetch();

        if ($result['count'] == 0) {
            $pdo->exec("INSERT INTO config (id, frais_achat) VALUES (1, 10)");
            echo "✓ Ligne 'config' ajoutée.\n";
        }
    }
    
    echo "\n✅ Migration complétée avec succès!\n";
    echo "\nTables créées/vérifiées:\n";
    echo "  • achats - Enregistrement des achats de marchandises\n";
    echo "  • config - Configuration de l'application (frais_achat: 10%)\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>
