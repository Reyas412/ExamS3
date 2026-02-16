<!-- Gestion des Besoins -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Liste des besoins (<?= count($besoins) ?>)</h3>
        <div>
            <a href="/besoins/create" class="btn btn-primary">Ajouter un besoin</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($besoins)): ?>
            <p class="text-muted">Aucun besoin enregistré.</p>
        <?php else: ?>
        
        <!-- Filtre par ville -->
        <form method="GET" action="/besoins" style="margin-bottom: 20px;">
            <select name="ville_id" onchange="this.form.submit()" style="padding: 5px; margin-right: 10px;">
                <option value="">Toutes les villes</option>
                <?php 
                $villes = Ville::getAll();
                foreach ($villes as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= ($ville_id ?? '') == $v['id'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <label style="margin-left: 20px;">
                <input type="checkbox" name="restants" value="1" <?= isset($_GET['restants']) ? 'checked' : '' ?> onchange="this.form.submit()">
                Besoins restants seulement
            </label>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Quantité</th>
                        <th>Prix Unit.</th>
                        <th>Montant</th>
                        <th>Satisfait</th>
                        <th>Restant</th>
                        <th>Couverture</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $i => $b): ?>
                    <?php 
                        $restant = floatval($b['quantite']) - floatval($b['quantite_satisfaite'] ?? 0);
                        $pct = floatval($b['quantite']) > 0 
                            ? round((floatval($b['quantite_satisfaite'] ?? 0) / floatval($b['quantite'])) * 100, 1)
                            : 0;
                        $statusClass = $pct >= 100 ? 'badge-success' : ($pct > 0 ? 'badge-warning' : 'badge-danger');
                        
                        // Vérifier si un don en nature est disponible
                        $pdo = getDatabase();
                        $stmt = $pdo->prepare("SELECT * FROM dons WHERE type = ? AND montant_restant > 0 AND designation = ? LIMIT 1");
                        $stmt->execute([$b['type'], $b['designation']]);
                        $don_nature = $stmt->fetch();
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($b['ville_nom']) ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= number_format($b['quantite'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite'] * $b['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['quantite_satisfaite'] ?? 0, 2, ',', ' ') ?></td>
                        <td><?= number_format($restant, 2, ',', ' ') ?></td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar <?= $statusClass ?>" style="width: <?= min($pct, 100) ?>%"></div>
                            </div>
                            <span class="badge <?= $statusClass ?>"><?= $pct ?>%</span>
                        </td>
                        <td>
                            <?php if ($restant > 0): ?>
                                <?php if ($don_nature): ?>
                                    <span class="badge badge-warning" title="Un don en <?= $b['type'] ?> est disponible pour <?= $b['designation'] ?>">Don dispo!</span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-success btn-sm" onclick="openAchatModal(<?= $b['id'] ?>, '<?= htmlspecialchars($b['designation']) ?>', <?= $restant ?>, <?= $b['prix_unitaire'] ?>)">
                                        🛒 Acheter
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="/besoins/edit/<?= $b['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <form action="/besoins/delete/<?= $b['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer ce besoin?')">
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

<!-- Modal pour achat -->
<div id="achat-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>🛒 Achat</h3>
        <form id="achat-form">
            <input type="hidden" id="modal-besoin-id">
            <input type="hidden" id="modal-prix-unitaire">
            
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
                <p>Prix unitaire: <strong id="modal-pu-display">0</strong></p>
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
            document.getElementById('modal-frais').value = data.frais_percent;
        });
});

// Ouvrir le modal
function openAchatModal(besoinId, besoinNom, quantiteRestante, prixUnitaire) {
    document.getElementById('modal-besoin-id').value = besoinId;
    document.getElementById('modal-besoin-nom').textContent = besoinNom + ' (Prix: ' + prixUnitaire + ')';
    document.getElementById('modal-besoin-restant').textContent = quantiteRestante;
    document.getElementById('modal-prix-unitaire').value = prixUnitaire;
    document.getElementById('modal-quantite').max = quantiteRestante;
    document.getElementById('modal-quantite').value = quantiteRestante;
    document.getElementById('modal-pu-display').textContent = prixUnitaire;
    document.getElementById('simulation-result').style.display = 'none';
    document.getElementById('achat-modal').style.display = 'block';
    updateMontant();
}

// Fermer le modal
function closeModal() {
    document.getElementById('achat-modal').style.display = 'none';
}

// Calculer le montant
function updateMontant() {
    const quantite = parseFloat(document.getElementById('modal-quantite').value) || 0;
    const frais = parseFloat(document.getElementById('modal-frais').value) || 0;
    const prixUnitaire = parseFloat(document.getElementById('modal-prix-unitaire').value) || 0;
    
    const montantBase = quantite * prixUnitaire;
    const montantTotal = montantBase * (1 + frais / 100);
    
    document.getElementById('modal-montant-base').textContent = montantBase.toLocaleString();
    document.getElementById('modal-montant-total').textContent = montantTotal.toLocaleString();
}

document.getElementById('modal-quantite').addEventListener('input', updateMontant);
document.getElementById('modal-frais').addEventListener('input', updateMontant);

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
