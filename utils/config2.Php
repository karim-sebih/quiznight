<?php
$host = 'localhost';  
$db = 'quiznight';  
$user = 'root';       
$pass = '';       
try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // Définition du mode d'erreur (affichage des erreurs SQL)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //echo "Connexion réussie à la base de données."; // (Décommente pour tester)

} catch (PDOException $e) {
    // En cas d'échec de la connexion
    die("Connection failed: " . $e->getMessage());
}
?>
