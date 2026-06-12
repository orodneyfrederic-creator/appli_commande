<?php
/**
 * Modèle Plat
 * Couche d'accès aux données pour les plats
 */
class Plat {
    private $conn;
    private $table_name = "plats";

    public $id_plat;
    public $id_categorie;
    public $nom;
    public $description;
    public $prix;
    public $disponible;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Récupérer tous les plats disponibles pour une catégorie donnée
     * @return PDOStatement
     */
    public function getByCategorie() {
        $query = "SELECT id_plat, id_categorie, nom, description, prix, disponible 
                  FROM " . $this->table_name . " 
                  WHERE id_categorie = :id_categorie AND disponible = 1 
                  ORDER BY nom ASC";

        $stmt = $this->conn->prepare($query);
        
        $this->id_categorie = htmlspecialchars(strip_tags($this->id_categorie));
        $stmt->bindParam(":id_categorie", $this->id_categorie);
        
        $stmt->execute();

        return $stmt;
    }

    /**
     * Récupérer tous les plats disponibles pour un restaurant (via jointure avec categories_menu)
     * @param int $id_restaurant
     * @return PDOStatement
     */
    public function getByRestaurant($id_restaurant) {
        $query = "SELECT p.id_plat, p.id_categorie, p.nom, p.description, p.prix, p.disponible, c.nom as categorie_nom 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories_menu c ON p.id_categorie = c.id_categorie
                  WHERE c.id_restaurant = :id_restaurant AND p.disponible = 1
                  ORDER BY c.nom ASC, p.nom ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_restaurant", $id_restaurant);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Mettre à jour la disponibilité d'un plat
     * @return boolean
     */
    public function updateDisponibilite() {
        $query = "UPDATE " . $this->table_name . " 
                  SET disponible = :disponible 
                  WHERE id_plat = :id_plat";

        $stmt = $this->conn->prepare($query);

        $this->disponible = htmlspecialchars(strip_tags($this->disponible));
        $this->id_plat = htmlspecialchars(strip_tags($this->id_plat));

        $stmt->bindParam(':disponible', $this->disponible);
        $stmt->bindParam(':id_plat', $this->id_plat);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
