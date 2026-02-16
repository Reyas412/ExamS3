<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>BNGRC</h1>
                <p>Gestion des Catastrophes</p>
            </div>
            <nav class="sidebar-nav">
                <a href="/" class="nav-link <?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon">&#9632;</span>
                    Tableau de Bord
                </a>
                <a href="/regions" class="nav-link <?= ($active ?? '') === 'regions' ? 'active' : '' ?>">
                    <span class="nav-icon">&#127758;</span>
                    Régions
                </a>
                <a href="/villes" class="nav-link <?= ($active ?? '') === 'villes' ? 'active' : '' ?>">
                    <span class="nav-icon">&#9962;</span>
                    Villes
                </a>
                <a href="/besoins" class="nav-link <?= ($active ?? '') === 'besoins' ? 'active' : '' ?>">
                    <span class="nav-icon">&#9998;</span>
                    Besoins
                </a>
                <a href="/dons" class="nav-link <?= ($active ?? '') === 'dons' ? 'active' : '' ?>">
                    <span class="nav-icon">&#10084;</span>
                    Dons
                </a>
                <a href="/dispatch" class="nav-link <?= ($active ?? '') === 'dispatch' ? 'active' : '' ?>">
                    <span class="nav-icon">&#10148;</span>
                    Dispatch
                </a>
                <a href="/achats" class="nav-link <?= ($active ?? '') === 'achats' ? 'active' : '' ?>">
                    <span class="nav-icon">&#128722;</span>
                    Achats
                </a>
            </nav>
            <div class="sidebar-footer">
                <p>&copy; 2026 BNGRC</p>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <header class="page-header">
                <h2><?= htmlspecialchars($title) ?></h2>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="page-body">
                <?= $body_content ?>
            </div>
        </main>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
