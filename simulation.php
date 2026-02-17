<?php
/**
 * VUE — Page Simulation
 * Fichier : views/simulation/index.php
 *
 * Variables depuis le Controller :
 *   $villes_besoins  → tableau : chaque ville avec ses besoins et dons attribués
 *                      Structure :
 *                      [
 *                        'ville'    => ['id'=>1, 'nom'=>'Toamasina'],
 *                        'besoins'  => [
 *                          [
 *                            'article'        => 'Riz',
 *                            'type'           => 'nature',
 *                            'qte_demandee'   => 100,
 *                            'unite'          => 'kg',
 *                            'prix_unitaire'  => 2000,
 *                            'qte_attribuee'  => 80,
 *                            'qte_restante'   => 20,
 *                            'montant_total'  => 200000,
 *                            'montant_satisfait' => 160000,
 *                            'pct_couvert'    => 80,
 *                          ], ...
 *                        ]
 *                      ]
 *   $totaux          → ['total'=>1700000, 'satisfait'=>1420000, 'restant'=>280000]
 *   $is_simule       → bool — true si on affiche le résultat de simulation
 *   $is_valide       → bool — true si la validation a été effectuée
 *   $success         → message de succès après validation
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Simulation — BNGRC</title>
  <link rel="stylesheet" href="/css/global.css">
  <link rel="stylesheet" href="/css/simulation.css">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<div class="container">

  <!-- ══════════════════════════════════
       EN-TÊTE
  ══════════════════════════════════ -->
  <div class="page-header">
    <div>
      <h1 class="page-title">🎮 Simulation du Dispatch</h1>
      <p class="page-subtitle">
        Visualise la répartition des dons <strong>avant</strong> de confirmer.
        Les dons sont dispatachés par ordre de date.
      </p>
    </div>

    <div class="sim-actions">
      <!-- BOUTON SIMULER — GET simple -->
      <form method="GET" action="/simulation">
        <button type="submit" name="action" value="simuler" class="btn btn-simulate">
          👁️ Simuler
        </button>
      </form>

      <!-- BOUTON VALIDER — POST pour modifier la DB -->
      <form method="POST" action="/simulation/valider"
            onsubmit="return confirm('Confirmer le dispatch ? Cette action est irréversible.')">
        <?php /* Token CSRF pour sécuriser le POST */ ?>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button type="submit" class="btn btn-success"
          <?= empty($is_simule) ? 'disabled title="Simulez d\'abord"' : '' ?>>
          ✅ Valider le dispatch
        </button>
      </form>
    </div>
  </div>

  <!-- MESSAGE SUCCÈS APRÈS VALIDATION -->
  <?php if (!empty($success)): ?>
    <div class="alert alert-success">
      ✅ <?= htmlspecialchars($success) ?>
      — <a href="/recapitulation" style="color:inherit;font-weight:700">Voir la récapitulation →</a>
    </div>
  <?php endif; ?>

  <!-- INFO : résultat non sauvegardé -->
  <?php if (!empty($is_simule) && empty($is_valide)): ?>
    <div class="alert alert-warning">
      ⚠️ Ceci est une <strong>simulation</strong> — aucune donnée n'est encore sauvegardée.
      Cliquez sur "Valider" pour confirmer le dispatch.
    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════
       ÉTAT VIDE (avant simulation)
  ══════════════════════════════════ -->
  <?php if (empty($is_simule)): ?>
    <div class="sim-empty-state">
      <span class="icon">👆</span>
      <p>Clique sur "Simuler" pour voir la répartition des dons</p>
      <small>Aucune donnée ne sera modifiée</small>
    </div>

  <?php else: ?>

    <!-- ══════════════════════════════════
         LÉGENDE
    ══════════════════════════════════ -->
    <div class="legend">
      <div class="legend-item">
        <div class="legend-dot" style="background:var(--green)"></div>
        Couvert à 100%
      </div>
      <div class="legend-item">
        <div class="legend-dot" style="background:var(--yellow)"></div>
        Partiellement couvert
      </div>
      <div class="legend-item">
        <div class="legend-dot" style="background:var(--red)"></div>
        Non couvert
      </div>
    </div>

    <!-- ══════════════════════════════════
         RÉSULTATS PAR VILLE
    ══════════════════════════════════ -->
    <?php if (empty($villes_besoins)): ?>
      <div class="alert alert-info">
        ℹ️ Aucun besoin ou aucun don enregistré. Commencez par saisir des besoins et des dons.
      </div>

    <?php else: ?>
      <?php foreach ($villes_besoins as $bloc): ?>
        <div class="sim-bloc-ville">

          <!-- EN-TÊTE VILLE -->
          <div class="sim-ville-header">
            <h3>📍 <?= htmlspecialchars($bloc['ville']['nom']) ?></h3>
            <span class="ville-tag">
              <?= count($bloc['besoins']) ?> besoin<?= count($bloc['besoins']) > 1 ? 's' : '' ?>
            </span>
          </div>

          <!-- BESOINS DE CETTE VILLE -->
          <?php foreach ($bloc['besoins'] as $b): ?>
            <?php
              /* Déterminer le statut pour les classes CSS */
              if ($b['pct_couvert'] >= 100) {
                $status_class = 'status-full';
                $status_label = '✅ Complet';
                $fill_class   = 'fill-full';
              } elseif ($b['pct_couvert'] > 0) {
                $status_class = 'status-partial';
                $status_label = '⚠️ Partiel ' . $b['pct_couvert'] . '%';
                $fill_class   = 'fill-partial';
              } else {
                $status_class = 'status-none';
                $status_label = '❌ Non couvert';
                $fill_class   = 'fill-none';
              }
            ?>
            <div class="need-row">
              <div>
                <div class="need-name">
                  <?= $b['type'] === 'nature' ? '🌾' : ($b['type'] === 'materiaux' ? '🏗️' : '💰') ?>
                  <?= htmlspecialchars($b['article']) ?>
                </div>

                <div class="need-detail">
                  Demandé :
                  <?= $b['qte_demandee'] ?> <?= $b['unite'] ?>
                  × <?= number_format($b['prix_unitaire'], 0, ',', ' ') ?> Ar
                  = <strong><?= number_format($b['montant_total'], 0, ',', ' ') ?> Ar</strong>
                </div>

                <?php if ($b['qte_attribuee'] > 0): ?>
                  <div class="need-detail ok">
                    Don attribué : <?= $b['qte_attribuee'] ?> <?= $b['unite'] ?>
                    (<?= number_format($b['montant_satisfait'], 0, ',', ' ') ?> Ar)
                  </div>
                <?php else: ?>
                  <div class="need-detail ko">
                    Aucun don disponible pour ce besoin
                  </div>
                <?php endif; ?>

                <?php if ($b['qte_restante'] > 0): ?>
                  <div class="need-detail warn">
                    Manque encore : <?= $b['qte_restante'] ?> <?= $b['unite'] ?>
                    (<?= number_format($b['montant_total'] - $b['montant_satisfait'], 0, ',', ' ') ?> Ar)
                  </div>
                <?php endif; ?>

                <!-- BARRE DE PROGRESSION -->
                <div class="progress-bar">
                  <div class="progress-fill <?= $fill_class ?>"
                       style="width:<?= min($b['pct_couvert'], 100) ?>%">
                  </div>
                </div>
              </div>

              <!-- STATUS TAG -->
              <span class="status-tag <?= $status_class ?>">
                <?= $status_label ?>
              </span>
            </div>
          <?php endforeach; ?>

        </div>
      <?php endforeach; ?>

      <!-- ══════════════════════════════════
           RÉSUMÉ GLOBAL
      ══════════════════════════════════ -->
      <div class="sim-resume">
        <div class="sim-resume-item">
          <span>Besoins totaux</span>
          <strong style="color:var(--accent)">
            <?= number_format($totaux['total'], 0, ',', ' ') ?> Ar
          </strong>
        </div>
        <div class="sim-resume-item">
          <span>Dons dispatachés</span>
          <strong style="color:var(--green)">
            <?= number_format($totaux['satisfait'], 0, ',', ' ') ?> Ar
          </strong>
        </div>
        <div class="sim-resume-item">
          <span>Restants</span>
          <strong style="color:var(--red)">
            <?= number_format($totaux['restant'], 0, ',', ' ') ?> Ar
          </strong>
        </div>
        <?php if (empty($is_valide)): ?>
          <div class="warning-note">
            ⚠️ Simulation — rien n'est encore sauvegardé
          </div>
        <?php endif; ?>
      </div>

    <?php endif; ?>
  <?php endif; ?>

</div>

<!-- ══════════════════════════════════
     JS — Animation barres au chargement
══════════════════════════════════ -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Anime les barres de progression au chargement
    document.querySelectorAll('.progress-fill').forEach(bar => {
      const targetWidth = bar.style.width;
      bar.style.width = '0%';
      setTimeout(() => {
        bar.style.width = targetWidth;
      }, 200);
    });
  });
</script>

</body>
</html>
