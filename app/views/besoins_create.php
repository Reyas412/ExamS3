<!-- Ajouter un Besoin -->
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
                        <option value="">-- Selectionner --</option>
                        <?php foreach ($villes as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required>
                        <option value="">-- Selectionner --</option>
                        <option value="nature">En nature</option>
                        <option value="materiaux">En materiaux</option>
                        <option value="argent">En argent</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="designation">Designation</label>
                    <input type="text" id="designation" name="designation" placeholder="Ex: Riz, Tole, Argent" required>
                </div>
                <div class="form-group">
                    <label for="quantite">Quantite</label>
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
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter le besoin</button>
                <a href="/besoins" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
