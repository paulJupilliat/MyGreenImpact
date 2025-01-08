<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/******************************************************
 * choose_entreprise.php
 ******************************************************/
session_start();
include 'connect.php';



/***********************************************************
 * 1) Gérer la recherche AJAX d'entreprises
 ***********************************************************/
if (isset($_GET['term'])) {
    // "term" = ce que l'utilisateur est en train de taper
    $term = $_GET['term'];

    // On exécute la requête SEULEMENT si l'utilisateur a tapé 3 caractères ou plus
    if (strlen($term) >= 3) {
        $sql = "SELECT entreprise_id, nom 
                FROM Entreprises
                WHERE nom LIKE :term
                LIMIT 15";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':term' => '%' . $term . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // On renvoie du JSON
        echo json_encode($results);
    } else {
        // Si moins de 3 caractères, on ne renvoie rien ou un tableau vide
        echo json_encode([]);
    }
    exit; // On arrête l'exécution ici, car c'est un appel AJAX.
}

/***********************************************************
 * 2) Gérer la soumission du formulaire (choix entreprise)
 ***********************************************************/
if (isset($_POST['enterprise_id'])) {
    session_start(); // Assurez-vous que la session est démarrée
    $enterprise_id = (int) $_POST['enterprise_id'];
    $user_id = $_SESSION['user_id'];

    // Définir la variable de session
    $_SESSION['entreprise_id'] = $enterprise_id;

    if ($enterprise_id > 0) {
        // Mettre à jour la base de données
        $sql_update = "UPDATE Utilisateurs
                       SET entreprise_id = :enterprise_id
                       WHERE user_id = :user_id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([
            ':enterprise_id' => $enterprise_id,
            ':user_id'       => $user_id
        ]);

        if ($stmt_update->rowCount() > 0) {
            echo 'Entreprise mise à jour avec succès dans la base de données.<br>';
            echo 'ID entreprise stocké dans la session : ' . $_SESSION['enterprise_id'];
        } else {
            echo 'Aucune mise à jour effectuée.';
        }

        // Redirection vers la page d'accueil
        header('Location: ../index.php');
        exit;
    } else {
        echo 'ID d\'entreprise invalide.';
        exit;
    }
}




?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <script src="choose_company.js"></script>
    <meta charset="UTF-8" />
    <title>Choisir une entreprise</title>
    <!-- Pour simplifier, j'insère directement du JS.
         Vous pouvez évidemment séparer votre code JS dans un fichier .js dédié. -->
    <link rel="stylesheet" href="choose_company.css">
</head>

<body>

<h1>Choisir une entreprise</h1>

<!-- Bouton Skip -->
<p>
    <a href="../index.php">Skip</a>
</p>

<form method="POST" action="choose_entreprise.php">
    <!-- Champ texte de recherche d'entreprise -->
    <label for="searchEnterprise">Entreprise :</label>
    <input type="text"
           id="searchEnterprise"
           name="searchEnterprise"
           autocomplete="off"
           placeholder="Tapez le nom d'une entreprise..."
    />

    <!-- Zone où seront affichées les suggestions -->
    <div id="suggestions"></div>

    <!-- Champ caché qui contiendra l'ID de l'entreprise sélectionnée -->
    <input type="hidden" id="enterprise_id" name="enterprise_id" value="">


    <!-- Bouton de validation -->
    <button type="submit">Valider</button>
</form>

</body>
</html>
