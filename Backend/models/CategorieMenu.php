<?php
/**
 * Modèle CategorieMenu
 * Couche d'accès aux données pour les catégories de menu
 */
class CategorieMenu {
    private $conn;
    private $table_name = "categories_menu";

    public $id_categorie;
    public $id_restaurant;
    public $nom;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Récupérer les catégories d'un restaurant spécifique
     * @return PDOStatement
     */
    public function getCategoriesByRestaurant() {
        $query = "SELECT id_categorie, id_restaurant, nom, description 
                  FROM " . $this->table_name . " 
                  WHERE id_restaurant = :id_restaurant 
                  ORDER BY nom ASC";

        $stmt = $this->conn->prepare($query);
        
        // Nettoyage et liaison
        $this->id_restaurant = htmlspecialchars(strip_tags($this->id_restaurant));
        $stmt->bindParam(":id_restaurant", $this->id_restaurant);
        
        $stmt->execute();

        return $stmt;
    }
}
?>
