<?php
/**
 * Script de test - Vérifier la structure de la base de données
 * Exécuter ce fichier pour vérifier que tout est correct
 */

require_once __DIR__ . '/app/config/database.php';

try {
    $pdo = getDatabase();
    
    echo "========================================\n";
    echo "VÉRIFICATION DE LA BASE DE DONNÉES\n";
    echo "========================================\n\n";
    
    // Vérifier les tables
    $stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gnbrc'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables présentes:\n";
    foreach ($tables as $table) {
        echo "  ✓ $table\n";
        
        // Afficher les colonnes de chaque table
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            echo "    - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";
    }
    
    // Vérifier les régions
    echo "========================================\n";
    echo "VÉRIFICATION DES DONNÉES\n";
    echo "========================================\n\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM regions");
    $result = $stmt->fetch();
    echo "Régions présentes: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM villes");
    $result = $stmt->fetch();
    echo "Villes présentes: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM besoins");
    $result = $stmt->fetch();
    echo "Besoins présents: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dons");
    $result = $stmt->fetch();
    echo "Dons présents: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dispatch");
    $result = $stmt->fetch();
    echo "Dispatch présents: {$result['count']}\n\n";
    
    // Tester une jointure
    echo "========================================\n";
    echo "TEST DE JOINTURE\n";
    echo "========================================\n\n";
    
    $stmt = $pdo->query("
        SELECT v.nom as ville, r.nom as region 
        FROM villes v 
        JOIN regions r ON v.idregion = r.id 
        LIMIT 5
    ");
    $results = $stmt->fetchAll();
    
    if (!empty($results)) {
        echo "Villes et leurs régions:\n";
        foreach ($results as $row) {
            echo "  • {$row['ville']} → {$row['region']}\n";
        }
    } else {
        echo "Aucune ville-région trouvée (données non importées)\n";
    }
    
    echo "\n✅ Base de données configurée correctement!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>
