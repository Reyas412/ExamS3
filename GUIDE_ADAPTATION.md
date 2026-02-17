# Guide d'Adaptation du Projet

## Vue d'ensemble

Votre projet a été adapté pour fonctionner avec la structure de base de données suivante :

### Structure de la base de données

```
DATABASE: gnbrc

TABLES:
├── regions (id, nom)
├── villes (id, nom, idregion)
├── besoins (id, ville_id, type, designation, quantite, prix_unitaire, date_saisie)
├── dons (id, type, designation, quantite, date_saisie)
└── dispatch (id, don_id, besoin_id, quantite_attribuee, date_dispatch)
```

## Changements effectués

### 1. Changement du nom de colonne
- `region_id` → `idregion` (pour correspondre à votre structure DB)

### 2. Fichiers modifiés

#### Modèles
- **app/models/Ville.php**
  - Fonction `getAll()`: Requête SQL mise à jour
  - Fonction `getById()`: Requête SQL mise à jour
  - Fonction `create()`: Paramètre changé
  - Fonction `update()`: Paramètre changé
  - Fonction `getByRegionId()`: Requête SQL mise à jour

- **app/models/DispatchModel.php**
  - Fonction `getStatsByRegionId()`: Requête SQL mise à jour

#### Contrôleurs
- **app/controllers/VilleController.php**
  - Fonction `create()`: Récupère le paramètre `idregion`

#### Vues
- **app/views/villes.php**
  - Formulaire: Changement du champ `region_id` en `idregion`

#### Données
- **data.sql**: Mise à jour de la structure CREATE TABLE et des INSERT

### 3. Nouveaux fichiers

- **setup_db.php**: Script pour initialiser la base de données
- **test_db.php**: Script pour vérifier que tout est configuré correctement
- **ADAPTATION_DB.md**: Documentation des changements (ce fichier)

## Étapes d'initialisation

### Option 1: Utiliser le script setup_db.php (Recommandé)

1. Accédez à: `http://localhost/examV2/ExamS3/setup_db.php`
2. Vous verrez les tables créées avec succès

### Option 2: Importer dans PhpMyAdmin

1. Ouvrez PhpMyAdmin
2. Créez une nouvelle base de données `gnbrc`
3. Importez le fichier `data.sql`

### Option 3: Exécuter depuis le terminal

```bash
cd c:\xampp\htdocs\examV2\ExamS3
php setup_db.php
```

## Vérification

Exécutez le script de test pour vérifier que tout est configuré:

```bash
php test_db.php
```

Ou accédez à: `http://localhost/examV2/ExamS3/test_db.php`

## Configuration de la connexion

La configuration de base de données se trouve dans:
**app/config/database.php**

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gnbrc');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Ajustez ces paramètres selon votre configuration XAMPP.

## Structure du projet

```
ExamS3/
├── app/
│   ├── config/
│   │   ├── database.php (Configuration DB)
│   │   └── routes.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── BesoinController.php
│   │   ├── DashboardController.php
│   │   ├── DispatchController.php
│   │   ├── DonController.php
│   │   ├── RegionController.php
│   │   ├── VilleController.php ✓ Modifié
│   │   └── ...
│   ├── models/
│   │   ├── Besoin.php
│   │   ├── DispatchModel.php ✓ Modifié
│   │   ├── Don.php
│   │   ├── Region.php
│   │   ├── User.php
│   │   └── Ville.php ✓ Modifié
│   └── views/
│       ├── villes.php ✓ Modifié
│       └── ...
├── public/
│   ├── index.php
│   └── assets/
├── setup_db.php ✓ Nouveau
├── test_db.php ✓ Nouveau
├── data.sql ✓ Modifié
└── ...
```

## Notes importantes

1. **Clés étrangères**: Les relations entre tables sont correctement configurées
2. **Cascade delete**: Les suppression de régions supprime les villes associées
3. **Types ENUM**: Les types (nature, matériaux, argent) sont définis comme ENUM
4. **Timestamps**: Les dates de création ont des valeurs par défaut (CURRENT_TIMESTAMP)

## Dépannage

### Si vous recevez une erreur de colonne non trouvée:
1. Vérifiez que vous utilisez le script setup_db.php
2. Exécutez test_db.php pour vérifier la structure

### Si vous recevez une erreur de clé étrangère:
1. Assurez-vous que les régions existent avant d'ajouter des villes
2. Vérifiez que les IDs correspondent

### Si PhpMyAdmin affiche des colonnes manquantes:
1. Supprimez la base de données `gnbrc`
2. Réexécutez setup_db.php

## Support

Pour toute question, consultez:
- Les fichiers de configuration dans `app/config/`
- Les modèles dans `app/models/`
- Les vues dans `app/views/`
