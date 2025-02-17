<?php
session_start();
if (isset($_POST['username']) && isset($_POST['password'])){
    header('Location:login.php');
    exit();



}
?>

<?php
include("../utils/config2.php");

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = $_GET['edit'];
} else {
    echo 'ID de la question non spécifié ou invalide.';
    exit;
}

if (isset($_POST['update_quiz'])) {
    $quiz_title = $_POST['quiz_title'];
    $quiz_description = $_POST['quiz_description'];
    $questions = $_POST['questions'] ?? [];

    if (empty($quiz_title) || empty($quiz_description) || empty($questions)) {
        $message[] = 'Veuillez remplir tous les champs et ajouter au moins une question.';
    } else {
        try {
            $pdo->beginTransaction();

            // Mise à jour du titre et de la description du quiz
            $updateQuiz = $pdo->prepare("UPDATE quizzes SET title = :title, description = :description WHERE quiz_id = :id");
            $updateQuiz->execute([
                ':title' => $quiz_title,
                ':description' => $quiz_description,
                ':id' => $id
            ]);

            // Traiter chaque question
            foreach ($questions as $questionId => $question) {
                if (is_numeric($questionId)) {
                    // Mise à jour d'une question existante
                    $updateQuestion = $pdo->prepare("UPDATE questions SET question_text = :question_text WHERE question_id = :id AND quiz_id = :quiz_id");
                    $updateQuestion->execute([
                        ':question_text' => $question['text'],
                        ':id' => $questionId,
                        ':quiz_id' => $id
                    ]);
                } else {
                    // Insertion d'une nouvelle question
                    $insertQuestion = $pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (:quiz_id, :question_text)");
                    $insertQuestion->execute([
                        ':quiz_id' => $id,
                        ':question_text' => $question['text']
                    ]);
                    // Récupération de l'ID auto-généré pour cette question
                    $newQuestionId = $pdo->lastInsertId();
                    // On utilise cet ID pour insérer les réponses associées
                    $questionId = $newQuestionId;
                }

                // Traitement des réponses pour chaque question
                foreach ($question['answers'] as $answerIndex => $answer) {
                    $is_correct = ($question['correct'] == $answerIndex) ? 1 : 0;

                    if (isset($answer['id'])) {
                        // Mise à jour d'une réponse existante
                        $updateAnswer = $pdo->prepare("UPDATE answers SET answer_text = :answer_text, is_correct = :is_correct WHERE answer_id = :id AND question_id = :question_id");
                        $updateAnswer->execute([
                            ':answer_text' => $answer['text'],
                            ':is_correct' => $is_correct,
                            ':id' => $answer['id'],
                            ':question_id' => $questionId
                        ]);
                    } else {
                        // Insertion d'une nouvelle réponse
                        $insertAnswer = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, :is_correct)");
                        $insertAnswer->execute([
                            ':question_id' => $questionId,
                            ':answer_text' => $answer['text'],
                            ':is_correct' => $is_correct
                        ]);
                    }
                }
            }

            $pdo->commit();
            $message[] = 'Quiz mis à jour avec succès !';
            header('Location: dashboard.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $message[] = 'Erreur lors de la mise à jour du quiz: ' . $e->getMessage();
        }
    }
}

// Juste après avoir récupéré $id = $_GET['edit'] :
if (isset($_GET['delete_question']) && is_numeric($_GET['delete_question'])) {
    $questionId = intval($_GET['delete_question']);

    // Supprimer les réponses liées
    $stmt = $pdo->prepare("DELETE FROM answers WHERE question_id = :questionId");
    $stmt->execute(['questionId' => $questionId]);

    // Supprimer la question
    $stmt = $pdo->prepare("DELETE FROM questions WHERE question_id = :questionId");
    if ($stmt->execute(['questionId' => $questionId])) {
        // IMPORTANT : on redirige en conservant l'ID du quiz
        header("Location: edit_quiz.php?edit=$id");
        exit;
    } else {
        echo "Erreur lors de la suppression de la question.";
    }
}



?>

<?php
// Initialisation du tableau des questions
$questionsData = [];

// Récupération des questions et leurs réponses associées
$questionsQuery = $pdo->prepare("
    SELECT q.question_id, q.question_text, 
           a.answer_id, a.answer_text, a.is_correct 
    FROM questions q
    LEFT JOIN answers a ON q.question_id = a.question_id
    WHERE q.quiz_id = :quiz_id
");
$questionsQuery->bindParam(':quiz_id', $id, PDO::PARAM_INT);
$questionsQuery->execute();

while ($question = $questionsQuery->fetch(PDO::FETCH_ASSOC)) {
    $questionId = $question['question_id'];
    
    if (!isset($questionsData[$questionId])) {
        $questionsData[$questionId] = [
            'text' => $question['question_text'],
            'answers' => []
        ];
    }

    if ($question['answer_id']) {
        $questionsData[$questionId]['answers'][] = [
            'id' => $question['answer_id'],
            'text' => $question['answer_text'],
            'is_correct' => $question['is_correct']
        ];
    }
}
?>
<script>
function removeQuestion(questionId) {
    if (confirm("Supprimer cette question ?")) {
        document.querySelector(`[data-id='${questionId}']`).remove();
    }
}
</script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashi.css">
    <title>Mettre à jour un quiz</title>
</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<span class="message">' . $msg . '</span>';
    }
}
?>

<div class="container">
    <div class="admin-quiz-form-container centered">
        
        <?php
        $select = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = :id");
        $select->bindParam(':id', $id, PDO::PARAM_INT);
        $select->execute();
        
        $row = $select->fetch(PDO::FETCH_ASSOC);

        if ($row) {
        ?>

<form action="" method="post">
    <input type="hidden" name="quiz_id" value="<?php echo $id; ?>">

    <h3 class="title">Mettre à jour le quiz</h3>
    
    <label for="quiz_title">Titre du quiz</label>
    <input type="text" id="quiz_title" name="quiz_title" class="box" 
           value="<?php echo htmlspecialchars($row['title']); ?>" required>

    <label for="quiz_description">Description du quiz</label>
<textarea id="quiz_description" 
          name="quiz_description" 
          class="box" 
          rows="3" 
          required><?php echo htmlspecialchars($row['description']); ?></textarea>

    <!-- Questions et réponses -->
   <div id="questions-container">
    <?php
    foreach ($questionsData as $questionId => $questionData) {
        echo '<div class="question-group" data-id="' . $questionId . '">';
        echo '<label>Question </label><br>';
        echo '<input type="text" name="questions[' . $questionId . '][text]" 
               value="' . htmlspecialchars($questionData['text']) . '" required>';

        echo '<div class="answers-container">';
        foreach ($questionData['answers'] as $answerIndex => $answer) {
            echo '<div class="answer">';
            echo '<input type="hidden" name="questions[' . $questionId . '][answers][' . $answerIndex . '][id]" 
                         value="' . $answer['id'] . '">';
            echo '<input type="text" name="questions[' . $questionId . '][answers][' . $answerIndex . '][text]" 
                   value="' . htmlspecialchars($answer['text']) . '" required>';
            echo '<label>';
            echo '<input type="radio" name="questions[' . $questionId . '][correct]" 
                   value="' . $answerIndex . '" ' . ($answer['is_correct'] == 1 ? 'checked' : '') . '>';
            echo ' Correcte';
            echo '</label>';
            echo '</div>';
        }
        echo '</div>';

        // Bouton pour supprimer cette question spécifique
       // On suppose que $id est l'ID du quiz, et $questionId est l'ID de la question
echo '<a href="edit_quiz.php?edit=' . $id . '&delete_question=' . $questionId . '" 
class="btn" 
onclick="return confirm(\'Supprimer cette question ?\');">
<i class="fas fa-trash"></i> Supprimer
</a>';
;

        echo '</div>';
    }
    ?>
</div>




    <button type="button" class="btn-add-question">Ajouter une question</button>
    <input type="submit" value="Mettre à jour" name="update_quiz" class="btn-add-question">
    <a href="dashboard.php" class="btn-add-question">Retour</a>
</form>

        <?php } else { echo '<span class="message">Quiz non trouvé.</span>'; } ?>

    </div>
</div>

<script>
// Ajout dynamique de nouvelles questions avec un préfixe "new_"
document.querySelector('.btn-add-question').addEventListener('click', function() {
    const container = document.getElementById('questions-container');
    const questionId = 'new_' + Date.now();

    const questionHTML = `
        <div class="question-group" data-id="${questionId}">
            <label>Question</label>
            <input type="text" name="questions[${questionId}][text]" required>
            <button type="button" class="btn-delete-question">🗑 Supprimer</button>
            
            <div class="answers-container">
                <label>Réponses :</label>
                ${Array(4).fill().map((_, i) => `
                    <div class="answer">
                        <input type="text" name="questions[${questionId}][answers][${i}][text]" placeholder="Réponse ${i + 1}" required>
                        <label>
                            <input type="radio" name="questions[${questionId}][correct]" value="${i}" required> Correcte
                        </label>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', questionHTML);
});

// Suppression dynamique des questions
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('btn-delete-question')) {
        event.preventDefault();
        const questionElement = event.target.closest('.question-group');
        const questionId = questionElement.getAttribute('data-id');

        if (confirm('Voulez-vous vraiment supprimer cette question ?')) {
            fetch('delete_question.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'question_id=' + questionId
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    questionElement.remove();
                } else {
                    alert('Erreur lors de la suppression.');
                }
            });
        }
    }
});
</script>

</body>
</html>
