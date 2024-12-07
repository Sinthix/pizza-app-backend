<?php
include_once '../models/Ingredient.php';

class IngredientController {
    private $db;
    private $requestMethod;

    public function __construct($db, $requestMethod) {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                $this->getIngredients();
                break;
            case 'POST':
                $this->createIngredient();
                break;
            case 'DELETE':
                $this->deleteIngredient();
                break;
            default:
                $this->notFoundResponse();
                break;
        }
    }

    public function getIngredients() {
        $ingredient = new Ingredient($this->db);
        $result = $ingredient->readAll();
        $ingredients = $result->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($ingredients);
    }

    private function createIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);
        $ingredient = new Ingredient($this->db);

        $ingredient->name = $input['name'];
        $ingredient->cost_price = $input['cost_price'];
        $ingredient->image_url = $input['image_url'];
        $ingredient->randomization_percentage = $input['randomization_percentage'];

        if($ingredient->create()) {
            echo json_encode(['message' => 'Ingredient created']);
        } else {
            echo json_encode(['message' => 'Ingredient not created']);
        }
    }

    private function deleteIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);
        $ingredient = new Ingredient($this->db);

        $ingredient->id = $input['id'];

        if($ingredient->delete()) {
            echo json_encode(['message' => 'Ingredient deleted']);
        } else {
            echo json_encode(['message' => 'Ingredient not deleted']);
        }
    }

    private function notFoundResponse() {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(['message' => 'Endpoint not found']);
    }
}