<?php
/**
 * Modèle Achat - Gestion des achats
 */
class Achat {
    
    /**
     * Obtenir le pourcentage de frais d'achat depuis la config
     */
    public static function getFraisPercent() {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT value FROM config WHERE config_key = 'purchase_fee_percent'");
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result ? floatval($result) : 0;
    }
    
    /**
     * Mettre à jour le pourcentage de frais d'achat
     */
    public static function setFraisPercent($percent) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO config (config_key, value) VALUES ('purchase_fee_percent', ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$percent, $percent]);
    }
    
    /**
     * Obtenir tous les achats (filtrable par ville)
     */
    public static function getAll($ville_id = null) {
        $pdo = getDatabase();
        
        $sql = "SELECT a.*, b.designation as besoin_designation, b.type as besoin_type, 
                       v.nom as ville_nom
                FROM achats a
                LEFT JOIN besoins b ON a.besoin_id = b.id
                LEFT JOIN villes v ON a.ville_id = v.id";
        
        if ($ville_id) {
            $sql .= " WHERE a.ville_id = ?";
            $stmt = $pdo->prepare($sql . " ORDER BY a.created_at DESC");
            $stmt->execute([$ville_id]);
        } else {
            $stmt = $pdo->query($sql . " ORDER BY a.created_at DESC");
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir un achat par ID
     */
    public static function getById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT a.*, b.designation as besoin_designation, b.type as besoin_type,
                              b.quantite as besoin_quantite, b.prix_unitaire, b.quantite_satisfaite,
                              v.nom as ville_nom
                       FROM achats a
                       LEFT JOIN besoins b ON a.besoin_id = b.id
                       LEFT JOIN villes v ON a.ville_id = v.id
                       WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Créer un achat simulé
     */
    public static function createSimuler($besoin_id, $quantite, $frais_percent = null) {
        $pdo = getDatabase();
        
        // Obtenir les détails du besoin
        $stmt = $pdo->prepare("SELECT * FROM besoins WHERE id = ?");
        $stmt->execute([$besoin_id]);
        $besoin = $stmt->fetch();
        
        if (!$besoin) {
            throw new Exception("Besoin non trouvé");
        }
        
        // Vérifier si un don en nature est disponible pour ce besoin
        $stmt = $pdo->prepare("
            SELECT d.* FROM dons d 
            WHERE d.type = ? AND d.quantite > 0
            AND d.designation = ?
            ORDER BY d.date_saisie ASC
            LIMIT 1
        ");
        $stmt->execute([$besoin['type'], $besoin['designation']]);
        $don_nature = $stmt->fetch();
        
        if ($don_nature) {
            throw new Exception("Un don en nature est disponible pour ce besoin. Utilisez d'abord ce don.");
        }
        
        // Calculer le montant
        $frais_percent = $frais_percent ?? self::getFraisPercent();
        $montant_base = $quantite * floatval($besoin['prix_unitaire']);
        $montant_total = $montant_base * (1 + $frais_percent / 100);
        
        // Créer l'achat simulé
        $stmt = $pdo->prepare("
            INSERT INTO achats (besoin_id, ville_id, quantite, montant_base, frais_percent, montant_total, status)
            VALUES (?, ?, ?, ?, ?, ?, 'simule')
        ");
        $stmt->execute([
            $besoin_id,
            $besoin['ville_id'],
            $quantite,
            round($montant_base, 2),
            $frais_percent,
            round($montant_total, 2)
        ]);
        
        return [
            'id' => $pdo->lastInsertId(),
            'besoin_id' => $besoin_id,
            'ville_id' => $besoin['ville_id'],
            'quantite' => $quantite,
            'montant_base' => round($montant_base, 2),
            'frais_percent' => $frais_percent,
            'montant_total' => round($montant_total, 2),
            'status' => 'simule'
        ];
    }
    
    /**
     * Simuler l'achat (sans persister) - retourne les détails de la simulation
     */
    public static function simuler($besoin_id, $quantite, $frais_percent = null) {
        $pdo = getDatabase();
        
        // Obtenir les détails du besoin
        $stmt = $pdo->prepare("SELECT * FROM besoins WHERE id = ?");
        $stmt->execute([$besoin_id]);
        $besoin = $stmt->fetch();
        
        if (!$besoin) {
            throw new Exception("Besoin non trouvé");
        }
        
        // Vérifier si un don en nature est disponible pour ce besoin
        // Pour type argent on vérifie montant_restant, pour nature/materiaux on vérifie la quantité restante
        if ($besoin['type'] === 'argent') {
            // Pour les besoins en argent, on n'a pas besoin de vérifier les dons nature
            $don_nature = null;
        } else {
            $stmt = $pdo->prepare("
                SELECT d.*, 
                       d.quantite - COALESCE((SELECT SUM(quantite_attribuee) FROM dispatch WHERE don_id = d.id), 0) as reste
                FROM dons d 
                WHERE d.type = ? 
                AND d.designation = ?
                AND (d.quantite - COALESCE((SELECT SUM(quantite_attribuee) FROM dispatch WHERE don_id = d.id), 0)) > 0
                ORDER BY d.date_saisie ASC
                LIMIT 1
            ");
            $stmt->execute([$besoin['type'], $besoin['designation']]);
            $don_nature = $stmt->fetch();
        }
        
        if ($don_nature) {
            throw new Exception("Un don en " . $besoin['type'] . " est disponible pour ce besoin: " . $don_nature['designation'] . " (" . number_format($don_nature['reste'], 0, ',', ' ') . " disponibles)");
        }
        
        // Calculer le montant
        $frais_percent = $frais_percent ?? self::getFraisPercent();
        $montant_base = $quantite * floatval($besoin['prix_unitaire']);
        $montant_total = $montant_base * (1 + $frais_percent / 100);
        
        // Obtenir les dons monétaires disponibles (FIFO)
        $stmt = $pdo->query("
            SELECT * FROM dons 
            WHERE type = 'argent' AND quantite > 0
            ORDER BY date_saisie ASC
        ");
        $dons_argent = $stmt->fetchAll();
        
        // Simuler l'allocation FIFO
        $allocation = [];
        $montant_couvert = 0;
        
        foreach ($dons_argent as $don) {
            if ($montant_couvert >= $montant_total) break;
            
            $montant_dispo = floatval($don['quantite']);
            $montant_necessaire = $montant_total - $montant_couvert;
            $montant_utilise = min($montant_dispo, $montant_necessaire);
            
            $allocation[] = [
                'don_id' => $don['id'],
                'don_designation' => $don['designation'],
                'don_date' => $don['date_saisie'],
                'montant_disponible' => $montant_dispo,
                'montant_utilise' => round($montant_utilise, 2)
            ];
            
            $montant_couvert += $montant_utilise;
        }
        
        $manquant = $montant_total - $montant_couvert;
        $est_couvert = $manquant <= 0;
        
        return [
            'besoin' => $besoin,
            'quantite' => $quantite,
            'montant_base' => round($montant_base, 2),
            'frais_percent' => $frais_percent,
            'montant_total' => round($montant_total, 2),
            'montant_couvert' => round($montant_couvert, 2),
            'manquant' => round(max(0, $manquant), 2),
            'est_couvert' => $est_couvert,
            'allocation' => $allocation
        ];
    }
    
    /**
     * Valider un achat (avec transaction)
     */
    public static function valider($besoin_id, $quantite, $frais_percent = null) {
        $pdo = getDatabase();
        
        try {
            $pdo->beginTransaction();
            
            // Obtenir les détails du besoin
            $stmt = $pdo->prepare("SELECT * FROM besoins WHERE id = ? FOR UPDATE");
            $stmt->execute([$besoin_id]);
            $besoin = $stmt->fetch();
            
            if (!$besoin) {
                throw new Exception("Besoin non trouvé");
            }
            
            // Calculer le montant
            $frais_percent = $frais_percent ?? self::getFraisPercent();
            $montant_base = $quantite * floatval($besoin['prix_unitaire']);
            $montant_total = $montant_base * (1 + $frais_percent / 100);
            
            // Obtenir les dons monétaires disponibles (FIFO) avec lock
            // Pour les besoins en argent, on utilise directement la quantite comme montant
            $stmt = $pdo->query("
                SELECT * FROM dons 
                WHERE type = 'argent' AND quantite > 0
                ORDER BY date_saisie ASC
                FOR UPDATE
            ");
            $dons_argent = $stmt->fetchAll();
            
            // Allouer les dons
            $allocation = [];
            $montant_couvert = 0;
            
            foreach ($dons_argent as $don) {
                if ($montant_couvert >= $montant_total) break;
                
                $montant_dispo = floatval($don['quantite']);
                $montant_necessaire = $montant_total - $montant_couvert;
                $montant_utilise = min($montant_dispo, $montant_necessaire);
                
                // Mettre à jour le don
                $nouveau_montant_restant = $montant_dispo - $montant_utilise;
                $stmt = $pdo->prepare("UPDATE dons SET quantite = ? WHERE id = ?");
                $stmt->execute([round($nouveau_montant_restant, 2), $don['id']]);
                
                $allocation[] = [
                    'don_id' => $don['id'],
                    'montant_utilise' => round($montant_utilise, 2)
                ];
                
                $montant_couvert += $montant_utilise;
            }
            
            // Vérifier si le montant est couvert (au moins partiellement)
            if ($montant_couvert <= 0) {
                throw new Exception("Aucun don monétaire disponible pour couvrir cet achat");
            }
            
            // Créer l'achat validé
            $stmt = $pdo->prepare("
                INSERT INTO achats (besoin_id, ville_id, quantite, montant_base, frais_percent, montant_total, status)
                VALUES (?, ?, ?, ?, ?, ?, 'valide')
            ");
            $stmt->execute([
                $besoin_id,
                $besoin['ville_id'],
                $quantite,
                round($montant_base, 2),
                $frais_percent,
                round($montant_total, 2)
            ]);
            
            $achat_id = $pdo->lastInsertId();
            
            // Créer les entrées dans achat_dispatch
            foreach ($allocation as $alloc) {
                $stmt = $pdo->prepare("
                    INSERT INTO achat_dispatch (achat_id, don_id, montant_utilise)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$achat_id, $alloc['don_id'], $alloc['montant_utilise']]);
            }
            
            // Mettre à jour le besoin
            $nouvelle_quantite_satisfaite = floatval($besoin['quantite_satisfaite']) + $quantite;
            
            $stmt = $pdo->prepare("
                UPDATE besoins 
                SET quantite_satisfaite = ?
                WHERE id = ?
            ");
            $stmt->execute([
                round($nouvelle_quantite_satisfaite, 2),
                $besoin_id
            ]);
            
            $pdo->commit();
            
            $manquant = $montant_total - $montant_couvert;
            
            return [
                'success' => true,
                'achat_id' => $achat_id,
                'montant_total' => round($montant_total, 2),
                'montant_couvert' => round($montant_couvert, 2),
                'manquant' => round(max(0, $manquant), 2),
                'allocation' => $allocation
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtenir le dispatch d'un achat
     */
    public static function getDispatch($achat_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT ad.*, d.designation as don_designation, d.date_saisie as don_date
            FROM achat_dispatch ad
            LEFT JOIN dons d ON ad.don_id = d.id
            WHERE ad.achat_id = ?
        ");
        $stmt->execute([$achat_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Supprimer un achat (et annuler le dispatch)
     */
    public static function delete($id) {
        $pdo = getDatabase();
        
        try {
            $pdo->beginTransaction();
            
            // Obtenir l'achat
            $achat = self::getById($id);
            if (!$achat) {
                throw new Exception("Achat non trouvé");
            }
            
            // Restaurer les dons (quantité pour les deux types)
            $dispatchs = self::getDispatch($id);
            foreach ($dispatchs as $d) {
                $stmt = $pdo->prepare("UPDATE dons SET quantite = quantite + ? WHERE id = ?");
                $stmt->execute([$d['montant_utilise'], $d['don_id']]);
            }
            
            // Restaurer le besoin
            $stmt = $pdo->prepare("
                UPDATE besoins 
                SET quantite_satisfaite = quantite_satisfaite - ?
                WHERE id = ?
            ");
            $stmt->execute([
                $achat['quantite'],
                $achat['besoin_id']
            ]);
            
            // Supprimer l'achat (cascadera supprimera achat_dispatch)
            $stmt = $pdo->prepare("DELETE FROM achats WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Couvrir un besoin avec les dons disponibles (dispatch direct)
     */
    public static function couvrirAvecDons($besoin_id) {
        $pdo = getDatabase();
        
        try {
            $pdo->beginTransaction();
            
            // Obtenir les détails du besoin
            $stmt = $pdo->prepare("SELECT * FROM besoins WHERE id = ? FOR UPDATE");
            $stmt->execute([$besoin_id]);
            $besoin = $stmt->fetch();
            
            if (!$besoin) {
                throw new Exception("Besoin non trouvé");
            }
            
            $quantite_restante = floatval($besoin['quantite']) - floatval($besoin['quantite_satisfaite'] ?? 0);
            
            if ($quantite_restante <= 0) {
                throw new Exception("Le besoin est déjà satisfait");
            }
            
            $allocation = [];
            $quantite_couverte = 0;
            
            // Dons en nature/materiaux avec même designation
            $stmt = $pdo->prepare("
                SELECT d.*, 
                       d.quantite - COALESCE((SELECT SUM(quantite_attribuee) FROM dispatch WHERE don_id = d.id), 0) as reste
                FROM dons d
                WHERE d.type = ? 
                AND d.designation = ?
                AND (d.quantite - COALESCE((SELECT SUM(quantite_attribuee) FROM dispatch WHERE don_id = d.id), 0)) > 0
                ORDER BY d.date_saisie ASC
                FOR UPDATE
            ");
            $stmt->execute([$besoin['type'], $besoin['designation']]);
            $dons_nature = $stmt->fetchAll();
            
            foreach ($dons_nature as $don) {
                if ($quantite_couverte >= $quantite_restante) break;
                
                $reste = floatval($don['reste']);
                $quantite_necessaire = $quantite_restante - $quantite_couverte;
                $quantite_utilise = min($reste, $quantite_necessaire);
                
                // Créer le dispatch
                $stmt = $pdo->prepare("
                    INSERT INTO dispatch (don_id, besoin_id, quantite_attribuee)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$don['id'], $besoin_id, $quantite_utilise]);
                
                $allocation[] = [
                    'don_id' => $don['id'],
                    'type' => $don['type'],
                    'quantite' => $quantite_utilise
                ];
                
                $quantite_couverte += $quantite_utilise;
            }
            
            // Dons en argent si encore besoin
            if ($quantite_couverte < $quantite_restante) {
                $stmt = $pdo->query("
                    SELECT * FROM dons 
                    WHERE type = 'argent' AND quantite > 0
                    ORDER BY date_saisie ASC
                    FOR UPDATE
                ");
                $dons_argent = $stmt->fetchAll();
                
                $montant_necessaire = ($quantite_restante - $quantite_couverte) * floatval($besoin['prix_unitaire']);
                $montant_couvert = 0;
                
                foreach ($dons_argent as $don) {
                    if ($montant_couvert >= $montant_necessaire) break;
                    
                    $montant_dispo = floatval($don['quantite']);
                    $montant_utilise = min($montant_dispo, $montant_necessaire - $montant_couvert);
                    
                    // Mettre à jour le don
                    $stmt = $pdo->prepare("UPDATE dons SET quantite = ? WHERE id = ?");
                    $stmt->execute([round($montant_dispo - $montant_utilise, 2), $don['id']]);
                    
                    $allocation[] = [
                        'don_id' => $don['id'],
                        'type' => 'argent',
                        'montant' => $montant_utilise
                    ];
                    
                    $montant_couvert += $montant_utilise;
                }
                
                // Créer un "achat" pour tracker l'utilisation de l'argent
                $stmt = $pdo->prepare("
                    INSERT INTO achats (besoin_id, ville_id, quantite, montant_base, frais_percent, montant_total, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'valide')
                ");
                $stmt->execute([
                    $besoin_id,
                    $besoin['ville_id'],
                    ($quantite_restante - $quantite_couverte),
                    $montant_couvert,
                    0,
                    $montant_couvert
                ]);
            }
            
            // Mettre à jour le besoin
            $nouvelle_quantite_satisfaite = floatval($besoin['quantite_satisfaite'] ?? 0) + $quantite_restante;
            $stmt = $pdo->prepare("UPDATE besoins SET quantite_satisfaite = ? WHERE id = ?");
            $stmt->execute([round(min($nouvelle_quantite_satisfaite, $besoin['quantite']), 2), $besoin_id]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'quantite_couverte' => $quantite_restante,
                'allocation' => $allocation
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
