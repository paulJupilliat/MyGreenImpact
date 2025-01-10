<?php
global $pdo;
include '../LOGIN/connect.php';
include '../index.php';

// Gestion de la suppression des actions
if (isset($_POST['delete_action'])) {
    $actionId = $_POST['action_id'];
    $actionType = $_POST['action_type'];

    if ($actionType === 'individuelle') {
        $query = $pdo->prepare("DELETE FROM Actions_Utilisateurs WHERE action_id = :action_id AND user_id = :user_id");
        $query->bindParam(':action_id', $actionId, PDO::PARAM_INT);
        $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    } else {
        $query = $pdo->prepare("DELETE FROM Actions_Entreprise WHERE action_id = :action_id AND entreprise_id = :entreprise_id");
        $query->bindParam(':action_id', $actionId, PDO::PARAM_INT);
        $query->bindParam(':entreprise_id', $_SESSION['entreprise_id'], PDO::PARAM_INT);
    }
    $query->execute();

    // Redirection pour éviter la resoumission du formulaire
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['view']) ? '?view=' . $_GET['view'] : ''));
    exit;
}

// Boutons de navigation
echo '<div style="margin-bottom: 20px;">';
echo '<a href="' . $_SERVER['PHP_SELF'] . '" class="button' . (!isset($_GET['view']) ? ' active' : '') . '">Nouvelles Actions</a> ';
echo '<a href="' . $_SERVER['PHP_SELF'] . '?view=completed" class="button' . (isset($_GET['view']) && $_GET['view'] === 'completed' ? ' active' : '') . '">Actions Réalisées</a>';
echo '</div>';


if (isset($_GET['view']) && $_GET['view'] === 'completed') {
    // Vue des actions réalisées
    echo '<h2>Actions Réalisées</h2>';
    echo '<div style="display: flex; justify-content: space-between;">';

    // Actions individuelles réalisées
    echo '<div style="width: 45%;">';
    echo '<h3>Actions individuelles</h3>';

    $query = $pdo->prepare("
        SELECT A.*, AU.date_realisation 
        FROM Actions A 
        INNER JOIN Actions_Utilisateurs AU ON A.action_id = AU.action_id 
        WHERE AU.user_id = :user_id
        ORDER BY A.domaine, A.niveau
    ");
    $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $query->execute();
    $actionsIndividuelles = $query->fetchAll(PDO::FETCH_ASSOC);

    $currentDomaine = '';
    foreach ($actionsIndividuelles as $action) {
        if ($currentDomaine !== $action['domaine']) {
            if ($currentDomaine !== '') echo '</div>';
            echo '<div class="domaine-group">';
            echo '<h4>' . htmlspecialchars($action['domaine']) . '</h4>';
            $currentDomaine = $action['domaine'];
        }

        echo '<div class="action-item">';
        echo '<div>';
        echo '<strong>Niveau ' . htmlspecialchars($action['niveau']) . ':</strong> ';
        echo htmlspecialchars($action['nom']) . ' - ' . htmlspecialchars($action['description']);
        echo '<br><small>Réalisée le: ' . date('d/m/Y', strtotime($action['date_realisation'])) . '</small>';
        echo '</div>';
        echo '<form method="POST" style="display: inline;">';
        echo '<input type="hidden" name="action_id" value="' . $action['action_id'] . '">';
        echo '<input type="hidden" name="action_type" value="individuelle">';
        echo '<button type="submit" name="delete_action" class="delete-btn">Supprimer</button>';
        echo '</form>';
        echo '</div>';
    }
    if ($currentDomaine !== '') echo '</div>';

    echo '</div>';

    // Actions collectives réalisées
    echo '<div style="width: 45%;">';
    echo '<h3>Actions collectives</h3>';

    $query = $pdo->prepare("
        SELECT A.*, AE.date_realisation 
        FROM Actions A 
        INNER JOIN Actions_Entreprise AE ON A.action_id = AE.action_id 
        WHERE AE.entreprise_id = :entreprise_id
        ORDER BY A.domaine, A.niveau
    ");
    $query->bindParam(':entreprise_id', $_SESSION['entreprise_id'], PDO::PARAM_INT);
    $query->execute();
    $actionsCollectives = $query->fetchAll(PDO::FETCH_ASSOC);

    $currentDomaine = '';
    foreach ($actionsCollectives as $action) {
        if ($currentDomaine !== $action['domaine']) {
            if ($currentDomaine !== '') echo '</div>';
            echo '<div class="domaine-group">';
            echo '<h4>' . htmlspecialchars($action['domaine']) . '</h4>';
            $currentDomaine = $action['domaine'];
        }

        echo '<div class="action-item">';
        echo '<div>';
        echo '<strong>Niveau ' . htmlspecialchars($action['niveau']) . ':</strong> ';
        echo htmlspecialchars($action['nom']) . ' - ' . htmlspecialchars($action['description']);
        echo '<br><small>Réalisée le: ' . date('d/m/Y', strtotime($action['date_realisation'])) . '</small>';
        echo '</div>';
        echo '<form method="POST" style="display: inline;">';
        echo '<input type="hidden" name="action_id" value="' . $action['action_id'] . '">';
        echo '<input type="hidden" name="action_type" value="collective">';
        echo '<button type="submit" name="delete_action" class="delete-btn">Supprimer</button>';
        echo '</form>';
        echo '</div>';
    }
    if ($currentDomaine !== '') echo '</div>';

    echo '</div>';
    echo '</div>';

} else {
    // Code existant pour l'affichage des nouvelles actions
    // Récupération des domaines
    $query = $pdo->prepare("SELECT DISTINCT domaine FROM Actions");
    $query->execute();
    $domaines = $query->fetchAll(PDO::FETCH_ASSOC);

    // Affichage du formulaire de sélection des domaines
    echo '<form method="POST" action="">';
    echo '<label for="domaine">Sélectionnez un domaine :</label>';
    echo '<select name="domaine" id="domaine" onchange="this.form.submit()">';
    echo '<option value="">--Choisissez un domaine--</option>';
    foreach ($domaines as $domaine) {
        $selected = (isset($_POST['domaine']) && $_POST['domaine'] == $domaine['domaine']) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($domaine['domaine']) . '" ' . $selected . '>' . htmlspecialchars($domaine['domaine']) . '</option>';
    }
    echo '</select>';
    echo '</form>';

    if (isset($_POST['domaine']) && !empty($_POST['domaine'])) {
        $selectedDomaine = $_POST['domaine'];

        $query = $pdo->prepare("
            SELECT DISTINCT A.*
            FROM Actions A
            WHERE A.domaine = :domaine
            AND NOT EXISTS (
                SELECT 1 FROM Actions_Utilisateurs AU 
                WHERE AU.action_id = A.action_id
            )
            AND NOT EXISTS (
                SELECT 1 FROM Actions_Entreprise AE 
                WHERE AE.action_id = A.action_id
            )
        ");

        $query->bindParam(':domaine', $selectedDomaine, PDO::PARAM_STR);
        $query->execute();
        $actions = $query->fetchAll(PDO::FETCH_ASSOC);

        // Séparer les actions individuelles et collectives
        $actionsIndividuelles = [];
        $actionsCollectives = [];
        foreach ($actions as $action) {
            if ($action['type_action'] === 'individuelle') {
                $actionsIndividuelles[] = $action;
            } else {
                $actionsCollectives[] = $action;
            }
        }

        echo '<div style="display: flex; justify-content: space-between;">';

        // Affichage des actions individuelles
        echo '<div style="width: 45%;">';
        echo '<h3>Actions individuelles</h3>';
        echo '<form method="POST" action="">';
        foreach ($actionsIndividuelles as $action) {
            echo '<div class="action-item">';
            echo '<h4>Niveau : ' . htmlspecialchars($action['niveau']) . '</h4>';
            echo '<input type="checkbox" name="actions_individuelles[' . htmlspecialchars($action['action_id']) . ']" value="cochée" id="action_ind_' . htmlspecialchars($action['action_id']) . '">';
            echo '<label for="action_ind_' . htmlspecialchars($action['action_id']) . '">' . htmlspecialchars($action['nom']) . ' - ' . htmlspecialchars($action['description']) . '</label>';
            echo '</div>';
        }
        echo '</div>';

        // Affichage des actions collectives
        echo '<div style="width: 45%;">';
        echo '<h3>Actions collectives</h3>';
        foreach ($actionsCollectives as $action) {
            echo '<div class="action-item">';
            echo '<h4>Niveau : ' . htmlspecialchars($action['niveau']) . '</h4>';
            echo '<input type="checkbox" name="actions_collectives[' . htmlspecialchars($action['action_id']) . ']" value="cochée" id="action_coll_' . htmlspecialchars($action['action_id']) . '">';
            echo '<label for="action_coll_' . htmlspecialchars($action['action_id']) . '">' . htmlspecialchars($action['nom']) . ' - ' . htmlspecialchars($action['description']) . '</label>';
            echo '</div>';
        }
        echo '</div>';

        echo '</div>';

        echo '<input type="hidden" name="domaine" value="' . htmlspecialchars($selectedDomaine) . '">';
        echo '<button type="submit" name="valider">Valider</button>';
        echo '</form>';
    }
}

// Traitement de la validation des nouvelles actions
if (isset($_POST['valider'])) {
    if (isset($_POST['actions_individuelles'])) {
        foreach ($_POST['actions_individuelles'] as $actionId => $status) {
            $query = $pdo->prepare("INSERT INTO Actions_Utilisateurs (user_id, action_id, date_realisation, status) VALUES (:user_id, :action_id, NOW(), :status)");
            $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $query->bindParam(':action_id', $actionId, PDO::PARAM_INT);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();
        }
    }

    if (isset($_POST['actions_collectives'])) {
        foreach ($_POST['actions_collectives'] as $actionId => $status) {
            $query = $pdo->prepare("INSERT INTO Actions_Entreprise (entreprise_id, action_id, date_realisation, status) VALUES (:entreprise_id, :action_id, NOW(), :status)");
            $query->bindParam(':entreprise_id', $_SESSION['entreprise_id'], PDO::PARAM_INT);
            $query->bindParam(':action_id', $actionId, PDO::PARAM_INT);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>