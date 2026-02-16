<!-- Gestion des Dons -->
<div class="card">
    <div class="card-header">
        <h3>Ajouter un don</h3>
    </div>
    <div class="card-body">
        <form action="/dons/create" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="nature">En nature</option>
                        <option value="matériaux">En matériaux</option>
                        <option value="argent">En argent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="designation">Désignation</label>
                    <input type="text" id="designation" name="designation" placeholder="Ex: Riz, Tôle, Argent" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="quantite">Quantité</label>
                    <input type="number" id="quantite" name="quantite" step="0.01" min="0" placeholder="Ex: 500" required>
                </div>
                <div class="form-group">
                    <label for="date_saisie">Date de saisie (optionnel)</label>
                    <input type="datetime-local" id="date_saisie" name="date_saisie">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter le don</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Liste des Dons (<?= count($dons) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($dons)): ?>
            <p class="text-muted">Aucun don enregistré.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Quantité</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dons as $i => $d): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($d['type']) ?></span></td>
                        <td><?= htmlspecialchars($d['designation']) ?></td>
                        <td><?= number_format($d['quantite'], 2, ',', ' ') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($d['date_saisie'])) ?></td>
                        <td>
                            <a href="/dons/edit/<?= $d['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <form action="/dons/delete/<?= $d['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer ce don ?')">
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
