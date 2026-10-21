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

if (isset($_POST['envoie'])) {
  if (!empty($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);

    // 1. On cherche l'utilisateur dans la table
            $checkUser = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
            $checkUser->execute([$email]);
            $user = $checkUser->fetch();

            if ($user) {
                // 2. On génère une clé secrète et une date d'expiration (+30 minutes)
                $token = bin2hex(random_bytes(32));
                $expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));

                // 3. On enregistre la clé dans la BDD
                $update = $pdo->prepare("UPDATE utilisateur SET token_reset = ?, token_expire = ? WHERE email = ?");
                $update->execute([$token, $expire, $email]);

                // 4. On prépare le lien avec ton port 3000
                $lien = "http://localhost:3000/html/reset-password.php?token=" . $token;

                // 5. On envoie le mail
                $to = $email;
                $subject = "Reinitialisation de votre mot de passe - L'Heritage des Vignes";
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: L'Héritage des Vignes <no-reply@heritagedesvignes.fr>\r\n";

                $body = "
                <h2>Bonjour " . htmlspecialchars($user['Prenom']) . ",</h2>
                <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                <p>Cliquez sur le lien ci-dessous pour créer un nouveau mot de passe (valable 30 minutes) :</p>
                <p><a href='" . $lien . "'>" . $lien . "</a></p>
                <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.</p>
                ";

                @mail($to, $subject, $body, $headers);

                $message = "Un e-mail de réinitialisation vous a été envoyé.";
}else {
    $message = "Un e-mail de réinitialisation vous a été envoyé.";
            }
        }
    }
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mot de passe oublié</title>
  <link rel="stylesheet" href="../css/mot-de-passe-oublie.css">
</head>
<body>
  <div class="formulair">
    <h2> Mot de passe oublié </h2>
    <?php if (!empty($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
      <form method="POST">
        <label for="email"> adresse email:</label>
        <input type="email" id="email" name="email" placeholder="ex: jean.dupont@mail.fr" required>
          <div class="envoie-connexion">
            <button class="button-envoie" type="submit" name="envoie"> Envoyer </button><br>
            <a class ="button-retour" href="connexion.php">Retour</a>
          </div>
      </form>
  </div>
</body>
</html>