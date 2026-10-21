<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: connexion.php");
    exit();
}

if (empty($_SESSION['panier'])) {
    header("Location: panier.php");
    exit();
}

$host     = '127.0.0.1';
$port     = '8889';$dbname   = 'heritages_vignes'; 
$username = 'root';$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId =$_SESSION['id_utilisateur'];

    $ids = array_keys($_SESSION['panier']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt =$pdo->prepare("SELECT * FROM produits WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $produits =$stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalGeneral = 0;
    foreach ($produits as $produit) {
        $qte = $_SESSION['panier'][$produit['id']];
        $totalGeneral += $produit['prix'] * $qte;
    }

    $insertCommande = $pdo->prepare("INSERT INTO commande (id_utilisateur, date_commande, total, statut) VALUES (?, NOW(), ?, 'Payée')");
    $insertCommande->execute([$userId, $totalGeneral]);

    $idCommande = $pdo->lastInsertId();

    $insertLigne = $pdo->prepare("INSERT INTO ligne_commande (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");

    foreach ($produits as $produit) {
        $qte = $_SESSION['panier'][$produit['id']];
        $insertLigne->execute([$idCommande, $produit['id'], $qte, $produit['prix']]);
    }

    unset($_SESSION['panier']);

    header("Location: confirmation-paiement.php?id=" . $idCommande);
    exit();

} catch (PDOException $e) {
    die("Erreur lors du traitement de la commande : " . $e->getMessage());
}