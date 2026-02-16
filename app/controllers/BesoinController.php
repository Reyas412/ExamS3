<?php
class BesoinController {
    
    public static function index() {
        $besoins = Besoin::getAll();
        $villes = Ville::getAll();
        
        // Appliquer les filtres
        $ville_id = isset($_GET['ville_id']) ? $_GET['ville_id'] : null;
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $restants = isset($_GET['restants']) ? $_GET['restants'] : null;
        
        if ($ville_id || $type || $restants) {
            $besoins = Besoin::getFiltered($ville_id, $type, $restants);
        }
        
        Flight::render('besoins', [
            'besoins' => $besoins,
            'villes' => $villes,
            'ville_id' => $ville_id
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Gestion des Besoins',
            'active' => 'besoins'
        ]);
    }

    public static function create() {
        // Si c'est une requete GET, afficher le formulaire
        if (Flight::request()->method == 'GET') {
            $villes = Ville::getAll();
            $besoins = Besoin::getAll();
            Flight::render('besoins_create', [
                'villes' => $villes,
                'besoins' => $besoins
            ], 'body_content');
            Flight::render('layout', [
                'title' => 'Ajouter un Besoin',
                'active' => 'besoins'
            ]);
            return;
        }
        
        // POST - traiter les donnees
        $data = Flight::request()->data;
        
        $ville_id = $data->ville_id;
        $type = $data->type;
        $designation = $data->designation;
        $quantite = $data->quantite;
        $prix_unitaire = $data->prix_unitaire;
        $date_saisie = $data->date_saisie ?: null;

        if (empty($ville_id) || empty($type) || empty($designation) || empty($quantite) || empty($prix_unitaire)) {
            Flight::redirect('/besoins?error=' . urlencode('Tous les champs obligatoires doivent être remplis'));
            return;
        }

        try {
            Besoin::create($ville_id, $type, $designation, $quantite, $prix_unitaire, $date_saisie);
            Flight::redirect('/besoins?success=' . urlencode('Besoin ajouté avec succès'));
        } catch (Exception $e) {
            Flight::redirect('/besoins?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }

    public static function edit($id) {
        $besoin = Besoin::getById($id);
        if (!$besoin) {
            Flight::redirect('/besoins?error=' . urlencode('Besoin non trouvé'));
            return;
        }
        $besoins = Besoin::getAll();
        $villes = Ville::getAll();
        Flight::render('besoins_edit', [
            'besoin' => $besoin,
            'besoins' => $besoins,
            'villes' => $villes
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Modifier un Besoin',
            'active' => 'besoins'
        ]);
    }

    public static function update($id) {
        $data = Flight::request()->data;
        
        $ville_id = $data->ville_id;
        $type = $data->type;
        $designation = $data->designation;
        $quantite = $data->quantite;
        $prix_unitaire = $data->prix_unitaire;
        $date_saisie = $data->date_saisie ?: null;

        if (empty($ville_id) || empty($type) || empty($designation) || empty($quantite) || empty($prix_unitaire)) {
            Flight::redirect('/besoins/edit/' . $id . '?error=' . urlencode('Tous les champs obligatoires doivent être remplis'));
            return;
        }

        try {
            Besoin::update($id, $ville_id, $type, $designation, $quantite, $prix_unitaire, $date_saisie);
            Flight::redirect('/besoins?success=' . urlencode('Besoin modifié avec succès'));
        } catch (Exception $e) {
            Flight::redirect('/besoins/edit/' . $id . '?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }

    public static function delete($id) {
        try {
            Besoin::delete($id);
            Flight::redirect('/besoins?success=' . urlencode('Besoin supprimé'));
        } catch (Exception $e) {
            Flight::redirect('/besoins?error=' . urlencode('Erreur lors de la suppression'));
        }
    }
}
