<?php
session_start();

$host     = '127.0.0.1';
$port     ='8889';
$dbname   = 'heritages_vignes'; 
$username = 'root';        
$password = 'root';

$message = "";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if(isset($_POST['envoi'])){
        if(!empty($_POST["email"]) && !empty($_POST["password"])){
            $email = htmlspecialchars($_POST["email"]);
            $password = $_POST["password"];

            $recuperUser = $pdo->prepare ("SELECT * FROM utilisateur WHERE email = ?");
            $recuperUser->execute([$email]);
            $user = $recuperUser->fetch();

            if($user && password_verify($password, $user["Mot_de_passe"])){
                $_SESSION["Prenom"]=$user ["Prenom"];
                $_SESSION["id_utilisateur"] = $user["id"];
                $_SESSION["role"] =$user ["role"];

                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){
                header("Location: produits.php");
                exit();
                }
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'){
                header("Location: index.php");
                exit();
                }
            }else{
                $message = " votre mots de passe ou adresse email est incorrect";
            }
        }
    }
}
catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Héritage des Vignes connexion</title>
    <link rel="stylesheet" href="../css/connexion.css">
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
        <div class=" formulair">
            <form action="" method="POST">
                <div class="ligne-formulair">
                    <label class="text-formulair" for="email" > Adresse Email</label>
                    <input class="input-formulair" type="email" id="email" name="email" placeholder="ex: jean.dupont@mail.fr" required>
                </div>
                <div class="ligne-formulair">
                    <label class="text-formulair" for="password"> Mot de passe</label>
                    <input class="input-formulair" type="password" id="password" name="password" placeholder="Tapez votre mot de passe" required>
                </div>
                <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message ?></p>
            <?php endif; ?>
                <div class="connexion-inscription">
                    <button class= "button-formulair" type="submit" name="envoi">Se connecter</button>
                    <a class= "button-inscription" href="inscription.php"> S'inscrire </a>
                </div><br>
                <a class="button-mdp" href="mot-de-passe-oublie.php"> Mots de passe oublié?</a>
            </form>
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