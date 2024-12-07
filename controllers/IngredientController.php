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
                    $response = $this->getAllIngredients();
                }
                break;
            case 'POST':
                $response = $this->createIngredient();
                break;
            case 'PUT':
                $response = $this->updateIngredient();
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

        $sanitizedInput = $this->sanitizeInput($input);

        $ingredient = new Ingredient($this->db);
        $ingredient->name = $sanitizedInput['name'];
        $ingredient->cost_price = $sanitizedInput['cost_price'];
        $ingredient->image_url = $sanitizedInput['image_url'];
        $ingredient->randomisation_percentage = $sanitizedInput['randomisation_percentage'];

        $ingredient->create();
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Ingredient created']);
        return $response;
    }

    private function updateIngredient() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateIngredientInput($input) || !isset($input['id'])) {
            return $this->unprocessableEntityResponse();
        }

        $sanitizedInput = $this->sanitizeInput($input);

        $ingredient = new Ingredient($this->db);
        $ingredient->id = (int)$input['id'];
        $ingredient->name = $sanitizedInput['name'];
        $ingredient->cost_price = $sanitizedInput['cost_price'];
        $ingredient->image_url = $sanitizedInput['image_url'];
        $ingredient->randomisation_percentage = $sanitizedInput['randomisation_percentage'];

        $ingredient->update();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Ingredient updated']);
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
            strlen($input['name']) < 2 ||
            strlen($input['name']) > 30 ||
            preg_match('/[\{\}\[\]"\!\.]/', $input['name']) ||
            !isset($input['cost_price']) ||
            !is_numeric($input['cost_price']) ||
            !isset($input['randomisation_percentage']) ||
            $input['randomisation_percentage'] < 0 ||
            $input['randomisation_percentage'] > 100
        ) {
            return false;
        }
        return true;
    }

    private function sanitizeInput($input) {
        $sanitized = [];
        $sanitized['name'] = htmlspecialchars(strip_tags($input['name'] ?? ''));
        $sanitized['cost_price'] = filter_var($input['cost_price'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $sanitized['image_url'] = htmlspecialchars(strip_tags($input['image_url'] ?? ''));
        $sanitized['randomisation_percentage'] = filter_var($input['randomisation_percentage'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        return $sanitized;
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