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

    public static function create() {
        $nom = Flight::request()->data->nom;
        $region_id = Flight::request()->data->region_id;
        
        if (empty($nom) || empty($region_id)) {
            Flight::redirect('/villes?error=' . urlencode('Le nom de la ville et la région sont requis'));
            return;
        }

        try {
            Ville::create($nom, $region_id);
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
