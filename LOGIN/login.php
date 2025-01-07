<?php
session_start();
include 'connect.php';

if (isset($_POST['signIn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashed_password = md5($password);

    $sql = "SELECT * FROM Utilisateurs WHERE email='$email' AND mot_de_passe='$hashed_password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['email'] = $row['email'];
        $_SESSION['user_id'] = $row['user_id'];
        header("Location: ../index.php");
        exit();
    } else {
        echo "Incorrect Email or Password!";
    }
}
?>
