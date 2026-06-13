<?php
// En-têtes HTTP POST
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id_commande) && !empty($data->statut)) {
    try {
        // Validation simple du statut
        $valid_statuses = ['en_attente', 'confirmée', 'en_préparation', 'en_livraison', 'livrée', 'annulée'];
        if(!in_array($data->statut, $valid_statuses)) {
            http_response_code(400);
            echo json_encode(["message" => "Statut invalide."]);
            exit();
        }
        
        $query = "UPDATE commandes SET statut = :statut WHERE id_commande = :id_commande";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":statut", $data->statut);
        $stmt->bindParam(":id_commande", $data->id_commande);
        
        if($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Le statut de la commande a été mis à jour avec succès."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erreur lors de la mise à jour du statut."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur serveur : " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Données de requête incomplètes (id_commande et statut requis)."]);
}
?>
