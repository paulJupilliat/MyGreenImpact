<?php
global $pdo;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'LOGIN/connect.php';
include 'LOGIN/get_name_entreprise.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
</head>
<body>
<div style="text-align:center; padding:15%;">
    <p style="font-size:50px; font-weight:bold;">
        <?php
        if (isset($_SESSION['email'])) {
            // Si l'utilisateur est connecté, afficher son email ou d'autres informations
            $email = $_SESSION['email'];
            $user_id = $_SESSION['user_id'];
            $entreprise_id = $_SESSION['entreprise_id'];
            echo "Hello, " . htmlspecialchars($email) .
                " : avec ton id ". htmlspecialchars($user_id);
        } else {
            // Si l'utilisateur n'est pas connecté, afficher un message générique
            echo "Welcome, Guest!";
        }
        ?>
    </p>

    <?php if (isset($_SESSION['email'])): ?>
        <!-- Bouton Logout si connecté -->
        <a href="LOGIN/logout.php" style="font-size:20px;">Logout</a>
        <a href="LOGIN/delete_account.php" style="font-size:20px;"> Delete account </a>
    <?php else: ?>
        <!-- Bouton Login si non connecté -->
        <a href="LOGIN/index.php" style="font-size:20px;">Login</a>
    <?php endif; ?>

    <?php
    if (isset($_SESSION['entreprise_id'])){
        //affichage du nom de l'entreprise
            $entreprise_id = $_SESSION['entreprise_id'];
            //affichage du nom de l'entreprise
            $entreprise_name = getEnterpriseNameById($pdo, $entreprise_id);
            $_SESSION['entreprise_name'] = $entreprise_name;
            if ($entreprise_name) {
                echo "Le nom de l'entreprise est : " . htmlspecialchars($entreprise_name);
                echo '<a href= "LOGIN/choose_entreprise.php"> Modifier</a>';
            } else {
                echo "Aucune entreprise trouvée pour cet ID.";
            }
    }
    elseif (isset($_SESSION['user_id'])){
        echo ('<a href= "LOGIN/choose_entreprise.php"> Se lier avec une entreprise</a>');
    }
    ?>

</div>
</body>
</html>


