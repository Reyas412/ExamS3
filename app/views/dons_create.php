<!-- Ajouter un Don -->
<div class="card">
    <div class="card-header">
        <h3>Ajouter un don</h3>
    </div>
    <div class="card-body">
        <form action="/dons/create" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required onchange="toggleDonFields()">
                        <option value="">-- Selectionner --</option>
                        <option value="nature">En nature</option>
                        <option value="materiaux">En materiaux</option>
                        <option value="argent">En argent</option>
                    </select>
                </div>
                <div class="form-group" id="designation-group">
                    <label for="designation">Designation</label>
                    <input type="text" id="designation" name="designation" placeholder="Ex: Riz, Tole, Argent">
                </div>
                <div class="form-group" id="montant-group" style="display: none;">
                    <label for="montant">Montant</label>
                    <input type="number" id="montant" name="montant" step="0.01" min="0" placeholder="Ex: 50000">
                </div>
            </div>
            <div class="form-row" id="quantite-row">
                <div class="form-group">
                    <label for="quantite">Quantite</label>
                    <input type="number" id="quantite" name="quantite" step="0.01" min="0" placeholder="Ex: 500">
                </div>
                <div class="form-group">
                    <label for="unite">Unite</label>
                    <select id="unite" name="unite">
                        <option value="">-- Selectionner --</option>
                        <option value="kg">Kg</option>
                        <option value="g">g</option>
                        <option value="l">Litre</option>
                        <option value="ml">ml</option>
                        <option value=" Unite">Unite</option>
                        <option value="piece">Pieces</option>
                        <option value="kit">Kit</option>
                        <option value="m">Metre</option>
                        <option value="m2">Metre carre</option>
                        <option value="tonne">Tonne</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_saisie">Date de saisie (optionnel)</label>
                    <input type="datetime-local" id="date_saisie" name="date_saisie">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter le don</button>
                <a href="/dons" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
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
