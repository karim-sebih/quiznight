<?php
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>index</title>
    <link rel="stylesheet" href="./css/quiz.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>

<body>
    <main>
        <p class="quiz_title">Quiz Night</p>
        <div class="quiz_logo">
            <img src="../quiznight/images/quiznightlogo.png" alt="quiz logo">
        </div>
        <div class="quiz_card">
            <div class="admin_card">
                <form action="./pages/login.php">
                    <input type="submit" value="Admin" class="btn" style="height:50px; width:100px" >
                </form>
                <form action="quizz.php">
                    <input type="submit" value="Player" class="btn" style="height:50px; width:100px">
                </form>
            </div>
        </div>
        <div id="footer">
            &copy; All rights reserved Karim, Jorge, Rais
        </div>
    </main>

</body>

</html>