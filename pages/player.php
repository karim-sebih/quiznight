<?php

$pdo = new PDO("mysql:host=localhost;dbname=quiznight;charset=utf8","root","");

// Récupérer l'ID du quiz à afficher (par exemple, via un paramètre GET)
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Requête pour récupérer les informations du quiz
$quiz_query = "SELECT title, description FROM Quizzes WHERE quiz_id = :quiz_id";
$quiz_stmt = $pdo->prepare($quiz_query);
$quiz_stmt->execute(['quiz_id' => $quiz_id]);
$quiz = $quiz_stmt->fetch();
$title = $quiz['title'];
$description = $quiz['description'];

// Requête pour récupérer les questions et les réponses associées
$questions_query = "SELECT question_id, question_text FROM Questions WHERE quiz_id = :quiz_id";
$questions_stmt = $pdo->prepare($questions_query);
$questions_stmt->execute(['quiz_id'=>$quiz_id]);
$questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);

$n =0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title); ?></title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
<body>
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

        /* input:checked + label {
            background-color: #ffdb4f;
            color:#512DA8
        } */

        .answer-button input:checked {
            background-color: #ffdb4f;
            color:#512DA8
        }

        .answer-button input {
            display: none; 
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
    <!-- <h1><?= htmlspecialchars($title); ?></h1>
    <p><?= htmlspecialchars($description); ?></p> -->

    <form class="quiz-container" action="result.php" method="post">
        <?php foreach ($questions as $question): $n++;?>
            <div>
                <p class="question-title">Question <?=$n?></p>
                <p class="question-text"><?= htmlspecialchars($question['question_text']); ?></p>
            </div>
            <div class="answer-grid">
                <?php
                // Requête pour récupérer les réponses de la question
                $answers_query = "SELECT answer_id, answer_text FROM Answers WHERE question_id = :question_id";
                $answers_stmt = $pdo->prepare($answers_query);
                $answers_stmt->execute(['question_id' => $question['question_id']]);
                $answers = $answers_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php foreach ($answers as $answer): ?>
                    <label class="answer-button" >
                        <input type="radio" name="answers[<?= $question['question_id']; ?>]" value="<?= $answer['answer_id']; ?>">
                        <!-- <span class="option"> --><?= htmlspecialchars($answer['answer_text']); ?><!--</span> -->
                    </label><br>
                <?php endforeach; ?>
                <p><br></p>
            </div>
        <?php endforeach; ?>
        <input type="hidden" name="nbAnswers" value="<?php echo count($questions);?>">
        <button type="submit">Soumettre</button>
        <!-- <script>
        function Selected() {
            const options = document.querySelectorAll('.answer-button');
            options.forEach(option => {
                const radio = option.previousElementSibling;
                if (radio.checked) {
                    option.classList.add('selected');
                } else {
                    option.classList.remove('selected');
                }
            });
        }

    </script> -->
    </form>
    
</body>
</html>