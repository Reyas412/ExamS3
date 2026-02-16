<?php
class Region {
    
    public static function getAll() {
        $pdo = getDatabase();
        $stmt = $pdo->query("SELECT * FROM regions ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public static function getById($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("SELECT * FROM regions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($nom) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO regions (nom) VALUES (?)");
        $stmt->execute([$nom]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $nom) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("UPDATE regions SET nom = ? WHERE id = ?");
        return $stmt->execute([$nom, $id]);
    }

    public static function delete($id) {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("DELETE FROM regions WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
