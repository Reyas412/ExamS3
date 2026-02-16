<?php

// Routes principales
Flight::route('GET /', 'DashboardController::index');

// Routes récapitulatives du dashboard
Flight::route('GET /dashboard/villes', 'DashboardController::villesRecap');
Flight::route('GET /dashboard/besoins', 'DashboardController::besoinsRecap');
Flight::route('GET /dashboard/dons', 'DashboardController::donsRecap');

// Régions
Flight::route('GET /regions', 'RegionController::index');
Flight::route('GET /regions/@id', 'RegionController::view');
Flight::route('POST /regions/create', 'RegionController::create');
Flight::route('POST /regions/delete/@id', 'RegionController::delete');

// Villes
Flight::route('GET /villes', 'VilleController::index');
Flight::route('GET /villes/create', 'VilleController::create');
Flight::route('GET /villes/@id', 'VilleController::view');
Flight::route('GET /villes/edit/@id', 'VilleController::edit');
Flight::route('POST /villes/create', 'VilleController::create');
Flight::route('POST /villes/update/@id', 'VilleController::update');
Flight::route('POST /villes/delete/@id', 'VilleController::delete');

// Besoins
Flight::route('GET /besoins', 'BesoinController::index');
Flight::route('GET /besoins/create', 'BesoinController::create');
Flight::route('GET /besoins/edit/@id', 'BesoinController::edit');
Flight::route('POST /besoins/update/@id', 'BesoinController::update');
Flight::route('POST /besoins/create', 'BesoinController::create');
Flight::route('POST /besoins/delete/@id', 'BesoinController::delete');

// Dons
Flight::route('GET /dons', 'DonController::index');
Flight::route('GET /dons/create', 'DonController::create');
Flight::route('GET /dons/edit/@id', 'DonController::edit');
Flight::route('POST /dons/update/@id', 'DonController::update');
Flight::route('POST /dons/create', 'DonController::create');
Flight::route('POST /dons/delete/@id', 'DonController::delete');

// Dispatch
Flight::route('GET /dispatch', 'DispatchController::index');
Flight::route('POST /dispatch/run', 'DispatchController::run');
Flight::route('POST /dispatch/reset', 'DispatchController::reset');
Flight::route('GET /dispatch/report', 'DispatchController::generateReport');

// Achats
Flight::route('GET /achats', 'AchatController::index');
Flight::route('POST /achats/delete/@id', 'AchatController::delete');

// API - Achats
Flight::route('GET /api/besoins', 'AchatController::apiBesoins');
Flight::route('POST /api/achats/simuler', 'AchatController::apiSimuler');
Flight::route('POST /api/achats/valider', 'AchatController::apiValider');
Flight::route('GET /api/achats', 'AchatController::apiIndex');
Flight::route('GET /api/recap', 'AchatController::apiRecap');
Flight::route('GET /api/config/frais', 'AchatController::apiGetFrais');
Flight::route('POST /api/config/frais', 'AchatController::apiSetFrais');

// Route 404
Flight::map('notFound', function() {
    Flight::redirect('/');
});
