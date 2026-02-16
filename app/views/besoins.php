<!-- Gestion des Besoins -->
<!-- Filtres -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3>Filtres</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group">
                <label for="filter_ville">Ville</label>
                <select id="filter_ville" name="ville_id">
                    <option value="">Toutes les villes</option>
                    <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= (isset($_GET['ville_id']) && $_GET['ville_id'] == $v['id']) ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_type">Type</label>
                <select id="filter_type" name="type">
                    <option value="">Tous les types</option>
                    <option value="nature" <?= (isset($_GET['type']) && $_GET['type'] == 'nature') ? 'selected' : '' ?>>En nature</option>
                    <option value="materiaux" <?= (isset($_GET['type']) && $_GET['type'] == 'materiaux') ? 'selected' : '' ?>>En materiaux</option>
                    <option value="argent" <?= (isset($_GET['type']) && $_GET['type'] == 'argent') ? 'selected' : '' ?>>En argent</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <a href="/besoins" class="btn btn-secondary">Reinitialiser</a>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Liste des besoins (<?= count($besoins) ?>)</h3>
        <a href="/besoins/create" class="btn btn-primary">Ajouter un besoin</a>
    </div>
    <div class="card-body">
        <?php if (empty($besoins)): ?>
            <p class="text-muted">Aucun besoin enregistre.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Designation</th>
                        <th>Quantite</th>
                        <th>P.U.</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $i => $b): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($b['ville_nom']) ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= number_format($b['quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite'] * $b['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($b['date_saisie'])) ?></td>
                        <td>
                            <a href="/besoins/edit/<?= $b['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <form action="/besoins/delete/<?= $b['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer ce besoin ?')">
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
