<?php
class Besoin {
    
    public static function getAll() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT b.*, v.nom AS ville_nom 
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
