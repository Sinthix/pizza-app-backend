<?php
class Ingredient {
    private $connection;
    private $table_name = "ingredients";
    
    public $id;
    public $name;
    public $cost_price;
    public $image_url;
    public $randomization_percentage;

    public function __construct($db) {
        $this->connection = $db;
    }

    // Get all ingredients
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create ingredient
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET name=:name, cost_price=:cost_price, image_url=:image_url, randomization_percentage=:randomization_percentage";
        $stmt = $this->connection->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->cost_price = htmlspecialchars(strip_tags($this->cost_price));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->randomization_percentage = htmlspecialchars(strip_tags($this->randomization_percentage));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":cost_price", $this->cost_price);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":randomization_percentage", $this->randomization_percentage);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete Ingredient
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . "WHERE id = :id";
        $stmt = $this->connection-prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}