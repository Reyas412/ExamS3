<?php
class Ville {
    
    public static function getAll() {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT v.*, r.nom as region_nom FROM villes v JOIN regions r ON v.region_id = r.id ORDER BY v.nom ASC");
        return $stmt->fetchAll();
    }

    public static function getById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT v.*, r.nom as region_nom FROM villes v JOIN regions r ON v.region_id = r.id WHERE v.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($nom, $region_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO villes (nom, region_id) VALUES (?, ?)");
        $stmt->execute([$nom, $region_id]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $nom, $region_id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("UPDATE villes SET nom = ?, region_id = ? WHERE id = ?");
        return $stmt->execute([$nom, $region_id, $id]);
    }

    public static function delete($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("DELETE FROM villes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
