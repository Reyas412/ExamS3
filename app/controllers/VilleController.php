<?php
class VilleController {
    
    public static function index() {
        $villes = Ville::getAll();
        $regions = Region::getAll();
        Flight::render('villes', ['villes' => $villes, 'regions' => $regions], 'body_content');
        Flight::render('layout', [
            'title' => 'Gestion des Villes',
            'active' => 'villes'
        ]);
    }

    public static function view($id) {
        $ville = Ville::getById($id);
        if (!$ville) {
            Flight::redirect('/villes?error=' . urlencode('Ville non trouvée'));
            return;
        }
        
        $besoins = Besoin::getByVilleId($id);
        $stats = DispatchModel::getStatsByVilleId($id);
        
        Flight::render('ville_detail', [
            'ville' => $ville,
            'besoins' => $besoins,
            'stats' => $stats
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Détails: ' . htmlspecialchars($ville['nom']),
            'active' => 'villes'
        ]);
    }

    public static function create() {
        $nom = Flight::request()->data->nom;
        $idregion = Flight::request()->data->idregion;
        
        if (empty($nom) || empty($idregion)) {
            Flight::redirect('/villes?error=' . urlencode('Le nom de la ville et la région sont requis'));
            return;
        }

        try {
            Ville::create($nom, $idregion);
            Flight::redirect('/villes?success=' . urlencode('Ville ajoutée avec succès'));
        } catch (Exception $e) {
            Flight::redirect('/villes?error=' . urlencode('Erreur: cette ville existe déjà'));
        }
    }

    public static function delete($id) {
        try {
            Ville::delete($id);
            Flight::redirect('/villes?success=' . urlencode('Ville supprimée'));
        } catch (Exception $e) {
            Flight::redirect('/villes?error=' . urlencode('Erreur lors de la suppression'));
        }
    }
}
