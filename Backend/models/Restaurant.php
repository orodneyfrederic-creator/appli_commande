<?php
/**
 * Modèle Restaurant
 * Couche d'accès aux données pour les restaurants
 */
class Restaurant {
    private $conn;
    private $table_name = "restaurants"; // Nom de la table inféré

    // Propriétés du restaurant
    public $id_restaurant;
    public $nom;
    public $adresse;
    public $telephone;
    public $actif;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Récupérer la liste des restaurants actifs
     * @return PDOStatement
     */
    public function getActifs() {
        // Sélectionner tous les restaurants où le statut est actif (1)
        $query = "SELECT id_restaurant, nom, adresse, telephone, actif 
                  FROM " . $this->table_name . " 
                  WHERE actif = 1 
                  ORDER BY nom ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtenir les détails d'un restaurant spécifique
     * @return array|boolean Détails du restaurant ou false
     */
    public function getDetails() {
        $query = "SELECT id_restaurant, nom, adresse, telephone, actif 
                  FROM " . $this->table_name . " 
                  WHERE id_restaurant = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_restaurant);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
}
?>
