<?php
/**
 * Contrôleur principal
 */
class HomeController {
    
    /**
     * Page d'accueil avec interface de test
     */
    public static function index() {
        Flight::render('home', [
            'title' => 'Flight Login API - MVC',
            'description' => 'Interface de test pour l\'API d\'authentification JSON avec Flight PHP (Architecture MVC)'
        ]);
    }
    
    /**
     * Test API ping
     */
    public static function ping() {
        header('Content-Type: application/json');
        echo json_encode(['pong' => true]);
    }
}