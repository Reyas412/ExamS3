<?php
class DashboardController {
    
    public static function index() {
        $dashboardData = DispatchModel::getDashboardData();
        $donsRecap = DispatchModel::getDonsRecap();
        
        // Regrouper par ville
        $parVille = [];
        foreach ($dashboardData as $row) {
            $villeId = $row['ville_id'];
            if (!isset($parVille[$villeId])) {
                $parVille[$villeId] = [
                    'nom' => $row['ville_nom'],
                    'besoins' => []
                ];
            }
            if ($row['besoin_id']) {
                $parVille[$villeId]['besoins'][] = $row;
            }
        }

        // Statistiques globales
        $totalBesoins = 0;
        $totalCouverts = 0;
        $totalMontant = 0;
        foreach ($dashboardData as $row) {
            if ($row['besoin_id']) {
                $totalBesoins += floatval($row['besoin_quantite']);
                $totalCouverts += floatval($row['quantite_attribuee']);
                $totalMontant += floatval($row['montant_total']);
            }
        }

        $totalDons = 0;
        $totalDistribue = 0;
        foreach ($donsRecap as $don) {
            $totalDons += floatval($don['don_quantite']);
            $totalDistribue += floatval($don['quantite_distribuee']);
        }

        Flight::render('dashboard', [
            'parVille' => $parVille,
            'donsRecap' => $donsRecap,
            'stats' => [
                'totalBesoins' => $totalBesoins,
                'totalCouverts' => $totalCouverts,
                'totalMontant' => $totalMontant,
                'totalDons' => $totalDons,
                'totalDistribue' => $totalDistribue,
                'nbVilles' => count($parVille),
                'tauxCouverture' => $totalBesoins > 0 ? round(($totalCouverts / $totalBesoins) * 100, 1) : 0
            ]
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Tableau de Bord',
            'active' => 'dashboard'
        ]);
    }
}
