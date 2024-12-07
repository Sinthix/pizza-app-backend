<?php
class Pizza {
    private $connection;
    private $table = "pizzas";
    
    public $id;
    public $name;
    public $selling_price;
    public $image_url;

    public function __construct($db) {
        $this->connection = $db;
    }

    public function create() {
        $query = "
            INSERT INTO " . $this->table . " (name, selling_price, image_url)
            VALUES (:name, :selling_price, :image_url);
        ";

        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':selling_price', $this->selling_price);
        $stmt->bindParam(':image_url', $this->image_url);

        return $stmt->execute();
    }

    public function update() {
        $query = "
            UPDATE " . $this->table . "
            SET name = :name, selling_price = :selling_price, image_url = :image_url
            WHERE id = :id;
        ";

        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':selling_price', $this->selling_price);
        $stmt->bindParam(':image_url', $this->image_url);

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
}