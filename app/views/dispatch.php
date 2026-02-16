<!-- Simulation du Dispatch -->
<div class="card">
    <div class="card-header">
        <h3>Actions</h3>
    </div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom: 16px;">
            Le dispatch distribue les dons aux besoins par ordre chronologique (FIFO). 
            Les dons sont attribués aux besoins qui correspondent au même type et à la même désignation.
        </p>
        <div class="btn-group">
            <form action="/dispatch/run" method="POST" class="inline-form">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Lancer la simulation du dispatch ? Cela va recalculer toutes les attributions.')">
                    &#9654; Lancer le Dispatch
                </button>
            </form>
            <form action="/dispatch/reset" method="POST" class="inline-form">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Réinitialiser toutes les attributions ?')">
                    &#10006; Réinitialiser
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Résultat du dispatch -->
<div class="card">
    <div class="card-header">
        <h3>Résultat du Dispatch (<?= count($dispatches) ?> attributions)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($dispatches)): ?>
            <p class="text-muted">Aucun dispatch effectué. Cliquez sur "Lancer le Dispatch" pour simuler la distribution.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Don</th>
                        <th>Type</th>
                        <th>Ville Destination</th>
                        <th>Besoin</th>
                        <th>Qté Attribuée</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispatches as $i => $d): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($d['don_designation']) ?> (<?= number_format($d['don_quantite'], 2, ',', ' ') ?>)</td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($d['don_type']) ?></span></td>
                        <td><strong><?= htmlspecialchars($d['ville_nom']) ?></strong></td>
                        <td><?= htmlspecialchars($d['besoin_designation']) ?> (besoin: <?= number_format($d['besoin_quantite'], 2, ',', ' ') ?>)</td>
                        <td><strong><?= number_format($d['quantite_attribuee'], 2, ',', ' ') ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($d['date_dispatch'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- État des dons après dispatch -->
<div class="card">
    <div class="card-header">
        <h3>&#10084; État des Dons après Dispatch</h3>
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
                        <th>Qté Totale</th>
                        <th>Distribué</th>
                        <th>Non distribué</th>
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
                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
