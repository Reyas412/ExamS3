<?php
class DispatchController {
    
    public static function index() {
        $dispatches = DispatchModel::getAll();
        $donsRecap = DispatchModel::getDonsRecap();
        Flight::render('dispatch', [
            'dispatches' => $dispatches,
            'donsRecap' => $donsRecap
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Simulation du Dispatch',
            'active' => 'dispatch'
        ]);
    }

    public static function run() {
        try {
            $result = DispatchModel::runDispatch();
            $count = count($result);
            Flight::redirect('/dispatch?success=' . urlencode("Dispatch exécuté : $count attributions effectuées"));
        } catch (Exception $e) {
            Flight::redirect('/dispatch?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }

    public static function reset() {
        try {
            DispatchModel::resetAll();
            Flight::redirect('/dispatch?success=' . urlencode('Dispatch réinitialisé'));
        } catch (Exception $e) {
            Flight::redirect('/dispatch?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }
}
