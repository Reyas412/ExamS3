<?php
/**
 * Contrôleur d'authentification
 */
class AuthController {
    
    /**
     * Enregistrer un nouvel utilisateur
     */
    public static function register() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$name || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing fields']);
            return;
        }

        $user = new User();
        $result = $user->create($name, $email, $password);
        
        if ($result['success']) {
            http_response_code(201);
        } else {
            http_response_code($result['error'] === 'Email already registered' ? 409 : 500);
        }
        
        echo json_encode($result);
    }
    
    /**
     * Connecter un utilisateur
     */

    public static function login() {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
            return;
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $name = trim($data['name'] ?? ''); // facultatif, sinon on peut générer un nom par défaut

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing fields']);
            return;
        }

        $user = new User();
        $result = $user->authenticate($email, $password);

        if ($result['success']) {
            // Utilisateur existant et authentifié
            echo json_encode($result);
            return;
        }

        // Si l'utilisateur n'existe pas ou mauvais mot de passe → on le crée
        if (!$name) {
            $name = explode('@', $email)[0]; // nom par défaut basé sur l'email
        }

        $createResult = $user->create($name, $email, $password);

        if ($createResult['success']) {
            // Récupérer l'utilisateur nouvellement créé
            $newUser = $user->findById($createResult['user_id']);
            echo json_encode(['success' => true, 'user' => $newUser, 'created' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $createResult['error']]);
        }
    }


}