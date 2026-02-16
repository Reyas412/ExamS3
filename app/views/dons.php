<!-- Gestion des Dons -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Liste des dons (<?= count($dons) ?>)</h3>
        <a href="/dons/create" class="btn btn-primary">Ajouter un don</a>
    </div>
    <div class="card-body">
        <?php if (empty($dons)): ?>
            <p class="text-muted">Aucun don enregistre.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Designation</th>
                        <th>Quantite</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dons as $i => $d): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><span class="badge badge-type"><?= htmlspecialchars($d['type']) ?></span></td>
                        <td><?= htmlspecialchars($d['designation']) ?></td>
                        <td><?= number_format($d['quantite'], 2, ',', ' ') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($d['date_saisie'])) ?></td>
                        <td>
                            <a href="/dons/edit/<?= $d['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <form action="/dons/delete/<?= $d['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer ce don ?')">
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
