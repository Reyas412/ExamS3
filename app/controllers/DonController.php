<?php
class DonController {
    
    public static function index() {
        $dons = Don::getAll();
        Flight::render('dons', ['dons' => $dons], 'body_content');
        Flight::render('layout', [
            'title' => 'Gestion des Dons',
            'active' => 'dons'
        ]);
    }

    public static function create() {
        // Si c'est une requete GET, afficher le formulaire
        if (Flight::request()->method == 'GET') {
            $dons = Don::getAll();
            Flight::render('dons_create', [
                'dons' => $dons
            ], 'body_content');
            Flight::render('layout', [
                'title' => 'Ajouter un Don',
                'active' => 'dons'
            ]);
            return;
        }
        
        // POST - traiter les donnees
        $data = Flight::request()->data;
        
        $type = $data->type;
        $designation = $data->designation;
        $quantite = $data->quantite;
        $date_saisie = $data->date_saisie ?: null;

        if (empty($type) || empty($designation) || empty($quantite)) {
            Flight::redirect('/dons?error=' . urlencode('Tous les champs obligatoires doivent être remplis'));
            return;
        }

        try {
            Don::create($type, $designation, $quantite, $date_saisie);
            Flight::redirect('/dons?success=' . urlencode('Don ajouté avec succès'));
        } catch (Exception $e) {
            Flight::redirect('/dons?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }

    public static function edit($id) {
        $don = Don::getById($id);
        if (!$don) {
            Flight::redirect('/dons?error=' . urlencode('Don non trouvé'));
            return;
        }
        $dons = Don::getAll();
        Flight::render('dons_edit', ['don' => $don, 'dons' => $dons], 'body_content');
        Flight::render('layout', [
            'title' => 'Modifier un Don',
            'active' => 'dons'
        ]);
    }

    public static function update($id) {
        $data = Flight::request()->data;
        
        $type = $data->type;
        $designation = $data->designation;
        $quantite = $data->quantite;
        $date_saisie = $data->date_saisie ?: null;

        if (empty($type) || empty($designation) || empty($quantite)) {
            Flight::redirect('/dons/edit/' . $id . '?error=' . urlencode('Tous les champs obligatoires doivent être remplis'));
            return;
        }

        try {
            Don::update($id, $type, $designation, $quantite, $date_saisie);
            Flight::redirect('/dons?success=' . urlencode('Don modifié avec succès'));
        } catch (Exception $e) {
            Flight::redirect('/dons/edit/' . $id . '?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }

    public static function delete($id) {
        try {
            Don::delete($id);
            Flight::redirect('/dons?success=' . urlencode('Don supprimé'));
        } catch (Exception $e) {
            Flight::redirect('/dons?error=' . urlencode('Erreur lors de la suppression'));
        }
    }
}
