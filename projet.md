
# 1. Contexte du projet

Le **BNGRC** (Bureau National de Gestion des Risques et des Catastrophes) souhaite développer une application web permettant de :

* Suivre les **besoins des sinistrés**
* Enregistrer les **dons reçus**
* Simuler la **distribution (dispatch)** des dons

⚠️ Important :
Les sinistrés ne sont **pas identifiés individuellement**.
La gestion se fait **par ville**.

---

# 2. Problématique métier

Après une catastrophe :

* Plusieurs villes sont touchées.
* Chaque ville a des besoins différents.
* Des dons arrivent progressivement.
* Il faut répartir ces dons de manière organisée et chronologique.

L’application doit permettre de centraliser et automatiser cette gestion.

---

# 3. Données à gérer

## 3.1 Les villes

Chaque ville représente un regroupement de sinistrés.

Exemple :

* Antsirabe
* Toamasina
* Mahajanga

---

## 3.2 Les besoins

Chaque ville possède des besoins de trois types :

### 1️⃣ En nature

* Riz
* Huile
* Sucre
* Etc.

### 2️⃣ En matériaux

* Tôle
* Clou
* Bois
* Etc.

### 3️⃣ En argent

---

### Structure d’un besoin

Chaque besoin doit contenir :

* Ville
* Type (nature / matériaux / argent)
* Désignation (ex: riz)
* Quantité
* Prix unitaire (⚠️ ne change jamais)
* Date de saisie

💡 Le prix unitaire est fixe → on peut calculer :

[
\text{Montant total du besoin} = \text{Quantité} \times \text{Prix unitaire}
]

---

## 3.3 Les dons

Les dons peuvent être :

* En nature
* En matériaux
* En argent

Chaque don doit contenir :

* Type
* Désignation
* Quantité
* Date de saisie

---

# 4. Logique principale : le dispatch

C’est la partie la plus importante du projet.

Le système doit :

1. Prendre les dons
2. Les distribuer aux villes
3. Respecter l’ordre chronologique

👉 Règle :
Le dispatch se fait **par ordre de date de saisie**.

Donc :

* Premier besoin saisi → servi en premier
* Premier don reçu → distribué en premier

Cela ressemble à une gestion de type **FIFO (First In First Out)**.

---

# 5. Tableau de bord (Dashboard)

L’application doit contenir une page principale qui affiche :

### Pour chaque ville :

* Liste des besoins
* Quantité demandée
* Quantité déjà couverte
* Quantité restante
* Dons attribués

Exemple :

| Ville     | Produit | Besoin  | Attribué | Reste  |
| --------- | ------- | ------- | -------- | ------ |
| Antsirabe | Riz     | 1000 kg | 600 kg   | 400 kg |

---

# 6. Ce que vous devez créer

Le document dit :

> "À vous de créer toutes les pages nécessaires"

Donc vous devez concevoir :

## Pages minimales :

1. Page gestion des villes
2. Page saisie des besoins
3. Page saisie des dons
4. Page simulation dispatch
5. Page tableau de bord
6. Page récapitulatif global

---

# 7. Modélisation technique (important pour votre examen)

Vous devez réfléchir en termes de base de données.

## Tables probables :

### Table villes

* id
* nom

### Table besoins

* id
* ville_id
* type
* designation
* quantite
* prix_unitaire
* date_saisie

### Table dons

* id
* type
* designation
* quantite
* date_saisie

### Table dispatch (relation dons ↔ besoins)

* id
* don_id
* besoin_id
* quantite_attribuee
* date_dispatch

---

# 8. Objectif pédagogique caché

Ce projet teste :

* Modélisation base de données
* Relations entre tables
* Logique métier
* Algorithme de répartition
* Calculs dynamiques
* Dashboard synthétique
* Travail en équipe
* Déploiement serveur

---

# 9. Partie finale : livraison

Le projet doit être :

* Fonctionnel
* Bien structuré
* Déployé sur le serveur ITU

---

# 10. Résumé simple

En une phrase :

Vous devez créer une application qui :

* Enregistre les besoins par ville
* Enregistre les dons
* Distribue automatiquement les dons
* Affiche un tableau de bord clair
* Respecte les règles de gestion données
