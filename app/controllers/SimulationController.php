<?php
class SimulationController {
    
    /**
     * Affiche la page de simulation d'achat
     */
    public static function index() {
        $pdo = getDatabase();
        
        // Récupérer les villes pour le filtre
        $villes = $pdo->query("SELECT id, nom FROM villes ORDER BY nom")->fetchAll();
        
        // Récupérer les besoins restants (non couverts ou partiellement couverts)
        $besoins = $pdo->query("
            SELECT b.*, v.nom AS ville_nom,
                   COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_couverte
            FROM besoins b
            JOIN villes v ON b.ville_id = v.id
            LEFT JOIN dispatch di ON di.besoin_id = b.id AND di.don_id IS NOT NULL
            WHERE b.type IN ('nature', 'materiaux', 'matériaux')
            GROUP BY b.id
            HAVING COALESCE(SUM(di.quantite_attribuee), 0) < b.quantite
            ORDER BY b.quantite - COALESCE(SUM(di.quantite_attribuee), 0) DESC
        ")->fetchAll();
        
        // Ajouter la quantité restante à chaque besoin
        foreach ($besoins as &$b) {
            $b['quantite_restante'] = floatval($b['quantite']) - floatval($b['quantite_couverte']);
            $b['montant_restant'] = $b['quantite_restante'] * floatval($b['prix_unitaire']);
        }
        
        // Récupérer les achats effectués
        $achats = $pdo->query("
            SELECT a.*, b.designation, v.nom AS ville_nom, c.frais_achat AS frais_achat
            FROM achats a
            JOIN besoins b ON a.besoin_id = b.id
            JOIN villes v ON b.ville_id = v.id
            JOIN config c ON a.idconfig = c.id
            ORDER BY a.date_achat DESC
        ")->fetchAll();
        
        // Récupérer l'argent disponible
        $argentTotal = $pdo->query("
            SELECT COALESCE(SUM(quantite), 0) as total
            FROM dons WHERE type = 'argent'
        ")->fetch();
        $argentBrut = floatval($argentTotal['total'] ?? 0);

        $argentUtilise = $pdo->query("
            SELECT COALESCE(SUM(montant_total), 0) as total
            FROM achats
        ")->fetch();
        $argentRestant = $argentBrut - floatval($argentUtilise['total'] ?? 0);

        // Récupérer la configuration (frais d'achat)
        $config = $pdo->query("SELECT id, frais_achat FROM config ORDER BY id ASC LIMIT 1")->fetch();
        if (!$config) {
            $pdo->prepare("INSERT INTO config (id, frais_achat) VALUES (1, 10)")->execute();
            $config = ['id' => 1, 'frais_achat' => 10];
        }
        $fraisAchat = floatval($config['frais_achat'] ?? 10);
        
        Flight::render('simulation', [
            'besoins' => $besoins,
            'villes' => $villes,
            'achats' => $achats,
            'argent_restant' => $argentRestant,
            'frais_achat' => $fraisAchat
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Simulation Achat',
            'active' => 'simulation'
        ]);
    }
    
    /**
     * Valide un achat (étape 2: validation)
     */
    public static function validate() {
        $data = Flight::request()->data;
        $besoin_id = intval($data->besoin_id);
        $pdo = getDatabase();
        
        try {
            $pdo->beginTransaction();
            
            // Récupérer les frais d'achat
            $config = $pdo->query("SELECT id, frais_achat FROM config ORDER BY id ASC LIMIT 1")->fetch();
            if (!$config) {
                $pdo->prepare("INSERT INTO config (id, frais_achat) VALUES (1, 10)")->execute();
                $config = ['id' => 1, 'frais_achat' => 10];
            }
            $configId = intval($config['id']);
            $fraisAchat = floatval($config['frais_achat'] ?? 10);
            
            // Récupérer le besoin
            $stmt = $pdo->prepare("
                SELECT b.*, 
                       COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_couverte
                FROM besoins b
                LEFT JOIN dispatch di ON di.besoin_id = b.id
                WHERE b.id = ?
                GROUP BY b.id
            ");
            $stmt->execute([$besoin_id]);
            $besoin = $stmt->fetch();
            
            if (!$besoin) {
                throw new Exception("Besoin non trouvé");
            }
            
            $quantiteRestante = floatval($besoin['quantite']) - floatval($besoin['quantite_couverte']);
            
            if ($quantiteRestante <= 0) {
                throw new Exception("Ce besoin est déjà totalement couvert");
            }
            
            // Vérifier si déjà couvert par des dons (pas par des achats)
            $donCouvert = $pdo->prepare("
                SELECT COALESCE(SUM(quantite_attribuee), 0) as total
                FROM dispatch
                WHERE besoin_id = ? AND don_id IS NOT NULL
            ");
            $donCouvert->execute([$besoin_id]);
            $quantiteDon = floatval($donCouvert->fetch()['total']);
            
            if ($quantiteDon > 0) {
                throw new Exception("Ce besoin est deja couvert par des dons (" . number_format($quantiteDon, 0, ',', ' ') . " unites). Utilisez d'abord le dispatch classique.");
            }
            
            // Calculer le montant avec frais
            $montantBase = $quantiteRestante * floatval($besoin['prix_unitaire']);
            $montantAvecFrais = $montantBase * (1 + $fraisAchat / 100);
            
            // Vérifier l'argent disponible
            $argentStmt = $pdo->query("
                SELECT COALESCE(SUM(quantite), 0) as total
                FROM dons WHERE type = 'argent'
            ");
            $argentTotal = floatval($argentStmt->fetch()['total']);
            
            $utiliseStmt = $pdo->query("
                SELECT COALESCE(SUM(montant_total), 0) as total
                FROM achats
            ");
            $argentUtilise = floatval($utiliseStmt->fetch()['total']);
            $argentRestant = $argentTotal - $argentUtilise;
            
            if ($argentRestant < $montantAvecFrais) {
                throw new Exception("Argent insuffisant. Montant nécessaire: " . number_format($montantAvecFrais, 0, ',', ' '));
            }
            
            // Créer l'achat
            $insertAchat = $pdo->prepare("
                INSERT INTO achats (besoin_id, quantite_achetee, montant_base, idconfig, montant_frais, montant_total)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $montantFrais = $montantBase * ($fraisAchat / 100);
            $insertAchat->execute([
                $besoin_id,
                $quantiteRestante,
                $montantBase,
                $configId,
                $montantFrais,
                $montantAvecFrais
            ]);
            
            // Créer le dispatch pour l'achat
            $insertDispatch = $pdo->prepare(
                "INSERT INTO dispatch (don_id, besoin_id, quantite_attribuee) VALUES (NULL, ?, ?)"
            );
            $insertDispatch->execute([$besoin_id, $quantiteRestante]);
            
            $pdo->commit();
            
            Flight::redirect('/dispatch?success=' . urlencode("Achat validé pour " . number_format($quantiteRestante, 2, ',', ' ') . " unités"));
            
        } catch (Exception $e) {
            $pdo->rollBack();
            Flight::redirect('/simulation?error=' . urlencode($e->getMessage()));
        }
    }
    
    /**
     * Retourne les données de récépissé en JSON (Ajax)
     */
    public static function recapJson() {
        $pdo = getDatabase();
        
        // Total besoins en montant
        $totalBesoins = $pdo->query("
            SELECT COALESCE(SUM(quantite * prix_unitaire), 0) as total
            FROM besoins
        ")->fetch()['total'];
        
        // Besoins satisfaits en montant (depuis dispatch)
        $satisfaits = $pdo->query("
            SELECT COALESCE(SUM(di.quantite_attribuee * b.prix_unitaire), 0) as total
            FROM dispatch di
            JOIN besoins b ON di.besoin_id = b.id
            WHERE di.don_id IS NOT NULL
        ")->fetch()['total'];
        
        // Argent utilisé pour les achats
        $argentUtilise = $pdo->query("
            SELECT COALESCE(SUM(montant_total), 0) as total
            FROM achats
        ")->fetch()['total'];
        
        // Ajouter l'argent utilisé aux besoins satisfaits
        $satisfaits += $argentUtilise;
        
        // Besoins restants
        $restants = floatval($totalBesoins) - floatval($satisfaits);
        
        // Retourner en JSON
        header('Content-Type: application/json');
        echo json_encode([
            'total_besoins' => number_format(floatval($totalBesoins), 0, ',', ' '),
            'satisfaits' => number_format(floatval($satisfaits), 0, ',', ' '),
            'restants' => number_format(floatval($restants), 0, ',', ' '),
            'taux' => floatval($totalBesoins) > 0 ? round((floatval($satisfaits) / floatval($totalBesoins)) * 100, 1) : 0
        ]);
    }
}
