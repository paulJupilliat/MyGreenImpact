<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Erreur : Aucun utilisateur connecté.");
}

$user_id = $_SESSION['user_id'];

try {
    // Utilisation de la syntaxe MySQLi au lieu de PDO
    $stmt = $conn->prepare("DELETE FROM Utilisateurs WHERE user_id = ?");
    $stmt->bind_param("i", $user_id); // "i" pour integer

    if ($stmt->execute()) {
        session_destroy();
        header("Location: ../index.php");
        exit();
    } else {
        echo "Erreur lors de la suppression : " . $stmt->error;
    }
} catch (Exception $e) {
    echo "Exception attrapée : " . $e->getMessage();
}
