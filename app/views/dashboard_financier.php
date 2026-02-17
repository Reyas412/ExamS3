<!-- Récapitulatif Financier -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Récapitulatif Financier</h3>
        <button id="refreshBtn" class="btn btn-primary" onclick="refreshFinancierData()">
            &#8635; Actualiser
        </button>
    </div>
    <div class="card-body">
        <!-- KPIs Stats -->
        <div class="stats-grid" style="margin-bottom: 30px;">
            <div class="stat-card">
                <div class="stat-value" id="totalBesoins"><?= number_format($stats['total_besoins'], 0, ',', ' ') ?></div>
                <div class="stat-label">Total Besoins (Ar)</div>
            </div>
            <div class="stat-card accent">
                <div class="stat-value" id="montantSatisfait"><?= number_format($stats['montant_satisfait'], 0, ',', ' ') ?></div>
                <div class="stat-label">Montant Satisfait (Ar)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="montantRestant"><?= number_format($stats['montant_restant'], 0, ',', ' ') ?></div>
                <div class="stat-label">Montant Restant (Ar)</div>
            </div>
            <div class="stat-card" style="background: var(--success);">
                <div class="stat-value" id="tauxCouverture"><?= $stats['taux_couverture'] ?>%</div>
                <div class="stat-label">Taux de Couverture</div>
            </div>
        </div>
        
        <!-- Dons en Argent -->
        <h4 style="margin-bottom: 15px; color: var(--text-muted);">Dons en Argent</h4>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="totalDonsArgent"><?= number_format($stats['total_dons_argent'], 0, ',', ' ') ?></div>
                <div class="stat-label">Total Dons Reçus (Ar)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="donsArgentUtilises"><?= number_format($stats['dons_argent_utilises'], 0, ',', ' ') ?></div>
                <div class="stat-label">Dons Utilisés (Ar)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="donsArgentRestants"><?= number_format($stats['dons_argent_restants'], 0, ',', ' ') ?></div>
                <div class="stat-label">Dons Restants (Ar)</div>
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="loadingIndicator" style="display: none; text-align: center; margin-top: 20px;">
            <span style="color: var(--text-muted);">Chargement...</span>
        </div>
    </div>
</div>

<script>
function refreshFinancierData() {
    const refreshBtn = document.getElementById('refreshBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    // Show loading
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '&#8635; Actualisation...';
    loadingIndicator.style.display = 'block';
    
    // Fetch data via AJAX
    fetch('/api/financier')
        .then(response => response.json())
        .then(data => {
            // Update values
            document.getElementById('totalBesoins').textContent = formatNumber(data.total_besoins);
            document.getElementById('montantSatisfait').textContent = formatNumber(data.montant_satisfait);
            document.getElementById('montantRestant').textContent = formatNumber(data.montant_restant);
            document.getElementById('tauxCouverture').textContent = data.taux_couverture + '%';
            
            document.getElementById('totalDonsArgent').textContent = formatNumber(data.total_dons_argent);
            document.getElementById('donsArgentUtilises').textContent = formatNumber(data.dons_argent_utilises);
            document.getElementById('donsArgentRestants').textContent = formatNumber(data.dons_argent_restants);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la récupération des données');
        })
        .finally(() => {
            // Hide loading
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '&#8635; Actualiser';
            loadingIndicator.style.display = 'none';
        });
}

function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(num));
}

// Auto-refresh every 30 seconds
setInterval(refreshFinancierData, 30000);
</script>
