# Adaptation du Projet à la Base de Données

## Changements effectués

### 1. Correction du nom de colonne
- **Ancien**: `region_id` 
- **Nouveau**: `idregion`

Les fichiers suivants ont été corrigés:

#### Modèle (app/models/Ville.php)
- `getAll()`: Requête SQL mise à jour
- `getById()`: Requête SQL mise à jour  
- `create()`: Paramètre changé de `$region_id` à `$idregion`
- `update()`: Paramètre changé de `$region_id` à `$idregion`
- `getByRegionId()`: Requête SQL mise à jour

#### Contrôleur (app/controllers/VilleController.php)
- `create()`: Récupère `idregion` au lieu de `region_id`

#### Vue (app/views/villes.php)
- Formulaire: Input `region_id` changé en `idregion`

### 2. Structure de Base de Données

Le projet utilise maintenant la structure suivante:

```sql
- regions (id, nom)
- villes (id, nom, idregion)
- besoins (id, ville_id, type, designation, quantite, prix_unitaire, date_saisie)
- dons (id, type, designation, quantite, date_saisie)
- dispatch (id, don_id, besoin_id, quantite_attribuee, date_dispatch)
```

### 3. Initialisation de la Base de Données

Pour initialiser la base de données avec la structure correcte:

1. Accédez à: `http://localhost/examV2/ExamS3/setup_db.php`
2. Ou exécutez depuis le terminal: `php setup_db.php`
3. Ou importez manuellement le SQL fourni dans PhpMyAdmin

## Vérification

✅ Modèle Ville adapté
✅ Contrôleur Ville adapté  
✅ Vue Villes adaptée
✅ Script de setup créé

## Notes importantes

- La colonne `idregion` utilise les bonnes clés étrangères
- Les contraintes `ON DELETE CASCADE` sont en place
- La configuration de base de données est en place (app/config/database.php)
