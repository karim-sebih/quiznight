<?php
include '../utils/config.php';

$message = '';

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['confirm_password'])) {
        $username = test_input($_POST['username']);
        $email = test_input($_POST['email']);
        $password = test_input($_POST['password']);
        $confirm_password = test_input($_POST['confirm_password']);
        // echo "username : $username<br/>email : $email<br/>password : $password<br/>c_pass : $confirm_password";
        // exit;
        // Validate username
        if (empty($username)) {
            $message .= "Le nom d'utilisateur est requis.<br>";
        } elseif (!preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
            $message .= "Seuls les lettres, les chiffres et les traits de soulignement sont autorisés pour le nom d'utilisateur.<br>";
        } elseif (strlen($username) > 10) {
            $message .= "Le nom d'utilisateur doit contenir au maximum 10 caractères.<br>";
        }

        // Validate email
        if (empty($email)) {
            $message .= "L'email est requis.<br>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message .= "Format d'identifiant email non valide.<br>";
        }

        // Validate password
        if (empty($password)) {
            $message .= "Le mot de passe est requis.<br>";
        } elseif (strlen($password) < 8) {
            $message .= "Le mot de passe doit contenir au moins 8 caractères.<br>";
        } elseif (!preg_match("/[A-Z]/", $password)) {
            $message .= "Le mot de passe doit contenir au moins une lettre majuscule.<br>";
        } elseif (!preg_match("/[a-z]/", $password)) {
            $message .= "Le mot de passe doit contenir au moins une lettre minuscule.<br>";
        } elseif (!preg_match("/[0-9]/", $password)) {
            $message .= "Le mot de passe doit contenir au moins un chiffre.<br>";
        } elseif (!preg_match("/[^\w]/", $password)) {
            $message .= "Le mot de passe doit contenir au moins un caractère spécial.<br>";
        }

        // Confirm password match
        if ($password !== $confirm_password) {
            $message .= "Les mots de passe ne correspondent pas.<br>";
        }

        if (empty($message)) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            // Vérifier si l'utilisateur existe déjà
            $sql = "SELECT * FROM users WHERE username = :username OR email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username, 'email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $message = "L'utilisateur ou l'email existe déjà.";
            } else {
                // Insérer le nouvel utilisateur dans la base de données
                $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password_hashed]);
                $message = "Inscription réussie !";
                header("Location: login.php"); // Rediriger après inscription réussie
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>signup</title>
    <link rel="stylesheet" href="../css/quiz.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>

<body>
    <main>
        <p class="quiz_title">Quiz Night</p>
        <div class="formulaire2">
            <div class="container_card2">
                <form action="signup.php" method="post">
                    <div class="tittle">
                        <h2>Inscription</h2>
                    </div>
                    <div class="fill">
                        <label for="username"></label>
                        <input type="text" id="username" name="username" placeholder="Username" required> <br>

                        <label for="email"></label>
                        <input type="email" id="email" name="email" placeholder="Email" required> <br>

                        <label for="password"></label>
                        <input type="password" id="password" name="password" placeholder="Password" required> <br>

                        <label for="confirm_password"></label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                        <span id='message'></span>
                    </div>

                    <input type="submit" value="S'inscrire" class="button2">

                    <p class="inscription"> "Avez vous déjà un compte ?"
                        <br><br><br> <a href="login.php">Connexion</a>
                    </p>
                </form>

            </div>

        </div>
        <div class="messageErr">
            <?php if ($message) echo "<p>$message</p>"; ?>
        </div>
    </main>
</body>

</html>
