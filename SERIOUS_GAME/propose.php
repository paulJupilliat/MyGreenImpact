<?php
global $pdo;
include '../LOGIN/connect.php'; // Connexion à la base de données
include '../index.php'; // Inclus si nécessaire pour l'interface

// Récupérer les domaines pour les afficher dans le formulaire
$query = $pdo->prepare("SELECT DISTINCT domaine FROM Actions");
$query->execute();
$domaines = $query->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id']; // Assurez-vous que $_SESSION['user_id'] contient l'ID de l'utilisateur connecté
    $theme = $_POST['theme'];
    $nom = $_POST['nom'];
    $niveau = $_POST['niveau'];
    $description = $_POST['description'];
    $dateProposition = date('Y-m-d'); // Date actuelle pour la date de proposition
    $status = 'en attente'; // Statut par défaut

    // Validation des données
    if (!empty($theme) && !empty($nom) && !empty($niveau) && !empty($description)) {
        // Insertion dans la table Propositions
        $query = $pdo->prepare("
            INSERT INTO Propositions_Defis (user_id, nom, description, domaine, niveau, status, date_proposition) 
            VALUES (:user_id, :nom, :description, :domaine, :niveau, :status, :date_proposition)
        ");
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->bindParam(':nom', $nom, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':domaine', $theme, PDO::PARAM_STR);
        $query->bindParam(':niveau', $niveau, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':date_proposition', $dateProposition, PDO::PARAM_STR);
        $query->execute();

        // Redirection après validation
        header("Location: ../index.php"); // Changez vers la page où vous voulez rediriger
        exit;
    } else {
        $error = "Tous les champs sont requis.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposer une action</title>
    <link rel="stylesheet" href="../styles.css"> <!-- Incluez votre fichier CSS -->
</head>
<body>
<h1>Proposer une action</h1>

<?php if (isset($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div>
        <label for="theme">Thème de l'action :</label>
        <select id="theme" name="theme" required>
            <option value="">--Choisissez un thème--</option>
            <?php foreach ($domaines as $domaine): ?>
                <option value="<?php echo htmlspecialchars($domaine['domaine']); ?>">
                    <?php echo htmlspecialchars($domaine['domaine']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="nom">Nom de l'action :</label>
        <input type="text" id="nom" name="nom" required>
    </div>
    <div>
        <label for="niveau">Niveau de l'action :</label>
        <select id="niveau" name="niveau" required>
            <option value="">--Choisissez un niveau--</option>
            <option value="débutant">Débutant</option>
            <option value="intermédiaire">Intermédiaire</option>
            <option value="confirmé">Confirmé</option>
        </select>
    </div>
    <div>
        <label for="description">Description de l'action :</label>
        <textarea id="description" name="description" rows="5" required></textarea>
    </div>
    <div>
        <button type="submit">Valider</button>
    </div>
</form>
</body>
</html>
