<?php
session_start();
include 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = htmlspecialchars($_POST['username']);
        $password = $_POST['password'];

        // Vérifier si l'utilisateur existe
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: protected.php');
        } else {
            $message = "Mauvais identifiants";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>login</title>
    <link rel="stylesheet" href="quiz.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>

<body>
    <main>
    <p class="quiz_title">Quiz Night</p>
        <div class="formulaire1">
            <div class="container_card">
                <form action="login.php" method="post">
                    <div class="tittle">
                        <h2>Connexion</h2>
                    </div>
                    <label for="username"></label>
                    <input type="text" id="username" name="username" placeholder="Username">

                    <label for="password"></label>
                    <input type="password" id="password" name="password" placeholder="Password">
                    <form action="dashboard.php">
                    <input type="submit" value="Se connecter" class="button">
                    </form>
                    <p class="inscription"> "Vous n'avez pas de compte"
                       <br><br><br> <a href="signup.php">Inscription</a>
                    </p>

                </form>
                <?php if ($message) echo "<p>$message</p>"; ?>
            </div>
        </div>
    </main>
</body>

</html>