<?php
// En-têtes HTTP GET
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Commande.php';

$database = new Database();
$db = $database->getConnection();

$commande = new Commande($db);

// Récupérer l'id_utilisateur depuis les paramètres GET
$id_utilisateur = isset($_GET['id_utilisateur']) ? $_GET['id_utilisateur'] : null;

if(!empty($id_utilisateur)) {
    $commande->id_utilisateur = $id_utilisateur;
    
    // Récupérer l'historique
    $stmt = $commande->getHistoriqueUtilisateur();
    $num = $stmt->rowCount();

    if($num > 0) {
        $commandes_arr = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $commande_item = [
                "id_commande" => $id_commande,
                "id_restaurant" => $id_restaurant,
                "restaurant_nom" => $restaurant_nom,
                "montant_total" => floatval($montant_total),
                "statut" => $statut,
                "date_commande" => $date_commande,
                "type_livraison" => $type_livraison,
                "adresse_livraison" => $adresse_livraison,
                "notes" => $notes
            ];
            
            $commandes_arr[] = $commande_item;
        }
        
        http_response_code(200);
        echo json_encode($commandes_arr);
    } else {
        http_response_code(200); // 200 avec tableau vide
        echo json_encode([]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "id_utilisateur manquant."]);
}
?>
