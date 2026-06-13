<?php
// En-têtes HTTP pour une API REST avec réponse JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Utilisateur.php';

$database = new Database();
$db = $database->getConnection();

$utilisateur = new Utilisateur($db);

// Lire les données JSON reçues
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->mot_de_passe)) {
    // On assigne l'email pour vérifier son existence en BDD
    $utilisateur->email = $data->email;
    $email_existe = $utilisateur->emailExiste();

    // Vérification du mot de passe en comparant la version envoyée en clair 
    // et le hash stocké en base de données.
    if($email_existe && password_verify($data->mot_de_passe, $utilisateur->mot_de_passe)) {
        
        // La connexion est réussie. Dans un système plus complexe (ex: JWT), on générerait un token ici.
        // Pour cet exemple, on retourne un message de succès et l'ID de l'utilisateur
        
        // Code HTTP 200 - OK
        http_response_code(200);
        echo json_encode([
            "message" => "Connexion réussie.",
            "utilisateur" => [
                "id_utilisateur" => $utilisateur->id_utilisateur,
                "nom" => $utilisateur->nom,
                "email" => $utilisateur->email,
                "role" => $utilisateur->role
            ]
        ]);
    } else {
        // Identifiants incorrects
        // Code HTTP 401 - Unauthorized
        http_response_code(401);
        echo json_encode(["message" => "Email ou mot de passe incorrect."]);
    }
} else {
    // Code HTTP 400 - Bad Request
    http_response_code(400);
    echo json_encode(["message" => "Données de connexion incomplètes."]);
}
?>
