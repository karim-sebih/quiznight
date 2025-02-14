<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
}
$pdo = new PDO("mysql:host=localhost;dbname=quiznight;charset=utf8","root","");

$user_answers = $_POST['answers'] ?? [];
$score = 0;
$nbAnswers = $_POST['nbAnswers'];

foreach ($user_answers as $question_id => $selected_answer_id) {
    $check_query = "SELECT is_correct FROM Answers WHERE answer_id = :answer_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute(['answer_id' => $selected_answer_id]);
    $result = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['is_correct'] == 1) {
        $score++;
    }
}

echo "<h1>Votre score : $score / $nbAnswers </h1>";
?>
<style>
        * {
            font-family: "Press Start 2P", serif;
            font-weight: 400;
            font-style: normal;
            color: #ffdb4f;
            background-color: #512DA8;
        }

        .answer-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, auto);
            gap: 10px;
            width: 100%;
        }

        .answer-button {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 50px; /* Hauteur fixe pour les boutons */
            width: 250px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .answer-button:hover {
            background-color: #0056b3;
        }

        .answer-button input {
            display: none; /* Masquer le bouton radio */
        }
/* 
        .quiz-container {
            background-color: #fff;
            color: #000;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
        }

        .question-title {
            background-color: #000;
            color: #fff;
            padding: 10px;
            border-radius: 5px 5px 0 0;
            font-size: 1.2em;
        }

        .question-text {
            background-color: #000;
            color: #fff;
            padding: 20px;
            border-radius: 0 0 5px 5px;
            font-size: 1em;
            white-space: pre-wrap; // Pour conserver les espaces et les retours à la ligne
        }  */
    </style>
    <a href="quizz.php">Jouer à un autre quiz</a>