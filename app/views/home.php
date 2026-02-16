<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($description) ?></p>
        </div>

        <div class="content">
            <div class="section">
                <h2>Enregistrement</h2>
                <form id="registerForm">
                    <div class="form-group">
                        <label for="regName">Nom complet</label>
                        <input type="text" id="regName" placeholder="Entrez votre nom" required>
                    </div>
                    <div class="form-group">
                        <label for="regEmail">Adresse email</label>
                        <input type="email" id="regEmail" placeholder="exemple@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="regPassword">Mot de passe</label>
                        <input type="password" id="regPassword" placeholder="Minimum 6 caractères" required>
                    </div>
                    <button type="submit">Créer un compte</button>
                </form>
                <div id="registerResponse" class="response" style="display: none;"></div>
            </div>

            <div class="section">
                <h2>Connexion</h2>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="loginEmail">Adresse email</label>
                        <input type="email" id="loginEmail" placeholder="exemple@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Mot de passe</label>
                        <input type="password" id="loginPassword" placeholder="Votre mot de passe" required>
                    </div>
                    <button type="submit">Se connecter</button>
                </form>
                <div id="loginResponse" class="response" style="display: none;"></div>
            </div>

            <div class="section">
                <h2>Documentation API</h2>
                <p><strong>Endpoints disponibles :</strong></p>
                <ul class="api-list">
                    <li><code>GET /ping</code> - Test de connexion à l'API</li>
                    <li><code>POST /register</code> - Créer un nouveau compte utilisateur</li>
                    <li><code>POST /login</code> - Authentifier un utilisateur existant</li>
                </ul>
                <button onclick="testPing()" class="btn-secondary">Tester la connexion</button>
                <div id="pingResponse" class="response" style="display: none;"></div>
            </div>
        </div>

        <div class="footer">
            <p>Architecture MVC avec Flight PHP | Dossiers: public/, app/, vendor/</p>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>