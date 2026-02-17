<!-- Gestion des Régions -->
<div class="card">
    <div class="card-header">
        <h3>Ajouter une région</h3>
    </div>
    <div class="card-body">
        <form action="/regions/create" method="POST" class="form-inline">
            <div class="form-group">
                <label for="nom">Nom de la région</label>
                <input type="text" id="nom" name="nom" placeholder="Ex: Analamanga" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Liste des Régions (<?= count($regions) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($regions)): ?>
            <p class="text-muted">Aucune région enregistrée.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($regions as $i => $region): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><a href="/regions/<?= $region['id'] ?>"><strong><?= htmlspecialchars($region['nom']) ?></strong></a></td>
                        <td>
                            <form action="/regions/delete/<?= $region['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer cette région ?')">
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
