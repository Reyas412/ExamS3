<?php
class DispatchModel {

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
     * Supprime tous les dispatches (reset)
     */
    public static function resetAll() {
        $pdo = getDatabase();
        $pdo->exec("DELETE FROM dispatch");
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
     * Algorithme de dispatch FIFO
     * 1. Prend les dons par ordre chronologique (date_saisie ASC)
     * 2. Pour chaque don, distribue aux besoins correspondants (même type + désignation)
     *    par ordre chronologique (date_saisie ASC)
     */
    public static function runDispatch() {
        $pdo = getDatabase();

        // Reset les dispatches existants
        self::resetAll();

        // Récupérer tous les dons par ordre chronologique
        $dons = $pdo->query("SELECT * FROM dons ORDER BY date_saisie ASC")->fetchAll();

        // Récupérer tous les besoins par ordre chronologique
        $besoins = $pdo->query("SELECT * FROM besoins ORDER BY date_saisie ASC")->fetchAll();

        // Tableau pour suivre combien chaque besoin a déjà reçu
        $besoinCouvert = [];
        foreach ($besoins as $b) {
            $besoinCouvert[$b['id']] = 0;
        }

        // Tableau pour suivre combien de chaque don a été distribué
        $donDistribue = [];
        foreach ($dons as $d) {
            $donDistribue[$d['id']] = 0;
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

                // Créer le dispatch
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

        return $dispatches;
    }

    /**
     * Récupère le tableau de bord : pour chaque ville, les besoins avec quantités couvertes
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
                COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_attribuee,
                (b.quantite - COALESCE(SUM(di.quantite_attribuee), 0)) AS quantite_reste
            FROM villes v
            LEFT JOIN besoins b ON b.ville_id = v.id
            LEFT JOIN dispatch di ON di.besoin_id = b.id
            GROUP BY v.id, v.nom, b.id, b.type, b.designation, b.quantite, b.prix_unitaire
            ORDER BY v.nom, b.date_saisie ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Récapitulatif global des dons (combien distribué vs total)
     */
    public static function getDonsRecap() {
        $pdo = getDatabase();
        $stmt = $pdo->query("
            SELECT 
                d.id,
                d.type,
                d.designation,
                d.quantite AS don_quantite,
                COALESCE(SUM(di.quantite_attribuee), 0) AS quantite_distribuee,
                (d.quantite - COALESCE(SUM(di.quantite_attribuee), 0)) AS quantite_restante
            FROM dons d
            LEFT JOIN dispatch di ON di.don_id = d.id
            GROUP BY d.id, d.type, d.designation, d.quantite
            ORDER BY d.date_saisie ASC
        ");
        return $stmt->fetchAll();
    }
}
