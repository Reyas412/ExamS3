<!-- Modifier un Don -->
<div class="card">
    <div class="card-header">
        <h3>Modifier un don</h3>
    </div>
    <div class="card-body">
        <form action="/dons/update/<?= $don['id'] ?>" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required onchange="toggleDonFields()">
                        <option value="">-- Sélectionner --</option>
                        <option value="nature" <?= $don['type'] === 'nature' ? 'selected' : '' ?>>En nature</option>
                        <option value="matériaux" <?= $don['type'] === 'matériaux' ? 'selected' : '' ?>>En matériaux</option>
                        <option value="argent" <?= $don['type'] === 'argent' ? 'selected' : '' ?>>En argent</option>
                    </select>
                </div>
                <div class="form-group" id="designation-group">
                    <label for="designation">Désignation</label>
                    <input type="text" id="designation" name="designation" placeholder="Ex: Riz, Tôle, Argent" value="<?= htmlspecialchars($don['designation']) ?>">
                </div>
                <div class="form-group" id="montant-group" style="display: none;">
                    <label for="montant">Montant</label>
                    <input type="number" id="montant" name="montant" step="0.01" min="0" placeholder="Ex: 50000" value="<?= $don['type'] === 'argent' ? htmlspecialchars($don['quantite']) : '' ?>">
                </div>
            </div>
            <div class="form-row" id="quantite-row">
                <div class="form-group">
                    <label for="quantite">Quantité</label>
                    <input type="number" id="quantite" name="quantite" step="0.01" min="0" value="<?= htmlspecialchars($don['quantite']) ?>">
                </div>
                <div class="form-group">
                    <label for="date_saisie">Date de saisie (optionnel)</label>
                    <input type="datetime-local" id="date_saisie" name="date_saisie" value="<?= $don['date_saisie'] ? substr($don['date_saisie'], 0, 16) : '' ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="/dons" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    toggleDonFields();
});

function toggleDonFields() {
    const type = document.getElementById('type').value;
    const designationGroup = document.getElementById('designation-group');
    const montantGroup = document.getElementById('montant-group');
    const quantiteRow = document.getElementById('quantite-row');
    const designationInput = document.getElementById('designation');
    const montantInput = document.getElementById('montant');
    const quantiteInput = document.getElementById('quantite');
    
    if (type === 'argent') {
        designationGroup.style.display = 'none';
        montantGroup.style.display = 'block';
        quantiteRow.style.display = 'none';
        
        designationInput.removeAttribute('required');
        quantiteInput.removeAttribute('required');
        montantInput.setAttribute('required', 'required');
    } else {
        designationGroup.style.display = 'block';
        montantGroup.style.display = 'none';
        quantiteRow.style.display = 'block';
        
        designationInput.setAttribute('required', 'required');
        quantiteInput.setAttribute('required', 'required');
        montantInput.removeAttribute('required');
    }
}
</script>

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
                        <th>Quantité/Montant</th>
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
                        <td><?= $d['type'] === 'argent' ? number_format($d['quantite'], 0, ',', ' ') . ' Ar' : number_format($d['quantite'], 2, ',', ' ') ?></td>
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
