<?php
/**
 * Point d'entrée de l'application BNGRC - Flight MVC
 */

// Définir le chemin racine de l'application
define('APP_ROOT', dirname(__DIR__));

// Chargement de Flight
require_once APP_ROOT . '/vendor/core-master/flight/Flight.php';

// Chargement de la configuration
require_once APP_ROOT . '/app/config/database.php';

// Chargement des modèles
require_once APP_ROOT . '/app/models/Ville.php';
require_once APP_ROOT . '/app/models/Besoin.php';
require_once APP_ROOT . '/app/models/Don.php';
require_once APP_ROOT . '/app/models/DispatchModel.php';
require_once APP_ROOT . '/app/models/Region.php';
require_once APP_ROOT . '/app/models/achat.php';

// Chargement des contrôleurs
require_once APP_ROOT . '/app/controllers/DashboardController.php';
require_once APP_ROOT . '/app/controllers/VilleController.php';
require_once APP_ROOT . '/app/controllers/BesoinController.php';
require_once APP_ROOT . '/app/controllers/DonController.php';
require_once APP_ROOT . '/app/controllers/DispatchController.php';
require_once APP_ROOT . '/app/controllers/RegionController.php';
require_once APP_ROOT . '/app/controllers/AchatController.php';

// Configuration de Flight
Flight::set('flight.views.path', APP_ROOT . '/app/views');

// Routes
require_once APP_ROOT . '/app/config/routes.php';

// Démarrer l'application
Flight::start();