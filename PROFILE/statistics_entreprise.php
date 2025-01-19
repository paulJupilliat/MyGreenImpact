<?php
global $pdo;
include('../index.php');

// Déterminer le type d'action (individuelle ou collective)
$actionType = isset($_GET['type']) && $_GET['type'] === 'collective' ? 'collective' : 'individuelle';

// Fonction pour calculer le score total possible par domaine
function getMaxScoreByDomain($pdo, $type) {
    $query = $pdo->prepare("
        SELECT domaine, SUM(points) as total_points
        FROM Actions
        WHERE type_action = :type_action
        GROUP BY domaine
    ");
    $query->bindParam(':type_action', $type, PDO::PARAM_STR);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Fonction pour calculer le score actuel de l'entreprise par domaine
function getCurrentScoreByDomain($pdo, $entreprise_id, $type, $user_id) {
    $table = $type === 'individuelle' ? 'Actions_Utilisateurs' : 'Actions_Entreprise';
    $query = $pdo->prepare("
        SELECT A.domaine, SUM(A.points) as score
        FROM Actions A
        INNER JOIN $table AE ON A.action_id = AE.action_id
        WHERE AE." . ($type === 'individuelle' ? 'user_id = :id' : 'entreprise_id = :id') . "
        GROUP BY A.domaine
    ");
    $id = $type === 'individuelle' ? $user_id : $entreprise_id;
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Nouvelle fonction pour calculer la moyenne des scores par domaine
function getAverageScoreByDomain($pdo, $type) {
    $table = $type === 'individuelle' ? 'Actions_Utilisateurs' : 'Actions_Entreprise';
    $query = $pdo->prepare("
        SELECT 
            A.domaine,
            ROUND(AVG(CASE 
                WHEN AE.action_id IS NOT NULL THEN A.points
                ELSE 0
            END), 1) as moyenne
        FROM Actions A
        LEFT JOIN $table AE ON A.action_id = AE.action_id
        WHERE A.type_action = :type_action
        GROUP BY A.domaine
    ");
    $query->bindParam(':type_action', $type, PDO::PARAM_STR);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Récupérer les données
$maxScores = getMaxScoreByDomain($pdo, $actionType);
$currentScores = getCurrentScoreByDomain($pdo, $_SESSION['entreprise_id'], $actionType, $_SESSION['user_id']);
$averageScores = getAverageScoreByDomain($pdo, $actionType);

// Calculer le total global
$totalMax = array_sum($maxScores);
$totalCurrent = array_sum($currentScores);
$totalAverage = array_sum($averageScores);

// Préparer les données pour le graphique
$domaines = array_keys($maxScores);
$currentScoreData = array();
$averageScoreData = array();
foreach ($domaines as $domaine) {
    $currentScoreData[] = isset($currentScores[$domaine]) ? $currentScores[$domaine] : 0;
    $averageScoreData[] = isset($averageScores[$domaine]) ? $averageScores[$domaine] : 0;
}

//calcul pour les barres de progressions
$progressData = [];
foreach ($maxScores as $domaine => $maxScore) {
    $currentScore = isset($currentScores[$domaine]) ? $currentScores[$domaine] : 0;
    $progressData[$domaine] = $maxScore > 0 ? round(($currentScore / $maxScore) * 100) : 0; // Calcul du pourcentage

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques de l'entreprise</title>
    <link rel="stylesheet" href="styles-entreprise.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<main>
    <section>
        <h2>Statistiques de l'entreprise</h2>

        <!-- Navigation pour basculer entre les types d'actions -->
        <div class="switch-type">
            <a href="?type=individuelle" class="button <?php echo $actionType === 'individuelle' ? 'active' : ''; ?>">Individuelles</a>
            <a href="?type=collective" class="button <?php echo $actionType === 'collective' ? 'active' : ''; ?>">Collectives</a>
        </div>

        <!-- Graphique -->
        <div class="chart-container">
            <canvas id="radarChart"></canvas>
        </div>

        <div class="progress-bars">
            <h3>Progression par domaine</h3>
            <?php foreach ($progressData as $domaine => $progress):


            ?>
                <div class="progress-container">
                    <span class="progress-label"><?php echo htmlspecialchars($domaine); ?> (<?php echo $progress; ?>%) </span>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<script>
    const ctx = document.getElementById('radarChart').getContext('2d');
    const radarChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: <?php echo json_encode($domaines); ?>,
            datasets: [
                {
                    label: 'Mon Entreprise',
                    data: <?php echo json_encode($currentScoreData); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Moyenne des entreprises',
                    data: <?php echo json_encode($averageScoreData); ?>,
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                }
            },
            scales: {
                r: {
                    ticks: {
                        display: true,
                        backdropColor: 'transparent',
                        font: {
                            size: 12
                        }
                    },
                    suggestedMin: 0,
                    suggestedMax: <?php echo max($maxScores); ?>
                }
            }
        }
    });
</script>
</body>
</html>
