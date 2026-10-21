<?php 
session_start();

if (!isset($_SESSION['id_utilisateur']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion.php");
    exit();
}

$host     = '127.0.0.1';
$port     = '8889';
$dbname   = 'heritages_vignes'; 
$username = 'root';        
$password = 'root';

$message = "";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['supprimer'])) {
        $id_suppr = (int)$_GET['supprimer'];
        
        if ($id_suppr !== (int)$_SESSION['id_utilisateur']) {
            $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
            $stmt->execute([$id_suppr]);
            $message = "Utilisateur supprimé.";
        } else {
            $message = "Vous ne pouvez pas supprimer votre propre compte.";
        }
    }

    $stmt = $pdo->query("SELECT id, Nom, Prenom, email, role FROM utilisateur ORDER BY id ASC");
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — Utilisateurs</title>
    <link rel="stylesheet" href="../css/utilisateurs.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italianno&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
        <nav class="nav">
            <ul class="nav-list">
                <li class="nav-button"><a href="index.php">Accueil</a></li>
                <li class="nav-button survol">
                    <a href="nos-vins.php">Nos vins</a>
                    <ul class="menu-survol">
                        <li class="text-liste"><a href="nos-vins.php?cat=blanc"> Blancs </a></li>
                        <li class="text-liste"><a href="nos-vins.php?cat=rouge"> Rouges </a></li>
                        <li class="text-liste"><a href="nos-vins.php?cat=roses"> Rosés </a></li>
                        <li class="text-liste"><a href="nos-vins.php?cat=champagne"> Champagnes </a></li>
                        <li class="text-liste"><a href="nos-vins.php?cat=cremants"> Crémants </a></li>
                    </ul>
                </li>
                <li class="nav-button"><a href="contact.php">Contact</a></li>
                
                <?php if (!isset($_SESSION['Prenom'])): ?>
                    <li class="nav-button"><a href="connexion.php">Connexion</a></li>
                <?php else: ?>
                    <li class="nav-button survol">
                        <a href="#">Bonjour <?= htmlspecialchars($_SESSION['Prenom']) ?></a>
                        <ul class="menu-survol">
                            <li class="text-liste">
                                <a href="compte_user.php"> Mon compte </a>
                                <a href="deconnexion.php">Déconnexion</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php else: ?>
        <nav class="nav">
            <ul class="nav-list">
                <li class="nav-button"><a href="produits.php">Produits</a></li>
                <li class="nav-button"><a href="utilisateurs.php">Utilisateurs</a></li>
                <li class="nav-button"><a href="commandes.php">Commandes clients</a></li>
                <li class="nav-button survol">
                    <a href="#">Bonjour <?= htmlspecialchars($_SESSION['Prenom']) ?></a>
                    <ul class="menu-survol">
                        <li class="text-liste">
                            <a href="deconnexion.php">Déconnexion</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

    <div class="nom-logo">
        <p class="nom"> L'Héritage des Vignes</p>
        <div class="logo-panier">
            <img class="logo" src="../logos/logo.png" alt="Logo du site">
            <a class="panier" href="panier.php"><img src="../logos/panier2.svg" alt="panier"></a>
        </div>
    </div>
</header>

    <main class="main">
        <div class="admin-box">
            <h2>Gestion des comptes utilisateurs</h2>

            <?php if (!empty($message)): ?>
                <p class="msg-error"><?= $message ?></p>
            <?php endif; ?>

            <div class="liste-utilisateurs">
                <?php foreach ($utilisateurs as $u): ?>
                    <div class="carte-user">
                        <div class="details-user">
                            <strong>#<?= $u['id'] ?> — <?= htmlspecialchars(($u['Prenom'] ?? '') . ' ' . ($u['Nom'] ?? '')) ?></strong>
                            <span>Email : <?= htmlspecialchars($u['email']) ?></span>
                            <span>Rôle : <strong><?= htmlspecialchars($u['role']) ?></strong></span>
                        </div>
                        <div>
                            <?php if ($u['id'] != $_SESSION['id_utilisateur']): ?>
                                <a href="utilisateurs.php?supprimer=<?= $u['id'] ?>" class="btn-del" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                            <?php else: ?>
                                <em>(Votre compte)</em>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>