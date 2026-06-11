<?php
// backend/models/Restaurant.php

class Restaurant {
    private $conn;
    private $table_name = "restaurant";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer tous les restaurants
    public function lireTous() {
        $query = "SELECT id_restaurant, nom, adresse, telephone, image_url FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>