<?php
include_once __DIR__ . '../models/Ingredient.php';

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
                if(isset($_GET['id'])) {
                    $response = $this-getIngredient($_GET['id']);
                } else {
                    $this->getAllIngredients();
                }
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

        header($response['status_code_header']);
        if($response['body']) {
            echo $response['body'];
        }
    }

    public function getAllIngredients() {
        $ingredient = new Ingredient($this->db);
        $result = $ingredient->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getIngredient($id) {
        $ingredient = new Ingredient($this->db);
        $result = $ingredient->find($id);

        if(!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);
        if(!$this->validateIngredientInput($input)) {
            return $this->unprocessableEntityResponse();
        }

        $ingredient = new Ingredient($this->db);
        $ingredient->name = $input['name'];
        $ingredient->cost_price = $input['cost_price'];
        $ingredient->image_url = $input['image_url'];
        $ingredient->randomization_percentage = $input['randomization_percentage'];

        $ingredient->create();
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Ingredient created']);
        return $response;
    }

    private function deleteIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!isset($input['id'])) {
            return $this->unprocessableEntityResponse();
        }


        $ingredient = new Ingredient($this->db);
        $ingredient->delete($input['id']);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Ingredient deleted']);
        return $response;
    }

    private function validateIngredientInput($input) {
        if(
            !isset($input['name']) ||
            !isset($input['cost_price']) ||
            !isset($input['randomization_percentage']) ||
            !is_numeric($input['cost_price']) ||
            !is_numeric($input['randomization_percentage'])
        ) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse() {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(['message' => 'Invalid input']);
        return $response;
    }

    private function notFoundResponse() {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode(['message' => 'Not iFound']);
        return $response;
    }
}