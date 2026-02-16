<!-- Détail Région -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= htmlspecialchars($region['nom']) ?></div>
        <div class="stat-label">Région</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= count($villes) ?></div>
        <div class="stat-label">Villes</div>
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
        <h3>Villes de <?= htmlspecialchars($region['nom']) ?></h3>
    </div>
    <div class="card-body">
        <?php if (empty($villes)): ?>
            <p class="text-muted">Aucune ville enregistrée dans cette région.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($villes as $i => $ville): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><a href="/villes/<?= $ville['id'] ?>"><strong><?= htmlspecialchars($ville['nom']) ?></strong></a></td>
                        <td>
                            <a href="/villes/<?= $ville['id'] ?>" class="btn btn-warning btn-sm">Voir</a>
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
    <a href="/regions" class="btn btn-secondary">&#8592; Retour aux régions</a>
</div>
