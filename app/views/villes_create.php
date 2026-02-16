<!-- Ajouter une Ville -->
<div class="card">
    <div class="card-header">
        <h3>Ajouter une ville</h3>
    </div>
    <div class="card-body">
        <form action="/villes/create" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="region_id">Region</label>
                    <select id="region_id" name="region_id" required>
                        <option value="">-- Selectionner une region --</option>
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nom">Nom de la ville</label>
                    <input type="text" id="nom" name="nom" placeholder="Ex: Antananarivo" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter</button>
                <a href="/villes" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
