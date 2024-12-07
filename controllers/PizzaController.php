<?php
include_once __DIR__ . '/../models/Pizza.php';

class PizzaController {
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
                    $response = $this-getPizza($_GET['id']);
                } else {
                    $response = $this->getAllPizzas();
                }
                break;
            case 'POST':
                $response = $this->createPizza();
                break;
            case 'PUT':
                $response = $this->updatePizza();
            case 'DELETE':
                $response = $this->deletePizza();
            default:
                $response = $this->notFoundResponse();
                break;
        }
        
        header($response['status_code_header']);
        if($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllPizzas() {
        $pizza = new Pizza($this->db);
        $result = $pizza->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getPizza($id) {
        $pizza = new Pizza($this->db);
        $result = $pizza->find($id);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createPizza() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$this->validatePizzaInput($input)) {
            return $this->unprocessableEntityResponse();
        }

        $pizza = new Pizza($this->db);
        $pizza->name = $input['name'];
        $pizza->selling_price = $input['selling_price'];
        $pizza->image_url = $input['image_url'];
        $pizza->ingredients = $input['ingredients'];

        $pizza->create();
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Pizza created']);
        return $response;
    }

    private function updatePizza() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$this->validatePizzaInput($input) || !isset($input['id'])) {
            return $this->unprocessableEntityResponse();
        }

        $pizza = new Pizza($this->db);
        $pizza->id = $input['id'];
        $pizza->name = $input['name'];
        $pizza->selling_price = $input['selling_price'];
        $pizza->image_url = $input['image_url'];
        $pizza->ingredients = $input['ingredients'];

        $pizza->update();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Pizza updated']);
        return $response;
    }

    private function deletePizza() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id'])) {
            return $this->unprocessableEntityResponse();
        }

        $pizza = new Pizza($this->db);
        $pizza->delete($input['id']);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Pizza deleted']);
        return $response;
    }

    private function validatePizzaInput($input) {
        if (
            !isset($input['name']) ||
            !isset($input['selling_price']) ||
            !is_numeric($input['selling_price']) ||
            !isset($input['ingredients']) ||
            !is_array($input['ingredients'])
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
        $response['body'] = json_encode(['message' => 'Not Found']);
        return $response;
    }
}