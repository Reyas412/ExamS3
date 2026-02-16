<!-- Simulation Achat V2 -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= number_format($argent_restant, 0, ',', ' ') ?></div>
        <div class="stat-label">Argent Disponible (Ariary)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $frais_achat ?>%</div>
        <div class="stat-label">Frais d'Achat</div>
    </div>
</div>

<!-- PARTIE 1: Achats depuis les besoins restants -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <h3>1. Effectuer un Achat (Besoins Restants)</h3>
        <p class="text-muted">Utilisez l'argent des dons pour acheter des besoins en nature et matériaux</p>
    </div>
    <div class="card-body">
        <!-- Filtre par ville -->
        <div style="margin-bottom: 15px;">
            <label for="filter-ville-achat"><strong>Filtrer par ville:</strong></label>
            <select id="filter-ville-achat" class="form-control" style="width: 200px; display: inline-block; margin-left: 10px;">
                <option value="">Toutes les villes</option>
                <?php foreach ($villes as $v): ?>
                    <option value="<?= htmlspecialchars($v['nom']) ?>"><?= htmlspecialchars($v['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if (empty($besoins)): ?>
            <p class="text-muted">Aucun besoin en nature ou matériaux à couvrir.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Qté Restante</th>
                        <th>Prix Unit.</th>
                        <th>Montant</th>
                        <th>Avec Frais (<?= $frais_achat ?>%)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $b): ?>
                    <?php 
                        $montant = $b['quantite_restante'] * $b['prix_unitaire'];
                        $montantFrais = $montant * (1 + $frais_achat / 100);
                        $peutAchter = $argent_restant >= $montantFrais;
                        $dejaCouvertParDon = $b['quantite_couverte'] > 0;
                    ?>
                    <tr class="achat-row" data-ville="<?= htmlspecialchars($b['ville_nom']) ?>">
                        <td><?= htmlspecialchars($b['ville_nom']) ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($b['type']) ?></span></td>
                        <td><?= htmlspecialchars($b['designation']) ?></td>
                        <td><?= number_format($b['quantite_restante'], 2, ',', ' ') ?></td>
                        <td><?= number_format($b['prix_unitaire'], 0, ',', ' ') ?></td>
                        <td><?= number_format($montant, 0, ',', ' ') ?></td>
                        <td><strong><?= number_format($montantFrais, 0, ',', ' ') ?></strong></td>
                        <td>
                            <?php if ($dejaCouvertParDon): ?>
                                <span class="badge badge-danger" title="Ce besoin est déjà partiellement couvert par des dons">Deja couvert</span>
                            <?php elseif ($peutAchter): ?>
                                <button type="button" class="btn btn-info btn-sm" onclick="simulerAchat(<?= $b['id'] ?>, '<?= htmlspecialchars($b['designation']) ?>', <?= $b['quantite_restante'] ?>, <?= $b['prix_unitaire'] ?>, <?= $montant ?>, <?= $montantFrais ?>)">
                                    Simuler
                                </button>
                                <form action="/simulation/validate" method="POST" class="inline-form" style="display: inline;">
                                    <input type="hidden" name="besoin_id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Valider cet achat pour <?= number_format($montantFrais, 0, ',', ' ') ?> Ariary (frais inclus) ?')">
                                        Acheter
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-danger">Fonds insuffisants</span>
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

<!-- PARTIE 2: Liste des achats effectués -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <h3>2. Historique des Achats</h3>
        <p class="text-muted">Liste des achats deja effectues avec les dons en argent</p>
    </div>
    <div class="card-body">
        <!-- Filtre par ville pour les achats -->
        <div style="margin-bottom: 15px;">
            <label for="filter-ville-liste"><strong>Filtrer par ville:</strong></label>
            <select id="filter-ville-liste" class="form-control" style="width: 200px; display: inline-block; margin-left: 10px;">
                <option value="">Toutes les villes</option>
                <?php foreach ($villes as $v): ?>
                    <option value="<?= htmlspecialchars($v['nom']) ?>"><?= htmlspecialchars($v['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if (empty($achats)): ?>
            <p class="text-muted">Aucun achat effectue pour le moment.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Quantite</th>
                        <th>Montant Base</th>
                        <th>Frais</th>
                        <th>Montant Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($achats as $a): ?>
                    <tr class="liste-row" data-ville="<?= htmlspecialchars($a['ville_nom']) ?>">
                        <td><?= htmlspecialchars($a['ville_nom']) ?></td>
                        <td><?= htmlspecialchars($a['designation']) ?></td>
                        <td><?= number_format($a['quantiteAchetee'], 2, ',', ' ') ?></td>
                        <td><?= number_format($a['montant_utilise'], 0, ',', ' ') ?></td>
                        <td><?= $a['frais'] ?>%</td>
                        <td><strong><?= number_format($a['montant_total'], 0, ',', ' ') ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($a['date_achat'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Récapitulatif financier -->
<div class="card">
    <div class="card-header">
        <h3>Récapitulatif Financier</h3>
    </div>
    <div class="card-body">
        <div id="recap-data">
            <p>Chargement...</p>
        </div>
        <button id="btn-refresh" class="btn btn-secondary" style="margin-top: 10px;">
            &#8635; Actualiser
        </button>
    </div>
</div>

<!-- Modal de simulation -->
<div id="modal-simulation" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Résultat de la Simulation</h3>
            <span class="close" onclick="fermerModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="simulation-result">
                <p><strong>Besoin:</strong> <span id="sim-besoin"></span></p>
                <p><strong>Quantité:</strong> <span id="sim-qte"></span></p>
                <p><strong>Prix unitaire:</strong> <span id="sim-prix"></span> Ar</p>
                <hr>
                <p><strong>Montant de base:</strong> <span id="sim-montant"></span> Ar</p>
                <p><strong>Frais (<?= $frais_achat ?>%):</strong> <span id="sim-frais"></span> Ar</p>
                <p><strong>Montant total:</strong> <span id="sim-total" style="font-size: 1.2em; font-weight: bold; color: green;"></span> Ar</p>
                <hr>
                <p><strong>Argent disponible:</strong> <span id="sim-disponible"></span> Ar</p>
                <p id="sim-verdict" style="font-weight: bold;"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="fermerModal()">Fermer</button>
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
    max-width: 500px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.modal-header h3 {
    margin: 0;
}
.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.close:hover {
    color: red;
}
.modal-body {
    padding: 15px 0;
}
.modal-footer {
    padding-top: 15px;
    text-align: right;
}
</style>

<script>
// Filtrage par ville pour les achats
document.getElementById('filter-ville-achat').addEventListener('change', function() {
    const ville = this.value;
    const rows = document.querySelectorAll('.achat-row');
    rows.forEach(row => {
        if (ville === '' || row.getAttribute('data-ville') === ville) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Filtrage par ville pour la liste
document.getElementById('filter-ville-liste').addEventListener('change', function() {
    const ville = this.value;
    const rows = document.querySelectorAll('.liste-row');
    rows.forEach(row => {
        if (ville === '' || row.getAttribute('data-ville') === ville) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

function simulerAchat(id, designation, qte, prix, montant, total) {
    document.getElementById('sim-besoin').textContent = designation;
    document.getElementById('sim-qte').textContent = qte.toLocaleString('fr-FR');
    document.getElementById('sim-prix').textContent = prix.toLocaleString('fr-FR');
    document.getElementById('sim-montant').textContent = montant.toLocaleString('fr-FR');
    
    const frais = total - montant;
    document.getElementById('sim-frais').textContent = frais.toLocaleString('fr-FR');
    document.getElementById('sim-total').textContent = total.toLocaleString('fr-FR');
    
    const disponible = <?= $argent_restant ?>;
    document.getElementById('sim-disponible').textContent = disponible.toLocaleString('fr-FR');
    
    const verdict = document.getElementById('sim-verdict');
    if (total <= disponible) {
        verdict.textContent = 'Achat possible';
        verdict.style.color = 'green';
    } else {
        const manquant = total - disponible;
        verdict.textContent = 'Fonds insuffisants (manque ' + manquant.toLocaleString('fr-FR') + ' Ar)';
        verdict.style.color = 'red';
    }
    
    document.getElementById('modal-simulation').style.display = 'block';
}

function fermerModal() {
    document.getElementById('modal-simulation').style.display = 'none';
}

// Fermer le modal en cliquant a l'exterieur
window.onclick = function(event) {
    const modal = document.getElementById('modal-simulation');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Ajax recap
document.getElementById('btn-refresh').addEventListener('click', function() {
    fetch('/simulation/recap')
        .then(response => response.json())
        .then(data => {
            document.getElementById('recap-data').innerHTML = 
                '<div class="stats-grid">' +
                '<div class="stat-card">' +
                '<div class="stat-value">' + data.total_besoins + '</div>' +
                '<div class="stat-label">Total Besoins (Ariary)</div>' +
                '</div>' +
                '<div class="stat-card accent">' +
                '<div class="stat-value">' + data.satisfaits + '</div>' +
                '<div class="stat-label">Satisfaits (Ariary)</div>' +
                '</div>' +
                '<div class="stat-card">' +
                '<div class="stat-value">' + data.restants + '</div>' +
                '<div class="stat-label">Restants (Ariary)</div>' +
                '</div>' +
                '<div class="stat-card">' +
                '<div class="stat-value">' + data.taux + '%</div>' +
                '<div class="stat-label">Taux de Couverture</div>' +
                '</div>' +
                '</div>';
        });
});

// Charger au demarrage
document.getElementById('btn-refresh').click();
</script>
