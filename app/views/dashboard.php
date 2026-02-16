<!-- Dashboard - KPIs -->
<div class="stats-grid">
    <a href="/dashboard/villes" class="stat-card" style="display: block; text-decoration: none; color: inherit;">
        <div class="stat-value"><?= $stats['nbVilles'] ?></div>
        <div class="stat-label">Villes</div>
    </a>
    <a href="/dashboard/besoins" class="stat-card" style="display: block; text-decoration: none; color: inherit;">
        <div class="stat-value"><?= number_format($stats['totalBesoins'], 0, ',', ' ') ?></div>
        <div class="stat-label">Total Besoins (qté)</div>
    </a>
    <a href="/dashboard/dons" class="stat-card" style="display: block; text-decoration: none; color: inherit;">
        <div class="stat-value"><?= number_format($stats['totalDons'], 0, ',', ' ') ?></div>
        <div class="stat-label">Dons Reçus (qté)</div>
    </a>
    <div class="stat-card accent">
        <div class="stat-value"><?= $stats['tauxCouverture'] ?>%</div>
        <div class="stat-label">Taux de Couverture</div>
    </div>
</div>

<!-- Bouton Rapport -->
<div style="text-align: right; margin-bottom: 20px;">
    <a href="/dispatch/report" class="btn btn-primary">&#128196; Télécharger Rapport</a>
</div>

<!-- Récapitulatif des Villes -->
<div id="villes-recap">
<!-- Tableau par ville -->
<?php foreach ($parVille as $villeId => $ville): ?>
<div class="card">
    <div class="card-header">
        <h3>&#9962; <?= htmlspecialchars($ville['nom']) ?></h3>
    </div>
    <div class="card-body">
        <?php if (empty($ville['besoins'])): ?>
            <p class="text-muted">Aucun besoin enregistré pour cette ville.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Produit</th>
                        <th>Besoin</th>
                        <th>Attribué</th>
                        <th>Reste</th>
                        <th>P.U.</th>
                        <th>Montant Total</th>
                        <th>Couverture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ville['besoins'] as $b): ?>
                    <?php 
                        $pct = floatval($b['besoin_quantite']) > 0 
                            ? round((floatval($b['quantite_attribuee']) / floatval($b['besoin_quantite'])) * 100, 1)
                            : 0;
                        $statusClass = $pct >= 100 ? 'badge-success' : ($pct > 0 ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= number_format($b['besoin_quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite_attribuee'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite_reste'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['montant_total'], 2, ',', ' ') ?></td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar <?= $statusClass ?>" style="width: <?= min($pct, 100) ?>%"></div>
                            </div>
                            <span class="badge <?= $statusClass ?>"><?= $pct ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Récapitulatif des Besoins -->
<div id="besoins-recap" class="card">
    <div class="card-header">
        <h3>&#128230; Récapitulatif des Besoins</h3>
    </div>
    <div class="card-body">
        <?php 
        // Collect all besoins for recap
        $allBesoins = [];
        foreach ($parVille as $villeId => $ville) {
            if (!empty($ville['besoins'])) {
                foreach ($ville['besoins'] as $b) {
                    $b['ville_nom'] = $ville['nom'];
                    $allBesoins[] = $b;
                }
            }
        }
        ?>
        <?php if (empty($allBesoins)): ?>
            <p class="text-muted">Aucun besoin enregistré.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Produit</th>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Attribué</th>
                        <th>Reste</th>
                        <th>Couverture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allBesoins as $b): ?>
                    <?php 
                        $pct = floatval($b['besoin_quantite']) > 0 
                            ? round((floatval($b['quantite_attribuee']) / floatval($b['besoin_quantite'])) * 100, 1)
                            : 0;
                        $statusClass = $pct >= 100 ? 'badge-success' : ($pct > 0 ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= htmlspecialchars($b['ville_nom']) ?></td>
                        <td><?= number_format($b['besoin_quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite_attribuee'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite_reste'], 2, ',', ' ') ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= $pct ?>%</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Récapitulatif des Dons -->
<div id="dons-recap" class="card">
    <div class="card-header">
        <h3>&#10084; Récapitulatif des Dons</h3>
    </div>
    <div class="card-body">
        <?php if (empty($donsRecap)): ?>
            <p class="text-muted">Aucun don enregistré.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Quantité Totale</th>
                        <th>Distribué</th>
                        <th>Restant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donsRecap as $don): ?>
                    <tr>
                        <td><span class="badge badge-type"><?= htmlspecialchars($don['type']) ?></span></td>
                        <td><?= htmlspecialchars($don['designation']) ?></td>
                        <td><?= number_format($don['don_quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($don['quantite_distribuee'], 2, ',', ' ') ?></td>
                        <td><?= number_format($don['quantite_restante'], 2, ',', ' ') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
