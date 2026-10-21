<?php
session_start();?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Héritage des Vignes acceuil</title>
    <link rel="stylesheet" href="../css/accueil.css">
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
    <div class="carousel">
        <div class="photocarousel">
            <img src="../images/carousel1.jpg" alt="Photo 1">
            <img src="../images/carousel2.jpg" alt="Photo 2">
            <img src="../images/carousel3.jpg" alt="Photo 3">
            <img src="../images/carousel4.jpg" alt="Photo 4">
            <img src="../images/carousel5.jpg" alt="Photo 5">
        </div>
    </div>
    <div class="presentation">
        <p> “L'Héritage des Vignes” est née d'une ambition claire : célébrer le patrimoine viticole et remettre le travail des vignerons au cœur de l'expérience. Nous sélectionnons des cuvées de caractère, issues de terroirs d'exception et de domaines engagés dans une viticulture durable et respectueuse du vivant.
Passioné de vin depuis mon enfance, cette passion m’a été transmise par mon grand-père. Il a su me faire découvrir les nombreuses subtilités de chaque cuvée.
Chaque vin présent dans notre catalogue est goûté, validé et accompagné de conseils précis pour sublimer vos dégustations, qu'il s'agisse d'un repas de fête, d'une bouteille à garder ou d'un moment de partage improvisé.
Plus qu'un catalogue, ”L'Héritage des Vignes” est un pont entre le vigneron et le passionné, pensé pour vous transmettre l'histoire, la vision et le savoir-faire qui se cachent derrière chaque étiquette.</p>
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
    <script src="../script/script.js"></script>
</body>
</html>