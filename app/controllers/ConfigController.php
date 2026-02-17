<?php
class ConfigController {
    
    public static function index() {
        $pdo = getDatabase();
        
        // Récupérer les paramètres
        $stmt = $pdo->query("SELECT id, frais_achat FROM config ORDER BY id ASC LIMIT 1");
        $row = $stmt->fetch();
        if (!$row) {
            $pdo->prepare("INSERT INTO config (id, frais_achat) VALUES (1, 10)")->execute();
            $row = ['id' => 1, 'frais_achat' => 10];
        }
        $configs = ['frais_achat' => $row['frais_achat']];
        
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
            $stmt = $pdo->prepare("UPDATE config SET frais_achat = ? WHERE id = 1");
            $stmt->execute([$data->frais_achat]);

            if ($stmt->rowCount() === 0) {
                $insert = $pdo->prepare("INSERT INTO config (id, frais_achat) VALUES (1, ?)");
                $insert->execute([$data->frais_achat]);
            }
        }
        
        Flight::redirect('/config?success=' . urlencode('Configuration mise à jour'));
    }
}
