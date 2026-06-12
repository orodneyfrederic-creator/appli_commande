<?php
/**
 * Modèle Avis
 * Couche d'accès aux données pour les avis et notes
 */
class Avis {
    private $conn;
    private $table_name = "avis";

    public $id_avis;
    public $id_utilisateur;
    public $id_restaurant;
    public $note;
    public $commentaire;
    public $date_avis;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Ajouter un avis (commentaire/note)
     * @return boolean
     */
    public function ajouter() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_utilisateur=:id_utilisateur, id_restaurant=:id_restaurant, note=:note, commentaire=:commentaire, date_avis=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->id_utilisateur = htmlspecialchars(strip_tags($this->id_utilisateur));
        $this->id_restaurant = htmlspecialchars(strip_tags($this->id_restaurant));
        $this->note = htmlspecialchars(strip_tags($this->note));
        $this->commentaire = htmlspecialchars(strip_tags($this->commentaire));

        $stmt->bindParam(":id_utilisateur", $this->id_utilisateur);
        $stmt->bindParam(":id_restaurant", $this->id_restaurant);
        $stmt->bindParam(":note", $this->note);
        $stmt->bindParam(":commentaire", $this->commentaire);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Lire les avis d'un restaurant spécifique
     * @return PDOStatement
     */
    public function lireParRestaurant() {
        $query = "SELECT a.id_avis, a.note, a.commentaire, a.date_avis, u.nom as utilisateur_nom 
                  FROM " . $this->table_name . " a
                  LEFT JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
                  WHERE a.id_restaurant = :id_restaurant 
                  ORDER BY a.date_avis DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_restaurant", $this->id_restaurant);
        $stmt->execute();

        return $stmt;
    }
}
?>
