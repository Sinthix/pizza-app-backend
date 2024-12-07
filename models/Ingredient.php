<?php
class Ingredient {
    private $connection;
    private $table = "ingredients";
    
    public $id;
    public $name;
    public $cost_price;
    public $image_url;
    public $randomization_percentage;

    public function __construct($db) {
        $this->connection = $db;

        if (!$this->connection) {
            throw new Exception("Database connection is not set in Ingredient model.");
        }
    }

    public function create() {
        $query = "
            INSERT INTO " . $this->table . " (name, cost_price, image_url, randomisation_percentage)
            VALUES (:name, :cost_price, :image_url, :randomisation_percentage);
        ";

        $stmt = $this->connection->prepare($query);

        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':cost_price', $this->cost_price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':randomisation_percentage', $this->randomization_percentage);

        return $stmt->execute();
    }

    public function update() {
        $query = "
            UPDATE " . $this->table . "
            SET name = :name, cost_price = :cost_price, image_url = :image_url, randomisation_percentage = :randomisation_percentage
            WHERE id = :id;
        ";

        $stmt = $this->connection->prepare($query);

        // Bind parameters
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':cost_price', $this->cost_price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':randomisation_percentage', $this->randomization_percentage);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "
            DELETE FROM " . $this->table . "
            WHERE id = :id;
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function findAll() {
        $query = "SELECT * FROM " . $this->table . ";";
        $stmt = $this->connection->query($query);

        if (!$stmt) {
            throw new Exception("Query failed: " . $this->connection->errorInfo()[2]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $query = "
            SELECT * FROM " . $this->table . "
            WHERE id = :id;
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findRandomizable() {
        $query = "
            SELECT * FROM " . $this->table . "
            WHERE randomisation_percentage > 0;
        ";

        $stmt = $this->connection->query($query);

        if (!$stmt) {
            throw new Exception("Query failed: " . $this->connection->errorInfo()[2]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}