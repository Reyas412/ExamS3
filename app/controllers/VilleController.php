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
            Flight::redirect('/villes?error=' . urlencode('Ville non trouvee'));
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
            'title' => 'Details: ' . htmlspecialchars($ville['nom']),
            'active' => 'villes'
        ]);
    }

    public static function create() {
        // Si c'est une requete GET, afficher le formulaire
        if (Flight::request()->method == 'GET') {
            $regions = Region::getAll();
            Flight::render('villes_create', [
                'regions' => $regions
            ], 'body_content');
            Flight::render('layout', [
                'title' => 'Ajouter une Ville',
                'active' => 'villes'
            ]);
            return;
        }
        
        // POST - traiter les donnees
        $nom = Flight::request()->data->nom;
        $region_id = Flight::request()->data->region_id;
        
        if (empty($nom) || empty($region_id)) {
            Flight::redirect('/villes?error=' . urlencode('Le nom de la ville et la region sont requis'));
            return;
        }

        try {
            Ville::create($nom, $region_id);
            Flight::redirect('/villes?success=' . urlencode('Ville ajoutee avec succes'));
        } catch (Exception $e) {
            Flight::redirect('/villes?error=' . urlencode('Erreur: cette ville existe deja'));
        }
    }

    public static function edit($id) {
        $ville = Ville::getById($id);
        if (!$ville) {
            Flight::redirect('/villes?error=' . urlencode('Ville non trouvee'));
            return;
        }
        $regions = Region::getAll();
        Flight::render('ville_edit', [
            'ville' => $ville,
            'regions' => $regions
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Modifier une Ville',
            'active' => 'villes'
        ]);
    }
    
    public static function update($id) {
        $nom = Flight::request()->data->nom;
        $region_id = Flight::request()->data->region_id;
        
        if (empty($nom) || empty($region_id)) {
            Flight::redirect('/villes/edit/' . $id . '?error=' . urlencode('Le nom et la region sont requis'));
            return;
        }
        
        try {
            Ville::update($id, $nom, $region_id);
            Flight::redirect('/villes?success=' . urlencode('Ville modifiee avec succes'));
        } catch (Exception $e) {
            Flight::redirect('/villes/edit/' . $id . '?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }

    public static function delete($id) {
        try {
            Ville::delete($id);
            Flight::redirect('/villes?success=' . urlencode('Ville supprimee'));
        } catch (Exception $e) {
            Flight::redirect('/villes?error=' . urlencode('Erreur lors de la suppression'));
        }
    }
}
