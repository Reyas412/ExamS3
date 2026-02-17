<?php
/**
 * VUE — Page Récapitulation
 * Fichier : views/recapitulation/index.php
 *
 * Variables depuis le Controller :
 *   $totaux      → ['total'=>1700000, 'satisfait'=>1420000, 'restant'=>280000]
 *   $par_ville   → tableau par ville :
 *                  [
 *                    ['ville_nom'=>'Toamasina', 'total'=>950000,
 *                     'satisfait'=>830000, 'restant'=>120000, 'pct'=>87],
 *                    ...
 *                  ]
 *   $last_update → datetime de la dernière mise à jour
 *
 * ROUTE AJAX (JSON) : GET /recapitulation/data
 *   → Renvoie les mêmes données en JSON pour le bouton Actualiser
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Récapitulation — BNGRC</title>
  <link rel="stylesheet" href="/css/global.css">
  <link rel="stylesheet" href="/css/recapitulation.css">
</head>
<body>

<?php include 'partials/navbar.php'; ?>

<div class="container">

  <!-- ══════════════════════════════════
       EN-TÊTE
  ══════════════════════════════════ -->
  <div class="page-header">
    <div>
      <h1 class="page-title">📊 Récapitulation</h1>
      <p class="page-subtitle">
        Bilan financier global — besoins totaux, satisfaits et restants en montant
      </p>
    </div>

    <!-- BOUTON ACTUALISER (Ajax) -->
    <div class="refresh-btn-wrap">
      <span class="last-update" id="last-update">
        Mis à jour : <?= htmlspecialchars($last_update ?? date('H:i:s')) ?>
      </span>
      <button class="btn btn-refresh" id="btn-actualiser" onclick="actualiserDonnees()">
        🔄 Actualiser
      </button>
    </div>
  </div>

  <!-- NOTIFICATION AJAX -->
  <div class="ajax-notif" id="ajax-notif">
    🔄 Données actualisées sans rechargement de page !
  </div>

  <!-- ══════════════════════════════════
       CARTES KPI
  ══════════════════════════════════ -->
  <div class="recap-kpi" id="recap-kpi">

    <div class="kpi-card total">
      <div class="kpi-label">📋 Besoins totaux</div>
      <div class="kpi-value" id="kpi-total">
        <?= number_format($totaux['total'], 0, ',', ' ') ?>
      </div>
      <div class="kpi-sub">Ar — tous besoins confondus</div>
    </div>

    <div class="kpi-card satisfait">
      <div class="kpi-label">✅ Besoins satisfaits</div>
      <div class="kpi-value" id="kpi-satisfait">
        <?= number_format($totaux['satisfait'], 0, ',', ' ') ?>
      </div>
      <div class="kpi-sub">
        Ar —
        <span id="kpi-pct-satisfait">
          <?= $totaux['total'] > 0
              ? round($totaux['satisfait'] / $totaux['total'] * 100)
              : 0 ?>%
        </span>
        couvert
      </div>
    </div>

    <div class="kpi-card restant">
      <div class="kpi-label">❌ Besoins restants</div>
      <div class="kpi-value" id="kpi-restant">
        <?= number_format($totaux['restant'], 0, ',', ' ') ?>
      </div>
      <div class="kpi-sub">
        Ar —
        <span id="kpi-pct-restant">
          <?= $totaux['total'] > 0
              ? round($totaux['restant'] / $totaux['total'] * 100)
              : 0 ?>%
        </span>
        non couvert
      </div>
    </div>

  </div>

  <!-- ══════════════════════════════════
       TABLEAU DÉTAIL PAR VILLE
  ══════════════════════════════════ -->
  <div class="section-label">Détail par ville</div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ville</th>
          <th>Besoins totaux</th>
          <th>Satisfaits</th>
          <th>Restants</th>
          <th>Taux de couverture</th>
        </tr>
      </thead>
      <tbody id="recap-tbody">

        <?php if (empty($par_ville)): ?>
          <tr>
            <td colspan="5" class="empty-state">
              Aucune donnée disponible
            </td>
          </tr>

        <?php else: ?>
          <?php foreach ($par_ville as $ligne): ?>
            <?php
              /* Choisir la couleur selon le taux */
              if ($ligne['pct'] >= 80) {
                $pct_class  = 'pct-high';
                $bar_color  = 'var(--green)';
              } elseif ($ligne['pct'] >= 50) {
                $pct_class  = 'pct-mid';
                $bar_color  = 'var(--yellow)';
              } else {
                $pct_class  = 'pct-low';
                $bar_color  = 'var(--red)';
              }
            ?>
            <tr>
              <td><strong>📍 <?= htmlspecialchars($ligne['ville_nom']) ?></strong></td>

              <td class="cell-total">
                <?= number_format($ligne['total'], 0, ',', ' ') ?> Ar
              </td>

              <td class="cell-satisfait">
                <?= number_format($ligne['satisfait'], 0, ',', ' ') ?> Ar
              </td>

              <td class="cell-restant">
                <?= number_format($ligne['restant'], 0, ',', ' ') ?> Ar
              </td>

              <td>
                <span class="pct-badge <?= $pct_class ?>">
                  <?= $ligne['pct'] ?>%
                </span>
                <div class="mini-bar">
                  <div class="mini-fill"
                       style="width:<?= $ligne['pct'] ?>%; background:<?= $bar_color ?>">
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

      </tbody>
    </table>
  </div>

</div>

<!-- ══════════════════════════════════
     JAVASCRIPT — AJAX ACTUALISER
══════════════════════════════════ -->
<script>
/**
 * actualiserDonnees()
 * Appelle /recapitulation/data en GET
 * Le controller répond avec du JSON
 * On met à jour les KPI et le tableau SANS recharger la page
 */
function actualiserDonnees() {
  const btn   = document.getElementById('btn-actualiser');
  const kpi   = document.getElementById('recap-kpi');
  const tbody = document.getElementById('recap-tbody');
  const notif = document.getElementById('ajax-notif');

  // 1. Animation de chargement
  btn.disabled   = true;
  btn.textContent = '⏳ Chargement...';
  kpi.classList.add('is-loading');
  tbody.classList.add('is-loading');

  // 2. Appel Ajax vers le controller PHP
  fetch('/recapitulation/data', {
    method: 'GET',
    headers: { 'Accept': 'application/json' }
  })
  .then(response => {
    if (!response.ok) throw new Error('Erreur serveur : ' + response.status);
    return response.json();
  })
  .then(data => {
    // 3. Mettre à jour les KPI
    majKPI(data.totaux);

    // 4. Mettre à jour le tableau
    majTableau(data.par_ville);

    // 5. Mettre à jour l'heure
    document.getElementById('last-update').textContent =
      'Mis à jour : ' + data.last_update;

    // 6. Afficher la notification
    notif.classList.add('show');
    setTimeout(() => notif.classList.remove('show'), 3000);
  })
  .catch(err => {
    alert('Erreur lors de la mise à jour : ' + err.message);
  })
  .finally(() => {
    // 7. Remettre le bouton
    btn.disabled    = false;
    btn.textContent = '🔄 Actualiser';
    kpi.classList.remove('is-loading');
    tbody.classList.remove('is-loading');
  });
}

/**
 * Met à jour les 3 cartes KPI
 * @param {Object} totaux - {total, satisfait, restant}
 */
function majKPI(totaux) {
  document.getElementById('kpi-total').textContent =
    formatAr(totaux.total);

  document.getElementById('kpi-satisfait').textContent =
    formatAr(totaux.satisfait);

  document.getElementById('kpi-restant').textContent =
    formatAr(totaux.restant);

  const pctSatisfait = totaux.total > 0
    ? Math.round(totaux.satisfait / totaux.total * 100)
    : 0;

  const pctRestant = totaux.total > 0
    ? Math.round(totaux.restant / totaux.total * 100)
    : 0;

  document.getElementById('kpi-pct-satisfait').textContent = pctSatisfait + '%';
  document.getElementById('kpi-pct-restant').textContent   = pctRestant   + '%';
}

/**
 * Reconstruit le tbody du tableau par ville
 * @param {Array} parVille - [{ville_nom, total, satisfait, restant, pct}, ...]
 */
function majTableau(parVille) {
  const tbody = document.getElementById('recap-tbody');

  if (!parVille || parVille.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="empty-state">Aucune donnée disponible</td>
      </tr>`;
    return;
  }

  tbody.innerHTML = parVille.map(ligne => {
    let pctClass, barColor;
    if (ligne.pct >= 80) {
      pctClass = 'pct-high'; barColor = 'var(--green)';
    } else if (ligne.pct >= 50) {
      pctClass = 'pct-mid';  barColor = 'var(--yellow)';
    } else {
      pctClass = 'pct-low';  barColor = 'var(--red)';
    }

    return `
      <tr>
        <td><strong>📍 ${escapeHtml(ligne.ville_nom)}</strong></td>
        <td class="cell-total">${formatAr(ligne.total)}</td>
        <td class="cell-satisfait">${formatAr(ligne.satisfait)}</td>
        <td class="cell-restant">${formatAr(ligne.restant)}</td>
        <td>
          <span class="pct-badge ${pctClass}">${ligne.pct}%</span>
          <div class="mini-bar">
            <div class="mini-fill" style="width:${ligne.pct}%;background:${barColor}"></div>
          </div>
        </td>
      </tr>`;
  }).join('');
}

/* ── UTILITAIRES ── */
function formatAr(n) {
  return Number(n).toLocaleString('fr-FR') + ' Ar';
}

function escapeHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

/* ── INIT : anime les mini-barres au chargement ── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.mini-fill').forEach(bar => {
    const w = bar.style.width;
    bar.style.width = '0%';
    setTimeout(() => bar.style.width = w, 300);
  });
});
</script>

</body>
</html>
