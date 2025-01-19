<?php
global $pdo;
session_start();
include 'connect.php';

if (isset($_POST['signUp'])) {
    // Récupérer les valeurs
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $hashed_password = md5($password);

    try {
        // Vérifier si l'email existe déjà
        $stmtCheck = $pdo->prepare("SELECT * FROM Utilisateurs WHERE email = :email");
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            echo "Email Address Already Exists!";
        } else {
            // Insérer l'utilisateur
            $stmtInsert = $pdo->prepare(
                "INSERT INTO Utilisateurs (email, mot_de_passe, date_inscription, role) 
                 VALUES (:email, :hashed_password, NOW(), 'utilisateur')"
            );
            $stmtInsert->bindParam(':email', $email);
            $stmtInsert->bindParam(':hashed_password', $hashed_password);

            if ($stmtInsert->execute()) {
                // Récupérer l'ID du nouvel utilisateur
                $userId = $pdo->lastInsertId();

                // Enregistrer l'ID utilisateur dans la session
                $_SESSION['user_id'] = $userId;
                $_SESSION['email'] = $email;

                // Redirection
                header("Location: choose_entreprise.php");
                exit();
            } else {
                // Gérer l'erreur
                $errorInfo = $stmtInsert->errorInfo();
                echo "Error: " . $errorInfo[2];
            }
        }
    } catch (PDOException $e) {
        echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
?>
