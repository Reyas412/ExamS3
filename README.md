# Flight Login MVC

Application d'authentification avec architecture MVC utilisant Flight PHP.

## Structure du projet

```
flight-login-mvc/
├── app/                     # Application principale
│   ├── config/             # Configuration (database, routes)
│   ├── controllers/        # Contrôleurs MVC
│   ├── models/            # Modèles de données
│   └── views/             # Vues/Templates
├── public/                # Dossier public (point d'entrée)
│   ├── assets/           # Assets statiques
│   │   ├── css/         # Feuilles de style
│   │   └── js/          # JavaScript
│   ├── images/          # Images
│   ├── upload/          # Fichiers uploadés
│   ├── index.php        # Point d'entrée
│   └── .htaccess        # Réécriture URLs
└── vendor/              # Dépendances (Flight)
```

## Installation

1. **Configuration de la base de données :**
```bash
mysql -u root -p < ../flight-login/create_db.sql
```

2. **Accès à l'application :**
```
http://localhost/19_janv_MDP/flight-login-mvc/public/
```

## Architecture MVC

### Modèles (`app/models/`)
- `User.php` : Gestion des utilisateurs (CRUD, authentification)

### Contrôleurs (`app/controllers/`)
- `HomeController.php` : Page d'accueil et ping
- `AuthController.php` : Authentification (register, login)

### Vues (`app/views/`)
- `home.php` : Interface utilisateur principale

### Configuration (`app/config/`)
- `database.php` : Connexion PDO
- `routes.php` : Définition des routes

## API Endpoints

- `GET /` - Interface web
- `GET /ping` - Test de connexion
- `POST /register` - Créer un compte
- `POST /login` - Se connecter

## Fonctionnalités

- ✅ Architecture MVC complète
- ✅ Séparation des responsabilités
- ✅ Dossier public comme point d'entrée
- ✅ Assets organisés (CSS, JS, images, uploads)
- ✅ Authentification sécurisée avec password_hash()
- ✅ API JSON REST
- ✅ Interface web responsive
- ✅ Validation côté client et serveur

## Sécurité

- Mots de passe hashés avec `password_hash()`
- Validation des entrées
- Échappement des sorties
- Séparation du code et des assets publics# ExamS3
