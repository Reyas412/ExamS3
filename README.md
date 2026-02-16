# BNGRC - Gestion des Catastrophes

Application web pour la gestion de la distribution des dons aux sinistrés par ville selon le principe FIFO (First In First Out).

## Fonctionnalités

### Version de base
- Gestion des villes
- Saisie des besoins (nature, matériaux, argent)
- Saisie des dons
- Distribution automatique (dispatch) avec algorithme FIFO
- Tableau de bord avec indicateurs KPI
- Système de régions

### Version 2.0 (V2)
- Achat de besoins avec dons en argent
- Configuration des frais d'achat
- Simulation d'achat avant validation
- Récapitulatif financier en temps réel (Ajax)
- Type de dispatch: Don vs Achat

## Installation

### Prérequis
- PHP 8.2+
- MySQL/MariaDB
- XAMPP (recommandé)

### Configuration

1. **Base de données:**
```bash
# Créer la base de données
mysql -u root -e "CREATE DATABASE gnbrc;"

# Importer les données
mysql -u root gnbrc < data.sql

# Mettre à jour vers V2 (optionnel)
php update_v2.php
```

2. **Lancer le serveur:**
```bash
php -S localhost:8000 -t public
```

3. **Accéder à l'application:**
```
http://localhost:8000
```

## Structure du projet

```
exam_S3_web-main/
├── app/
│   ├── config/
│   │   ├── database.php      # Configuration base de données
│   │   └── routes.php        # Définition des routes
│   ├── controllers/
│   │   ├── DashboardController.php
│   │   ├── VilleController.php
│   │   ├── BesoinController.php
│   │   ├── DonController.php
│   │   ├── DispatchController.php
│   │   ├── RegionController.php
│   │   ├── SimulationController.php  # V2
│   │   └── ConfigController.php      # V2
│   ├── models/
│   │   ├── Ville.php
│   │   ├── Besoin.php
│   │   ├── Don.php
│   │   ├── DispatchModel.php
│   │   └── Region.php
│   └── views/
│       ├── dashboard.php
│       ├── villes.php
│       ├── besoins.php
│       ├── dons.php
│       ├── dispatch.php
│       ├── regions.php
│       ├── simulation.php     # V2
│       └── config.php         # V2
├── public/
│   ├── index.php             # Point d'entrée
│   └── assets/
│       ├── css/style.css
│       └── js/app.js
├── data.sql                  # Données de base
├── update_v2.php             # Script mise à jour V2
├── README.md
├── README_V2.md              # Documentation V2
└── TESTING.md                # Scénarios de test
```

## Routes

| Route | Méthode | Description |
|-------|---------|-------------|
| `/` | GET | Tableau de bord |
| `/villes` | GET | Gestion des villes |
| `/villes/@id` | GET | Détail d'une ville |
| `/besoins` | GET | Gestion des besoins |
| `/dons` | GET | Gestion des dons |
| `/dispatch` | GET | Page de distribution |
| `/dispatch/run` | POST | Lancer le dispatch |
| `/regions` | GET | Gestion des régions |
| `/simulation` | GET | Simulation d'achat (V2) |
| `/simulation/validate` | POST | Valider un achat (V2) |
| `/config` | GET | Configuration (V2) |

## Documentation

- [README_V2.md](README_V2.md) - Documentation complète de la version 2.0
- [TESTING.md](TESTING.md) - Scénarios de test

## Fonctionnalités détaillées

### Distribution FIFO
L'algorithme de distribution respecte strictement l'ordre chronologique:
- Les besoins les plus anciens sont servis en premier
- Les dons les plus anciens sont utilisés en premier
- Les correspondances se font sur type + désignation

### Indicateurs KPI
- Nombre de villes
- Total des besoins
- Total des dons
- Taux de couverture global
- Couverture par ville

### Type de dispatch (V2)
- **Don**: Attribution classique via dispatch automatique
- **Achat**: Achat direct de besoins via dons en argent
