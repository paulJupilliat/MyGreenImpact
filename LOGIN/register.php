<?php
include 'connect.php';

if (isset($_POST['signUp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashed_password = md5($password);

    // Vérifier si l'email existe déjà
    $checkEmail = "SELECT * FROM Utilisateurs WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        echo "Email Address Already Exists!";
    } else {
        // Insérer l'utilisateur dans la base de données
        $insertQuery = "INSERT INTO Utilisateurs (email, mot_de_passe, date_inscription, role) 
                        VALUES ('$email', '$hashed_password', NOW(), 'utilisateur')";

        if ($conn->query($insertQuery) === TRUE) {
            echo "Registration successful! You can now log in.";
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>
