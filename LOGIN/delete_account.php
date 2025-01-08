<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Erreur : Aucun utilisateur connecté.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $user_id = $_SESSION['user_id'];

    try {
        // Préparer la requête en PDO
        $stmt = $pdo->prepare("DELETE FROM Utilisateurs WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Si la suppression réussit
            session_destroy();
            header("Location: ../index.php");
            exit();
        } else {
            // En cas de problème, on peut récupérer le message d’erreur
            $errorInfo = $stmt->errorInfo();
            echo "Erreur lors de la suppression : " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        echo "Exception attrapée : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de suppression</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20%;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            text-align: center;
        }

        .modal-content button {
            margin: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<h1>Mon compte</h1>
<p>Cliquez sur le bouton ci-dessous pour supprimer votre compte :</p>
<button id="deleteButton">Supprimer mon compte</button>

<!-- Popup modal -->
<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Confirmez la suppression</h2>
        <p>Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.</p>
        <form method="POST" action="">
            <button type="submit" name="confirm" value="yes">Oui, supprimer</button>
            <button type="button" id="cancelButton">Non, annuler</button>
        </form>
    </div>
</div>

<script>
    // Ouvrir le modal
    const modal = document.getElementById("confirmationModal");
    const deleteButton = document.getElementById("deleteButton");
    const cancelButton = document.getElementById("cancelButton");
    const closeModal = document.querySelector(".close");

    deleteButton.onclick = function () {
        modal.style.display = "block";
    };

    // Fermer le modal via le bouton "Non"
    cancelButton.onclick = function () {
        modal.style.display = "none";
    };

    // Fermer le modal via la croix
    closeModal.onclick = function () {
        modal.style.display = "none";
    };

    // Fermer le modal en cliquant en dehors de la boîte
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
</script>

</body>
</html>