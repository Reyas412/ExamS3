<!-- Simulation du Dispatch -->
<!-- KPIs Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_besoins'], 0, ',', ' ') ?></div>
        <div class="stat-label">Total Besoins</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_dons'], 0, ',', ' ') ?></div>
        <div class="stat-label">Dons Reçus</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['total_dispatche'], 0, ',', ' ') ?></div>
        <div class="stat-label">Total Dispatché</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-value"><?= $stats['taux_couverture'] ?>%</div>
        <div class="stat-label">Taux de Couverture</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Type de Dispatch</h3>
    </div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom: 16px;">
            Choisissez le mode de distribution des dons :
        </p>
        <div class="dispatch-types">
            <label class="dispatch-option">
                <input type="radio" name="dispatch_type" value="fifo_dons" checked>
                <div class="dispatch-card">
                    <div class="dispatch-icon">🔵 1️⃣</div>
                    <div class="dispatch-title">FIFO (par date des dons)</div>
                    <div class="dispatch-desc">Le premier don saisi est le premier distribué</div>
                </div>
            </label>
            <label class="dispatch-option">
                <input type="radio" name="dispatch_type" value="proportionnel">
                <div class="dispatch-card">
                    <div class="dispatch-icon">🔵 2️⃣</div>
                    <div class="dispatch-title">Dispatch proportionnel</div>
                    <div class="dispatch-desc">Répartition équitable selon le poids des besoins</div>
                </div>
            </label>
            <label class="dispatch-option">
                <input type="radio" name="dispatch_type" value="fifo_besoins">
                <div class="dispatch-card">
                    <div class="dispatch-icon">🔵 3️⃣</div>
                    <div class="dispatch-title">FIFO Besoins</div>
                    <div class="dispatch-desc">Priorité aux besoins les plus anciens</div>
                </div>
            </label>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Actions</h3>
    </div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom: 16px;">
        </p>
        <div class="btn-group">
            <form action="/dispatch/run" method="POST" class="inline-form">
                <select name="dispatch_type" class="dispatch-select" style="padding: 10px; margin-right: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="fifo_dons">🔵 FIFO (par date des dons)</option>
                    <option value="proportionnel">🔵 Dispatch proportionnel</option>
                    <option value="fifo_besoins">🔵 FIFO Besoins</option>
                </select>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Lancer la simulation du dispatch ? Cela va recalculer toutes les attributions.')">
                    &#9654; Lancer le Dispatch
                </button>
            </form>
            <form action="/dispatch/reset" method="POST" class="inline-form">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Réinitialiser toutes les attributions ?')">
                    &#10006; Réinitialiser
                </button>
            </form>
            <a href="/dispatch/report" class="btn btn-secondary">
                &#128196; Télécharger Rapport
            </a>
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

<!-- Résultat des Achats (Besoins couverts par achats) -->
<div class="card">
    <div class="card-header">
        <h3>Achats effectués (<?= count($achats) ?> achats)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($achats)): ?>
            <p class="text-muted">Aucun achat effectué.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Montant Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($achats as $i => $a): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($a['ville_nom']) ?></strong></td>
                        <td><?= htmlspecialchars($a['besoin_designation']) ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($a['besoin_type']) ?></span></td>
                        <td><strong><?= number_format($a['quantite'], 2, ',', ' ') ?></strong></td>
                        <td><?= number_format($a['montant_total'], 2, ',', ' ') ?> Ar</td>
                        <td><span class="badge <?= $a['status'] === 'valide' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
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

<!-- État des besoins après dispatch -->
<div class="card">
    <div class="card-header">
        <h3>&#128230; État des Besoins après Dispatch</h3>
    </div>
    <div class="card-body">
        <?php if (empty($besoinsRecap)): ?>
            <p class="text-muted">Aucun besoin enregistré.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Ville</th>
                        <th>Qté Totale</th>
                        <th>Reçu</th>
                        <th>Restant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoinsRecap as $besoin): ?>
                    <?php 
                        $pct = floatval($besoin['besoin_quantite']) > 0 
                            ? round((floatval($besoin['quantite_recue']) / floatval($besoin['besoin_quantite'])) * 100, 1)
                            : 0;
                        $statusClass = $pct >= 100 ? 'badge-success' : ($pct > 0 ? 'badge-warning' : 'badge-danger');
                        $statusText = $pct >= 100 ? 'Satisfait' : ($pct > 0 ? 'Partiellement satisfait' : 'Non satisfait');
                    ?>
                    <tr>
                        <td><span class="badge badge-type"><?= htmlspecialchars($besoin['type']) ?></span></td>
                        <td><?= htmlspecialchars($besoin['designation']) ?></td>
                        <td><?= htmlspecialchars($besoin['ville_nom']) ?></td>
                        <td><?= number_format($besoin['besoin_quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($besoin['quantite_recue'], 2, ',', ' ') ?></td>
                        <td><?= number_format($besoin['quantite_restante'], 2, ',', ' ') ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
