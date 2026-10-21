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

    // Mise à jour du statut d'une commande
    if (isset($_POST['modifier_statut'])) {
        $id_commande = (int)$_POST['id_commande'];
        $nouveau_statut = htmlspecialchars($_POST['statut']);

        $stmt = $pdo->prepare("UPDATE commande SET statut = ? WHERE id = ?");
        $stmt->execute([$nouveau_statut, $id_commande]);
        $message = "Le statut de la commande #$id_commande a été mis à jour.";
    }

    // Récupération de toutes les commandes avec les infos du client
    $sql = "SELECT c.id AS id_commande, c.date_commande, c.total, c.statut, u.Nom, u.Prenom, u.email 
            FROM commande c 
            JOIN utilisateur u ON c.id_utilisateur = u.id 
            ORDER BY c.date_commande DESC";
    $commandes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — Commandes</title>
    <link rel="stylesheet" href="../css/commandes.css">
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
        <h2>Gestion des commandes clients</h2>

        <?php if (!empty($message)): ?>
            <p class="msg-success"><?= $message ?></p>
        <?php endif; ?>

        <div class="liste-produits">
            <?php foreach ($commandes as $cmd): ?>
                <div class="carte-commande">
                    <div class="carte-commande-header">
                        <div>
                            <strong>Commande #<?= $cmd['id_commande'] ?></strong> — 
                            <span>Client : <?= htmlspecialchars($cmd['Prenom'] . ' ' . $cmd['Nom']) ?> (<?= htmlspecialchars($cmd['email']) ?>)</span>
                        </div>
                        <div>
                            <span>Date : <?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></span>
                        </div>
                    </div>
                    <div class="carte-commande-details">
                        <strong>Articles :</strong>
                        <ul class="liste-articles">
                            <?php
                            $stmt_lignes = $pdo->prepare("
                                SELECT lc.quantite, lc.prix_unitaire, p.Nom 
                                FROM ligne_commande lc 
                                JOIN produits p ON lc.id_produit = p.id 
                                WHERE lc.id_commande = ?
                            ");
                            $stmt_lignes->execute([$cmd['id_commande']]);
                            $lignes = $stmt_lignes->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($lignes as $ligne): ?>
                                <li>
                                    <?= htmlspecialchars($ligne['Nom']) ?> — 
                                    <?= $ligne['quantite'] ?> x <?= number_format($ligne['prix_unitaire'], 2) ?> €
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <strong>Total : <?= number_format($cmd['total'], 2) ?> €</strong>
                    </div>

                    <!-- Formulaire de modification du statut -->
                    <form action="commandes.php" method="POST" class="form-statut">
                        <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                        <label for="statut-<?= $cmd['id_commande'] ?>">Statut :</label>
                        <select name="statut" id="statut-<?= $cmd['id_commande'] ?>">
                            <option value="Payée" <?= $cmd['statut'] === 'Payée' ? 'selected' : '' ?>>Payée</option>
                            <option value="En préparation" <?= $cmd['statut'] === 'En préparation' ? 'selected' : '' ?>>En préparation</option>
                            <option value="Expédiée" <?= $cmd['statut'] === 'Expédiée' ? 'selected' : '' ?>>Expédiée</option>
                            <option value="Livrée" <?= $cmd['statut'] === 'Livrée' ? 'selected' : '' ?>>Livrée</option>
                            <option value="Annulée" <?= $cmd['statut'] === 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                        </select>
                        <button type="submit" name="modifier_statut">Mettre à jour</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <?php if (empty($commandes)): ?>
                <p>Aucune commande n'a été passée pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>