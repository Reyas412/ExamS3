<?php
/**
 * Contrôleur Achat - Gestion des achats
 */
class AchatController {
    
    /**
     * Liste des achats
     */
    public static function index() {
        $ville_id = Flight::request()->query['ville_id'] ?? null;
        $achats = Achat::getAll($ville_id);
        
        // Récupérer les besoins restants qui peuvent être couverts
        $pdo = getDatabase();
        $params = [];
        $sql = "
            SELECT b.*, v.nom as ville_nom,
                   (b.quantite - COALESCE(b.quantite_satisfaite, 0)) as quantite_restante
            FROM besoins b
            LEFT JOIN villes v ON b.ville_id = v.id
            WHERE (b.quantite - COALESCE(b.quantite_satisfaite, 0)) > 0
        ";
        if ($ville_id) {
            $sql .= " AND b.ville_id = ?";
            $params[] = $ville_id;
        }
        $sql .= " ORDER BY v.nom, b.designation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $besoins = $stmt->fetchAll();
        
        Flight::render('achats', [
            'achats' => $achats,
            'besoins' => $besoins,
            'ville_id' => $ville_id
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Gestion des Achats',
            'active' => 'achats'
        ]);
    }
    
    /**
     * API: Obtenir les besoins restants
     * GET /api/besoins?ville_id=...&restants=1
     */
    public static function apiBesoins() {
        $ville_id = Flight::request()->query['ville_id'] ?? null;
        $restants = Flight::request()->query['restants'] ?? null;
        
        $pdo = getDatabase();
        
        $sql = "
            SELECT b.*, v.nom as ville_nom,
                   (b.quantite - COALESCE(b.quantite_satisfaite, 0)) as quantite_restante,
                   (b.montant_restant) as montant_restant
            FROM besoins b
            LEFT JOIN villes v ON b.ville_id = v.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($ville_id) {
            $sql .= " AND b.ville_id = ?";
            $params[] = $ville_id;
        }
        
        if ($restants) {
            $sql .= " AND (b.quantite - COALESCE(b.quantite_satisfaite, 0)) > 0";
        }
        
        $sql .= " ORDER BY b.date_saisie DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $besoins = $stmt->fetchAll();
        
        Flight::json($besoins);
    }
    
    /**
     * API: Simuler un achat
     * POST /api/achats/simuler
     */
    public static function apiSimuler() {
        $data = Flight::request()->data;
        $besoin_id = $data->besoin_id;
        $quantite = floatval($data->quantite);
        $frais_percent = isset($data->frais_percent) ? floatval($data->frais_percent) : null;
        
        if (!$besoin_id || $quantite <= 0) {
            Flight::json(['error' => 'Paramètres invalides'], 400);
            return;
        }
        
        try {
            $result = Achat::simuler($besoin_id, $quantite, $frais_percent);
            Flight::json($result);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * API: Valider un achat
     * POST /api/achats/valider
     */
    public static function apiValider() {
        $data = Flight::request()->data;
        $besoin_id = $data->besoin_id;
        $quantite = floatval($data->quantite);
        $frais_percent = isset($data->frais_percent) ? floatval($data->frais_percent) : null;
        
        if (!$besoin_id || $quantite <= 0) {
            Flight::json(['error' => 'Paramètres invalides'], 400);
            return;
        }
        
        try {
            $result = Achat::valider($besoin_id, $quantite, $frais_percent);
            Flight::json($result);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * API: Liste des achats
     * GET /api/achats?ville_id=...
     */
    public static function apiIndex() {
        $ville_id = Flight::request()->query['ville_id'] ?? null;
        
        $achats = Achat::getAll($ville_id);
        
        Flight::json($achats);
    }
    
    /**
     * API: Dons disponibles pour un besoin
     * GET /api/achats/dons-disponibles?besoin_id=...
     */
    public static function apiDonsDisponibles() {
        $besoin_id = Flight::request()->query['besoin_id'] ?? null;
        
        if (!$besoin_id) {
            Flight::json(['error' => 'besoin_id requis'], 400);
            return;
        }
        
        $pdo = getDatabase();
        
        // Obtenir les détails du besoin
        $stmt = $pdo->prepare("SELECT * FROM besoins WHERE id = ?");
        $stmt->execute([$besoin_id]);
        $besoin = $stmt->fetch();
        
        if (!$besoin) {
            Flight::json(['error' => 'Besoin non trouvé'], 404);
            return;
        }
        
        $dons = [];
        
        // Dons en nature/materiaux avec même designation
        $stmt = $pdo->prepare("
            SELECT d.*, 
                   d.quantite - COALESCE((SELECT SUM(quantite_attribuee) FROM dispatch WHERE don_id = d.id), 0) as quantite_restante
            FROM dons d
            WHERE d.type = ? 
            AND d.designation = ?
            AND (d.quantite - COALESCE((SELECT SUM(quantite_attribuee) FROM dispatch WHERE don_id = d.id), 0)) > 0
            ORDER BY d.date_saisie ASC
        ");
        $stmt->execute([$besoin['type'], $besoin['designation']]);
        $dons_nature = $stmt->fetchAll();
        
        foreach ($dons_nature as $don) {
            $dons[] = [
                'id' => $don['id'],
                'type' => $don['type'],
                'designation' => $don['designation'],
                'quantite_restante' => floatval($don['quantite_restante']),
                'montant_disponible' => floatval($don['quantite_restante']) * floatval($besoin['prix_unitaire'])
            ];
        }
        
        // Dons en argent (toujours disponibles pour tous les besoins)
        $stmt = $pdo->query("
            SELECT d.*
            FROM dons d
            WHERE d.type = 'argent' 
            AND d.quantite > 0
            ORDER BY d.date_saisie ASC
        ");
        $dons_argent = $stmt->fetchAll();
        
        foreach ($dons_argent as $don) {
            $dons[] = [
                'id' => $don['id'],
                'type' => 'argent',
                'designation' => 'Argent',
                'quantite_restante' => null,
                'montant_disponible' => floatval($don['quantite'])
            ];
        }
        
        Flight::json($dons);
    }
    
    /**
     * API: Couvrir un besoin avec les dons disponibles
     * POST /api/achats/couvrir-avec-dons
     */
    public static function apiCouvrirAvecDons() {
        $data = Flight::request()->data;
        $besoin_id = $data->besoin_id;
        
        if (!$besoin_id) {
            Flight::json(['error' => 'besoin_id requis'], 400);
            return;
        }
        
        try {
            $result = Achat::couvrirAvecDons($besoin_id);
            Flight::json($result);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * API: Récapitulatif
     * GET /api/recap?ville_id=...
     */
    public static function apiRecap() {
        $ville_id = Flight::request()->query['ville_id'] ?? null;
        
        $pdo = getDatabase();
        
        // Total besoins
        $sql = "SELECT COALESCE(SUM(quantite), 0) FROM besoins";
        $params = [];
        if ($ville_id) {
            $sql .= " WHERE ville_id = ?";
            $params[] = $ville_id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $total_besoins = floatval($stmt->fetchColumn());
        
        // Besoins satisfaits
        $sql = "SELECT COALESCE(SUM(quantite_satisfaite), 0) FROM besoins";
        if ($ville_id) {
            $sql .= " WHERE ville_id = ?";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $satisfaits = floatval($stmt->fetchColumn());
        
        // Besoins restants
        $restants = $total_besoins - $satisfaits;
        
        Flight::json([
            'total_besoins' => $total_besoins,
            'satisfaits' => $satisfaits,
            'restants' => $restants,
            'taux_satisfaction' => $total_besoins > 0 ? round(($satisfaits / $total_besoins) * 100, 1) : 0
        ]);
    }
    
    /**
     * API: Configuration des frais
     * GET /api/config/frais
     */
    public static function apiGetFrais() {
        $frais = Achat::getFraisPercent();
        Flight::json(['frais_percent' => $frais]);
    }
    
    /**
     * API: Mettre à jour les frais
     * POST /api/config/frais
     */
    public static function apiSetFrais() {
        $data = Flight::request()->data;
        $frais_percent = floatval($data->frais_percent);
        
        if ($frais_percent < 0 || $frais_percent > 100) {
            Flight::json(['error' => 'Le pourcentage doit être entre 0 et 100'], 400);
            return;
        }
        
        Achat::setFraisPercent($frais_percent);
        Flight::json(['success' => true, 'frais_percent' => $frais_percent]);
    }
    
    /**
     * Supprimer un achat
     */
    public static function delete($id) {
        try {
            Achat::delete($id);
            Flight::redirect('/achats?success=' . urlencode('Achat supprimé'));
        } catch (Exception $e) {
            Flight::redirect('/achats?error=' . urlencode($e->getMessage()));
        }
    }
}
