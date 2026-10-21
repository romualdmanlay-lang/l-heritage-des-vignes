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

    // Ajout d'un vin
    if (isset($_POST['ajouter_produit'])) {
        $nom         = htmlspecialchars($_POST['nom']);
        $description = htmlspecialchars($_POST['description']);
        $prix        = (float)$_POST['prix'];
        $stock       = (int)$_POST['stock'];
        $categorie   = $_POST['categorie'];

        $nom_image = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

            $filename_base = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
            $filename_clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename_base);

            $nom_image = $filename_clean . '.' . $extension;

            $dossier = __DIR__ ."/../images/vins/" . $categorie . "/";
            if (!is_dir($dossier)) {
                mkdir($dossier, 0777, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $nom_image)){
                $message =" Erreur: impossible de deplacer l'image dans le dossier".$dossier;
            }
            } else{
                $message= "Erreur lors du telechargement de l'image ( code erreur : " . $_FILES["image"]["error"].")";
            }

        if (empty($message)){
            $stmt = $pdo->prepare("INSERT INTO produits (Nom, description, prix, stock, categorie, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $prix, $stock, $categorie, $nom_image]);
            $message = "Le vin a été ajouté au catalogue avec son image.";
        }
    }

    // Modification du stock
    if (isset($_POST['modifier_stock'])) {
        $id_produit    = (int)$_POST['id_produit'];
        $nouveau_stock = (int)$_POST['stock'];

        $stmt = $pdo->prepare("UPDATE produits SET stock = ? WHERE id = ?");
        $stmt->execute([$nouveau_stock, $id_produit]);
        $message = "Stock mis à jour !";
    }

    // Suppression d'un vin
    if (isset($_GET['supprimer'])) {
        $id_suppr = (int)$_GET['supprimer'];
        $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
        $stmt->execute([$id_suppr]);
        header("Location: produits.php");
        exit();
    }

    $produits = $pdo->query("SELECT * FROM produits ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — Produits</title>
    <link rel="stylesheet" href="../css/stock.css">
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
            <h2>Gestion des vins et des stocks</h2>

            <?php if (!empty($message)): ?>
                <p class="msg-success"><?= $message ?></p>
            <?php endif; ?>

            <div class="form-carte">
                <h3>Ajouter un nouveau vin</h3>
                <form action="produits.php" method="POST" enctype="multipart/form-data">
                    <div class="champ">
                        <label for="nom">Nom du vin</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="champ">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="champ">
                        <label for="prix">Prix (€)</label>
                        <input type="number" step="0.01" id="prix" name="prix" required>
                    </div>
                    <div class="champ">
                        <label for="stock">Stock initial</label>
                        <input type="number" id="stock" name="stock" min="0" required>
                    </div>
                    <div class="champ">
                        <label for="categorie">Categorie</label>
                        <select id="categorie" name="categorie" required>
                            <option value="blanc">Blanc</option>
                            <option value="rouge">Rouge</option>
                            <option value="roses">Rosé</option>
                            <option value="champagne">Champagne</option>
                            <option value="cremants">Crémant</option>
                        </select>
                    </div>
                    <div class="champ">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" required>
                    </div>
                    <button type="submit" name="ajouter_produit" class="btn-add">Ajouter le vin</button>
                </form>
            </div>

            <h3>Liste des produits</h3>
            <div class="liste-produits">
                <?php foreach ($produits as $p): ?>
                    <div class="ligne-produit">
                        <div class="info-produit">
                            <strong>#<?= $p['id'] ?></strong>
                            <span><strong><?= htmlspecialchars($p['Nom']) ?></strong> (<?= htmlspecialchars($p['categorie']) ?>)</span>
                            <span><?= number_format($p['prix'], 2) ?> €</span>
                        </div>
                        <div class="info-produit">
                            <form action="produits.php" method="POST" class="form-stock-inline">
                                <input type="hidden" name="id_produit" value="<?= $p['id'] ?>">
                                <label>Stock :</label>
                                <input type="number" name="stock" value="<?= $p['stock'] ?>" class="input-stock">
                                <button type="submit" name="modifier_stock">MAJ</button>
                            </form>
                            <a href="produits.php?supprimer=<?= $p['id'] ?>" class="btn-del" onclick="return confirm('Supprimer ce vin ?');">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>