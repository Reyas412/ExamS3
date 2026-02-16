<!-- Configuration -->
<div class="card">
    <div class="card-header">
        <h3>Paramètres du Système</h3>
    </div>
    <div class="card-body">
        <form action="/config/update" method="POST">
            <div class="form-group">
                <label for="frais_achat">Frais d'achat (%)</label>
                <input type="number" id="frais_achat" name="frais_achat" 
                       value="<?= htmlspecialchars($configs['frais_achat'] ?? '10') ?>" 
                       min="0" max="100" step="0.1" required>
                <small class="text-muted">Pourcentage de frais ajouté au montant des achats</small>
            </div>
            
            <div class="form-group">
                <label for="devise">Devise</label>
                <input type="text" id="devise" value="Ariary" disabled>
                <small class="text-muted">La devise ne peut pas être modifiée</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
