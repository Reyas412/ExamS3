<!-- Modifier une Ville -->
<div class="card">
    <div class="card-header">
        <h3>Modifier une ville</h3>
    </div>
    <div class="card-body">
        <form action="/villes/update/<?= $ville['id'] ?>" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="region_id">Region</label>
                    <select id="region_id" name="region_id" required>
                        <option value="">-- Selectionner une region --</option>
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($r['id'] == $ville['region_id']) ? 'selected' : '' ?>><?= htmlspecialchars($r['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nom">Nom de la ville</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($ville['nom']) ?>" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="/villes" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
