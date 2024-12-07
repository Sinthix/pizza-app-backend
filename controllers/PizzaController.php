<?php
require_once __DIR__ . '/../models/Pizza.php';
require_once __DIR__ . '/../config/database.php';

class PizzaController {
    private $db;
    private $pizzaModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pizzaModel = new Pizza($this->db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'all') {
                        $this->getAllPizzas();
                    } elseif ($action === 'single' && isset($_GET['id'])) {
                        $this->getPizzaById((int)$_GET['id']);
                    } else {
                        http_response_code(400);
                        echo json_encode(["message" => "Invalid GET request"]);
                    }
                    break;
                case 'POST':
                    $this->createPizza();
                    break;
                case 'PUT':
                    $this->updatePizza();
                    break;
                case 'DELETE':
                    $this->deletePizza();
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(["message" => "Method not allowed"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Server error", "error" => $e->getMessage()]);
        }
    }

    private function getAllPizzas() {
        $pizzas = $this->pizzaModel->findAll();
        echo json_encode($pizzas);
    }

    private function getPizzaById($id) {
        $pizza = $this->pizzaModel->find($id);
        if ($pizza) {
            echo json_encode($pizza);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Pizza not found"]);
        }
    }

    private function createPizza() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$this->validatePizzaInput($input)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid input"]);
            return;
        }

        $this->pizzaModel->name = $input['name'];
        $this->pizzaModel->selling_price = $input['selling_price'];
        $this->pizzaModel->image_url = $input['image_url'];

        if ($this->pizzaModel->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Pizza created"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create pizza"]);
        }
    }

    private function updatePizza() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['id']) || !$this->validatePizzaInput($input)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid input"]);
            return;
        }

        $this->pizzaModel->id = (int)$input['id'];
        $this->pizzaModel->name = $input['name'];
        $this->pizzaModel->selling_price = $input['selling_price'];
        $this->pizzaModel->image_url = $input['image_url'];

        if ($this->pizzaModel->update()) {
            echo json_encode(["message" => "Pizza updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update pizza"]);
        }
    }

    private function deletePizza() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid input"]);
            return;
        }

        if ($this->pizzaModel->delete((int)$input['id'])) {
            echo json_encode(["message" => "Pizza deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete pizza"]);
        }
    }

    private function validatePizzaInput($input) {
        return isset($input['name']) && strlen($input['name']) >= 2 && strlen($input['name']) <= 50 &&
            !preg_match('/[\{\}\[\]"\!\.]/', $input['name']) &&
            isset($input['selling_price']) && is_numeric($input['selling_price']) &&
            isset($input['image_url']);
    }
}