<?php
/**
 * VUE — Achat (Simulation achat)
 * Variables depuis le Controller:
 *   $besoins, $villes, $achats, $argent_restant, $frais_achat
 */

$argentRestant = floatval($argent_restant ?? 0);
$totalDepense = 0;
if (!empty($achats)) {
    foreach ($achats as $a) {
        $totalDepense += floatval($a['montant_total'] ?? 0);
    }
}
?>

<div class="achat-page">
    <div class="achat-hero">
        <div>
            <h2 class="achat-title">Achat des besoins restants</h2>
            <p class="achat-subtitle">
                Utilisez l'argent des dons pour couvrir les besoins non satisfaits.
                L'achat couvre automatiquement la quantite restante.
            </p>
        </div>
        <div class="achat-kpis">
            <div class="achat-pill">
                <span>Argent disponible</span>
                <strong><?= number_format($argentRestant, 0, ',', ' ') ?> Ar</strong>
            </div>
            <div class="achat-pill achat-pill-accent">
                <span>Frais d'achat</span>
                <strong><?= number_format($frais_achat, 2, ',', ' ') ?>%</strong>
            </div>
        </div>
    </div>

    <div class="achat-layout">
        <div class="achat-card">
            <div class="achat-card-header">
                <h3>Nouvel achat</h3>
                <span class="achat-badge">Via dons en argent</span>
            </div>
            <div class="achat-card-body">
                <?php if (empty($besoins)): ?>
                    <div class="achat-empty">
                        Aucun besoin en nature ou materiaux a couvrir pour le moment.
                    </div>
                <?php else: ?>
                    <form action="/simulation/validate" method="POST" id="achat-form">
                        <div class="achat-form-group">
                            <label class="achat-label" for="achat-besoin">Choisir un besoin</label>
                            <select id="achat-besoin" name="besoin_id" class="achat-select" required>
                                <option value="">-- Choisir un besoin --</option>
                                <?php foreach ($besoins as $b): ?>
                                    <?php
                                        if ($b['quantite_restante'] <= 0) {
                                            continue;
                                        }
                                        $hasDon = $b['don_couvert'] > 0 ? 1 : 0;
                                    ?>
                                    <option
                                        value="<?= $b['id'] ?>"
                                        data-prix="<?= $b['prix_unitaire'] ?>"
                                        data-rest="<?= $b['quantite_restante'] ?>"
                                        data-ville="<?= htmlspecialchars($b['ville_nom']) ?>"
                                        data-has-don="<?= $hasDon ?>"
                                    >
                                        <?= htmlspecialchars($b['designation']) ?>
                                        (reste <?= number_format($b['quantite_restante'], 2, ',', ' ') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="achat-hint">Seuls les besoins non couverts apparaissent ici.</div>
                        </div>

                        <div class="achat-form-group">
                            <label class="achat-label" for="achat-quantite">Quantite a acheter</label>
                            <input type="number" id="achat-quantite" name="quantite_achetee" class="achat-select" min="1" step="0.01" placeholder="Ex: 50" required>
                            <div class="achat-hint">La quantite ne peut pas depasser le reste du besoin.</div>
                        </div>


                        <div class="achat-form-group">
                            <label class="achat-label">Resume du calcul</label>
                            <div class="achat-calc">
                                <div class="achat-calc-row">
                                    <span>Quantite restante</span>
                                    <strong id="achat-qte">0</strong>
                                </div>
                                <div class="achat-calc-row">
                                    <span>Montant de base</span>
                                    <strong id="achat-base">0 Ar</strong>
                                </div>
                                <div class="achat-calc-row">
                                    <span>Frais (<?= number_format($frais_achat, 2, ',', ' ') ?>%)</span>
                                    <strong id="achat-frais">0 Ar</strong>
                                </div>
                                <div class="achat-calc-row achat-calc-total">
                                    <span>Total debite</span>
                                    <strong id="achat-total">0 Ar</strong>
                                </div>
                            </div>
                        </div>

                        <div class="achat-error" id="achat-error">
                            Achat impossible: un don en nature ou materiaux couvre deja ce besoin.
                        </div>
                        <div class="achat-error" id="achat-quantite-error">
                            La quantite depasse le reste disponible pour ce besoin.
                        </div>
                        <div class="achat-error" id="achat-fonds">
                            Fonds insuffisants pour cet achat.
                        </div>

                        <button type="submit" class="achat-btn" id="achat-submit">Confirmer l'achat</button>
                        <div class="achat-hint">Le resume se met a jour apres la selection et la quantite.</div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="achat-card">
            <div class="achat-card-header">
                <h3>Achats effectues</h3>
                <span class="achat-badge achat-badge-muted">Historique</span>
            </div>
            <div class="achat-card-body achat-list">
                <?php if (empty($achats)): ?>
                    <div class="achat-empty">Aucun achat enregistre pour le moment.</div>
                <?php else: ?>
                    <?php foreach ($achats as $a): ?>
                        <div class="achat-list-item">
                            <div class="achat-list-icon">🛒</div>
                            <div class="achat-list-info">
                                <div class="achat-list-title">
                                    <?= htmlspecialchars($a['designation']) ?> — <?= number_format($a['quantite_achetee'], 2, ',', ' ') ?>
                                </div>
                                <div class="achat-list-sub">
                                    <?= htmlspecialchars($a['ville_nom']) ?>
                                    • Base <?= number_format($a['montant_base'], 0, ',', ' ') ?> Ar
                                    • Frais <?= number_format($a['frais_achat'], 2, ',', ' ') ?>%
                                </div>
                            </div>
                            <div class="achat-list-amt">
                                <?= number_format($a['montant_total'], 0, ',', ' ') ?> Ar
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="achat-card-footer">
                <span>Total depense</span>
                <strong><?= number_format($totalDepense, 0, ',', ' ') ?> Ar</strong>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const select = document.getElementById('achat-besoin');
    const qte = document.getElementById('achat-qte');
    const inputQte = document.getElementById('achat-quantite');
    const base = document.getElementById('achat-base');
    const frais = document.getElementById('achat-frais');
    const total = document.getElementById('achat-total');
    const btn = document.getElementById('achat-submit');
    const error = document.getElementById('achat-error');
    const qteError = document.getElementById('achat-quantite-error');
    const fonds = document.getElementById('achat-fonds');
    const argentRestant = <?= json_encode($argentRestant) ?>;

    if (!select) {
        return;
    }

    const format = (value) => {
        return new Intl.NumberFormat('fr-FR').format(value) + ' Ar';
    };

    const reset = () => {
        qte.textContent = '0';
        base.textContent = '0 Ar';
        frais.textContent = '0 Ar';
        total.textContent = '0 Ar';
        error.style.display = 'none';
        qteError.style.display = 'none';
        fonds.style.display = 'none';
        btn.disabled = true;
    };

    const update = () => {
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) {
            reset();
            return;
        }

        const quantiteRestante = parseFloat(opt.dataset.rest || '0');
        const prix = parseFloat(opt.dataset.prix || '0');
        const quantite = parseFloat(inputQte.value || '0');
        const montantBase = quantite * prix;
        const montantTotal = montantBase * (1 + (<?= json_encode(floatval($frais_achat)) ?> / 100));
        const hasDon = opt.dataset.hasDon === '1';

        qte.textContent = new Intl.NumberFormat('fr-FR').format(quantite || 0);
        base.textContent = format(montantBase);
        frais.textContent = format(montantTotal - montantBase);
        total.textContent = format(montantTotal);

        error.style.display = hasDon ? 'block' : 'none';
        qteError.style.display = (!hasDon && quantite > quantiteRestante) ? 'block' : 'none';
        fonds.style.display = (!hasDon && montantTotal > argentRestant) ? 'block' : 'none';

        btn.disabled = hasDon || quantite <= 0 || quantite > quantiteRestante || montantTotal > argentRestant;
    };

    select.addEventListener('change', update);
    inputQte.addEventListener('input', update);
    reset();
})();
</script>
