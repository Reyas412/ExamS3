<!-- Récapitulatif des Dons -->
<h2 style="margin-bottom: 20px;">&#10084; Récapitulatif des Dons</h2>

<!-- Stats -->
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
        <div class="stat-label">Dons Reçus (qté)</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-value"><?= $stats['tauxCouverture'] ?>%</div>
        <div class="stat-label">Taux de Couverture</div>
    </div>
</div>

<!-- Bouton Rapport -->
<div style="text-align: right; margin-bottom: 20px;">
    <a href="/dispatch/report" class="btn btn-primary">&#128196; Télécharger Rapport</a>
</div>

<!-- Tableau des dons -->
<div class="card">
    <div class="card-header">
        <h3>&#10084; Détail des Dons</h3>
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
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donsRecap as $don): ?>
                    <?php 
                        $pct = floatval($don['don_quantite']) > 0 
                            ? round((floatval($don['quantite_distribuee']) / floatval($don['don_quantite'])) * 100, 1)
                            : 0;
                        $statusClass = $pct >= 100 ? 'badge-success' : ($pct > 0 ? 'badge-warning' : 'badge-danger');
                        $statusText = $pct >= 100 ? 'Entièrement distribué' : ($pct > 0 ? 'Partiellement distribué' : 'Non distribué');
                    ?>
                    <tr>
                        <td><span class="badge badge-type"><?= htmlspecialchars($don['type']) ?></span></td>
                        <td><?= htmlspecialchars($don['designation']) ?></td>
                        <td><?= number_format($don['don_quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($don['quantite_distribuee'], 2, ',', ' ') ?></td>
                        <td><?= number_format($don['quantite_restante'], 2, ',', ' ') ?></td>
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

<!-- Récapitulatif des Villes -->
<div class="card">
    <div class="card-header">
        <h3>&#9962; Récapitulatif des Villes</h3>
    </div>
    <div class="card-body">
        <?php 
        $parVille = DispatchModel::getParVille();
        ?>
        <?php if (empty($parVille)): ?>
            <p class="text-muted">Aucune ville enregistrée.</p>
        <?php else: ?>
        <?php foreach ($parVille as $villeId => $ville): ?>
            <h4 style="margin-top: 15px;">&#9962; <?= htmlspecialchars($ville['nom']) ?></h4>
            <?php if (empty($ville['besoins'])): ?>
                <p class="text-muted">Aucun besoin pour cette ville.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Produit</th>
                            <th>Besoin</th>
                            <th>Attribué</th>
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
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Récapitulatif des Besoins -->
<div class="card">
    <div class="card-header">
        <h3>&#128230; Récapitulatif des Besoins</h3>
    </div>
    <div class="card-body">
        <?php 
        $dashboardData = DispatchModel::getDashboardData();
        $allBesoins = [];
        foreach ($dashboardData as $row) {
            if ($row['besoin_id']) {
                $allBesoins[] = $row;
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
