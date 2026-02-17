# Flux d'Achat et Récapitulation - Documentation

## 📊 Vue d'ensemble du système

Le système comporte maintenant 2 workflows principaux:

### 1️⃣ **Dispatch des Dons** (Existant)
- **Route:** `/dispatch`
- **Objectif:** Distribuer les dons existants aux besoins par ordre chronologique (FIFO)
- **Résultat:** Montrer combien de besoins sont couverts par les dons

### 2️⃣ **Simulation d'Achat** (Nouveau)
- **Route:** `/simulation`
- **Objectif:** Acheter les marchandises manquantes avec l'argent disponible
- **Résultat:** Redirige vers `/dispatch` pour voir la récapitulation complète

## 🔄 Flux utilisateur

```
1. Page d'accueil
   ↓
2. Clic sur "Achat" (nouveau lien dans le menu)
   ↓
3. Affichage: /simulation
   - Liste les besoins non couverts
   - Affiche l'argent disponible
   - Affiche les frais d'achat (défaut: 10%)
   ↓
4. L'utilisateur sélectionne un besoin et valide l'achat
   ↓
5. Backend: Crée un achat + crée un dispatch
   ↓
6. Redirection: /dispatch
   - Affiche la RÉCAPITULATION complète
   - Montre les dons + les achats
   - Montre le taux de couverture final
```

## 📋 Tables de base de données

### Table `achats` (Nouvelle)
```sql
CREATE TABLE achats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    besoin_id INT NOT NULL,                    -- Le besoin acheté
    quantite_achetee DECIMAL(10,2) NOT NULL,  -- Quantité achetée
    montant_base DECIMAL(12,2) NOT NULL,      -- Coût sans frais
    frais_pourcentage DECIMAL(5,2) NOT NULL,  -- % de frais (ex: 10)
    montant_frais DECIMAL(12,2) NOT NULL,     -- Montant des frais
    montant_total DECIMAL(12,2) NOT NULL,     -- Cout total avec frais
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE
)
```

### Table `config` (Nouvelle)
```sql
CREATE TABLE config (
    cle VARCHAR(100) PRIMARY KEY,              -- Clé de configuration
    valeur VARCHAR(255) NOT NULL               -- Valeur
)
```

**Configurations utilisées:**
- `frais_achat`: Pourcentage de frais d'achat (défaut: 10)

## 🛠️ Étapes d'installation

### 1. Créer les tables
```bash
php migrate_achats.php
```

Ou manuellement via PhpMyAdmin, importer:
```sql
CREATE TABLE achats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    besoin_id INT NOT NULL,
    quantite_achetee DECIMAL(10,2) NOT NULL,
    montant_base DECIMAL(12,2) NOT NULL,
    frais_pourcentage DECIMAL(5,2) NOT NULL DEFAULT 10,
    montant_frais DECIMAL(12,2) NOT NULL,
    montant_total DECIMAL(12,2) NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE
);

CREATE TABLE config (
    cle VARCHAR(100) PRIMARY KEY,
    valeur VARCHAR(255) NOT NULL
);

INSERT INTO config (cle, valeur) VALUES ('frais_achat', '10');
```

### 2. Vérifier les routes
✅ Routes ajoutées dans `app/config/routes.php`:
- `GET /simulation` → `SimulationController::index()`
- `POST /simulation/validate` → `SimulationController::validate()`

### 3. Vérifier le menu
✅ Lien "Achat" ajouté dans `app/views/layout.php`

## 📝 Fichiers modifiés

### Fichiers étendus
1. **app/views/layout.php**
   - ➕ Ajout du lien "Achat" dans le menu latéral

2. **app/config/routes.php**
   - ➕ Routes pour `/simulation`

3. **app/controllers/SimulationController.php**
   - 🔧 Modification: `validate()` redirige vers `/dispatch` (pas `/simulation`)
   - 🔧 Correction des colonnes de la table `achats`

### Fichiers créés
1. **migrate_achats.php**
   - Crée les tables `achats` et `config`
   - Initialise les frais d'achat à 10%

## 🎯 Logique métier

### Workflow Simulation d'Achat

1. **Consulter les besoins non couverts**
   ```sql
   SELECT b.* 
   FROM besoins b
   LEFT JOIN dispatch di ON di.besoin_id = b.id
   WHERE COALESCE(SUM(di.quantite_attribuee), 0) < b.quantite
   ```

2. **Vérifier l'argent disponible**
   ```sql
   SELECT SUM(quantite) FROM dons WHERE type = 'argent'
   ```

3. **Calculer le coût avec frais**
   ```
   coûtTotal = (quantité × prixUnitaire) × (1 + frais%)
   ```

4. **Créer un achat**
   - Insert dans `achats`
   - Insert dans `dispatch` (avec `don_id = NULL` pour identifier comme achat)

5. **Rediriger vers la récapitulation**
   - `/dispatch` affiche tous les besoins couverts (dons + achats)

## 🧮 Exemple de flux complet

**Données initiales:**
- Besoin: 100 unités de riz à 2€/unité = 200€
- Dons: 50 unités de riz + 500€ en argent
- Frais d'achat: 10%

**Actions:**
1. Dispatch des dons → 50 unités couverts
2. Besoin restant: 50 unités
3. Achat: 50 × 2€ = 100€ + 10€ (frais) = 110€ total
4. Argent restant: 500€ - 110€ = 390€
5. **Résultat:** Besoin 100% couvert

## ⚠️ Points importants

- ✓ Table `achats` stocke chaque achat effectué
- ✓ Table `dispatch` stocke tous les dispatches (dons + achats via `don_id`)
- ✓ Table `config` permet de configurer les frais
- ✓ Validation: L'argent disponible doit suffire
- ✓ Redirection automatique vers la récapitulation

## 🔍 Vérification

Pour vérifier que tout fonctionne:
1. Accédez à `http://localhost/examV2/ExamS3/`
2. Créez quelques besoins et dons
3. Cliquez sur "Achat" dans le menu
4. Sélectionnez un besoin et validez
5. Vous devez voir la récapitulation à `/dispatch`

## ❓ Dépannage

**Erreur: "Table achats not found"**
→ Exécutez `php migrate_achats.php`

**Erreur: "Unknown column"**
→ Vérifiez que la table achats a les bonnes colonnes

**Achat non sauvegardé**
→ Vérifiez que l'argent disponible est suffisant

---

**Date mise à jour:** 16 février 2026
