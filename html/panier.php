<?php
session_start();

$host     = '127.0.0.1;port=8889';
$dbname   = 'heritages_vignes'; 
$username = 'root';        
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Supprimer un article
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_suppr = (int)$_GET['id'];
    unset($_SESSION['panier'][$id_suppr]);
    header('Location: panier.php');
    exit();
}

$articles_panier = [];
$total_general = 0;

if (!empty($_SESSION['panier'])) {
    $ids = array_keys($_SESSION['panier']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($produits as $produit) {
        $qte = $_SESSION['panier'][$produit['id']];
        $total_ligne = $produit['prix'] * $qte;
        $total_general += $total_ligne;

        $articles_panier[] = [
            'id' => $produit['id'],
            'nom' => $produit['Nom'],
            'image' => $produit['image'],
            'categorie' => $produit['categorie'],
            'prix' => $produit['prix'],
            'quantite' => $qte,
            'total' => $total_ligne
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Héritage des Vignes pannier</title>
    <link rel="stylesheet" href="../css/panier.css">
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
    <main class="panier-conteneur">
        <h2>Votre Panier</h2>

        <?php if (empty($articles_panier)): ?>
            <div class="panier-vide">
                <p>Votre panier est actuellement vide.</p>
            </div>
        <?php else: ?>
            <div class="liste-articles">
                <?php foreach ($articles_panier as $item): ?>
                    <div class="carte-panier">
                        <div class="image-produit">
                            <img src="../images/vins/<?= htmlspecialchars($item['categorie']) ?>/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                        </div>
                        <div class="details-produit">
                            <h3><?= htmlspecialchars($item['nom']) ?></h3>
                            <p class="prix-unitaire">Prix unitaire : <?= number_format($item['prix'], 2) ?> €</p>
                            <p class="quantite">Quantité : <?= $item['quantite'] ?></p>
                            <p class="prix-total">Sous-total : <?= number_format($item['total'], 2) ?> €</p>
                        </div>
                        <div class="action-produit">
                            <a href="panier.php?action=supprimer&id=<?= $item['id'] ?>" class="lien-supprimer">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="recapitulatif-panier">
                <p class="total-commande">Total : <strong><?= number_format($total_general, 2) ?> €</strong></p>
            </div>
        <?php endif; ?>
        <a class="button-paiment" href="traitement-paiement.php"> Procéder au paiement</a>
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