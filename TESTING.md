# Scénarios de Test - Application BNGRC

## Vue d'ensemble
Cette application gère la distribution de dons aux sinistrés par ville selon le principe FIFO (First In First Out).

## Prérequis
- Base de données `gnbrc` créée avec les tables et données de base
- Serveur PHP lancé : `php -S localhost:8000 -t public`
- Accès via : http://localhost:8000

---

## Scénario 1 : Gestion des Villes

### 1.1 Ajouter une nouvelle ville
1. Aller sur `/villes`
2. Dans "Ajouter une ville" :
   - Nom : "Toliara"
   - Cliquer "Ajouter"
3. Vérifier que la ville apparaît dans la liste

### 1.2 Supprimer une ville
1. Sur `/villes`, cliquer "Supprimer" sur une ville sans besoins
2. Confirmer la suppression
3. Vérifier que la ville disparaît de la liste

### 1.3 Tentative de suppression avec besoins
1. Ajouter un besoin à une ville
2. Tenter de supprimer cette ville
3. Vérifier que la suppression échoue (message d'erreur)

---

## Scénario 2 : Saisie des Besoins

### 2.1 Ajouter un besoin en nature
1. Aller sur `/besoins`
2. Remplir :
   - Ville : "Antsirabe"
   - Type : "En nature"
   - Désignation : "Huile de cuisson"
   - Quantité : 300
   - Prix unitaire : 6.50
   - Date : laisser vide (auto)
3. Cliquer "Ajouter le besoin"
4. Vérifier l'apparition dans la liste

### 2.2 Ajouter un besoin en matériaux
1. Sur `/besoins` :
   - Ville : "Toamasina"
   - Type : "En matériaux"
   - Désignation : "Ciment"
   - Quantité : 500
   - Prix unitaire : 15.00
2. Ajouter et vérifier

### 2.3 Ajouter un besoin en argent
1. Sur `/besoins` :
   - Ville : "Mahajanga"
   - Type : "En argent"
   - Désignation : "Argent liquide"
   - Quantité : 20000
   - Prix unitaire : 1.00
2. Ajouter et vérifier

### 2.4 Supprimer un besoin
1. Cliquer "Supprimer" sur un besoin
2. Confirmer
3. Vérifier la disparition

---

## Scénario 3 : Saisie des Dons

### 3.1 Ajouter un don en nature
1. Aller sur `/dons`
2. Remplir :
   - Type : "En nature"
   - Désignation : "Huile de cuisson"
   - Quantité : 250
   - Date : laisser vide
3. Ajouter et vérifier

### 3.2 Ajouter un don en matériaux
1. Sur `/dons` :
   - Type : "En matériaux"
   - Désignation : "Ciment"
   - Quantité : 400
2. Ajouter et vérifier

### 3.3 Ajouter un don en argent
1. Sur `/dons` :
   - Type : "En argent"
   - Désignation : "Argent liquide"
   - Quantité : 15000
2. Ajouter et vérifier

### 3.4 Supprimer un don
1. Cliquer "Supprimer" sur un don
2. Confirmer
3. Vérifier la disparition

---

## Scénario 4 : Simulation du Dispatch

### 4.1 Premier dispatch - Attribution partielle
1. S'assurer d'avoir des besoins et dons correspondants
2. Aller sur `/dispatch`
3. Cliquer "Lancer le Dispatch"
4. Vérifier :
   - Attributions créées dans le tableau
   - État des dons mis à jour (partiellement distribué)

### 4.2 Dispatch avec correspondance parfaite
1. Ajouter un don qui correspond exactement à un besoin restant
2. Relancer le dispatch
3. Vérifier l'attribution complète

### 4.3 Dispatch avec surplus
1. Ajouter un don plus grand que le besoin restant
2. Dispatch : vérifier que seul le nécessaire est attribué
3. Vérifier que le don a encore du reste

### 4.4 Reset du dispatch
1. Cliquer "Réinitialiser"
2. Confirmer
3. Vérifier que toutes les attributions disparaissent

---

## Scénario 5 : Consultation du Dashboard

### 5.1 Vue d'ensemble
1. Aller sur `/` (dashboard)
2. Vérifier :
   - Statistiques globales (nb villes, total besoins/dons, taux couverture)
   - Tableaux par ville avec besoins et couverture

### 5.2 Détails par ville
1. Pour chaque ville :
   - Vérifier la liste des besoins
   - Vérifier les barres de progression
   - Vérifier les calculs (attribué, reste, couverture %)

### 5.3 Récapitulatif des dons
1. En bas du dashboard :
   - Vérifier l'état de chaque don (distribué/partiellement/non distribué)
   - Vérifier les quantités distribuées vs total

---

## Scénario 6 : Cas d'erreur et validation

### 6.1 Formulaire vide
1. Sur n'importe quel formulaire d'ajout
2. Laisser tous les champs vides
3. Cliquer "Ajouter"
4. Vérifier le message d'erreur

### 6.2 Ville inexistante pour besoin
1. Supprimer une ville
2. Tenter d'ajouter un besoin à cette ville supprimée
3. Vérifier l'erreur

### 6.3 Don sans correspondance
1. Ajouter un don avec type/désignation qui ne correspond à aucun besoin
2. Lancer dispatch
3. Vérifier que ce don reste non distribué

### 6.4 Quantités négatives ou nulles
1. Tenter d'ajouter besoin/don avec quantité = 0
2. Vérifier le rejet

---

## Scénario 7 : Test de l'algorithme FIFO

### 7.1 Ordre chronologique des besoins
1. Ajouter 3 besoins à la même ville (même type/désignation) à des dates différentes
2. Ajouter un don qui couvre partiellement
3. Dispatch : vérifier que le besoin le plus ancien est servi en premier

### 7.2 Ordre chronologique des dons
1. Ajouter 2 dons identiques à des dates différentes
2. Dispatch : vérifier que le don le plus ancien est utilisé en premier

### 7.3 Distribution équitable
1. Besoins : A (100), B (200), C (150) - même type
2. Don : 250 unités
3. Dispatch : A reçoit 100, B reçoit 150, C reçoit 0
4. Vérifier la logique FIFO

---

## Scénario 8 : Navigation et Interface

### 8.1 Navigation sidebar
1. Tester tous les liens du menu latéral
2. Vérifier l'activation visuelle (highlight) de l'onglet actif

### 8.2 Responsive design
1. Redimensionner la fenêtre
2. Vérifier l'adaptation sur mobile (sidebar se réduit)

### 8.3 Messages de feedback
1. Effectuer des actions réussies
2. Vérifier les messages verts de succès
3. Effectuer des actions avec erreur
4. Vérifier les messages rouges d'erreur

---

## Scénario 9 : Données de test complètes

Pour un test complet, créer cette séquence :

### Étape 1 : Villes
- Antsirabe, Toamasina, Mahajanga, Toliara

### Étape 2 : Besoins chronologiques
1. Antsirabe - Riz - 1000 kg - 2.50€ - 01/01/2023
2. Toamasina - Tôle - 500 unités - 12€ - 02/01/2023
3. Mahajanga - Argent - 50000€ - 1€ - 03/01/2023
4. Antsirabe - Huile - 300L - 6€ - 04/01/2023
5. Toamasina - Riz - 800 kg - 2.50€ - 05/01/2023

### Étape 3 : Dons chronologiques
1. Riz - 1200 kg - 01/02/2023
2. Tôle - 400 unités - 02/02/2023
3. Argent - 30000€ - 03/02/2023
4. Huile - 250L - 04/02/2023

### Étape 4 : Dispatch et vérifications
1. Lancer dispatch
2. Vérifier attributions :
   - Riz 1000kg → Antsirabe (besoin 1)
   - Riz 200kg → Toamasina (besoin 5)
   - Tôle 400u → Toamasina (besoin 2)
   - Argent 30000€ → Mahajanga (besoin 3)
   - Huile 250L → Antsirabe (besoin 4)
3. Vérifier dashboard et couverture

---

## Points de vérification finaux

- [ ] Toutes les pages s'affichent sans erreur PHP
- [ ] Base de données accessible et cohérente
- [ ] Algorithme FIFO fonctionne correctement
- [ ] Interface responsive et ergonomique
- [ ] Messages d'erreur appropriés
- [ ] Calculs de couverture exacts
- [ ] Suppression en cascade fonctionne
- [ ] Données persistent après refresh

---

## Commandes utiles

```bash
# Lancer le serveur
php -S localhost:8000 -t public

# Accéder à l'app
http://localhost:8000

# Reset base de données
mysql -u root gnbrc < data.sql
```

---

## Notes importantes

- Le dispatch respecte strictement l'ordre chronologique (date_saisie)
- Les correspondances se font sur type + désignation (case insensitive)
- Les quantités peuvent être partielles
- Le dashboard se met à jour automatiquement après chaque action
- Toutes les suppressions sont confirmées par l'utilisateur

---

## Scénario 10 : Achat avec dons argent (V2)

### 10.1 Configuration des frais d'achat
1. Aller sur `/config`
2. Vérifier que le champ "Frais d'achat" est visible (défaut: 10%)
3. Modifier la valeur (ex: 15%)
4. Cliquer "Enregistrer"
5. Vérifier le message de succès

### 10.2 Page de simulation d'achat
1. Aller sur `/simulation`
2. Vérifier la liste des besoins restants (nature et matériaux uniquement)
3. Vérifier l'argent disponible (dons en argent non utilisés)
4. Vérifier le pourcentage de frais affiché

### 10.3 Calcul des frais
1. Sur la page simulation, noter un besoin:
   - Quantité restante : 100
   - Prix unitaire : 1 000 Ar
   - Montant de base : 100 000 Ar
2. Avec 10% de frais:
   - Montant final attendu : 110 000 Ar
3. Vérifier que le calcul est correct

### 10.4 Validation d'un achat (sufficient funds)
1. S'assurer d'avoir assez d'argent disponible
2. Cliquer "Valider l'achat" sur un besoin
3. Vérifier le message de succès
4. Vérifier que:
   - Un enregistrement est créé dans la table `achats`
   - Un enregistrement est créé dans `dispatch` (type: achat)
   - L'argent est déduit du disponible

### 10.5 Validation d'un achat (insufficient funds)
1. Calculer le montant nécessaire pour un besoin
2. S'assurer que l'argent disponible est inférieur
3. Tenter de valider l'achat
4. Vérifier le message d'erreur avec le montant nécessaire

### 10.6 Achat sur besoin déjà couvert
1. сделать d'abord un dispatch normal qui couvre un besoin
2. Tenter d'acheter ce besoin via simulation
3. Vérifier le message d'erreur

### 10.7 Récapitulatif financier (Ajax)
1. Sur la page simulation, vérifier le bloc récapitulatif
2. Vérifier les valeurs:
   - Total besoins en montant
   - Besoins satisfaits
   - Besoins restants
   - Taux de couverture
3. Cliquer sur le bouton de rafraîchissement
4. Vérifier que les données se mettent à jour

### 10.8 Type de dispatch
1. Après un achat, aller sur `/dispatch`
2. Vérifier que la colonne "Type" affiche:
   - "Don" pour les attributions classiques
   - "Achat" pour les achats via simulation

---

## Scénario 11 : Navigation V2

### 11.1 Accès au menu Simulation
1. Dans le sidebar, cliquer sur "Simulation Achat"
2. Vérifier que la page se charge correctement
3. Vérifier que le menu esthighlighté

### 11.2 Accès au menu Configuration
1. Dans le sidebar, cliquer sur "Configuration"
2. Vérifier que la page se charge correctement
3. Vérifier que le menu esthighlighté

---

## Points de vérification V2

- [ ] Page /config accessible et fonctionnelle
- [ ] Modification des frais d'achat persistée
- [ ] Page /simulation affiche les besoins restants
- [ ] Calcul des frais correct (Montant + X%)
- [ ] Achat validé avec fonds suffisants
- [ ] Erreur affichée avec fonds insuffisants
- [ ] Achat impossible sur besoin déjà couvert
- [ ] Ajax récapitulatif fonctionne
- [ ] Type "Achat" visible dans dispatch
- [ ] Menu V2 accessible depuis sidebar

---

## Commandes supplémentaires V2

```bash
# Mettre à jour la base de données V2
php update_v2.php

# Tester l'API recap
curl http://localhost:8000/simulation/recap
```