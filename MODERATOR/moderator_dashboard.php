<?php
global $pdo;
session_start();
include '../LOGIN/connect.php';

// Vérification du rôle du modérateur
if ($_SESSION['role'] !== 'modérateur') {
    echo "Accès refusé.";
    exit();
}

// Gestion des actions : validation, suppression ou modification
$message = ""; // Message pour afficher le statut

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['validate'])) {
            // Validation de la proposition
            $proposition_id = intval($_POST['proposition_id']);
            if (empty($_POST['type_action']) || empty($_POST['points']) || empty($_POST['domaine'])) {
                $message = "Erreur : Tous les champs obligatoires doivent être remplis pour valider.";
            } else {
                $type_action = $_POST['type_action']; // Récupération du type d'action choisi par le modérateur
                $points = intval($_POST['points']); // Récupération des points modifiés par le modérateur
                $domaine = $_POST['domaine']; // Récupération du domaine sélectionné par le modérateur

                // Récupérer la proposition à valider
                $stmt = $pdo->prepare("SELECT * FROM Propositions_Defis WHERE proposition_id = :proposition_id");
                $stmt->bindParam(':proposition_id', $proposition_id);
                $stmt->execute();
                $proposition = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($proposition) {
                    // Insertion dans la table "Actions"
                    $stmt = $pdo->prepare("INSERT INTO Actions (nom, description, type_action, niveau, domaine, points) 
                                            VALUES (:nom, :description, :type_action, :niveau, :domaine, :points)");
                    $stmt->execute([
                        ':nom' => $proposition['nom'],
                        ':description' => $proposition['description'],
                        ':type_action' => $type_action, // Type d'action choisi par le modérateur
                        ':niveau' => $proposition['niveau'],
                        ':domaine' => $domaine, // Domaine sélectionné par le modérateur
                        ':points' => $points, // Points modifiés par le modérateur
                    ]);

                    // Suppression de la proposition après validation
                    $stmt = $pdo->prepare("DELETE FROM Propositions_Defis WHERE proposition_id = :proposition_id");
                    $stmt->bindParam(':proposition_id', $proposition_id);
                    $stmt->execute();

                    $message = "Action validée et ajoutée avec succès.";
                } else {
                    $message = "Erreur : Proposition introuvable.";
                }
            }
        } elseif (isset($_POST['delete'])) {
            // Suppression de la proposition
            $proposition_id = intval($_POST['proposition_id']);
            $stmt = $pdo->prepare("DELETE FROM Propositions_Defis WHERE proposition_id = :proposition_id");
            $stmt->bindParam(':proposition_id', $proposition_id);
            $stmt->execute();
            $message = "Proposition supprimée avec succès.";
        }
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// Récupération de toutes les propositions
$stmt = $pdo->prepare("SELECT * FROM Propositions_Defis");
$stmt->execute();
$propositions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de tous les domaines disponibles
$stmt = $pdo->prepare("SELECT DISTINCT domaine FROM Actions");
$stmt->execute();
$domaines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Propositions</title>
</head>
<body>
<h1>Gestion des Propositions de Défis</h1>

<?php if ($message): ?>
    <div style="color: <?= strpos($message, 'Erreur') === false ? 'green' : 'red' ?>;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<table border="1">
    <thead>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Description</th>
        <th>Domaine</th>
        <th>Niveau</th>
        <th>Points</th>
        <th>Date Proposition</th>
        <th>Actions</th>
        <th>Modération</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($propositions as $proposition): ?>
        <tr>
            <form method="POST">
                <td><?= htmlspecialchars($proposition['proposition_id']) ?></td>
                <td><input type="text" name="nom" value="<?= htmlspecialchars($proposition['nom']) ?>"></td>
                <td><textarea name="description"> <?= htmlspecialchars($proposition['description']) ?> </textarea></td>
                <td>
                    <select name="domaine" <?= isset($_POST['validate']) ? 'required' : '' ?> >
                        <option value="">--Choisissez un domaine--</option>
                        <?php foreach ($domaines as $domaine): ?>
                            <option value="<?= htmlspecialchars($domaine['domaine']) ?>" <?= $proposition['domaine'] === $domaine['domaine'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($domaine['domaine']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="niveau" <?= isset($_POST['validate']) ? 'required' : '' ?> >
                        <option value="débutant" <?= $proposition['niveau'] === 'débutant' ? 'selected' : '' ?>>Débutant</option>
                        <option value="intermédiaire" <?= $proposition['niveau'] === 'intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                        <option value="confirmé" <?= $proposition['niveau'] === 'confirmé' ? 'selected' : '' ?>>Confirmé</option>
                    </select>
                </td>
                <td><input type="number" name="points" value="<?= htmlspecialchars($proposition['points']) ?>" <?= isset($_POST['validate']) ? 'required' : '' ?> ></td>
                <td><?= htmlspecialchars($proposition['date_proposition']) ?></td>
                <td>
                    <select name="type_action" <?= isset($_POST['validate']) ? 'required' : '' ?>>
                        <option value="individuelle">Individuelle</option>
                        <option value="collective">Collective</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="proposition_id" value="<?= $proposition['proposition_id'] ?>">
                    <button type="submit" name="validate">Valider</button>
                    <button type="submit" name="delete">Supprimer</button>
                </td>
            </form>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>