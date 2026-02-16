<!-- Dashboard -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['nbVilles'] ?></div>
        <div class="stat-label">Villes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['totalBesoins'], 0, ',', ' ') ?></div>
        <div class="stat-label">Total Besoins (qté)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['totalDons'], 0, ',', ' ') ?></div>
        <div class="stat-label">Total Dons (qté)</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-value"><?= $stats['tauxCouverture'] ?>%</div>
        <div class="stat-label">Taux de Couverture</div>
    </div>
</div>

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

<!-- Récapitulatif des Dons -->
<div class="card">
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
