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
     * Vérifie si un achat a déjà été effectué
     */
    public static function hasAchats() {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT COUNT(*) FROM achats");
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
     * Récupère tous les achats (besoins couverts par achats)
     */
    public static function getAllAchats() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT a.*, 
                   b.designation AS besoin_designation, b.type AS besoin_type, b.quantite AS besoin_quantite,
                   v.nom AS ville_nom
            FROM achats a
            JOIN besoins b ON a.besoin_id = b.id
            JOIN villes v ON a.ville_id = v.id
            ORDER BY a.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Récupère tous les dispatches et achats combinés
     */
    public static function getAllWithAchats() {
        $pdo = getDatabase();
        
        // Get dispatch data
        $dispatches = self::getAll();
        
        // Get achat data
        $achats = self::getAllAchats();
        
        return [
            'dispatches' => $dispatches,
            'achats' => $achats
        ];
    }

    /**
     * Supprime tous les dispatches et réinitialise les dons et besoins (reset complet)
     */
    public static function resetAll() {
        $pdo = getDatabase();
        
        try {
            $pdo->beginTransaction();
            
            // 1. Supprimer tous les dispatches et restaurer les dons
            $dispatches = $pdo->query("SELECT * FROM dispatch")->fetchAll();
            
            foreach ($dispatches as $d) {
                // Restaurer la quantité dans les dons (nature)
                $stmt = $pdo->prepare("UPDATE dons SET quantite = quantite + ? WHERE id = ?");
                $stmt->execute([$d['quantite_attribuee'], $d['don_id']]);
            }
            
            // 2. Supprimer tous les dispatches
            $pdo->exec("DELETE FROM dispatch");
            
            // 3. Supprimer les achat_dispatch et restaurer les dons argent
            $achatDispatches = $pdo->query("SELECT * FROM achat_dispatch")->fetchAll();
            
            foreach ($achatDispatches as $ad) {
                // Restaurer la quantité (montant) dans les dons argent
                $stmt = $pdo->prepare("UPDATE dons SET quantite = quantite + ? WHERE id = ?");
                $stmt->execute([$ad['montant_utilise'], $ad['don_id']]);
            }
            
            // 4. Supprimer les achat_dispatch et achats
            $pdo->exec("DELETE FROM achat_dispatch");
            $pdo->exec("DELETE FROM achats");
            
            $pdo->commit();
            
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Crée un enregistrement de dispatch
     */
    public static function create($don_id, $besoin_id, $quantite_attribuee) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO dispatch (don_id, besoin_id, quantite_attribuee) VALUES (?, ?, ?)");
        $stmt->execute([$don_id, $besoin_id, $quantite_attribuee]);
        return $pdo->lastInsertId();
    }

    /**
     * Algorithme de dispatch principal avec support de plusieurs types
     * Peut être exécuté plusieurs fois sans obligation de réinitialiser.
     */
    public static function runDispatch($dispatchType = 'fifo_dons') {
        switch ($dispatchType) {
            case 'proportionnel':
                return self::runDispatchProportionnel();
            case 'fifo_besoins':
                return self::runDispatchFifoBesoins();
            case 'fifo_dons':
            default:
                return self::runDispatchFifoDons();
        }
    }
    
    /**
     * Dispatch FIFO Dons (par date des dons)
     * Le premier don saisi est le premier à être utilisé pour couvrir les besoins.
     * Ce mode respecte la chronologie des dons et évite de laisser d'anciens dons inutilisés.
     * Peut être exécuté plusieurs fois sans obligation de réinitialiser.
     */
    public static function runDispatchFifoDons() {
        $pdo = getDatabase();

        // Récupérer tous les dons par ordre chronologique
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();

        // Récupérer tous les besoins par ordre chronologique
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY date_saisie ASC")->fetchAll();

        // Tableau pour suivre combien chaque besoin a déjà reçu (depuis la DB)
        $besoinCouvert = [];
        foreach ($besoins as $b) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE besoin_id = ?");
            $stmt->execute([$b['id']]);
            $besoinCouvert[$b['id']] = floatval($stmt->fetchColumn());
        }

        // Tableau pour suivre combien de chaque don a été distribué (depuis la DB)
        $donDistribue = [];
        foreach ($dons as $d) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE don_id = ?");
            $stmt->execute([$d['id']]);
            $donDistribue[$d['id']] = floatval($stmt->fetchColumn());
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

                // Créer ou mettre à jour le dispatch
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
        
        // Mettre à jour les besoins avec les quantités satisfaites
        foreach ($besoinCouvert as $besoin_id => $qte_couverte) {
            // Récupérer la quantité actuelle dans la DB pour faire la différence
            $stmt = $pdo->prepare("SELECT quantite_satisfaite FROM besoins WHERE id = ?");
            $stmt->execute([$besoin_id]);
            $oldQte = floatval($stmt->fetchColumn() ?? 0);
            
            // La nouvelle quantité satisfaite est ce qui est maintenant dans dispatch
            $newQte = $qte_couverte;
            
            if ($newQte != $oldQte) {
                $stmt = $pdo->prepare("UPDATE besoins SET quantite_satisfaite = ? WHERE id = ?");
                $stmt->execute([$newQte, $besoin_id]);
            }
        }

        return $dispatches;
    }
    
    /**
     * Dispatch proportionnel
     * Répartit un don entre plusieurs besoins selon leur poids relatif.
     * Utilise la méthode du "plus grand reste" pour distribuer tout l'argent.
     * Peut être exécuté plusieurs fois sans obligation de réinitialiser.
     */
    public static function runDispatchProportionnel() {
        $pdo = getDatabase();

        // Récupérer tous les dons
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();

        // Récupérer tous les besoins
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY date_saisie ASC")->fetchAll();

        // Tableau pour suivre combien chaque besoin a déjà reçu (depuis la DB)
        $besoinCouvert = [];
        foreach ($besoins as $b) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE besoin_id = ?");
            $stmt->execute([$b['id']]);
            $besoinCouvert[$b['id']] = floatval($stmt->fetchColumn());
        }

        // Tableau pour suivre combien de chaque don a été distribué (depuis la DB)
        $donDistribue = [];
        foreach ($dons as $d) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE don_id = ?");
            $stmt->execute([$d['id']]);
            $donDistribue[$d['id']] = floatval($stmt->fetchColumn());
        }

        $dispatches = [];

        // Pour chaque don
        foreach ($dons as $don) {
            $donReste = floatval($don['quantite']) - $donDistribue[$don['id']];

            if ($donReste <= 0) continue;

            // Trouver les besoins correspondants (même type et désignation)
            $besoinsCorrespondants = [];
            foreach ($besoins as $besoin) {
                // Vérifier correspondance type et désignation
                if ($don['type'] !== $besoin['type'] || 
                    strtolower($don['designation']) !== strtolower($besoin['designation'])) {
                    continue;
                }

                $besoinReste = floatval($besoin['quantite']) - $besoinCouvert[$besoin['id']];
                if ($besoinReste > 0) {
                    $besoinsCorrespondants[] = [
                        'id' => $besoin['id'],
                        'reste' => $besoinReste
                    ];
                }
            }

            if (empty($besoinsCorrespondants)) continue;

            // Calculer le total des besoins restants pour cette désignation
            $totalBesoins = array_sum(array_column($besoinsCorrespondants, 'reste'));

            // Si le don est suffisant pour couvrir tous les besoins
            if ($donReste >= $totalBesoins) {
                foreach ($besoinsCorrespondants as $besoin) {
                    $qte = $besoin['reste'];
                    if ($qte > 0) {
                        self::create($don['id'], $besoin['id'], $qte);
                        $besoinCouvert[$besoin['id']] += $qte;
                        $donDistribue[$don['id']] += $qte;
                        $dispatches[] = [
                            'don_id' => $don['id'],
                            'besoin_id' => $besoin['id'],
                            'quantite_attribuee' => $qte
                        ];
                    }
                }
            } else {
                // Distribution proportionnelle avec méthode du plus grand reste
                // Calculer la proportion pour chaque besoin
                $distributions = [];
                $resteTotal = 0;
                
                foreach ($besoinsCorrespondants as $besoin) {
                    $proportion = $besoin['reste'] / $totalBesoins;
                    $qte = $donReste * $proportion;
                    
                    // Partie entière (floor)
                    $qteEntiere = floor($qte);
                    
                    // Fraction (ce qui reste après l'arrondi)
                    $fraction = $qte - $qteEntiere;
                    
                    $distributions[] = [
                        'id' => $besoin['id'],
                        'qte' => $qteEntiere,
                        'fraction' => $fraction
                    ];
                    
                    $resteTotal += $qteEntiere;
                }
                
                // Calculer ce qui reste à distribuer
                $reste = $donReste - $resteTotal;
                
                // Trier par fraction décroissante pour distribuer le reste aux plus grandes fractions
                usort($distributions, function($a, $b) {
                    return $b['fraction'] - $a['fraction'];
                });
                
                // Distribuer le reste (+1 à chaque besoin avec les plus grandes fractions)
                $i = 0;
                while ($reste > 0 && $i < count($distributions)) {
                    $distributions[$i]['qte'] += 1;
                    $reste--;
                    $i++;
                }
                
                // Créer les dispatches
                foreach ($distributions as $dist) {
                    if ($dist['qte'] > 0) {
                        self::create($don['id'], $dist['id'], $dist['qte']);
                        $besoinCouvert[$dist['id']] += $dist['qte'];
                        $donDistribue[$don['id']] += $dist['qte'];
                        $dispatches[] = [
                            'don_id' => $don['id'],
                            'besoin_id' => $dist['id'],
                            'quantite_attribuee' => $dist['qte']
                        ];
                    }
                }
            }
        }
        
        // Mettre à jour les besoins avec les quantités satisfaites
        foreach ($besoinCouvert as $besoin_id => $qte_couverte) {
            // Récupérer la quantité actuelle dans la DB
            $stmt = $pdo->prepare("SELECT quantite_satisfaite FROM besoins WHERE id = ?");
            $stmt->execute([$besoin_id]);
            $oldQte = floatval($stmt->fetchColumn() ?? 0);
            
            $newQte = $qte_couverte;
            
            if ($newQte != $oldQte) {
                $stmt = $pdo->prepare("UPDATE besoins SET quantite_satisfaite = ? WHERE id = ?");
                $stmt->execute([$newQte, $besoin_id]);
            }
        }

        return $dispatches;
    }
    
    /**
     * Dispatch par priorité des besoins (FIFO besoins - plus petit besoin en premier)
     * Distribue les dons en priorité aux besoins avec les plus petites quantités.
     * Les besoins sont traités par ordre croissant de quantité restante.
     * Ce mode favorise les villes qui ont des petits besoins en premier.
     * Peut être exécuté plusieurs fois sans obligation de réinitialiser.
     */
    public static function runDispatchFifoBesoins() {
        $pdo = getDatabase();

        // Récupérer tous les dons
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();

        // Récupérer tous les besoins TRIÉS PAR QUANTITÉ CROISSANTE (plus petit besoin en premier)
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY quantite ASC")->fetchAll();

        // Tableau pour suivre combien chaque besoin a déjà reçu (depuis la DB)
        $besoinCouvert = [];
        foreach ($besoins as $b) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE besoin_id = ?");
            $stmt->execute([$b['id']]);
            $besoinCouvert[$b['id']] = floatval($stmt->fetchColumn());
        }

        // Tableau pour suivre combien de chaque don a été distribué (depuis la DB)
        $donDistribue = [];
        foreach ($dons as $d) {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch WHERE don_id = ?");
            $stmt->execute([$d['id']]);
            $donDistribue[$d['id']] = floatval($stmt->fetchColumn());
        }

        $dispatches = [];

        // Pour chaque besoin trié par quantité croissante
        foreach ($besoins as $besoin) {
            $besoinReste = floatval($besoin['quantite']) - $besoinCouvert[$besoin['id']];
            
            if ($besoinReste <= 0) continue;

            // Trouver les dons correspondants (même type et désignation)
            foreach ($dons as $don) {
                if ($besoinReste <= 0) break;

                // Vérifier correspondance type et désignation
                if ($don['type'] !== $besoin['type'] || 
                    strtolower($don['designation']) !== strtolower($besoin['designation'])) {
                    continue;
                }

                $donReste = floatval($don['quantite']) - $donDistribue[$don['id']];
                if ($donReste <= 0) continue;

                // Quantité à attribuer = min(reste du don, reste du besoin)
                $qte = min($donReste, $besoinReste);

                // Créer ou mettre à jour le dispatch
                self::create($don['id'], $besoin['id'], $qte);

                $besoinCouvert[$besoin['id']] += $qte;
                $donDistribue[$don['id']] += $qte;
                $besoinReste -= $qte;

                $dispatches[] = [
                    'don_id' => $don['id'],
                    'besoin_id' => $besoin['id'],
                    'quantite_attribuee' => $qte
                ];
            }
        }
        
        // Mettre à jour les besoins avec les quantités satisfaites
        foreach ($besoinCouvert as $besoin_id => $qte_couverte) {
            // Récupérer la quantité actuelle dans la DB
            $stmt = $pdo->prepare("SELECT quantite_satisfaite FROM besoins WHERE id = ?");
            $stmt->execute([$besoin_id]);
            $oldQte = floatval($stmt->fetchColumn() ?? 0);
            
            $newQte = $qte_couverte;
            
            if ($newQte != $oldQte) {
                $stmt = $pdo->prepare("UPDATE besoins SET quantite_satisfaite = ? WHERE id = ?");
                $stmt->execute([$newQte, $besoin_id]);
            }
        }

        return $dispatches;
    }

    /**
     * Récupère le tableau de bord : pour chaque ville, les besoins avec quantités couvertes
     * Inclut les données des deux sources: dispatch et achats
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
                COALESCE(b.quantite_satisfaite, 0) AS quantite_attribuee,
                (b.quantite - COALESCE(b.quantite_satisfaite, 0)) AS quantite_reste
            FROM villes v
            LEFT JOIN besoins b ON b.ville_id = v.id
            GROUP BY v.id, v.nom, b.id, b.type, b.designation, b.quantite, b.prix_unitaire, b.quantite_satisfaite
            ORDER BY v.nom, b.date_saisie ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Récapitulatif global des dons (combien distribué vs total)
     * Inclut les dons utilisés dans dispatch et achat_dispatch
     */
    public static function getDonsRecap() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT 
                d.id,
                d.type,
                d.designation,
                d.quantite AS don_quantite,
                COALESCE(dispatch_total.quantite_distribuee, 0) + COALESCE(achat_total.montant_utilise, 0) AS quantite_distribuee,
                (d.quantite - COALESCE(dispatch_total.quantite_distribuee, 0) - COALESCE(achat_total.montant_utilise, 0)) AS quantite_restante
            FROM dons d
            LEFT JOIN (
                SELECT don_id, COALESCE(SUM(quantite_attribuee), 0) AS quantite_distribuee
                FROM dispatch
                GROUP BY don_id
            ) dispatch_total ON d.id = dispatch_total.don_id
            LEFT JOIN (
                SELECT don_id, COALESCE(SUM(montant_utilise), 0) AS montant_utilise
                FROM achat_dispatch
                GROUP BY don_id
            ) achat_total ON d.id = achat_total.don_id
            ORDER BY d.date_saisie ASC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Récapitulatif global des besoins (combien satisfait vs total)
     * Inclut les données des deux sources: dispatch et achats
     */
    public static function getBesoinsRecap() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT 
                b.id,
                b.type,
                b.designation,
                b.quantite AS besoin_quantite,
                v.nom AS ville_nom,
                COALESCE(b.quantite_satisfaite, 0) AS quantite_recue,
                (b.quantite - COALESCE(b.quantite_satisfaite, 0)) AS quantite_restante
            FROM besoins b
            LEFT JOIN villes v ON b.ville_id = v.id
            ORDER BY b.date_saisie ASC
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
        
        // Total besoins satisfaits (utilise quantite_satisfaite qui est mis a jour par dispatch ET achats)
        $totalSatisfait = $pdo->query("SELECT COALESCE(SUM(quantite_satisfaite), 0) FROM besoins")->fetchColumn();
        
        // Total dons reçus
        $totalDons = $pdo->query("SELECT COALESCE(SUM(quantite), 0) FROM dons")->fetchColumn();
        
        // Total dispatché (somme des deux sources)
        $totalDispatcheDispatch = $pdo->query("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM dispatch")->fetchColumn();
        $totalDispatcheAchats = $pdo->query("SELECT COALESCE(SUM(montant_utilise), 0) FROM achat_dispatch")->fetchColumn();
        $totalDispatche = $totalDispatcheDispatch + $totalDispatcheAchats;
        
        // Taux de couverture global
        $tauxCouverture = $totalBesoins > 0 ? round(($totalSatisfait / $totalBesoins) * 100, 2) : 0;
        
        return [
            'total_besoins' => floatval($totalBesoins),
            'total_satisfait' => floatval($totalSatisfait),
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
                COALESCE(SUM(b.quantite_satisfaite), 0) AS total_couvert
            FROM villes v
            LEFT JOIN besoins b ON b.ville_id = v.id
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
                COALESCE(SUM(b.quantite_satisfaite), 0) AS total_couvert
            FROM besoins b
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
                COALESCE(SUM(quantite), 0) AS total_besoins,
                COALESCE(SUM(quantite_satisfaite), 0) AS total_couvert
            FROM besoins
            WHERE ville_id = ?
            GROUP BY ville_id
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
                COALESCE(SUM(b.quantite_satisfaite), 0) AS total_couvert
            FROM besoins b
            JOIN villes v ON b.ville_id = v.id
            WHERE v.region_id = ?
            GROUP BY v.region_id
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
