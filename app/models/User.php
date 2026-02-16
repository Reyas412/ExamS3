<?php
/**
 * Modèle User - Gestion des utilisateurs
 */
class User {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDatabase();
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create($name, $email, $password) {
        // Vérifier si l'email existe déjà
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email already registered'];
        }
        
        // Hasher le mot de passe
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insérer l'utilisateur
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $result = $stmt->execute([$name, $email, $hash]);
        
        if ($result) {
            return ['success' => true, 'user_id' => $this->pdo->lastInsertId()];
        } else {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    /**
     * Authentifier un utilisateur
     */
    public function authenticate($email, $password) {
        $stmt = $this->pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Retirer le mot de passe de la réponse
        unset($user['password']);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Récupérer un utilisateur par ID
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}