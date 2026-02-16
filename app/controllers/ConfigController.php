<?php
class ConfigController {
    
    public static function index() {
        $pdo = getDatabase();
        
        // Récupérer les paramètres
        $stmt = $pdo->query("SELECT * FROM config");
        $configs = [];
        while ($row = $stmt->fetch()) {
            $configs[$row['cle']] = $row['valeur'];
        }
        
        Flight::render('config', ['configs' => $configs], 'body_content');
        Flight::render('layout', [
            'title' => 'Configuration',
            'active' => 'config'
        ]);
    }
    
    public static function update() {
        $data = Flight::request()->data;
        $pdo = getDatabase();
        
        // Mettre à jour les frais d'achat
        if (isset($data->frais_achat)) {
            $stmt = $pdo->prepare("INSERT INTO config (cle, valeur) VALUES ('frais_achat', ?) ON DUPLICATE KEY UPDATE valeur = ?");
            $stmt->execute([$data->frais_achat, $data->frais_achat]);
        }
        
        Flight::redirect('/config?success=' . urlencode('Configuration mise à jour'));
    }
}
