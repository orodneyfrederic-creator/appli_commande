<?php
// En-têtes HTTP POST
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Commande.php';

$database = new Database();
$db = $database->getConnection();

$commande = new Commande($db);

// Récupération des données POST (le panier)
$data = json_decode(file_get_contents("php://input"), true); // true = retourner un tableau associatif

// On vérifie que toutes les infos nécessaires sont présentes
if(
    !empty($data['id_utilisateur']) &&
    !empty($data['id_restaurant']) &&
    !empty($data['montant_total']) &&
    !empty($data['lignes_panier']) && is_array($data['lignes_panier'])
) {
    // Attribution des valeurs à l'objet
    $commande->id_utilisateur = $data['id_utilisateur'];
    $commande->id_restaurant = $data['id_restaurant'];
    $commande->montant_total = $data['montant_total'];
    $commande->statut = "en_attente"; // Statut initial par défaut
    $commande->type_livraison = isset($data['type_livraison']) ? $data['type_livraison'] : 'livraison';
    $commande->adresse_livraison = isset($data['adresse_livraison']) ? $data['adresse_livraison'] : null;
    $commande->notes = isset($data['notes']) ? $data['notes'] : null;
    
    // Le tableau des plats commandés
    $commande->lignes_panier = $data['lignes_panier'];

    // Appel à la méthode créerCommande() qui gère la TRANSACTION PDO
    if($commande->creerCommande()) {
        // Succès
        http_response_code(201);
        echo json_encode([
            "message" => "La commande a été passée avec succès.",
            "id_commande" => $commande->id_commande // on renvoie l'ID pour d'éventuels paiements successifs
        ]);
    } else {
        // Erreur serveur (la transaction a échoué)
        http_response_code(503);
        echo json_encode(["message" => "Impossible de passer la commande. Une erreur est survenue."]);
    }
} else {
    // Requête invalide ou données manquantes
    http_response_code(400);
    echo json_encode(["message" => "Données de commande incomplètes. Impossible de créer la commande."]);
}
?>
