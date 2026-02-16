<!-- Gestion des Achats -->
<h2 style="margin-bottom: 20px;">🛒 Gestion des Achats</h2>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value" id="stat-total">-</div>
        <div class="stat-label">Total Besoins</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="stat-satisfaits">-</div>
        <div class="stat-label">Satisfaits</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="stat-restants">-</div>
        <div class="stat-label">Restants</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-value" id="stat-taux">-</div>
        <div class="stat-label">Taux</div>
    </div>
</div>

<!-- Configuration des frais -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3>⚙️ Configuration des Frais d'Achat</h3>
    </div>
    <div class="card-body">
        <form id="frais-form" style="display: flex; align-items: center; gap: 10px;">
            <label>Pourcentage de frais:</label>
            <input type="number" id="frais-percent" min="0" max="100" step="0.1" value="10" style="width: 80px; padding: 5px;">
            <span>%</span>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>

<!-- Filtre par ville -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" action="/achats" style="display: flex; gap: 10px; align-items: center;">
            <label>Filtrer par ville:</label>
            <select name="ville_id" style="padding: 5px;">
                <option value="">Toutes les villes</option>
                <?php 
                $villes = Ville::getAll();
                foreach ($villes as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= $ville_id == $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            <button type="button" class="btn btn-info" onclick="loadRecap()">🔄 Actualiser</button>
        </form>
    </div>
</div>

<!-- Liste des achats -->
<div class="card">
    <div class="card-header">
        <h3>📋 Historique des Achats</h3>
    </div>
    <div class="card-body">
        <?php if (empty($achats)): ?>
            <p class="text-muted">Aucun achat enregistré.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Besoin</th>
                        <th>Ville</th>
                        <th>Quantité</th>
                        <th>Montant Base</th>
                        <th>Frais</th>
                        <th>Montant Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($achats as $i => $a): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
                        <td><?= htmlspecialchars($a['besoin_designation']) ?> (<?= htmlspecialchars($a['besoin_type']) ?>)</td>
                        <td><?= htmlspecialchars($a['ville_nom']) ?></td>
                        <td><?= number_format($a['quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($a['montant_base'], 2, ',', ' ') ?></td>
                        <td><?= $a['frais_percent'] ?>%</td>
                        <td><strong><?= number_format($a['montant_total'], 2, ',', ' ') ?></strong></td>
                        <td><span class="badge <?= $a['status'] == 'valide' ? 'badge-success' : 'badge-warning' ?>"><?= $a['status'] ?></span></td>
                        <td>
                            <?php if ($a['status'] == 'valide'): ?>
                            <form action="/achats/delete/<?= $a['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer cet achat?')">
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour achat -->
<div id="achat-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>🛒 Achat</h3>
        <form id="achat-form">
            <input type="hidden" id="modal-besoin-id">
            
            <div style="margin-bottom: 15px;">
                <label>Besoin:</label>
                <span id="modal-besoin-nom" style="font-weight: bold;"></span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Quantité restante:</label>
                <span id="modal-besoin-restant"></span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="modal-quantite">Quantité à acheter:</label>
                <input type="number" id="modal-quantite" min="0.01" step="0.01" required style="width: 100%; padding: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="modal-frais">Frais (%):</label>
                <input type="number" id="modal-frais" min="0" max="100" step="0.1" style="width: 100%; padding: 8px;">
            </div>
            
            <div style="margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px;">
                <p>Montant base: <strong id="modal-montant-base">0</strong></p>
                <p>Montant total (avec frais): <strong id="modal-montant-total">0</strong></p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="simulerAchat()">🔍 Simuler</button>
                <button type="button" class="btn btn-primary" onclick="validerAchat()">✓ Valider</button>
            </div>
        </form>
        
        <div id="simulation-result" style="margin-top: 20px; display: none;">
            <h4>Résultat de la simulation:</h4>
            <div id="simulation-details"></div>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
}
.close {
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
</style>

<script>
// Charger les frais au démarrage
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/config/frais')
        .then(r => r.json())
        .then(data => {
            document.getElementById('frais-percent').value = data.frais_percent;
            document.getElementById('modal-frais').value = data.frais_percent;
        });
    loadRecap();
});

// Sauvegarder les frais
document.getElementById('frais-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const frais = document.getElementById('frais-percent').value;
    fetch('/api/config/frais', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'frais_percent=' + frais
    })
    .then(r => r.json())
    .then(data => {
        alert('Frais mis à jour: ' + data.frais_percent + '%');
        document.getElementById('modal-frais').value = data.frais_percent;
    });
});

// Charger le recap
function loadRecap() {
    fetch('/api/recap')
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.total_besoins.toLocaleString();
            document.getElementById('stat-satisfaits').textContent = data.satisfaits.toLocaleString();
            document.getElementById('stat-restants').textContent = data.restants.toLocaleString();
            document.getElementById('stat-taux').textContent = data.taux_satisfaction + '%';
        });
}

// Ouvrir le modal
function openAchatModal(besoinId, besoinNom, quantiteRestante) {
    document.getElementById('modal-besoin-id').value = besoinId;
    document.getElementById('modal-besoin-nom').textContent = besoinNom;
    document.getElementById('modal-besoin-restant').textContent = quantiteRestante;
    document.getElementById('modal-quantite').max = quantiteRestante;
    document.getElementById('modal-quantite').value = quantiteRestante;
    document.getElementById('simulation-result').style.display = 'none';
    document.getElementById('achat-modal').style.display = 'block';
}

// Fermer le modal
function closeModal() {
    document.getElementById('achat-modal').style.display = 'none';
}

// Calculer le montant
document.getElementById('modal-quantite').addEventListener('input', updateMontant);
document.getElementById('modal-frais').addEventListener('input', updateMontant);

function updateMontant() {
    const quantite = parseFloat(document.getElementById('modal-quantite').value) || 0;
    const frais = parseFloat(document.getElementById('modal-frais').value) || 0;
    // Le prix unitaire devrait être récupéré du besoin
    document.getElementById('modal-montant-base').textContent = 'À calculer...';
    document.getElementById('modal-montant-total').textContent = 'À calculer...';
}

// Simuler l'achat
function simulerAchat() {
    const besoinId = document.getElementById('modal-besoin-id').value;
    const quantite = document.getElementById('modal-quantite').value;
    const frais = document.getElementById('modal-frais').value;
    
    fetch('/api/achats/simuler', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'besoin_id=' + besoinId + '&quantite=' + quantite + '&frais_percent=' + frais
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        let html = '<p>Montant total: <strong>' + data.montant_total.toLocaleString() + '</strong></p>';
        html += '<p>Montant couvert: <strong>' + data.montant_couvert.toLocaleString() + '</strong></p>';
        
        if (data.manquant > 0) {
            html += '<p style="color: red;">Manquant: <strong>' + data.manquant.toLocaleString() + '</strong></p>';
        }
        
        html += '<h5>Dons utilisés (FIFO):</h5><ul>';
        data.allocation.forEach(a => {
            html += '<li>' + a.don_designation + ': ' + a.montant_utilise.toLocaleString() + '</li>';
        });
        html += '</ul>';
        
        document.getElementById('simulation-details').innerHTML = html;
        document.getElementById('simulation-result').style.display = 'block';
        
        document.getElementById('modal-montant-base').textContent = data.montant_base.toLocaleString();
        document.getElementById('modal-montant-total').textContent = data.montant_total.toLocaleString();
    })
    .catch(err => alert('Erreur: ' + err));
}

// Valider l'achat
function validerAchat() {
    if (!confirm('Voulez-vous valider cet achat?')) return;
    
    const besoinId = document.getElementById('modal-besoin-id').value;
    const quantite = document.getElementById('modal-quantite').value;
    const frais = document.getElementById('modal-frais').value;
    
    fetch('/api/achats/valider', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'besoin_id=' + besoinId + '&quantite=' + quantite + '&frais_percent=' + frais
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        alert('Achat validé avec succès!');
        closeModal();
        location.reload();
    })
    .catch(err => alert('Erreur: ' + err));
}
</script>
