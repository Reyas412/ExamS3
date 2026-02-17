<!-- Modifier un Besoin -->
<div class="card">
    <div class="card-header">
        <h3>Modifier un besoin</h3>
    </div>
    <div class="card-body">
        <form action="/besoins/update/<?= $besoin['id'] ?>" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="ville_id">Ville</label>
                    <select id="ville_id" name="ville_id" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($villes as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= $besoin['ville_id'] == $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required onchange="toggleBesoinFields()">
                        <option value="">-- Sélectionner --</option>
                        <option value="nature" <?= $besoin['type'] === 'nature' ? 'selected' : '' ?>>En nature</option>
                        <option value="matériaux" <?= $besoin['type'] === 'matériaux' ? 'selected' : '' ?>>En matériaux</option>
                        <option value="argent" <?= $besoin['type'] === 'argent' ? 'selected' : '' ?>>En argent</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" id="designation-group">
                    <label for="designation">Désignation</label>
                    <input type="text" id="designation" name="designation" placeholder="Ex: Riz, Tôle, Argent" value="<?= htmlspecialchars($besoin['designation']) ?>">
                </div>
                <div class="form-group" id="montant-group" style="display: none;">
                    <label for="montant">Montant</label>
                    <input type="number" id="montant" name="montant" step="0.01" min="0" placeholder="Ex: 100000" value="<?= $besoin['type'] === 'argent' ? htmlspecialchars($besoin['quantite']) : '' ?>">
                </div>
            </div>
            <div class="form-row" id="quantite-prix-row">
                <div class="form-group">
                    <label for="quantite">Quantité</label>
                    <input type="number" id="quantite" name="quantite" step="0.01" min="0" value="<?= htmlspecialchars($besoin['quantite']) ?>">
                </div>
                <div class="form-group">
                    <label for="prix_unitaire">Prix unitaire</label>
                    <input type="number" id="prix_unitaire" name="prix_unitaire" step="0.01" min="0" value="<?= htmlspecialchars($besoin['prix_unitaire']) ?>">
                </div>
            </div>
            <div class="form-row" id="date-row">
                <div class="form-group">
                    <label for="date_saisie">Date de saisie (optionnel)</label>
                    <input type="datetime-local" id="date_saisie" name="date_saisie" value="<?= $besoin['date_saisie'] ? substr($besoin['date_saisie'], 0, 16) : '' ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="/besoins" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    toggleBesoinFields();
});

function toggleBesoinFields() {
    const type = document.getElementById('type').value;
    const designationGroup = document.getElementById('designation-group');
    const montantGroup = document.getElementById('montant-group');
    const quantitePrixRow = document.getElementById('quantite-prix-row');
    const designationInput = document.getElementById('designation');
    const montantInput = document.getElementById('montant');
    const quantiteInput = document.getElementById('quantite');
    const prixInput = document.getElementById('prix_unitaire');
    
    if (type === 'argent') {
        designationGroup.style.display = 'none';
        montantGroup.style.display = 'block';
        quantitePrixRow.style.display = 'none';
        
        designationInput.removeAttribute('required');
        quantiteInput.removeAttribute('required');
        prixInput.removeAttribute('required');
        montantInput.setAttribute('required', 'required');
    } else {
        designationGroup.style.display = 'block';
        montantGroup.style.display = 'none';
        quantitePrixRow.style.display = 'block';
        
        designationInput.setAttribute('required', 'required');
        quantiteInput.setAttribute('required', 'required');
        prixInput.setAttribute('required', 'required');
        montantInput.removeAttribute('required');
    }
}
</script>

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
                        <th>Quantité/Montant</th>
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
                        <td><?= $b['type'] === 'argent' ? number_format($b['quantite'], 0, ',', ' ') . ' Ar' : number_format($b['quantite'], 2, ',', ' ') ?></td>
                        <td><?= $b['type'] === 'argent' ? '-' : number_format($b['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite'] * $b['prix_unitaire'], 0, ',', ' ') ?></td>
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
