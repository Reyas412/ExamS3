<!-- Gestion des Besoins -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Liste des besoins (<?= count($besoins) ?>)</h3>
        <div>
            <a href="/besoins/create" class="btn btn-primary">Ajouter un besoin</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($besoins)): ?>
            <p class="text-muted">Aucun besoin enregistré.</p>
        <?php else: ?>
        
        <!-- Filtre par ville -->
        <form method="GET" action="/besoins" style="margin-bottom: 20px;">
            <select name="ville_id" onchange="this.form.submit()" style="padding: 5px; margin-right: 10px;">
                <option value="">Toutes les villes</option>
                <?php 
                $villes = Ville::getAll();
                foreach ($villes as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= ($ville_id ?? '') == $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <label style="margin-left: 20px;">
                <input type="checkbox" name="restants" value="1" <?= isset($_GET['restants']) ? 'checked' : '' ?> onchange="this.form.submit()">
                Besoins restants seulement
            </label>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Quantité/Montant</th>
                        <th>Montant Total</th>
                        <th>Satisfait</th>
                        <th>Restant</th>
                        <th>Couverture</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $i => $b): ?>
                    <?php 
                        $restant = floatval($b['quantite']) - floatval($b['quantite_satisfaite'] ?? 0);
                        $pct = floatval($b['quantite']) > 0 
                            ? round((floatval($b['quantite_satisfaite'] ?? 0) / floatval($b['quantite'])) * 100, 1)
                            : 0;
                        $statusClass = $pct >= 100 ? 'badge-success' : ($pct > 0 ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($b['ville_nom']) ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= $b['type'] === 'argent' ? number_format($b['quantite'], 0, ',', ' ') . ' Ar' : number_format($b['quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite'] * $b['prix_unitaire'], 0, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite_satisfaite'] ?? 0, 2, ',', ' ') ?></td>
                        <td><?= number_format($restant, 2, ',', ' ') ?></td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar <?= $statusClass ?>" style="width: <?= min($pct, 100) ?>%"></div>
                            </div>
                            <span class="badge <?= $statusClass ?>"><?= $pct ?>%</span>
                        </td>
                        <td>
                            <a href="/besoins/edit/<?= $b['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <form action="/besoins/delete/<?= $b['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer ce besoin?')">
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
