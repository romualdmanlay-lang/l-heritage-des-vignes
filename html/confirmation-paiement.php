<?php
session_start();

$idCommande = $_GET['id'] ?? null;

if (!$idCommande) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de commande</title>
    <link rel="stylesheet" href="../css/panier.css">
</head>
<body>
    <main style="text-align: center; padding: 50px;">
        <h2>Merci pour votre commande !</h2>
        <p>Votre commande <strong>n°<?= htmlspecialchars($idCommande) ?></strong> a bien été validée et enregistrée.</p>
        <a href="nos-vins.php" style="display:inline-block; margin-top: 20px; padding: 10px 20px; background:#800020; color:#fff; text-decoration:none; border-radius:5px;">Retour à la boutique</a>
    </main>
</body>
</html>