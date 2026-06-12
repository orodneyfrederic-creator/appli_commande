<?php
/**
 * Configuration de la base de données
 * Couche de configuration pour l'architecture 3-tiers
 */
class Database {
    // Identifiants de base de données
    private $host = "localhost";
    private $db_name = "db_commande";
    private $username = "root";
    private $password = "";
    public $conn;

    /**
     * Obtenir la connexion à la base de données
     * @return PDO|null Retourne l'instance PDO ou null en cas d'erreur
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Création de l'instance PDO avec le mode d'erreur exception pour sécuriser et faciliter le debug
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", 
                $this->username, 
                $this->password
            );
            // On s'assure de lever une exception si une erreur SQL survient
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // On force le renvoi des données sous forme de tableau associatif
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // En production, on éviterait d'afficher l'erreur brute pour des raisons de sécurité
            echo json_encode(["message" => "Erreur de connexion à la base de données : " . $exception->getMessage()]);
            exit;
        }

        return $this->conn;
    }
}
?>