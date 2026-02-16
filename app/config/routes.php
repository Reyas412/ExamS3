<?php

// Routes principales
Flight::route('GET /', 'DashboardController::index');

// Régions
Flight::route('GET /regions', 'RegionController::index');
Flight::route('POST /regions/create', 'RegionController::create');
Flight::route('POST /regions/delete/@id', 'RegionController::delete');

// Villes
Flight::route('GET /villes', 'VilleController::index');
Flight::route('POST /villes/create', 'VilleController::create');
Flight::route('POST /villes/delete/@id', 'VilleController::delete');

// Besoins
Flight::route('GET /besoins', 'BesoinController::index');
Flight::route('GET /besoins/edit/@id', 'BesoinController::edit');
Flight::route('POST /besoins/update/@id', 'BesoinController::update');
Flight::route('POST /besoins/create', 'BesoinController::create');
Flight::route('POST /besoins/delete/@id', 'BesoinController::delete');

// Dons
Flight::route('GET /dons', 'DonController::index');
Flight::route('GET /dons/edit/@id', 'DonController::edit');
Flight::route('POST /dons/update/@id', 'DonController::update');
Flight::route('POST /dons/create', 'DonController::create');
Flight::route('POST /dons/delete/@id', 'DonController::delete');

// Dispatch
Flight::route('GET /dispatch', 'DispatchController::index');
Flight::route('POST /dispatch/run', 'DispatchController::run');
Flight::route('POST /dispatch/reset', 'DispatchController::reset');

// Route 404
Flight::map('notFound', function() {
    Flight::redirect('/');
});
