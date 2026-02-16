<?php
class Besoin {
    
    public static function getAll() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT b.*, v.nom AS ville_nom, 
                   COALESCE(b.quantite_satisfaite, 0) as quantite_satisfaite
            FROM besoins b 
            JOIN villes v ON b.ville_id = v.id 
            ORDER BY b.date_saisie ASC
        ");
        return $stmt->fetchAll();
    }

    public static function getById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT b.*, v.nom AS ville_nom 
            FROM besoins b 
            JOIN villes v ON b.ville_id = v.id 
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getFiltered($ville_id = null, $type = null, $restants = null) {
        $pdo = getDatabase();
        
        $sql = "
            SELECT b.*, v.nom AS ville_nom, 
                   COALESCE(b.quantite_satisfaite, 0) as quantite_satisfaite
            FROM besoins b 
            JOIN villes v ON b.ville_id = v.id 
            WHERE 1=1
        ";
        $params = [];
        
        if ($ville_id) {
            $sql .= " AND b.ville_id = ?";
            $params[] = $ville_id;
        }
        
        if ($type) {
            $sql .= " AND b.type = ?";
            $params[] = $type;
        }
        
        if ($restants) {
            $sql .= " AND (b.quantite - COALESCE(b.quantite_satisfaite, 0)) > 0";
        }
        
        $sql .= " ORDER BY b.date_saisie ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public static function getByVilleId($ville_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT b.*, v.nom AS ville_nom, 
                   COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_attribuee
            FROM besoins b
            JOIN villes v ON b.ville_id = v.id
            LEFT JOIN dispatch di ON di.besoin_id = b.id
            WHERE b.ville_id = ?
            GROUP BY b.id
            ORDER BY b.date_saisie ASC
        ");
        $stmt->execute([$ville_id]);
        return $stmt->fetchAll();
    }

    public static function create($ville_id, $type, $designation, $quantite, $prix_unitaire, $date_saisie = null) {
        $pdo = getDatabase();
        if ($date_saisie) {
            $stmt = $pdo->prepare("INSERT INTO besoins (ville_id, type, designation, quantite, prix_unitaire, date_saisie) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ville_id, $type, $designation, $quantite, $prix_unitaire, $date_saisie]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO besoins (ville_id, type, designation, quantite, prix_unitaire) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$ville_id, $type, $designation, $quantite, $prix_unitaire]);
        }
        return $pdo->lastInsertId();
    }

    public static function delete($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("DELETE FROM besoins WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function update($id, $ville_id, $type, $designation, $quantite, $prix_unitaire, $date_saisie = null) {
        $pdo = getDatabase();
        if ($date_saisie) {
            $stmt = $pdo->prepare("UPDATE besoins SET ville_id = ?, type = ?, designation = ?, quantite = ?, prix_unitaire = ?, date_saisie = ? WHERE id = ?");
            $stmt->execute([$ville_id, $type, $designation, $quantite, $prix_unitaire, $date_saisie, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE besoins SET ville_id = ?, type = ?, designation = ?, quantite = ?, prix_unitaire = ? WHERE id = ?");
            $stmt->execute([$ville_id, $type, $designation, $quantite, $prix_unitaire, $id]);
        }
        return $stmt->rowCount();
    }

    /**
     * Récupère le reste (quantité non couverte) pour un besoin donné
     */
    public static function getResteById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT b.quantite - COALESCE(SUM(d.quantite_attribuee), 0) AS reste
            FROM besoins b
            LEFT JOIN dispatch d ON d.besoin_id = b.id
            WHERE b.id = ?
            GROUP BY b.id
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? floatval($row['reste']) : 0;
    }
}
