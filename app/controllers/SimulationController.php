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
            LEFT JOIN dispatch di ON di.besoin_id = b.id AND di.type_dispatch = 'don'
            WHERE b.type IN ('nature', 'materiaux')
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
            SELECT a.*, b.designation, v.nom AS ville_nom
            FROM achats a
            JOIN besoins b ON a.besoin_id = b.id
            JOIN villes v ON b.ville_id = v.id
            ORDER BY a.date_achat DESC
        ")->fetchAll();
        
        // Récupérer l'argent disponible
        $argentTotal = $pdo->query("
            SELECT COALESCE(SUM(quantite), 0) - COALESCE(SUM(d.quantite_distribuee), 0) as argent_restant
            FROM dons d
            LEFT JOIN (
                SELECT don_id, SUM(quantite_attribuee) as quantite_distribuee
                FROM dispatch
                GROUP BY don_id
            ) d ON d.id = d.don_id
            WHERE d.type = 'argent'
        ")->fetch();
        
        $argentRestant = floatval($argentTotal['argent_restant'] ?? 0);
        
        // Récupérer les frais d'achat
        $fraisStmt = $pdo->query("SELECT valeur FROM config WHERE cle = 'frais_achat'");
        $fraisAchat = floatval($fraisStmt->fetch()['valeur'] ?? 10);
        
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
            $fraisStmt = $pdo->query("SELECT valeur FROM config WHERE cle = 'frais_achat'");
            $fraisAchat = floatval($fraisStmt->fetch()['valeur'] ?? 10);
            
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
                WHERE besoin_id = ? AND type_dispatch = 'don'
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
                SELECT COALESCE(SUM(montant_utilise), 0) as total
                FROM achats
            ");
            $argentUtilise = floatval($utiliseStmt->fetch()['total']);
            $argentRestant = $argentTotal - $argentUtilise;
            
            if ($argentRestant < $montantAvecFrais) {
                throw new Exception("Argent insuffisant. Montant nécessaire: " . number_format($montantAvecFrais, 0, ',', ' '));
            }
            
            // Créer l'achat
            $insertAchat = $pdo->prepare("
                INSERT INTO achats (besoin_id, montant_utilise, frais, montant_total, quantiteAchetee)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insertAchat->execute([
                $besoin_id,
                $montantBase,
                $fraisAchat,
                $montantAvecFrais,
                $quantiteRestante
            ]);
            
            // Créer le dispatch
            $insertDispatch = $pdo->prepare(
                "INSERT INTO dispatch (don_id, besoin_id, quantite_attribuee) VALUES (NULL, ?, ?)"
            );
            $insertDispatch->execute([$besoin_id, $quantiteRestante]);
            
            $pdo->commit();
            
            Flight::redirect('/simulation?success=' . urlencode("Achat validé pour {$quantiteRestante} unités"));
            
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
