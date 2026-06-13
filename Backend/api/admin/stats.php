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
    // 1. Revenu total
    $query = "SELECT SUM(montant_total) AS total_revenue FROM commandes WHERE statut != 'annulée'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_revenue = floatval($row['total_revenue'] ?? 0);

    // 2. Nombre total de commandes
    $query = "SELECT COUNT(*) AS total_orders FROM commandes";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_orders = intval($row['total_orders'] ?? 0);

    // 3. Nombre de clients
    $query = "SELECT COUNT(*) AS total_clients FROM utilisateurs WHERE role = 'client'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_clients = intval($row['total_clients'] ?? 0);

    // 4. Nombre de restaurants
    $query = "SELECT COUNT(*) AS total_restaurants FROM restaurants";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_restaurants = intval($row['total_restaurants'] ?? 0);

    // 5. Commandes par statut
    $query = "SELECT statut, COUNT(*) as count FROM commandes GROUP BY statut";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $status_stats = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_stats[$row['statut']] = intval($row['count']);
    }

    // 6. 5 Dernières commandes
    $query = "SELECT c.id_commande, c.date_commande, c.montant_total, c.statut, u.nom as client_nom, r.nom as restaurant_nom 
              FROM commandes c 
              LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur 
              LEFT JOIN restaurants r ON c.id_restaurant = r.id_restaurant 
              ORDER BY c.date_commande DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recent_orders = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recent_orders[] = [
            "id_commande" => $row['id_commande'],
            "date_commande" => $row['date_commande'],
            "montant_total" => floatval($row['montant_total']),
            "statut" => $row['statut'],
            "client_nom" => $row['client_nom'],
            "restaurant_nom" => $row['restaurant_nom']
        ];
    }

    // Réponse de succès
    http_response_code(200);
    echo json_encode([
        "stats" => [
            "total_revenue" => $total_revenue,
            "total_orders" => $total_orders,
            "total_clients" => $total_clients,
            "total_restaurants" => $total_restaurants,
            "status_distribution" => $status_stats
        ],
        "recent_orders" => $recent_orders
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la récupération des statistiques : " . $e->getMessage()]);
}
?>
