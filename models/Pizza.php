<?php
class Pizza {
    private $connection;
    private $table_name = "pizzas";

    public $id;
    public $name;
    public $selling_price;
    public $image_url;
    public $ingredients;

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

        if ($stmt->execute()) {
            $this->id = $this->connection->lastInsertId();

            if (!empty($this->ingredients)) {
                $this->addIngredients($this->id, $this->ingredients);
            }
            return true;
        }

        return false;
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

        if ($stmt->execute()) {
            $this->removeIngredients($this->id);
            if (!empty($this->ingredients)) {
                $this->addIngredients($this->id, $this->ingredients);
            }
            return true;
        }

        return false;
    }

    public function delete($id) {
        $this->removeIngredients($id);

        $query = "
            DELETE FROM " . $this->table . "
            WHERE id = :id;
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function findAll() {
        $query = "
            SELECT p.*, GROUP_CONCAT(pi.ingredient_id) AS ingredients
            FROM " . $this->table . " p
            LEFT JOIN pizza_ingredients pi ON p.id = pi.pizza_id
            GROUP BY p.id;
        ";
        $stmt = $this->connection->query($query);

        $pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($pizzas as &$pizza) {
            $pizza['ingredients'] = $pizza['ingredients'] ? explode(',', $pizza['ingredients']) : [];
        }
        return $pizzas;
    }

    public function find($id) {
        $query = "
            SELECT p.*, GROUP_CONCAT(pi.ingredient_id) AS ingredients
            FROM " . $this->table . " p
            LEFT JOIN pizza_ingredients pi ON p.id = pi.pizza_id
            WHERE p.id = :id
            GROUP BY p.id;
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pizza = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pizza) {
            $pizza['ingredients'] = $pizza['ingredients'] ? explode(',', $pizza['ingredients']) : [];
        }
        return $pizza;
    }

    private function addIngredients($pizzaId, $ingredients) {
        $query = "
            INSERT INTO pizza_ingredients (pizza_id, ingredient_id)
            VALUES (:pizza_id, :ingredient_id);
        ";
        $stmt = $this->connection->prepare($query);

        foreach ($ingredients as $ingredientId) {
            $stmt->bindParam(':pizza_id', $pizzaId, PDO::PARAM_INT);
            $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    private function removeIngredients($pizzaId) {
        $query = "
            DELETE FROM pizza_ingredients
            WHERE pizza_id = :pizza_id;
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':pizza_id', $pizzaId, PDO::PARAM_INT);
        $stmt->execute();
    }
}