<?php
session_start();

$host     = '127.0.0.1';
$port     = '8889';
$dbname   = 'heritages_vignes'; 
$username = 'root';        
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if(isset($_GET["chercher"]) && !empty($_GET["chercher"])) {
        $recherche = htmlspecialchars($_GET ["chercher"]);
        $requete =$pdo->prepare("SELECT * FROM produits WHERE categorie LIKE :query");
        $requete->execute(['query' => '%' . $recherche . '%']);
    
    }elseif(isset($_GET['cat']) && !empty($_GET['cat'])) {
        $categorie = $_GET['cat'];
        $requete = $pdo->prepare("SELECT * FROM produits WHERE categorie = :cat");
        $requete->execute(['cat' => $categorie]);

    } else {
        $requete = $pdo->query("SELECT * FROM produits");
    }
    $vins =$requete->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Héritage des Vignes nos vins</title>
    <link rel="stylesheet" href="../css/nos vins.css">
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
<div class="stock">
    <div class="categorie">Nos vins</div>
    <form class="recherche" method="GET">
        <input class="button-recherche" type="search" name="chercher" placeholder="ex: champagne">
        <input class="button-envoyer" type="submit" name="envoyer" value="rechercher">
    </form>
    <div class="blanc">
        <?php foreach ($vins as $vin): ?>
            <div class="article-carte">
                <img src="../images/vins/<?= htmlspecialchars($vin['categorie']) ?>/<?= htmlspecialchars($vin['image']) ?>" alt="<?= htmlspecialchars($vin['Nom']) ?>">
                <p class="detail-vin">
                    <strong><?= htmlspecialchars($vin['Nom']) ?></strong><br>
                    <?= htmlspecialchars($vin['description']) ?><br>
                    <span><?= number_format($vin['prix'], 2) ?> €</span>
                </p>
                <?php if ($vin['stock'] > 0): ?>
                    <form action="ajouter-panier.php" method="post" class="ajout-rapide">
                        <input type="hidden" name="vin_id" value="<?= $vin['id'] ?>">
                        <input  type="number" 
                                name="quantite"
                                class="qte-input" 
                                value="1" 
                                min="1" 
                                max="<?= $vin['stock'] ?>">
                        <button type="submit" 
                                class="button-ajouter" 
                                data-id="<?= $vin['id'] ?>">
                            Ajouter au panier
                        </button>
                    </form>
                <?php else: ?>
                    <p class="badge-rupture" >Rupture de stock</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
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