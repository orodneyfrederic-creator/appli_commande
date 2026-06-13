<?php
// En-têtes HTTP GET
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Récupérer toutes les commandes avec les infos utilisateur et restaurant
    $query = "SELECT c.id_commande, c.id_utilisateur, c.id_restaurant, c.date_commande, c.montant_total, c.statut, c.type_livraison, c.adresse_livraison, c.notes, 
                     u.nom as utilisateur_nom, u.email as client_email, u.telephone as client_telephone, r.nom as restaurant_nom
              FROM commandes c 
              LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur 
              LEFT JOIN restaurants r ON c.id_restaurant = r.id_restaurant 
              ORDER BY c.date_commande DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $commandes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // On va chercher les lignes pour chaque commande
        $id_cmd = $row['id_commande'];
        $query_lignes = "SELECT lc.quantite, lc.prix_unitaire, lc.sous_total, p.nom as plat_nom 
                         FROM lignes_commande lc 
                         LEFT JOIN plats p ON lc.id_plat = p.id_plat 
                         WHERE lc.id_commande = :id_commande";
        $stmt_lignes = $db->prepare($query_lignes);
        $stmt_lignes->bindParam(":id_commande", $id_cmd);
        $stmt_lignes->execute();
        
        $lignes = [];
        while($row_ligne = $stmt_lignes->fetch(PDO::FETCH_ASSOC)) {
            $lignes[] = [
                "plat_nom" => $row_ligne['plat_nom'],
                "quantite" => intval($row_ligne['quantite']),
                "prix_unitaire" => floatval($row_ligne['prix_unitaire']),
                "sous_total" => floatval($row_ligne['sous_total'] ?? ($row_ligne['quantite'] * $row_ligne['prix_unitaire']))
            ];
        }
        
        $commandes[] = [
            "id_commande" => $row['id_commande'],
            "id_utilisateur" => $row['id_utilisateur'],
            "id_restaurant" => $row['id_restaurant'],
            "utilisateur_nom" => $row['utilisateur_nom'],
            "client_email" => $row['client_email'],
            "client_telephone" => $row['client_telephone'],
            "restaurant_nom" => $row['restaurant_nom'],
            "date_commande" => $row['date_commande'],
            "montant_total" => floatval($row['montant_total']),
            "statut" => $row['statut'],
            "type_livraison" => $row['type_livraison'],
            "adresse_livraison" => $row['adresse_livraison'],
            "notes" => $row['notes'],
            "lignes" => $lignes
        ];
    }
    
    http_response_code(200);
    echo json_encode(["records" => $commandes]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la récupération des commandes : " . $e->getMessage()]);
}
?>
