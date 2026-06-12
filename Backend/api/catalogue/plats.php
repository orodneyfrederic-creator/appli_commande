<?php
// En-têtes HTTP GET
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../models/Plat.php';

$database = new Database();
$db = $database->getConnection();

$plat = new Plat($db);

// Vérification si on demande par catégorie ou par restaurant globalement
if(isset($_GET['id_categorie'])) {
    $plat->id_categorie = $_GET['id_categorie'];
    $stmt = $plat->getByCategorie();
} else if(isset($_GET['id_restaurant'])) {
    $stmt = $plat->getByRestaurant($_GET['id_restaurant']);
} else {
    // Mauvaise requête, paramètre manquant
    http_response_code(400);
    echo json_encode(["message" => "Paramètre id_categorie ou id_restaurant requis."]);
    exit();
}

$num = $stmt->rowCount();

if($num > 0) {
    $plats_arr = [];
    $plats_arr["records"] = [];

    while ($row = $stmt->fetch()) {
        extract($row);

        $plat_item = [
            "id_plat" => $id_plat,
            "id_categorie" => $id_categorie,
            "nom" => html_entity_decode($nom),
            "description" => html_entity_decode($description),
            "prix" => $prix,
            "disponible" => $disponible
        ];
        
        // On ajoute le nom de la catégorie si on fait une requête par restaurant
        if(isset($categorie_nom)) {
            $plat_item["categorie_nom"] = html_entity_decode($categorie_nom);
        }

        array_push($plats_arr["records"], $plat_item);
    }

    http_response_code(200);
    echo json_encode($plats_arr);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Aucun plat disponible trouvé."]);
}
?>
