<?php
/**
 * Modèle Notification
 * Couche d'accès aux données pour les alertes et notifications utilisateur
 */
class Notification {
    private $conn;
    private $table_name = "notifications";

    public $id_notification;
    public $id_utilisateur;
    public $message;
    public $lue;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Récupérer les notifications d'un utilisateur (lues et non lues)
     * @return PDOStatement
     */
    public function getPourUtilisateur() {
        $query = "SELECT id_notification, message, lue, date_creation 
                  FROM " . $this->table_name . " 
                  WHERE id_utilisateur = :id_utilisateur 
                  ORDER BY date_creation DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_utilisateur", $this->id_utilisateur);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Marquer une notification comme lue
     * @return boolean
     */
    public function marquerLue() {
        $query = "UPDATE " . $this->table_name . " 
                  SET lue = 1 
                  WHERE id_notification = :id_notification AND id_utilisateur = :id_utilisateur";

        $stmt = $this->conn->prepare($query);

        $this->id_notification = htmlspecialchars(strip_tags($this->id_notification));
        $this->id_utilisateur = htmlspecialchars(strip_tags($this->id_utilisateur));

        $stmt->bindParam(':id_notification', $this->id_notification);
        $stmt->bindParam(':id_utilisateur', $this->id_utilisateur);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
