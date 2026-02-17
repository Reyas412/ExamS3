<!-- Gestion des Achats -->
<h2 style="margin-bottom: 20px;">💰 Gestion des Achats / Couverture</h2>

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

<!-- Liste des besoins à couvrir -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3>📦 Besoins à couvrir</h3>
    </div>
    <div class="card-body">
        <?php if (empty($besoins)): ?>
            <p class="text-muted">Aucun besoin restant à couvrir.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Quantité Restante</th>
                        <th>Prix Unit.</th>
                        <th>Montant Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $i => $b): ?>
                    <?php 
                        $quantite_restante = floatval($b['quantite_restante']);
                        $montant_total = $quantite_restante * floatval($b['prix_unitaire']);
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($b['ville_nom']) ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= number_format($quantite_restante, 2, ',', ' ') ?></td>
                        <td><?= number_format($b['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td><strong><?= number_format($montant_total, 0, ',', ' ') ?></strong></td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm" 
                                onclick="openCouvrirModal(<?= $b['id'] ?>, '<?= htmlspecialchars($b['designation']) ?>', <?= $quantite_restante ?>, <?= $b['prix_unitaire'] ?>, '<?= $b['type'] ?>')">
                                💰 Couvrir
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
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

<!-- Modal pour couvrir -->
<div id="couvrir-modal" class="modal" style="display: none;">
    <div class="modal-content" style="background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%); max-height: 90vh; overflow-y: auto;">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 style="text-align: center; color: #1976D2; margin-bottom: 20px;">💰 Couvrir un besoin</h3>
        
        <!-- Info du besoin -->
        <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #2196F3;">
            <h4 style="color: #1565C0; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">📦 <span>Besoin</span></h4>
            <p style="margin: 8px 0; font-size: 15px; color: #000;"><strong>Nom:</strong> <span id="modal-besoin-nom" style="color: #000; font-weight: bold;"></span></p>
            <p style="margin: 8px 0; font-size: 15px; color: #000;"><strong>Quantité restante:</strong> <span id="modal-besoin-restant" style="color: #000; font-weight: bold; background: #fff; padding: 2px 8px; border-radius: 4px;"></span></p>
            <p style="margin: 8px 0; font-size: 15px; color: #000;"><strong>Prix unitaire:</strong> <span id="modal-prix-unitaire-display" style="color: #000; font-weight: bold; background: #fff; padding: 2px 8px; border-radius: 4px;"></span> Ar</p>
        </div>
        
        <!-- Achat -->
        <div id="achat-section" style="background: white; padding: 20px; border-radius: 12px; border: 2px solid #4CAF50;">
            <h4 style="color: #2E7D32; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">💳 <span>Achat</span></h4>
            <form id="achat-form">
                <input type="hidden" id="modal-besoin-id">
                <input type="hidden" id="modal-prix-unitaire">
                <input type="hidden" id="modal-besoin-type">
                
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="modal-quantite" style="color: #333; font-weight: 600; display: block; margin-bottom: 6px;">Quantité à acheter:</label>
                    <input type="number" id="modal-quantite" min="0.01" step="0.01" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                </div>
                
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="modal-frais" style="color: #333; font-weight: 600; display: block; margin-bottom: 6px;">Frais (%):</label>
                    <input type="number" id="modal-frais" min="0" max="100" step="0.1" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                </div>
                
                <div class="simulation-box" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #4CAF50; border-radius: 12px; padding: 20px; margin: 20px 0;">
                    <p style="margin: 10px 0; font-size: 16px; color: #000;">Montant base: <strong id="modal-montant-base" style="color: #1565C0; font-size: 18px;">0</strong> Ar</p>
                    <p style="margin: 10px 0; font-size: 18px; color: #000;">Montant total (avec frais): <strong id="modal-montant-total" style="color: #2E7D32; font-size: 22px;">0</strong> Ar</p>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 25px;">
                    <button type="button" class="btn btn-secondary" onclick="simulerAchat()" style="flex: 1; padding: 14px; font-size: 16px; font-weight: 600;">🔍 Simuler</button>
                    <button type="submit" class="btn btn-success" style="flex: 1; padding: 14px; font-size: 16px; font-weight: 600;">💰 Acheter</button>
                </div>
            </form>
            
            <div id="simulation-result" style="display: none; margin-top: 20px; background: #fff3cd; padding: 15px; border-radius: 8px; border: 2px solid #ffc107;">
                <h4 style="color: #856404; margin-bottom: 10px;">📊 Résultat de la simulation:</h4>
                <div id="simulation-details" style="color: #000;"></div>
            </div>
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
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background-color: white;
    padding: 25px;
    border-radius: 10px;
    width: 90%;
    max-width: 450px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.modal-content h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
}
.modal-content h4 {
    margin-top: 0;
    color: #555;
}
.modal-content label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #555;
}
.modal-content input {
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
    padding: 10px;
    font-size: 14px;
    box-sizing: border-box;
}
.modal-content input:focus {
    border-color: #4CAF50;
    outline: none;
}
.simulation-box {
    background: #e8f5e9;
    border: 1px solid #4CAF50;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}
.simulation-box p {
    margin: 8px 0;
    font-size: 14px;
}
.close {
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #999;
}
.close:hover {
    color: #333;
}
.form-group {
    margin-bottom: 15px;
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
    
    // Vérifier s'il y a un besoin_id dans l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const besoinId = urlParams.get('besoin_id');
    if (besoinId) {
        const besoins = <?= json_encode($besoins) ?>;
        const besoin = besoins.find(b => b.id == besoinId);
        if (besoin) {
            const quantiteRestante = parseFloat(besoin.quantite_restante);
            openCouvrirModal(besoin.id, besoin.designation, quantiteRestante, parseFloat(besoin.prix_unitaire), besoin.type);
        }
    }
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
function openCouvrirModal(besoinId, besoinNom, quantiteRestante, prixUnitaire, besoinType) {
    document.getElementById('modal-besoin-id').value = besoinId;
    document.getElementById('modal-besoin-nom').textContent = besoinNom;
    document.getElementById('modal-besoin-restant').textContent = quantiteRestante;
    document.getElementById('modal-prix-unitaire').value = prixUnitaire;
    document.getElementById('modal-prix-unitaire-display').textContent = prixUnitaire;
    document.getElementById('modal-besoin-type').value = besoinType;
    document.getElementById('modal-quantite').max = quantiteRestante;
    document.getElementById('modal-quantite').value = quantiteRestante;
    document.getElementById('simulation-result').style.display = 'none';
    document.getElementById('couvrir-modal').style.display = 'block';
    
    // Mettre à jour le montant
    updateMontant();
}

// Fermer le modal
function closeModal() {
    document.getElementById('couvrir-modal').style.display = 'none';
    const url = new URL(window.location);
    url.searchParams.delete('besoin_id');
    window.history.replaceState({}, '', url);
}

// Calculer le montant
function updateMontant() {
    const quantite = parseFloat(document.getElementById('modal-quantite').value) || 0;
    const frais = parseFloat(document.getElementById('modal-frais').value) || 0;
    const prixUnitaire = parseFloat(document.getElementById('modal-prix-unitaire').value) || 0;
    
    const montantBase = quantite * prixUnitaire;
    const montantTotal = montantBase * (1 + frais / 100);
    
    document.getElementById('modal-montant-base').textContent = Math.round(montantBase).toLocaleString();
    document.getElementById('modal-montant-total').textContent = Math.round(montantTotal).toLocaleString();
}

document.getElementById('modal-quantite').addEventListener('input', updateMontant);
document.getElementById('modal-frais').addEventListener('input', updateMontant);

// Soumission du formulaire
document.getElementById('achat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    validerAchat();
});

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
        
        let html = '<p>Montant total: <strong>' + Math.round(data.montant_total).toLocaleString() + '</strong></p>';
        html += '<p>Montant couvert: <strong>' + Math.round(data.montant_couvert).toLocaleString() + '</strong></p>';
        
        if (data.manquant > 0) {
            html += '<p style="color: red;">Manquant: <strong>' + Math.round(data.manquant).toLocaleString() + '</strong></p>';
        }
        
        html += '<h5>Dons utilisés (FIFO):</h5><ul>';
        data.allocation.forEach(a => {
            html += '<li>' + a.don_designation + ': ' + Math.round(a.montant_utilise).toLocaleString() + '</li>';
        });
        html += '</ul>';
        
        document.getElementById('simulation-details').innerHTML = html;
        document.getElementById('simulation-result').style.display = 'block';
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
