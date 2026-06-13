<?php
// En-têtes HTTP GET
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../models/CategorieMenu.php';

$database = new Database();
$db = $database->getConnection();

$categorie = new CategorieMenu($db);

// Récupérer l'id_restaurant depuis les paramètres GET
$id_restaurant = isset($_GET['id_restaurant']) ? $_GET['id_restaurant'] : null;

if(!empty($id_restaurant)) {
    $categorie->id_restaurant = $id_restaurant;
    
    // Récupérer les catégories
    $stmt = $categorie->getCategoriesByRestaurant();
    $num = $stmt->rowCount();

    if($num > 0) {
        $categories_arr = [];
        $categories_arr["records"] = [];
        
        while ($row = $stmt->fetch()) {
            extract($row);
            
            $categorie_item = [
                "id_categorie" => $id_categorie,
                "id_restaurant" => $id_restaurant,
                "nom" => html_entity_decode($nom),
                "description" => html_entity_decode($description)
            ];
            
            array_push($categories_arr["records"], $categorie_item);
        }
        
        http_response_code(200);
        echo json_encode($categories_arr);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "id_restaurant requis."]);
}
?>
