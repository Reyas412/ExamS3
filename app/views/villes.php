<!-- Gestion des Villes -->
<div class="card">
    <div class="card-header">
        <h3>Ajouter une ville</h3>
    </div>
    <div class="card-body">
        <form action="/villes/create" method="POST" class="form-inline">
            <div class="form-group">
                <label for="region_id">Région</label>
                <select id="region_id" name="region_id" required>
                    <option value="">-- Sélectionner une région --</option>
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nom">Nom de la ville</label>
                <input type="text" id="nom" name="nom" placeholder="Ex: Antananarivo" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Liste des Villes (<?= count($villes) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($villes)): ?>
            <p class="text-muted">Aucune ville enregistrée.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Région</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($villes as $i => $ville): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($ville['nom']) ?></td>
                        <td><?= htmlspecialchars($ville['region_nom']) ?></td>
                        <td>
                            <form action="/villes/delete/<?= $ville['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer cette ville et tous ses besoins ?')">
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
