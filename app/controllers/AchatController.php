<?php
class AchatController {

    /**
     * Affiche la page de simulation d'achat
     */
    public static function index() {
        $besoins = Besoin::getAll();
        $villes = Ville::getAll();
        $achats = Achat::getAll();
        $argentRestant = Achat::getArgentRestant();
        $config = Achat::getConfig();
        $fraisAchat = floatval($config['frais_achat'] ?? 10);

        // Ajouter la quantite restante et couverture par dons
        foreach ($besoins as &$b) {
            $reste = Besoin::getResteById($b['id']);
            $b['quantite_restante'] = $reste;
            $b['don_couvert'] = Achat::getDonCouvert($b['id']);
            $b['montant_restant'] = $b['quantite_restante'] * floatval($b['prix_unitaire']);
        }

        Flight::render('simulation', [
            'besoins' => $besoins,
            'villes' => $villes,
            'achats' => $achats,
            'argent_restant' => $argentRestant,
            'frais_achat' => $fraisAchat
        ], 'body_content');
        Flight::render('layout', [
            'title' => 'Simulation Achat',
            'active' => 'simulation'
        ]);
    }

    /**
     * Valide un achat
     */
    public static function validate() {
        $data = Flight::request()->data;
        $besoin_id = intval($data->besoin_id);
        $quantiteAchetee = floatval($data->quantite_achetee ?? 0);
        $pdo = getDatabase();

        try {
            $pdo->beginTransaction();

            $config = Achat::getConfig();
            $configId = intval($config['id']);
            $fraisAchat = floatval($config['frais_achat'] ?? 10);

            $besoin = Achat::getBesoinAvecCouverture($besoin_id);
            if (!$besoin) {
                throw new Exception("Besoin non trouve");
            }

            $quantiteRestante = floatval($besoin['quantite']) - floatval($besoin['quantite_couverte']);
            if ($quantiteRestante <= 0) {
                throw new Exception("Ce besoin est deja totalement couvert");
            }

            if ($quantiteAchetee <= 0) {
                throw new Exception("La quantite a acheter est invalide");
            }

            if ($quantiteAchetee > $quantiteRestante) {
                throw new Exception("La quantite depasse le reste disponible");
            }

            $quantiteDon = Achat::getDonCouvert($besoin_id);
            if ($quantiteDon > 0) {
                throw new Exception("Ce besoin est deja couvert par des dons (" . number_format($quantiteDon, 0, ',', ' ') . " unites). Utilisez d'abord le dispatch classique.");
            }

            $montantBase = $quantiteAchetee * floatval($besoin['prix_unitaire']);
            $montantFrais = $montantBase * ($fraisAchat / 100);
            $montantAvecFrais = $montantBase + $montantFrais;

            $argentRestant = Achat::getArgentRestant();
            if ($argentRestant < $montantAvecFrais) {
                throw new Exception("Argent insuffisant. Montant necessaire: " . number_format($montantAvecFrais, 0, ',', ' '));
            }

            Achat::create(
                $besoin_id,
                $quantiteAchetee,
                $montantBase,
                $configId,
                $montantFrais,
                $montantAvecFrais
            );

            $insertDispatch = $pdo->prepare(
                "INSERT INTO dispatch (don_id, besoin_id, quantite_attribuee) VALUES (NULL, ?, ?)"
            );
            $insertDispatch->execute([$besoin_id, $quantiteAchetee]);

            $pdo->commit();

            Flight::redirect('/dispatch?success=' . urlencode("Achat valide pour " . number_format($quantiteAchetee, 2, ',', ' ') . " unites"));

        } catch (Exception $e) {
            $pdo->rollBack();
            Flight::redirect('/simulation?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Retourne les donnees de recapitulatif en JSON
     */
    public static function recapJson() {
        $pdo = getDatabase();

        $totalBesoins = $pdo->query("
            SELECT COALESCE(SUM(quantite * prix_unitaire), 0) as total
            FROM besoins
        ")->fetch()['total'];

        $satisfaits = $pdo->query("
            SELECT COALESCE(SUM(di.quantite_attribuee * b.prix_unitaire), 0) as total
            FROM dispatch di
            JOIN besoins b ON di.besoin_id = b.id
            WHERE di.don_id IS NOT NULL
        ")->fetch()['total'];

        $argentUtilise = Achat::getTotalDepense();
        $satisfaits += $argentUtilise;

        $restants = floatval($totalBesoins) - floatval($satisfaits);

        header('Content-Type: application/json');
        echo json_encode([
            'total_besoins' => number_format(floatval($totalBesoins), 0, ',', ' '),
            'satisfaits' => number_format(floatval($satisfaits), 0, ',', ' '),
            'restants' => number_format(floatval($restants), 0, ',', ' '),
            'taux' => floatval($totalBesoins) > 0 ? round((floatval($satisfaits) / floatval($totalBesoins)) * 100, 1) : 0
        ]);
    }
}
