<?php
class Pizza {
    private $connection;
    private $table_name = "pizzas";

    public $id;
    public $name;
    public $selling_price;
    public $image_url;

    public function __construct($db) {
        $this->connection = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSER INTO " . $this->table_name . " SET name=:name, selling_price=:selling_price, image_url=:image_url";
        $stmt = $this->connection->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->selling_price = htmlspecialchars(strip_tags($this->selling_price));
        $this->image_utl = htmlspecialchars(strip_tags($this->image_url));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":selling_price", $this->selling_price);
        $stmt->bindParam(":image_url", $this->image_url);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}