<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=quiznight;charset=utf8","root","");

$quizzes_query = "SELECT quiz_id, title, description FROM Quizzes";
$quizzes_stmt = $pdo->query($quizzes_query);
$quizzes = $quizzes_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Quiz</title>
    <style>
        * {
            font-family: "Press Start 2P", serif;
            font-weight: 400;
            font-style: normal;
            color: #ffdb4f;
            background-color: #512DA8;
        }

        /* CSS pour styliser la liste des quiz */
        .quiz-list {
            list-style-type: none;
            padding: 0;
        }

        .quiz-item {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color:rgb(178, 131, 248);
        }

        .quiz-item h2 {
            margin-top: 0;
            background-color:rgb(178, 131, 248);
        }

        .quiz-item p {
            margin: 5px 0 0;
            color: #AA5;
            background-color:rgb(178, 131, 248);
        }

        .quiz-item a {
            display: inline-block;
            margin-top: 10px;
            color: #007BFF;
            text-decoration: none;
            background-color: #ffdb4f;
        }

        .quiz-item a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Liste des Quiz</h1>
    <ul class="quiz-list">
        <?php foreach ($quizzes as $quiz): ?>
            <li class="quiz-item">
                <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                <a href="player.php?quiz_id=<?php echo $quiz['quiz_id']; ?>">Jouer</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>