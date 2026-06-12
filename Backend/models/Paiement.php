<?php
/**
 * Modèle Paiement
 * Couche d'accès aux données pour les paiements
 */
class Paiement {
    private $conn;
    private $table_name = "paiements";

    public $id_paiement;
    public $id_commande;
    public $montant;
    public $methode_paiement;
    public $statut;
    public $date_paiement;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Enregistrer un paiement lié à une commande
     * @return boolean
     */
    public function enregistrer() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_commande=:id_commande, montant=:montant, methode_paiement=:methode_paiement, statut=:statut, date_paiement=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->id_commande = htmlspecialchars(strip_tags($this->id_commande));
        $this->montant = htmlspecialchars(strip_tags($this->montant));
        $this->methode_paiement = htmlspecialchars(strip_tags($this->methode_paiement));
        $this->statut = htmlspecialchars(strip_tags($this->statut));

        $stmt->bindParam(":id_commande", $this->id_commande);
        $stmt->bindParam(":montant", $this->montant);
        $stmt->bindParam(":methode_paiement", $this->methode_paiement);
        $stmt->bindParam(":statut", $this->statut);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
