<?php

class IngredientController {
    private $db;
    private $ingredient;

    public function __construct() {
        include_once '../config/Database.php';
        include_once '../models/Ingredient.php';

        $database = new Database();
        $this->db = $database->getConnection();

        $this->ingredient = new Ingredient($this->db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];
        
        if (strpos($path, '/ingredients') !== false) {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $this->getIngredient($_GET['id']);
                    } else {
                        $this->getAllIngredients();
                    }
                    break;
                case 'POST':
                    $this->createIngredient();
                    break;
                case 'PUT':
                    $this->updateIngredient();
                    break;
                case 'DELETE':
                    $this->deleteIngredient();
                    break;
                default:
                    $this->sendResponse(405, ['error' => 'Method not allowed']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Endpoint not found']);
        }
    }

    private function getAllIngredients() {
        try {
            $ingredients = $this->ingredient->findAll();
            $this->sendResponse(200, $ingredients);
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    private function getIngredient($id) {
        try {
            $ingredient = $this->ingredient->find($id);
            if ($ingredient) {
                $this->sendResponse(200, $ingredient);
            } else {
                $this->sendResponse(404, ['error' => 'Ingredient not found']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    private function createIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);

        if ($this->validateIngredientInput($input)) {
            $this->ingredient->name = $input['name'];
            $this->ingredient->cost_price = $input['cost_price'];
            $this->ingredient->image_url = $input['image_url'];
            $this->ingredient->randomization_percentage = $input['randomization_percentage'];

            try {
                if ($this->ingredient->create()) {
                    $this->sendResponse(201, ['message' => 'Ingredient created successfully']);
                } else {
                    $this->sendResponse(500, ['error' => 'Failed to create ingredient']);
                }
            } catch (Exception $e) {
                $this->sendResponse(500, ['error' => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(400, ['error' => 'Invalid input data']);
        }
    }

    private function updateIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);

        if ($this->validateIngredientInput($input) && isset($input['id'])) {
            $this->ingredient->id = $input['id'];
            $this->ingredient->name = $input['name'];
            $this->ingredient->cost_price = $input['cost_price'];
            $this->ingredient->image_url = $input['image_url'];
            $this->ingredient->randomization_percentage = $input['randomization_percentage'];

            try {
                if ($this->ingredient->update()) {
                    $this->sendResponse(200, ['message' => 'Ingredient updated successfully']);
                } else {
                    $this->sendResponse(500, ['error' => 'Failed to update ingredient']);
                }
            } catch (Exception $e) {
                $this->sendResponse(500, ['error' => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(400, ['error' => 'Invalid input data']);
        }
    }

    private function deleteIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['id'])) {
            try {
                if ($this->ingredient->delete($input['id'])) {
                    $this->sendResponse(200, ['message' => 'Ingredient deleted successfully']);
                } else {
                    $this->sendResponse(500, ['error' => 'Failed to delete ingredient']);
                }
            } catch (Exception $e) {
                $this->sendResponse(500, ['error' => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(400, ['error' => 'Invalid input data']);
        }
    }

    private function validateIngredientInput($input) {
        if (
            !isset($input['name']) ||
            strlen($input['name']) < 2 ||
            strlen($input['name']) > 30 ||
            preg_match('/[\{\}\[\]"\!\.]/', $input['name']) ||
            !isset($input['cost_price']) ||
            !is_numeric($input['cost_price']) ||
            !isset($input['randomization_percentage']) ||
            $input['randomization_percentage'] < 0 ||
            $input['randomization_percentage'] > 100
        ) {
            return false;
        }
        return true;
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}