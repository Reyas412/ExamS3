<?php
/**
 * Service de dispatch - Contient la logique métier des algorithmes de distribution
 * 
 * Ce service gère les trois types de dispatch:
 * - FIFO Dons: Distribution par ordre chronologique des dons
 * - Proportionnel: Distribution proportionnelle selon le poids des besoins
 * - FIFO Besoins: Distribution prioritaire aux besoins les plus anciens
 */

class DispatchService {
    
    /**
     * Exécute le dispatch selon le type spécifié
     */
    public static function run($dispatchType = 'fifo_dons') {
        switch ($dispatchType) {
            case 'proportionnel':
                return self::runProportionnel();
            case 'fifo_besoins':
                return self::runFifoBesoins();
            case 'fifo_dons':
            default:
                return self::runFifoDons();
        }
    }
    
    /**
     * Dispatch FIFO Dons (par date des dons)
     * Le premier don saisi est le premier à être utilisé pour couvrir les besoins.
     */
    public static function runFifoDons() {
        $pdo = getDatabase();
        
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY date_saisie ASC")->fetchAll();
        
        $besoinCouvert = self::getCouvertParBesoin($besoins);
        $donDistribue = self::getDistribueParDon($dons);
        
        $dispatches = [];
        
        foreach ($dons as $don) {
            $donReste = floatval($don['quantite']) - $donDistribue[$don['id']];
            if ($donReste <= 0) continue;
            
            foreach ($besoins as $besoin) {
                if (!self::matchTypeDesignation($don, $besoin)) continue;
                
                $besoinReste = floatval($besoin['quantite']) - $besoinCouvert[$besoin['id']];
                if ($besoinReste <= 0) continue;
                
                $qte = min($donReste, $besoinReste);
                
                self::createDispatch($don['id'], $besoin['id'], $qte);
                
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
        
        self::updateBesoinsSatisfaits($besoinCouvert);
        
        return $dispatches;
    }
    
    /**
     * Dispatch proportionnel
     * Répartit les dons entre plusieurs besoins selon leur poids relatif
     */
    public static function runProportionnel() {
        $pdo = getDatabase();
        
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY date_saisie ASC")->fetchAll();
        
        $besoinCouvert = self::getCouvertParBesoin($besoins);
        $donDistribue = self::getDistribueParDon($dons);
        
        $dispatches = [];
        
        foreach ($dons as $don) {
            $donReste = floatval($don['quantite']) - $donDistribue[$don['id']];
            if ($donReste <= 0) continue;
            
            // Trouver les besoins correspondants
            $besoinsCorrespondants = [];
            foreach ($besoins as $besoin) {
                if (!self::matchTypeDesignation($don, $besoin)) continue;
                
                $besoinReste = floatval($besoin['quantite']) - $besoinCouvert[$besoin['id']];
                if ($besoinReste > 0) {
                    $besoinsCorrespondants[] = [
                        'id' => $besoin['id'],
                        'reste' => $besoinReste
                    ];
                }
            }
            
            if (empty($besoinsCorrespondants)) continue;
            
            // Calculer le total des besoins restants
            $totalBesoins = array_sum(array_column($besoinsCorrespondants, 'reste'));
            
            if ($totalBesoins <= 0) continue;
            
            // Distribution proportionnelle
            $resteADistribuer = $donReste;
            
            foreach ($besoinsCorrespondants as &$b) {
                if ($resteADistribuer <= 0) break;
                
                $proportion = $b['reste'] / $totalBesoins;
                $qte = floor($proportion * $donReste);
                
                if ($qte > 0) {
                    self::createDispatch($don['id'], $b['id'], $qte);
                    
                    $besoinCouvert[$b['id']] += $qte;
                    $donDistribue[$don['id']] += $qte;
                    $resteADistribuer -= $qte;
                    
                    $dispatches[] = [
                        'don_id' => $don['id'],
                        'besoin_id' => $b['id'],
                        'quantite_attribuee' => $qte
                    ];
                }
            }
            
            // Distribuer le reste avec la méthode du plus grand reste
            if ($resteADistribuer > 0) {
                usort($besoinsCorrespondants, function($a, $b) {
                    return $b['reste'] - $a['reste'];
                });
                
                foreach ($besoinsCorrespondants as &$b) {
                    if ($resteADistribuer <= 0) break;
                    
                    $qte = min(1, $resteADistribuer);
                    
                    self::createDispatch($don['id'], $b['id'], $qte);
                    
                    $besoinCouvert[$b['id']] += $qte;
                    $donDistribue[$don['id']] += $qte;
                    $resteADistribuer -= $qte;
                    
                    $dispatches[] = [
                        'don_id' => $don['id'],
                        'besoin_id' => $b['id'],
                        'quantite_attribuee' => $qte
                    ];
                }
            }
        }
        
        self::updateBesoinsSatisfaits($besoinCouvert);
        
        return $dispatches;
    }
    
    /**
     * Dispatch FIFO Besoins (par date des besoins)
     * Les besoins les plus anciens sont couverts en premier
     */
    public static function runFifoBesoins() {
        $pdo = getDatabase();
        
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY date_saisie ASC")->fetchAll();
        
        $besoinCouvert = self::getCouvertParBesoin($besoins);
        $donDistribue = self::getDistribueParDon($dons);
        
        $dispatches = [];
        
        // Trier les besoins par date (plus ancien en premier)
        usort($besoins, function($a, $b) {
            return strtotime($a['date_saisie']) - strtotime($b['date_saisie']);
        });
        
        foreach ($besoins as $besoin) {
            $besoinReste = floatval($besoin['quantite']) - $besoinCouvert[$besoin['id']];
            if ($besoinReste <= 0) continue;
            
            foreach ($dons as $don) {
                if (!self::matchTypeDesignation($don, $besoin)) continue;
                
                $donReste = floatval($don['quantite']) - $donDistribue[$don['id']];
                if ($donReste <= 0) continue;
                
                $qte = min($donReste, $besoinReste);
                
                self::createDispatch($don['id'], $besoin['id'], $qte);
                
                $besoinCouvert[$besoin['id']] += $qte;
                $donDistribue[$don['id']] += $qte;
                $besoinReste -= $qte;
                
                $dispatches[] = [
                    'don_id' => $don['id'],
                    'besoin_id' => $besoin['id'],
                    'quantite_attribuee' => $qte
                ];
                
                if ($besoinReste <= 0) break;
            }
        }
        
        self::updateBesoinsSatisfaits($besoinCouvert);
        
        return $dispatches;
    }
    
    // =========================================================================
    // Méthodes utilitaires privées
    // =========================================================================
    
    /**
     * Vérifie si le type et la désignation correspondent entre don et besoin
     */
    private static function matchTypeDesignation($don, $besoin) {
        return $don['type'] === $besoin['type'] && 
               strtolower($don['designation']) === strtolower($besoin['designation']);
    }
    
    /**
     * Récupère les quantités déjà couvertes pour chaque besoin
     */
    private static function getCouvertParBesoin($besoins) {
        $pdo = getDatabase();
        $besoinCouvert = [];
        
        foreach ($besoins as $b) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE besoin_id = ?");
            $stmt->execute([$b['id']]);
            $besoinCouvert[$b['id']] = floatval($stmt->fetchColumn());
        }
        
        return $besoinCouvert;
    }
    
    /**
     * Récupère les quantités déjà distribuées pour chaque don
     */
    private static function getDistribueParDon($dons) {
        $pdo = getDatabase();
        $donDistribue = [];
        
        foreach ($dons as $d) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE don_id = ?");
            $stmt->execute([$d['id']]);
            $donDistribue[$d['id']] = floatval($stmt->fetchColumn());
        }
        
        return $donDistribue;
    }
    
    /**
     * Crée un enregistrement de dispatch
     */
    private static function createDispatch($don_id, $besoin_id, $quantite_attribuee) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO dispatch (don_id, besoin_id, quantite_attribuee) VALUES (?, ?, ?)");
        $stmt->execute([$don_id, $besoin_id, $quantite_attribuee]);
    }
    
    /**
     * Met à jour les besoins avec les quantités satisfaites
     */
    private static function updateBesoinsSatisfaits($besoinCouvert) {
        $pdo = getDatabase();
        
        foreach ($besoinCouvert as $besoin_id => $qte_couverte) {
            $stmt = $pdo->prepare("UPDATE besoins SET quantite_satisfaite = ? WHERE id = ?");
            $stmt->execute([$qte_couverte, $besoin_id]);
        }
    }
    
    /**
     * Réinitialise tous les dispatches et restaure les dons
     */
    public static function resetAll() {
        $pdo = getDatabase();
        
        try {
            $pdo->beginTransaction();
            
            // Restaurer les dons nature
            $dispatches = $pdo->query("SELECT * FROM dispatch")->fetchAll();
            foreach ($dispatches as $d) {
                $stmt = $pdo->prepare("UPDATE dons SET quantite = quantite + ? WHERE id = ?");
                $stmt->execute([$d['quantite_attribuee'], $d['don_id']]);
            }
            
            // Supprimer les dispatches
            $pdo->exec("DELETE FROM dispatch");
            
            // Restaurer les dons argent et supprimer les achats
            $achatDispatches = $pdo->query("SELECT * FROM achat_dispatch")->fetchAll();
            foreach ($achatDispatches as $ad) {
                $stmt = $pdo->prepare("UPDATE dons SET quantite = quantite + ? WHERE id = ?");
                $stmt->execute([$ad['montant_utilise'], $ad['don_id']]);
            }
            
            $pdo->exec("DELETE FROM achat_dispatch");
            $pdo->exec("DELETE FROM achats");
            
            $pdo->commit();
            
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
