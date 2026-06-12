<?php
/**
 * Modèle Utilisateur
 * Couche d'accès aux données (Data Access Layer)
 */
class Utilisateur {
    private $conn;
    private $table_name = "utilisateurs"; // Nom de la table inféré

    // Propriétés de l'utilisateur
    public $id_utilisateur;
    public $nom;
    public $email;
    public $mot_de_passe;
    public $date_inscription;

    /**
     * Constructeur avec $db en tant que connexion à la base de données
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Inscription d'un nouvel utilisateur
     * @return boolean Succès ou échec de l'insertion
     */
    public function inscription() {
        // Requête d'insertion
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nom = :nom, email = :email, mot_de_passe = :mot_de_passe";

        // Préparation de la requête
        $stmt = $this->conn->prepare($query);

        // Nettoyage des données pour éviter les failles XSS
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        // Le mot de passe est déjà haché dans le contrôleur (API), on le bind direct
        
        // Liaison des valeurs
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":mot_de_passe", $this->mot_de_passe);

        // Exécution de la requête
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Vérifier si un email existe (utilisé pour le login et pour éviter les doublons à l'inscription)
     * @return boolean
     */
    public function emailExiste() {
        // Requête pour vérifier l'email
        $query = "SELECT id_utilisateur, nom, mot_de_passe 
                  FROM " . $this->table_name . " 
                  WHERE email = ? 
                  LIMIT 0,1";

        // Préparation et exécution
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        // Si l'email existe, on assigne les valeurs aux propriétés de l'objet pour un accès facile
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->id_utilisateur = $row['id_utilisateur'];
            $this->nom = $row['nom'];
            $this->mot_de_passe = $row['mot_de_passe']; // Le hash stocké en base
            return true;
        }
        return false;
    }

    /**
     * Obtenir le profil d'un utilisateur par son ID
     * @return array|boolean Les données de l'utilisateur ou false
     */
    public function getProfil() {
        $query = "SELECT id_utilisateur, nom, email, date_inscription 
                  FROM " . $this->table_name . " 
                  WHERE id_utilisateur = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_utilisateur);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
}
?>
