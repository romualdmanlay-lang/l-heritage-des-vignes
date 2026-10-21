<?php
session_start();

$host     = '127.0.0.1';
$port     = '8889';
$dbname   = 'heritages_vignes'; 
$username = 'root';        
$password = 'root';

$message = "";
$erreur = "";
$token_valide = false;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Vérification du token dans l'URL (méthode GET)
    if (isset($_GET['token']) && !empty($_GET['token'])) {
        $token = $_GET['token'];
        // On cherche un utilisateur avec ce token ET une date d'expiration supérieure à MAINTENANT (NOW())
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE token_reset = ? AND token_expire > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if ($user) {
            $token_valide = true;
        } else {
            $erreur = "Le lien de réinitialisation est invalide ou a expiré.";
        }
    } else {
        $erreur = "Aucun jeton de sécurité fourni.";
    }
    // 2. Traitement du formulaire lors du changement de mot de passe (méthode POST)
    if ($token_valide && isset($_POST['changer_mdp'])) {
        $mdp1 = $_POST['mdp1'];
        $mdp2 = $_POST['mdp2'];

        if (!empty($mdp1) && !empty($mdp2)) {
            if ($mdp1 === $mdp2) {
                // Hachage sécurisé du nouveau mot de passe
                $mdp_hache = password_hash($mdp1, PASSWORD_BCRYPT);
                // Mise à jour du mot de passe + suppression du token pour empêcher une réutilisation
                $update = $pdo->prepare("UPDATE utilisateur SET Mot_de_passe = ?, token_reset = NULL, token_expire = NULL WHERE id = ?");
                $update->execute([$mdp_hache, $user['id']]);
                $message = "Votre mot de passe a bien été réinitialisé ! Vous pouvez maintenant vous connecter.";
                $token_valide = false; // Masque le formulaire après le succès
            } else {
                $erreur = "Les deux mots de passe ne correspondent pas.";
            }
        } else {
            $erreur = "Veuillez remplir tous les champs.";
        }
    }

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe</title>
    <link rel="stylesheet" href="../css/mot-de-passe-oublie.css">
</head>
<body>
    <div class="formulair">
    <h2>Nouveau mot de passe</h2>

    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if (!empty($erreur)): ?>
        <p style="color: red;"><?= htmlspecialchars($erreur) ?></p>
        <p><a href="mot-de-passe-oublie.php">Faire une nouvelle demande</a></p>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
        <p><a class="button-retour" href="connexion.php">Se connecter</a></p>
    <?php endif; ?>

    <!-- Le formulaire ne s'affiche QUE si le token est valide -->
    <?php if ($token_valide): ?>
        <form method="POST">
            <label for="mdp1">Nouveau mot de passe :</label>
            <input type="password" id="mdp1" name="mdp1" placeholder="••••••••" required>

            <label for="mdp2">Confirmer le mot de passe :</label>
            <input type="password" id="mdp2" name="mdp2" placeholder="••••••••" required>

            <div class="envoie-connexion">
                <button class="button-envoie" type="submit" name="changer_mdp">Valider</button>
            </div>
        </form>
    <?php endif; ?>

    </div>
</body>
</html>