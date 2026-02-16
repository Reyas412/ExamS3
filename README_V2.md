# BNGRC - Version 2.0

## Vue d'ensemble

La version 2.0 de l'application BNGRC introduit la possibilité d'**acheter des besoins en utilisant les dons en argent**. Cette fonctionnalité permet aux organisations d'utiliser les dons en espèces disponibles pour acheter directement les besoins en nature et matériaux nécessaires aux secours en cas de catastrophe.

## Nouvelles fonctionnalités

### 1. Configuration des frais d'achat

- Pourcentage de frais d'achat configurable (défaut: 10%)
- Formule: `Montant final = Montant achat + (Montant achat × X%)`
- Exemple: Pour 100 000 Ar avec 10% de frais = 110 000 Ar

**Accès:** `/config`

### 2. Simulation d'achat

- Visualiser les besoins restants (nature et matériaux uniquement)
- Voir les fonds disponibles provenant des dons en argent
- Simuler les achats avant de s'engager

**Fonctionnalités:**
- Calcul en temps réel avec les frais
- Calcul de la quantité restante
- Affichage du montant maximum achetable
- Séparation entre simulation et achat réel

**Accès:** `/simulation`

### 3. Récapitulatif financier (Ajax)

- Résumé financier en temps réel
- Montant total des besoins
- Besoins satisfaits
- Besoins restants
- Pourcentage de couverture
- Capacité de rafraîchissement automatique

**Point d'accès:** `/simulation/recap` (JSON)

### 4. Validation des achats

- Processus en deux étapes: simulation puis validation
- Basé sur les transactions pour l'intégrité des données
- Crée à la fois les enregistrements `achats` et `dispatch`

## Modifications de la base de données

### Nouvelles tables

#### Table `config`
Stocke la configuration de l'application:
- `frais_achat`: Pourcentage de frais pour les achats (défaut: 10%)

#### Table `achats`
Suivi des achats:
- `id`: Clé primaire
- `besoin_id`: Clé étrangère vers les besoins
- `don_id`: Lien optionnel vers le don utilisé
- `montant_utilise`: Montant de base sans les frais
- `frais`: Pourcentage de frais appliqué
- `montant_total`: Montant total incluant les frais
- `quantiteAchetee`: Quantité achetée
- `date_achat`: Horodatage de l'achat

### Tables modifiées

#### Table `dispatch`
Ajout de:
- `type_dispatch`: ENUM('don', 'achat') - distingue les dons classiques des achats

## Routes

| Route | Méthode | Description |
|-------|---------|-------------|
| `/simulation` | GET | Afficher la page de simulation |
| `/simulation/validate` | POST | Valider et exécuter l'achat |
| `/simulation/recap` | GET | Obtenir le résumé financier (JSON) |
| `/config` | GET | Afficher la page de configuration |
| `/config/update` | POST | Mettre à jour la configuration |

## Flux de travail

### Effectuer un achat

1. **Naviguer vers la page de simulation**
   - Aller à `/simulation`
   - Visualiser la liste des besoins restants (nature/matériaux uniquement)
   - Voir l'argent disponible

2. **Vérifier les détails de l'achat**
   - Chaque besoin affiche la quantité restante
   - Prix unitaire affiché
   - Total avec frais calculé automatiquement

3. **Valider l'achat**
   - Cliquer sur le bouton "Valider l'achat"
   - Le système vérifie les fonds disponibles
   - Crée un enregistrement d'achat
   - Crée un enregistrement de dispatch
   - Redirige avec message de succès/erreur

### Configuration

1. Aller à `/config`
2. Modifier le pourcentage de frais
3. Enregistrer les modifications

## Calculs financiers

### Fonds disponibles
```
Disponible = Total des dons en argent - Déjà utilisé pour les achats
```

### Total d'un achat
```
Total = Quantité × Prix unitaire
Total final = Total + (Total × Frais%)
```

### Taux de couverture
```
Taux = (Satisfaits via dons + Achats) / Total besoins × 100
```

## Détails techniques

### Contrôleurs
- `SimulationController.php` - Gère la simulation et la validation des achats
- `ConfigController.php` - Gère la gestion de la configuration

### Vues
- `simulation.php` - Page principale de simulation
- `config.php` - Page de configuration

### Intégration Ajax
La page de simulation utilise JavaScript pour récupérer les données du récapitulatif financier:
```javascript
fetch('/simulation/recap')
  .then(response => response.json())
  .then(data => {
    // Mettre à jour l'interface avec data.total_besoins, data.satisfaits, data.restants, data.taux
  });
```

## Gestion des erreurs

- Fonds insuffisants: Affiche un message d'erreur avec le montant nécessaire
- Besoin déjà couvert: Empêche l'achat double
- Erreurs de base de données: Rollback de la transaction avec message adapté

## Devise

Tous les montants sont en **Ariary (Ar)** avec espace comme séparateur de milliers et virgule pour les décimales.
