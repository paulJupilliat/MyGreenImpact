<?php
session_start();
include 'connect.php';

if (isset($_POST['signIn'])) {
    // On récupère les champs du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // On hashe le mot de passe (même méthode que dans votre code initial)
    $hashed_password = md5($password);

    try {
        // On prépare une requête paramétrée pour éviter les injections SQL
        $stmt = $pdo->prepare("SELECT * FROM Utilisateurs 
                               WHERE email = :email 
                               AND mot_de_passe = :hashed_password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hashed_password', $hashed_password);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_NAMED);

        if ($row) {
            // Si on a trouvé un utilisateur, on ouvre la session
            $_SESSION['email']   = $row['email'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['entreprise_id'] = $row['entreprise_id'];
            echo ($row['entreprise_id']);

            header("Location: ../index.php");
            exit();
        } else {
            // Aucun résultat => identifiants incorrects
            echo "Incorrect Email or Password!";
        }
    } catch (PDOException $e) {
        echo "Erreur lors de la connexion : " . $e->getMessage();
    }
}
?>

