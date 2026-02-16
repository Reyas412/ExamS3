<?php
class RegionController {
    
    public static function index() {
        $regions = Region::getAll();
        Flight::render('regions', ['regions' => $regions], 'body_content');
        Flight::render('layout', [
            'title' => 'Gestion des Régions',
            'active' => 'regions'
        ]);
    }

    public static function view($id) {
        $region = Region::getById($id);
        if (!$region) {
            Flight::redirect('/regions?error=' . urlencode('Région non trouvée'));
            return;
        }
        
        $villes = Ville::getByRegionId($id);
        $stats = DispatchModel::getStatsByRegionId($id);
        
        Flight::render('region_detail', [
            'region' => $region,
            'villes' => $villes,
            'stats' => $stats
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Détails: ' . htmlspecialchars($region['nom']),
            'active' => 'regions'
        ]);
    }

    public static function create() {
        $nom = Flight::request()->data->nom;
        
        if (empty($nom)) {
            Flight::redirect('/regions?error=' . urlencode('Le nom de la région est requis'));
            return;
        }

        try {
            Region::create($nom);
            Flight::redirect('/regions?success=' . urlencode('Région ajoutée avec succès'));
        } catch (Exception $e) {
            Flight::redirect('/regions?error=' . urlencode('Erreur: cette région existe déjà'));
        }
    }

    public static function delete($id) {
        try {
            Region::delete($id);
            Flight::redirect('/regions?success=' . urlencode('Région supprimée'));
        } catch (Exception $e) {
            Flight::redirect('/regions?error=' . urlencode('Erreur lors de la suppression'));
        }
    }
}
