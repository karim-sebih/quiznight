<?php
session_start();

if (isset($_POST['username']) && isset($_POST['password'])) {
    header('Location: login.php');
    exit;
}

?>
<?php
include("../utils/config2.php");

class QuizManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function deleteQuiz($id) {
        if (is_numeric($id)) {
            try {
                $deleteAnswers = $this->pdo->prepare("DELETE answers FROM answers JOIN questions ON answers.question_id = questions.question_id WHERE questions.quiz_id = :id");
                $deleteAnswers->bindParam(':id', $id, PDO::PARAM_INT);
                $deleteAnswers->execute();

                $deleteQuestions = $this->pdo->prepare("DELETE FROM questions WHERE quiz_id = :id");
                $deleteQuestions->bindParam(':id', $id, PDO::PARAM_INT);
                $deleteQuestions->execute();

                $deleteQuiz = $this->pdo->prepare("DELETE FROM quizzes WHERE quiz_id = :id");
                $deleteQuiz->bindParam(':id', $id, PDO::PARAM_INT);

                if ($deleteQuiz->execute()) {
                    header('Location:dashboard.php');
                    exit;
                } else {
                    echo "<p class='error-msg'>Erreur lors de la suppression.</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error-msg'>Erreur : " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='error-msg'>ID invalide.</p>";
        }
    }

    public function addQuiz($quiz_title, $quiz_description, $questions) {
        if (empty($quiz_title) || empty($quiz_description) || empty($questions)) {
            echo '<p class="error-msg">Veuillez remplir tous les champs et ajouter au moins une question.</p>';
            return;
        }
        
        try {
            $insertQuiz = $this->pdo->prepare("INSERT INTO quizzes (title, description) VALUES (:title, :description)");
            $insertQuiz->execute([
                ':title' => $quiz_title,
                ':description' => $quiz_description
            ]);
            
            $quiz_id = $this->pdo->lastInsertId();

            foreach ($questions as $questionId => $question) {
                $insertQuestion = $this->pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (:quiz_id, :question_text)");
                $insertQuestion->execute([
                    ':quiz_id' => $quiz_id,
                    ':question_text' => $question['text']
                ]);
                
                $question_id = $this->pdo->lastInsertId();

                foreach ($question['answers'] as $index => $answer) {
                    $is_correct = ($question['correct'] == $index) ? 1 : 0;
                    $insertAnswer = $this->pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, :is_correct)");
                    $insertAnswer->execute([
                        ':question_id' => $question_id,
                        ':answer_text' => $answer['text'],
                        ':is_correct' => $is_correct
                    ]);
                }
            }
            
            echo '<p class="success-msg">Nouveau quiz ajouté avec succès.</p>';
        } catch (Exception $e) {
            echo '<p class="error-msg">Erreur : ' . $e->getMessage() . '</p>';
        }
    }
}

$quizManager = new QuizManager($pdo);

if (isset($_GET['delete'])) {
    $quizManager->deleteQuiz($_GET['delete']);
}

if (isset($_POST['save_quiz'])) {
    $quizManager->addQuiz($_POST['quiz_title'], $_POST['quiz_description'], $_POST['questions'] ?? []);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashi.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kodchasan:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700&family=Press+Start+2P&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/ca3234fc7d.js" crossorigin="anonymous"></script>
    <title>Tableau de bord Admin</title>
</head>
<body>
<header>
    <div class="navbar">
        <div class="logo">
            <a href="../index.php">
                <img src="../images/quiznight.png" alt="logo quiznight">
            </a>
        </div>
        <div class="buttons">
            <a href="deconnexion.php">Déconnexion</a>
        </div>
        <div class="burger-menu-button">
            <i class="fa-solid fa-bars"></i>
        </div>
    </div>
    <div class="burger-menu open">
        <ul class="links">
           
        </ul>
    </div>
</header>

<div class="container">
    <br>
    <br>
    <h2 class="msg-bienvenue">Bienvenue sur votre tableau de bord Admin</h2>
    <br>
    <br>
    <p class="access">Gérez ici les quiz et leurs questions.</p>
    <br>
</div>
<br>
<br>
<section class="admin-dashboard">
    <div class="container">
        <a href="#" class="btnadd">Créer un nouveau quiz</a>
    </div>

    <!-- Popup de création de quiz -->
    <div class="popup">
        <div class="popup-content">
            <img src="../images/icons8.png" alt="Fermer" class="close" role="button">
            <div class="admin-quiz-form-container">
                <form action="dashboard.php" method="post">
                    <h3>Nouveau Quiz</h3>
                    
                    <!-- Champs du quiz -->
                    <label>Titre du quiz</label>
                    <input type="text" name="quiz_title" class="box" required>
                    
                    <label>Description</label>
                    <textarea name="quiz_description" class="box" rows="3"></textarea>

                    <!-- Conteneur pour les questions -->
                    <div id="questions-container">
                        <!-- Les questions seront ajoutées dynamiquement ici -->
                    </div>

                    <!-- Boutons d'action -->
                    <button type="button" class="btn-add-question" onclick="addQuestion()">
                        Ajouter une question
                    </button>
                    
                    <input type="submit" class="btn" name="save_quiz" value="Enregistrer le quiz">
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Hide popup initially
            document.querySelector(".popup").style.display = "none";

            // Show popup when "Add Product" button is clicked
            document.querySelector(".btnadd").addEventListener("click", function (e) {
                e.preventDefault(); // Prevent link default action
                document.querySelector(".popup").style.display = "flex";
            });

            // Hide popup when close button is clicked
            document.querySelector(".close").addEventListener("click", function () {
                document.querySelector(".popup").style.display = "none";
            });
        });
    </script>

    <!-- Liste des quiz existants -->
    <div class="quiz-display">
        <br>
        <table class="quiz-display-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Questions</th>
                    
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupération des quiz depuis la base de données
                $stmt = $pdo->query("
                    SELECT q.*, 
                           COUNT(DISTINCT qu.question_id) as question_count,
                           COUNT(DISTINCT a.answer_id) as answer_count
                    FROM quizzes q
                    LEFT JOIN questions qu ON q.quiz_id = qu.quiz_id
                    LEFT JOIN answers a ON qu.question_id = a.question_id
                    GROUP BY q.quiz_id
                ");
                
                while ($quiz = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '
                    <tr>
                        <td>'.htmlspecialchars($quiz['title']).'</td>
                        <td>'.htmlspecialchars($quiz['description']).'</td>
                        <td>'.$quiz['question_count'].'</td>
                       
                        <td>
                            <a href="edit_quiz.php?edit='.$quiz['quiz_id'].'" class="btn">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="?delete='.$quiz['quiz_id'].'" class="btn" onclick="return confirm(\'Supprimer ce quiz ?\')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </td>
                    </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
// Gestion de l'interface dynamique
document.addEventListener("DOMContentLoaded", () => {
    const popup = document.querySelector(".popup");
    
    // Affichage du popup
    document.querySelector(".btnadd").addEventListener("click", (e) => {
        e.preventDefault();
        popup.style.display = "flex";
    });

    // Fermeture du popup
    document.querySelector(".close").addEventListener("click", () => {
        popup.style.display = "none";
    });
});

// Fonction pour ajouter dynamiquement une question
function addQuestion() {
    const container = document.getElementById('questions-container');
    const questionId = Date.now(); // ID unique

    const questionHTML = `
        <div class="question-group" data-id="${questionId}">
            <h4>Question #${container.children.length + 1}</h4>
            
            <label>Texte de la question</label>
            <input type="text" name="questions[${questionId}][text]" required class =question-group>
            
            <div class="answers-container">
                <label>Réponses :</label>
                ${Array(4).fill().map((_, i) => `
                    <div class="answer">
                        <input type="text" name="questions[${questionId}][answers][${i}][text]" 
                               placeholder="Réponse ${i + 1}" required>
                        <label>
                            <input type="radio" name="questions[${questionId}][correct]" value="${i}" required>
                            Correcte
                        </label>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', questionHTML);
}
</script>
</body>
</html>