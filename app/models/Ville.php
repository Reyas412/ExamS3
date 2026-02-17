<?php
class Ville {
    
    public static function getAll() {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT v.*, r.nom as region_nom FROM villes v JOIN regions r ON v.idregion = r.id ORDER BY v.nom ASC");
        return $stmt->fetchAll();
    }

    public static function getById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT v.*, r.nom as region_nom FROM villes v JOIN regions r ON v.idregion = r.id WHERE v.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($nom, $idregion) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO villes (nom, idregion) VALUES (?, ?)");
        $stmt->execute([$nom, $idregion]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $nom, $idregion) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("UPDATE villes SET nom = ?, idregion = ? WHERE id = ?");
        return $stmt->execute([$nom, $idregion, $id]);
    }

    public static function getByRegionId($region_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            SELECT v.*, r.nom as region_nom
            FROM villes v
            JOIN regions r ON v.idregion = r.id
            WHERE v.idregion = ?
            ORDER BY v.nom ASC
        ");
        $stmt->execute([$region_id]);
        return $stmt->fetchAll();
    }

    public static function delete($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("DELETE FROM villes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
