<?php
// En-têtes HTTP pour méthode GET
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../models/Restaurant.php';

$database = new Database();
$db = $database->getConnection();

$restaurant = new Restaurant($db);

// Récupérer les restaurants actifs
$stmt = $restaurant->getActifs();
$num = $stmt->rowCount();

if($num > 0) {
    // Tableau pour stocker les résultats
    $restaurants_arr = [];
    $restaurants_arr["records"] = [];

    // Boucle à travers les résultats de la base de données
    while ($row = $stmt->fetch()) {
        // Extraction de la ligne, $row['nom'] devient $nom etc.
        extract($row);

        $restaurant_item = [
            "id_restaurant" => $id_restaurant,
            "nom" => html_entity_decode($nom),
            "adresse" => html_entity_decode($adresse),
            "telephone" => $telephone,
            "actif" => $actif
        ];

        // Ajouter dans le tableau
        array_push($restaurants_arr["records"], $restaurant_item);
    }

    // Code HTTP 200 - OK
    http_response_code(200);
    echo json_encode($restaurants_arr);

} else {
    // Code HTTP 404 - Not Found
    http_response_code(404);
    echo json_encode(["message" => "Aucun restaurant actif trouvé."]);
}
?>
