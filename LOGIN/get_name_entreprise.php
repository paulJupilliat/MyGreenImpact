<?php
/**
 * Récupère le nom d'une entreprise à partir de son ID.
 *
 * @param PDO $pdo Instance de la connexion PDO.
 * @param int $enterprise_id L'ID de l'entreprise.
 * @return string|null Le nom de l'entreprise ou null si l'entreprise n'existe pas.
 */
function getEnterpriseNameById(PDO $pdo, int $enterprise_id): ?string {
    try {
        // Prépare la requête pour récupérer le nom de l'entreprise
        $stmt = $pdo->prepare("SELECT nom FROM Entreprises WHERE entreprise_id = :enterprise_id");
        $stmt->bindParam(':enterprise_id', $enterprise_id, PDO::PARAM_INT);
        $stmt->execute();

        // Récupère le résultat
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {

            return $result['nom'];
        } else {
            return null; // Si aucun résultat trouvé
        }
    } catch (PDOException $e) {
        // En cas d'erreur, affichez un message et retournez null
        error_log("Erreur dans getEnterpriseNameById: " . $e->getMessage());
        return null;
    }
}