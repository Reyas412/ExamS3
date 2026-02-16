<!-- Gestion des Besoins -->
<div class="card">
    <div class="card-header">
        <h3>Ajouter un besoin</h3>
    </div>
    <div class="card-body">
        <form action="/besoins/create" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="ville_id">Ville</label>
                    <select id="ville_id" name="ville_id" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($villes as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="nature">En nature</option>
                        <option value="matériaux">En matériaux</option>
                        <option value="argent">En argent</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="designation">Désignation</label>
                    <input type="text" id="designation" name="designation" placeholder="Ex: Riz, Tôle, Argent" required>
                </div>
                <div class="form-group">
                    <label for="quantite">Quantité</label>
                    <input type="number" id="quantite" name="quantite" step="0.01" min="0" placeholder="Ex: 1000" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="prix_unitaire">Prix unitaire</label>
                    <input type="number" id="prix_unitaire" name="prix_unitaire" step="0.01" min="0" placeholder="Ex: 2.50" required>
                </div>
                <div class="form-group">
                    <label for="date_saisie">Date de saisie (optionnel)</label>
                    <input type="datetime-local" id="date_saisie" name="date_saisie">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter le besoin</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Liste des Besoins (<?= count($besoins) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($besoins)): ?>
            <p class="text-muted">Aucun besoin enregistré.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Quantité</th>
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
