<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vin_id'])) {
    $id       = (int)$_POST['vin_id'];
    $quantite = (int)$_POST['quantite'];

    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id] += $quantite;
    } else {
        $_SESSION['panier'][$id] = $quantite;
    }
}


header('Location: nos-vins.php');
exit();