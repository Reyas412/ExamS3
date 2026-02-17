<?php
class Don {
    
    public static function getAll() {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC");
        return $stmt->fetchAll();
    }

    public static function getById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT * FROM dons WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($type, $designation, $quantite, $unite = null, $date_saisie = null) {
        $pdo = getDatabase();
        if ($date_saisie) {
            $stmt = $pdo->prepare("INSERT INTO dons (type, designation, quantite, unite, date_saisie) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$type, $designation, $quantite, $unite, $date_saisie]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO dons (type, designation, quantite, unite) VALUES (?, ?, ?, ?)");
            $stmt->execute([$type, $designation, $quantite, $unite]);
        }
        return $pdo->lastInsertId();
    }

    public static function delete($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("DELETE FROM dons WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function update($id, $type, $designation, $quantite, $unite = null, $date_saisie = null) {
        $pdo = getDatabase();
        if ($date_saisie) {
            $stmt = $pdo->prepare("UPDATE dons SET type = ?, designation = ?, quantite = ?, unite = ?, date_saisie = ? WHERE id = ?");
            $stmt->execute([$type, $designation, $quantite, $unite, $date_saisie, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE dons SET type = ?, designation = ?, quantite = ?, unite = ? WHERE id = ?");
            $stmt->execute([$type, $designation, $quantite, $unite, $id]);
        }
        return $stmt->rowCount();
    }

    /**
     * Récupère la quantité restante (non encore dispatchée) d'un don
     */
    public static function getResteById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT d.quantite - COALESCE(SUM(di.quantite_attribuee), 0) AS reste
            FROM dons d
            LEFT JOIN dispatch di ON di.don_id = d.id
            WHERE d.id = ?
            GROUP BY d.id
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? floatval($row['reste']) : 0;
    }
}
