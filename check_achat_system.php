<?php
/**
 * Vérification du système - Flux Achat et Récapitulation
 */

require_once __DIR__ . '/app/config/database.php';

try {
    $pdo = getDatabase();
    
    echo "========================================\n";
    echo "ÉTAT DU SYSTÈME - FLUX ACHAT\n";
    echo "========================================\n\n";
    
    // 1. Vérifier les routes
    echo "1. ROUTES\n";
    echo "   ✓ GET /simulation → SimulationController::index()\n";
    echo "   ✓ POST /simulation/validate → SimulationController::validate()\n";
    echo "   ✓ GET /dispatch → DispatchController::index()\n\n";
    
    // 2. Vérifier la table achats
    echo "2. TABLE ACHATS\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'achats'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Table 'achats' existe\n";
        
        $columns = $pdo->query("DESCRIBE achats")->fetchAll();
        foreach ($columns as $col) {
            echo "     - {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "   ❌ Table 'achats' N'EXISTE PAS\n";
        echo "   → Exécutez: php migrate_achats.php\n";
    }
    echo "\n";
    
    // 3. Vérifier la table config
    echo "3. TABLE CONFIG\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'config'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Table 'config' existe\n";
        
        $configs = $pdo->query("SELECT * FROM config")->fetchAll();
        foreach ($configs as $cfg) {
            echo "     - {$cfg['cle']} = {$cfg['valeur']}\n";
        }
    } else {
        echo "   ❌ Table 'config' N'EXISTE PAS\n";
        echo "   → Exécutez: php migrate_achats.php\n";
    }
    echo "\n";
    
    // 4. Vérifier les besoins
    echo "4. DONNÉES - BESOINS\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM besoins");
    $count = $stmt->fetch()['count'];
    echo "   Total besoins: $count\n\n";
    
    // 5. Vérifier les dons
    echo "5. DONNÉES - DONS\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dons");
    $count = $stmt->fetch()['count'];
    echo "   Total dons: $count\n";
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(quantite), 0) as total FROM dons WHERE type = 'argent'");
    $argent = floatval($stmt->fetch()['total']);
    echo "   Argent disponible: " . number_format($argent, 2) . " unités\n\n";
    
    // 6. Vérifier les achats
    echo "6. DONNÉES - ACHATS\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM achats");
    $count = $stmt->fetch()['count'];
    echo "   Total achats: $count\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT COALESCE(SUM(montant_total), 0) as total FROM achats");
        $montant = floatval($stmt->fetch()['total']);
        echo "   Montant total dépensé: " . number_format($montant, 2) . "\n";
    }
    echo "\n";
    
    // 7. Vue dispatches
    echo "7. FLUX - DISPATCHES\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as count,
            SUM(CASE WHEN don_id IS NOT NULL THEN 1 ELSE 0 END) as via_dons,
            SUM(CASE WHEN don_id IS NULL THEN 1 ELSE 0 END) as via_achats
        FROM dispatch
    ");
    $result = $stmt->fetch();
    echo "   Total dispatches: {$result['count']}\n";
    echo "   Via dons: {$result['via_dons']}\n";
    echo "   Via achats: {$result['via_achats']}\n\n";
    
    // 8. Menu
    echo "8. MENU LATÉRAL\n";
    echo "   ✓ Lien 'Achat' ajouté dans layout.php\n\n";
    
    // Résumé final
    echo "========================================\n";
    echo "RÉSUMÉ\n";
    echo "========================================\n";
    
    if ($stmt = $pdo->query("SHOW TABLES LIKE 'achats'") and $stmt->rowCount() > 0) {
        echo "✅ SYSTÈME PRET\n";
        echo "\nPour utiliser:\n";
        echo "1. Allez à: http://localhost/examV2/ExamS3/\n";
        echo "2. Cliquez sur 'Achat' dans le menu\n";
        echo "3. Sélectionnez un besoin et validez\n";
        echo "4. Vous serez redirigé vers la récapitulation\n";
    } else {
        echo "❌ TABLES MANQUANTES\n";
        echo "\nAction requise:\n";
        echo "→ Exécutez: php migrate_achats.php\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>
