<?php
class DispatchModel {

    /**
     * Vérifie si un dispatch existe déjà pour un don et un besoin
     */
    public static function exists($don_id, $besoin_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM dispatch WHERE don_id = ? AND besoin_id = ?");
        $stmt->execute([$don_id, $besoin_id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie si un dispatch a déjà été effectué
     */
    public static function hasDispatched() {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT COUNT(*) FROM dispatch");
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère tous les dispatches avec les infos jointes
     */
    public static function getAll() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT di.*, 
                   d.type AS don_type, d.designation AS don_designation, d.quantite AS don_quantite,
                   b.designation AS besoin_designation, b.quantite AS besoin_quantite,
                   v.nom AS ville_nom
            FROM dispatch di
            JOIN dons d ON di.don_id = d.id
            JOIN besoins b ON di.besoin_id = b.id
            JOIN villes v ON b.ville_id = v.id
            ORDER BY di.date_dispatch ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Supprime tous les dispatches (reset)
     */
    public static function resetAll() {
        $pdo = getDatabase();
        $pdo->exec("DELETE FROM dispatch");
    }

    /**
     * Crée un enregistrement de dispatch (avec vérification anti-double)
     */
    public static function create($don_id, $besoin_id, $quantite_attribuee) {
        // Vérification anti double-dispatch
        if (self::exists($don_id, $besoin_id)) {
            throw new Exception("Dispatch déjà existant pour ce don et besoin");
        }
        
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO dispatch (don_id, besoin_id, quantite_attribuee) VALUES (?, ?, ?)");
        $stmt->execute([$don_id, $besoin_id, $quantite_attribuee]);
        return $pdo->lastInsertId();
    }

    /**
     * Algorithme de dispatch FIFO avec contrôle anti double-dispatch
     * 1. Vérifie si un dispatch a déjà été effectué
     * 2. Prend les dons par ordre chronologique (date_saisie ASC)
     * 3. Pour chaque don, distribue aux besoins correspondants (même type + désignation)
     *    par ordre chronologique (date_saisie ASC)
     */
    public static function runDispatch() {
        // Vérification: si déjà dispatché, demander un reset
        if (self::hasDispatched()) {
            throw new Exception("Un dispatch a déjà été effectué. Veuillez réinitialiser avant de relancer.");
        }
        
        $pdo = getDatabase();

        // Récupérer tous les dons par ordre chronologique
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();

        // Récupérer tous les besoins par ordre chronologique
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY date_saisie ASC")->fetchAll();

        // Tableau pour suivre combien chaque besoin a déjà reçu
        $besoinCouvert = [];
        foreach ($besoins as $b) {
            $besoinCouvert[$b['id']] = 0;
        }

        // Tableau pour suivre combien de chaque don a été distribué
        $donDistribue = [];
        foreach ($dons as $d) {
            $donDistribue[$d['id']] = 0;
        }

        $dispatches = [];

        // Pour chaque don (FIFO)
        foreach ($dons as $don) {
            $donReste = floatval($don['quantite']) - $donDistribue[$don['id']];

            if ($donReste <= 0) continue;

            // Trouver les besoins correspondants (même type et désignation)
            foreach ($besoins as $besoin) {
                if ($donReste <= 0) break;

                // Vérifier correspondance type et désignation
                if ($don['type'] !== $besoin['type'] || 
                    strtolower($don['designation']) !== strtolower($besoin['designation'])) {
                    continue;
                }

                $besoinReste = floatval($besoin['quantite']) - $besoinCouvert[$besoin['id']];
                if ($besoinReste <= 0) continue;

                // Quantité à attribuer = min(reste du don, reste du besoin)
                $qte = min($donReste, $besoinReste);

                // Créer le dispatch (avec vérification anti-double)
                self::create($don['id'], $besoin['id'], $qte);

                $besoinCouvert[$besoin['id']] += $qte;
                $donDistribue[$don['id']] += $qte;
                $donReste -= $qte;

                $dispatches[] = [
                    'don_id' => $don['id'],
                    'besoin_id' => $besoin['id'],
                    'quantite_attribuee' => $qte
                ];
            }
        }

        return $dispatches;
    }

    /**
     * Récupère le tableau de bord : pour chaque ville, les besoins avec quantités couvertes
     */
    public static function getDashboardData() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT 
                v.id AS ville_id,
                v.nom AS ville_nom,
                b.id AS besoin_id,
                b.type,
                b.designation,
                b.quantite AS besoin_quantite,
                b.prix_unitaire,
                (b.quantite * b.prix_unitaire) AS montant_total,
                COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_attribuee,
                (b.quantite - COALESCE(SUM(di.quantite_attribuee), 0)) AS quantite_reste
            FROM villes v
            LEFT JOIN besoins b ON b.ville_id = v.id
            LEFT JOIN dispatch di ON di.besoin_id = b.id
            GROUP BY v.id, v.nom, b.id, b.type, b.designation, b.quantite, b.prix_unitaire
            ORDER BY v.nom, b.date_saisie ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Récapitulatif global des dons (combien distribué vs total)
     */
    public static function getDonsRecap() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT 
                d.id,
                d.type,
                d.designation,
                d.quantite AS don_quantite,
                COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_distribuee,
                (d.quantite - COALESCE(SUM(di.quantite_attribuee), 0)) AS quantite_restante
            FROM dons d
            LEFT JOIN dispatch di ON di.don_id = d.id
            GROUP BY d.id, d.type, d.designation, d.quantite
            ORDER BY d.date_saisie ASC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Statistiques globales pour le dashboard
     */
    public static function getGlobalStats() {
        $pdo = getDatabase();
        
        // Total besoins
        $totalBesoins = $pdo->query("SELECT COALESCE(SUM(quantite), 0) FROM besoins")->fetchColumn();
        
        // Total dons reçus
        $totalDons = $pdo->query("SELECT COALESCE(SUM(quantite), 0) FROM dons")->fetchColumn();
        
        // Total dispatché
        $totalDispatche = $pdo->query("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch")->fetchColumn();
        
        // Taux de couverture global
        $tauxCouverture = $totalBesoins > 0 ? round(($totalDispatche / $totalBesoins) * 100, 2) : 0;
        
        return [
            'total_besoins' => floatval($totalBesoins),
            'total_dons' => floatval($totalDons),
            'total_dispatche' => floatval($totalDispatche),
            'taux_couverture' => $tauxCouverture
        ];
    }
    
    /**
     * Statistiques par ville
     */
    public static function getStatsByVille() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT 
                v.id,
                v.nom AS ville_nom,
                COALESCE(SUM(b.quantite), 0) AS total_besoins,
                COALESCE(SUM(di.quantite_attribuee), 0) AS total_couvert
            FROM villes v
            LEFT JOIN besoins b ON b.ville_id = v.id
            LEFT JOIN dispatch di ON di.besoin_id = b.id
            GROUP BY v.id, v.nom
            ORDER BY v.nom
        ");
        $results = $stmt->fetchAll();
        
        foreach ($results as &$row) {
            $row['taux_couverture'] = $row['total_besoins'] > 0 
                ? round(($row['total_couvert'] / $row['total_besoins']) * 100, 2) 
                : 0;
        }
        
        return $results;
    }
    
    /**
     * Statistiques par type
     */
    public static function getStatsByType() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT 
                b.type,
                COALESCE(SUM(b.quantite), 0) AS total_besoins,
                COALESCE(SUM(di.quantite_attribuee), 0) AS total_couvert
            FROM besoins b
            LEFT JOIN dispatch di ON di.besoin_id = b.id
            GROUP BY b.type
        ");
        $results = $stmt->fetchAll();
        
        foreach ($results as &$row) {
            $row['taux_couverture'] = $row['total_besoins'] > 0 
                ? round(($row['total_couvert'] / $row['total_besoins']) * 100, 2) 
                : 0;
        }
        
        return $results;
    }
    
    public static function getStatsByVilleId($ville_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(b.quantite), 0) AS total_besoins,
                COALESCE(SUM(di.quantite_attribuee), 0) AS total_couvert
            FROM besoins b
            LEFT JOIN dispatch di ON di.besoin_id = b.id
            WHERE b.ville_id = ?
            GROUP BY b.ville_id
        ");
        $stmt->execute([$ville_id]);
        $result = $stmt->fetch();
        
        $taux = $result && $result['total_besoins'] > 0 
            ? round(($result['total_couvert'] / $result['total_besoins']) * 100, 2) 
            : 0;
        
        return [
            'total_besoins' => floatval($result['total_besoins'] ?? 0),
            'total_couvert' => floatval($result['total_couvert'] ?? 0),
            'taux_couverture' => $taux
        ];
    }
    
    public static function getStatsByRegionId($region_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(b.quantite), 0) AS total_besoins,
                COALESCE(SUM(di.quantite_attribuee), 0) AS total_couvert
            FROM besoins b
            JOIN villes v ON b.ville_id = v.id
            LEFT JOIN dispatch di ON di.besoin_id = b.id
            WHERE v.idregion = ?
            GROUP BY v.idregion
        ");
        $stmt->execute([$region_id]);
        $result = $stmt->fetch();
        
        $taux = $result && $result['total_besoins'] > 0 
            ? round(($result['total_couvert'] / $result['total_besoins']) * 100, 2) 
            : 0;
        
        return [
            'total_besoins' => floatval($result['total_besoins'] ?? 0),
            'total_couvert' => floatval($result['total_couvert'] ?? 0),
            'taux_couverture' => $taux
        ];
    }
    
    /**
     * Génère le rapport final
     */
    public static function generateReport() {
        $stats = self::getGlobalStats();
        $statsByVille = self::getStatsByVille();
        $statsByType = self::getStatsByType();
        
        // Trouver la ville la plus impactée (plus de besoins)
        $villePlusImpactee = null;
        $maxBesoins = 0;
        foreach ($statsByVille as $v) {
            if ($v['total_besoins'] > $maxBesoins) {
                $maxBesoins = $v['total_besoins'];
                $villePlusImpactee = $v;
            }
        }
        
        // Trouver la ville la mieux couverte
        $villeMieuxCouverte = null;
        $maxTaux = -1;
        foreach ($statsByVille as $v) {
            if ($v['total_besoins'] > 0 && $v['taux_couverture'] > $maxTaux) {
                $maxTaux = $v['taux_couverture'];
                $villeMieuxCouverte = $v;
            }
        }
        
        $report = "============================================\n";
        $report .= "       RAPPORT FINAL - BNGRC\n";
        $report .= "============================================\n\n";
        
        $report .= "--- STATISTIQUES GLOBALES ---\n";
        $report .= "Total besoins: " . number_format($stats['total_besoins'], 2, ',', ' ') . "\n";
        $report .= "Total dons reçus: " . number_format($stats['total_dons'], 2, ',', ' ') . "\n";
        $report .= "Total dispatché: " . number_format($stats['total_dispatche'], 2, ',', ' ') . "\n";
        $report .= "Taux de couverture global: " . $stats['taux_couverture'] . "%\n\n";
        
        $report .= "--- PAR VILLE ---\n";
        foreach ($statsByVille as $v) {
            $report .= sprintf("%-20s | Besoins: %10s | Couvert: %10s | Taux: %6s%%\n",
                $v['ville_nom'],
                number_format($v['total_besoins'], 2, ',', ' '),
                number_format($v['total_couvert'], 2, ',', ' '),
                $v['taux_couverture']
            );
        }
        $report .= "\n";
        
        $report .= "--- PAR TYPE ---\n";
        foreach ($statsByType as $t) {
            $report .= sprintf("%-15s | Besoins: %10s | Couvert: %10s | Taux: %6s%%\n",
                $t['type'],
                number_format($t['total_besoins'], 2, ',', ' '),
                number_format($t['total_couvert'], 2, ',', ' '),
                $t['taux_couverture']
            );
        }
        $report .= "\n";
        
        $report .= "--- RÉSUMÉ ---\n";
        $report .= "Ville la plus impactée: " . ($villePlusImpactee ? $villePlusImpactee['ville_nom'] . " (" . number_format($villePlusImpactee['total_besoins'], 2, ',', ' ') . ")" : "N/A") . "\n";
        $report .= "Ville la mieux couverte: " . ($villeMieuxCouverte ? $villeMieuxCouverte['ville_nom'] . " (" . $villeMieuxCouverte['taux_couverture'] . "%)" : "N/A") . "\n\n";
        
        $report .= "============================================\n";
        $report .= "Rapport généré le: " . date('d/m/Y H:i:s') . "\n";
        $report .= "============================================\n";
        
        return $report;
    }
}
