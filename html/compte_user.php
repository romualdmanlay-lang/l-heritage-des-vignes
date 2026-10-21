<?php
session_start();

// 1. Sécurité : Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['id_utilisateur'])) {
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

    $userId = $_SESSION['id_utilisateur'];

    // 2. Traitement de la modification du profil
    if (isset($_POST['update_profil'])) {
        $nom    = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email  = htmlspecialchars($_POST['email']);

        if (!empty($nom) && !empty($prenom) && !empty($email)) {
            $update = $pdo->prepare("UPDATE utilisateur SET Nom = ?, Prenom = ?, email = ? WHERE id = ?");
            $update->execute([$nom, $prenom, $email, $userId]);
            $message = "Vos informations ont bien été mises à jour.";
        } else {
            $message = "Veuillez remplir tous les champs.";
        }
    }

    // 3. Récupération des informations de l'utilisateur
    $requeteUser = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ?");
    $requeteUser->execute([$userId]);
    $user = $requeteUser->fetch();

    // 4. Récupération de l'historique des commandes
    $requeteCommandes = $pdo->prepare("SELECT * FROM commande WHERE id_utilisateur = ? ORDER BY date_commande DESC");
    $requeteCommandes->execute([$userId]);
    $commandes = $requeteCommandes->fetchAll();

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - L'Héritage des Vignes</title>
    <link rel="stylesheet" href="../css/compte_user.css">
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
                    <a href="#">Bonjour <?= htmlspecialchars($_SESSION['Prenom']) ?> (Admin)</a>
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
        <div class="formulair">
            <h2>Mon Espace Client</h2>

            <?php if (!empty($message)): ?>
                <p class="message"><?= $message ?></p>
            <?php endif; ?>

            <!-- Formulaire de modification des informations -->
            <h3>Mes Informations</h3>
            <form method="POST">
                <div class="ligne-formulair">
                    <label class="text-formulair" for="nom">Nom :</label>
                    <input class="input-formulair" type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['Nom'] ?? '') ?>" required>
                </div>

                <div class="ligne-formulair">
                    <label class="text-formulair" for="prenom">Prénom :</label>
                    <input class="input-formulair" type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['Prenom'] ?? '') ?>" required>
                </div>

                <div class="ligne-formulair">
                    <label class="text-formulair" for="email">Adresse Email :</label>
                    <input class="input-formulair" type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>

                <button class="button-formulair" type="submit" name="update_profil">Enregistrer les modifications</button>
            </form>

            <br><br>

            <!-- Historique des commandes sous forme de Cartes (Blocs) -->
            <h3>Mon Historique de Commandes</h3>

            <?php if (!empty($commandes)): ?>
                
                <!-- On boucle sur chaque commande pour créer un bloc -->
                <?php foreach ($commandes as $cmd): ?>
                    <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 8px;">
                        <p><strong>Commande n° :</strong> #<?= $cmd['id'] ?></p>
                        <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></p>
                        <p><strong>Total :</strong> <?= number_format($cmd['total'], 2, ',', ' ') ?> €</p>
                        <p><strong>Statut :</strong> <?= htmlspecialchars($cmd['statut']) ?></p>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p>Vous n'avez pas encore passé de commande.</p>
            <?php endif; ?>

            <br>
            <a class="button-deconnexion" href="deconnexion.php">Se déconnecter</a>
        </div>
    </main>
    <footer class="footer">
    <div class="footer-label">
        <div class="planet">
            <img class="logo-planet" src="../logos/planet.png" alt="Logo 1%">
            <p class="text-planet">Dans un souci d’écologie “L’Héritage des Vignes” collabore avec 1% for the planet</p>
        </div>
        <img class="label" src="../logos/qualité.png" alt="Logo label">
    </div>
        <div class="mention">
            <a class="mention-button" type="button" href="mention-legal.html">Mentions légales</a>
        </div>
    </footer>
</body>
</html>