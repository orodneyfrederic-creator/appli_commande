<?php
/**
 * Modèle Commande
 * Couche d'accès aux données pour les commandes (avec transaction PDO)
 */
class Commande {
    private $conn;
    private $table_commande = "commandes";
    private $table_lignes = "lignes_commande";

    public $id_commande;
    public $id_utilisateur;
    public $id_restaurant;
    public $montant_total;
    public $statut;
    
    // Pour la création de la commande
    public $lignes_panier = []; // Tableau associatif contenant id_plat, quantite, prix_unitaire

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Création d'une commande et de ses lignes en utilisant une TRANSACTION PDO
     * L'utilisation de transactions garantit que si une ligne échoue, la commande n'est pas insérée (intégrité).
     * @return boolean
     */
    public function creerCommande() {
        try {
            // Début de la transaction
            $this->conn->beginTransaction();

            // 1. Insertion de la commande principale
            $query_cmd = "INSERT INTO " . $this->table_commande . " 
                          SET id_utilisateur=:id_utilisateur, id_restaurant=:id_restaurant, montant_total=:montant_total, statut=:statut, date_commande=NOW()";
            
            $stmt_cmd = $this->conn->prepare($query_cmd);

            // Nettoyage
            $this->id_utilisateur = htmlspecialchars(strip_tags($this->id_utilisateur));
            $this->id_restaurant = htmlspecialchars(strip_tags($this->id_restaurant));
            $this->montant_total = htmlspecialchars(strip_tags($this->montant_total));
            $this->statut = htmlspecialchars(strip_tags($this->statut)); // ex: 'en_attente'

            $stmt_cmd->bindParam(":id_utilisateur", $this->id_utilisateur);
            $stmt_cmd->bindParam(":id_restaurant", $this->id_restaurant);
            $stmt_cmd->bindParam(":montant_total", $this->montant_total);
            $stmt_cmd->bindParam(":statut", $this->statut);

            $stmt_cmd->execute();
            
            // Récupérer l'ID de la commande tout juste insérée
            $this->id_commande = $this->conn->lastInsertId();

            // 2. Insertion des lignes de la commande
            $query_ligne = "INSERT INTO " . $this->table_lignes . " 
                            SET id_commande=:id_commande, id_plat=:id_plat, quantite=:quantite, prix_unitaire=:prix_unitaire";
            
            $stmt_ligne = $this->conn->prepare($query_ligne);

            foreach($this->lignes_panier as $ligne) {
                $id_plat = htmlspecialchars(strip_tags($ligne['id_plat']));
                $quantite = htmlspecialchars(strip_tags($ligne['quantite']));
                $prix_unitaire = htmlspecialchars(strip_tags($ligne['prix_unitaire']));

                $stmt_ligne->bindParam(":id_commande", $this->id_commande);
                $stmt_ligne->bindParam(":id_plat", $id_plat);
                $stmt_ligne->bindParam(":quantite", $quantite);
                $stmt_ligne->bindParam(":prix_unitaire", $prix_unitaire);

                $stmt_ligne->execute();
            }

            // Si tout s'est bien passé, on valide la transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // En cas d'erreur (exception), on annule toutes les modifications depuis beginTransaction()
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Récupérer l'historique des commandes d'un utilisateur
     * @return PDOStatement
     */
    public function getHistoriqueUtilisateur() {
        $query = "SELECT c.id_commande, c.id_restaurant, c.montant_total, c.statut, c.date_commande, r.nom as restaurant_nom
                  FROM " . $this->table_commande . " c
                  LEFT JOIN restaurants r ON c.id_restaurant = r.id_restaurant
                  WHERE c.id_utilisateur = :id_utilisateur 
                  ORDER BY c.date_commande DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_utilisateur", $this->id_utilisateur);
        $stmt->execute();

        return $stmt;
    }
}
?>
