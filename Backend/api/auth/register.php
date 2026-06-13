<?php
// En-têtes HTTP requis pour autoriser l'accès (CORS) et définir le format JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Inclure les fichiers de configuration et d'accès aux données
include_once '../../config/database.php';
include_once '../../models/Utilisateur.php';

// Initialisation de la base de données
$database = new Database();
$db = $database->getConnection();

// Initialisation de l'objet Utilisateur
$utilisateur = new Utilisateur($db);

// Récupération des données envoyées via la méthode POST au format JSON
$data = json_decode(file_get_contents("php://input"));

// Vérification que les données ne sont pas vides
if(
    !empty($data->nom) &&
    !empty($data->email) &&
    !empty($data->mot_de_passe)
) {
    // Attribution des valeurs de l'utilisateur
    $utilisateur->nom = $data->nom;
    $utilisateur->email = $data->email;
    
    // Hacher le mot de passe avant de l'attribuer
    $utilisateur->mot_de_passe = password_hash($data->mot_de_passe, PASSWORD_BCRYPT);

    // Vérification de l'existence de l'email
    if($utilisateur->emailExiste()) {
        // Code de réponse HTTP - 400 Bad Request
        http_response_code(400);
        echo json_encode(["message" => "Cet email est déjà utilisé."]);
    } else {
        // Création de l'utilisateur
        if($utilisateur->inscription()) {
            // Code de réponse HTTP - 201 Created
            http_response_code(201);
            echo json_encode(["message" => "Utilisateur créé avec succès."]);
        } else {
            // Code de réponse HTTP - 503 Service Unavailable
            http_response_code(503);
            echo json_encode(["message" => "Impossible de créer l'utilisateur."]);
        }
    }
} else {
    // Code de réponse HTTP - 400 Bad Request (Données incomplètes)
    http_response_code(400);
    echo json_encode(["message" => "Données incomplètes. Impossible de créer l'utilisateur."]);
}
?>
