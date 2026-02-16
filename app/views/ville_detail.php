<!-- Détail Ville -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= htmlspecialchars($ville['nom']) ?></div>
        <div class="stat-label">Ville</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= htmlspecialchars($ville['region_nom']) ?></div>
        <div class="stat-label">Région</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_besoins'], 0, ',', ' ') ?></div>
        <div class="stat-label">Total Besoins</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-value"><?= $stats['taux_couverture'] ?>%</div>
        <div class="stat-label">Taux de Couverture</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Besoins de <?= htmlspecialchars($ville['nom']) ?></h3>
    </div>
    <div class="card-body">
        <?php if (empty($besoins)): ?>
            <p class="text-muted">Aucun besoin enregistré pour cette ville.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Quantité</th>
                        <th>Attribué</th>
                        <th>Reste</th>
                        <th>Couverture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $b): ?>
                    <?php 
                        $pct = floatval($b['quantite']) > 0 
                            ? round((floatval($b['quantite_attribuee']) / floatval($b['quantite'])) * 100, 1)
                            : 0;
                        $reste = floatval($b['quantite']) - floatval($b['quantite_attribuee']);
                        
                        // Badge couleur dynamique
                        if ($pct >= 100) {
                            $statusClass = 'badge-success';
                            $badgeText = '100%';
                        } elseif ($pct >= 50) {
                            $statusClass = 'badge-warning';
                            $badgeText = $pct . '%';
                        } else {
                            $statusClass = 'badge-danger';
                            $badgeText = $pct . '%';
                        }
                    ?>
                    <tr>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= number_format($b['quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite_attribuee'], 2, ',', ' ') ?></td>
                        <td><?= number_format($reste, 2, ',', ' ') ?></td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar <?= $statusClass ?>" style="width: <?= min($pct, 100) ?>%"></div>
                            </div>
                            <span class="badge <?= $statusClass ?>"><?= $badgeText ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div style="margin-top: 20px;">
    <a href="/villes" class="btn btn-secondary">&#8592; Retour aux villes</a>
</div>
