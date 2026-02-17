<?php
class Achat{
  public static function getAll() {
    $pdo = getDatabase();
    $stmt = $pdo->query("
      SELECT a.*, b.designation, v.nom AS ville_nom, c.frais_achat AS frais_achat
      FROM achats a
      JOIN besoins b ON a.besoin_id = b.id
      JOIN villes v ON b.ville_id = v.id
      JOIN config c ON a.idconfig = c.id
      ORDER BY a.date_achat DESC
    ");
    return $stmt->fetchAll();
  }

  public static function getById($id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM achats WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function create($besoin_id, $quantite_achetee, $montant_base, $idconfig, $montant_frais, $montant_total) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("
      INSERT INTO achats (besoin_id, quantite_achetee, montant_base, idconfig, montant_frais, montant_total)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$besoin_id, $quantite_achetee, $montant_base, $idconfig, $montant_frais, $montant_total]);
    return $pdo->lastInsertId();
  }

  public static function delete($id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("DELETE FROM achats WHERE id = ?");
    return $stmt->execute([$id]);
  }

  public static function getLast() {
    $pdo = getDatabase();
    $stmt = $pdo->query("SELECT * FROM achats ORDER BY date_achat DESC LIMIT 1");
    return $stmt->fetch();
  }

  // recupere la somme des montants total pour connaitre l'argent deja depense
  public static function getTotalDepense() {
    $pdo = getDatabase();
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant_total), 0) as total FROM achats");
    $row = $stmt->fetch();
    return floatval($row['total'] ?? 0);
  }

  // savoir si un besoin est deja achete
  public static function getByBesoinId($besoin_id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM achats WHERE besoin_id = ?");
    $stmt->execute([$besoin_id]);
    return $stmt->fetchAll();
  }

  public static function getConfig() {
    $pdo = getDatabase();
    $row = $pdo->query("SELECT id, frais_achat FROM config ORDER BY id ASC LIMIT 1")->fetch();
    if (!$row) {
      $pdo->prepare("INSERT INTO config (id, frais_achat) VALUES (1, 10)")->execute();
      return ['id' => 1, 'frais_achat' => 10];
    }
    return $row;
  }

  public static function getArgentRestant() {
    $pdo = getDatabase();
    $total = $pdo->query("SELECT COALESCE(SUM(quantite), 0) as total FROM dons WHERE type = 'argent'")->fetch();
    $argentBrut = floatval($total['total'] ?? 0);
    return $argentBrut - self::getTotalDepense();
  }

  public static function getBesoinsRestants() {
    $pdo = getDatabase();
    $stmt = $pdo->query("
      SELECT b.*, v.nom AS ville_nom,
           COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_couverte
      FROM besoins b
      JOIN villes v ON b.ville_id = v.id
      LEFT JOIN dispatch di ON di.besoin_id = b.id AND di.don_id IS NOT NULL
      WHERE b.type IN ('nature', 'materiaux', 'matériaux')
      GROUP BY b.id
      HAVING COALESCE(SUM(di.quantite_attribuee), 0) < b.quantite
      ORDER BY b.quantite - COALESCE(SUM(di.quantite_attribuee), 0) DESC
    ");
    return $stmt->fetchAll();
  }

  public static function getBesoinAvecCouverture($besoin_id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("
      SELECT b.*, COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_couverte
      FROM besoins b
      LEFT JOIN dispatch di ON di.besoin_id = b.id
      WHERE b.id = ?
      GROUP BY b.id
    ");
    $stmt->execute([$besoin_id]);
    return $stmt->fetch();
  }

  public static function getDonCouvert($besoin_id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("
      SELECT COALESCE(SUM(quantite_attribuee), 0) as total
      FROM dispatch
      WHERE besoin_id = ? AND don_id IS NOT NULL
    ");
    $stmt->execute([$besoin_id]);
    $row = $stmt->fetch();
    return floatval($row['total'] ?? 0);
  }
}

?>