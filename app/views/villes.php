<!-- Gestion des Villes -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Liste des villes (<?= count($villes) ?>)</h3>
        <a href="/villes/create" class="btn btn-primary">Ajouter une ville</a>
    </div>
    <div class="card-body">
        <?php if (empty($villes)): ?>
            <p class="text-muted">Aucune ville enregistree.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Region</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($villes as $i => $ville): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><a href="/villes/<?= $ville['id'] ?>"><strong><?= htmlspecialchars($ville['nom']) ?></strong></a></td>
                        <td><?= htmlspecialchars($ville['region_nom']) ?></td>
                        <td>
                            <a href="/villes/<?= $ville['id'] ?>" class="btn btn-info btn-sm">Voir</a>
                            <a href="/villes/edit/<?= $ville['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
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
