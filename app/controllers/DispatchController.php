<?php
class DispatchController {
    
    public static function index() {
        $dispatchesData = DispatchModel::getAllWithAchats();
        $dispatches = $dispatchesData['dispatches'];
        $achats = $dispatchesData['achats'];
        $donsRecap = DispatchModel::getDonsRecap();
        $besoinsRecap = DispatchModel::getBesoinsRecap();
        $stats = DispatchModel::getGlobalStats();
        
        Flight::render('dispatch', [
            'dispatches' => $dispatches,
            'achats' => $achats,
            'donsRecap' => $donsRecap,
            'besoinsRecap' => $besoinsRecap,
            'stats' => $stats
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Simulation du Dispatch',
            'active' => 'dispatch'
        ]);
    }

    public static function run() {
        try {
            $dispatchType = $_POST['dispatch_type'] ?? 'fifo_dons';
            
            // Valider le type de dispatch
            $allowedTypes = ['fifo_dons', 'proportionnel', 'fifo_besoins'];
            if (!in_array($dispatchType, $allowedTypes)) {
                $dispatchType = 'fifo_dons';
            }
            
            // Utiliser le service de dispatch
            $result = DispatchService::run($dispatchType);
            $count = count($result);
            
            $typeNames = [
                'fifo_dons' => 'FIFO dons',
                'proportionnel' => 'Proportionnel',
                'fifo_besoins' => 'FIFO besoins'
            ];
            
            Flight::redirect('/dispatch?success=' . urlencode("Dispatch {$typeNames[$dispatchType]} exécuté : $count attributions effectuées"));
        } catch (Exception $e) {
            Flight::redirect('/dispatch?error=' . urlencode('Erreur: ' . $e->getMessage()));
        }
    }

    public static function reset() {
        try {
            // Utiliser le service pour réinitialiser
            DispatchService::resetAll();
            Flight::redirect('/dispatch?success=' . urlencode('Dispatch réinitialisé'));
        } catch (Exception $e) {
            Flight::redirect('/dispatch?error=' . urlencode('Erreur lors de la réinitialisation'));
        }
    }
    
    public static function generateReport() {
        $report = DispatchModel::generateReport();
        
        // Créer le fichier
        $filename = 'rapport_bngrc_' . date('Ymd_His') . '.txt';
        $filepath = APP_ROOT . '/public/upload/' . $filename;
        
        file_put_contents($filepath, $report);
        
        // Télécharger le fichier
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=' . basename($filepath));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}
