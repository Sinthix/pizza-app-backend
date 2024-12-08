<?php


class PizzaController {
    private $db;
    private $pizzaModel;

    public function __construct() {
        include_once __DIR__ . '/../models/Pizza.php';
        include_once __DIR__ . '/../config/database.php';

        $database = new Database();
        $this->db = $database->getConnection();

        $this->pizzaModel = new Pizza($this->db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];

        if(strpos($path, '/pizzas') !== false) {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $this->getPizzaById($_GET['id']);
                    } else {
                        $this->getAllPizzas();
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
                    $this->sendResponse(405, ['error' => 'Method not allowed']);
            }
        } else {
            $this->sendResponse(404, ['error' => 'Endpoint not found']);
        }
    }

    private function getAllPizzas() {
        $pizzas = $this->pizzaModel->findAll();
        $this->sendResponse(200, $pizzas);
    }

    private function getPizzaById($id) {
        try {
            $pizza = $this->pizzaModel->find($id);
            if ($pizza) {
                $this->sendResponse(200, $pizza);
            } else {
                $this->sendResponse(404, ['error' => 'Ingredient not found']);
            }
        }catch (Exception $e) {
            $this->sendResponse(500, ['error' => $e->getMessage()]);
        }
    }

    private function createPizza() {
        $input = json_decode(file_get_contents("php://input"), true);

        if ($this->validatePizzaInput($input)) {
            $this->pizzaModel->name = $input['name'];
            $this->pizzaModel->selling_price = $input['selling_price'];
            $this->pizzaModel->image_url = $input['image_url'];

            try {
                if ($this->pizzaModel->create()) {
                    $this->sendResponse(201, ['message' => 'Pizza created successfully']);
                } else {
                    $this->sendResponse(500, ['error' => 'Failed to create pizza']);
                }
            } catch (Exception $e) {
                $this->sendResponse(500, ['error' => $e->getMessage()]);
            }
        }else {
            $this->sendResponse(400, ['error' => 'Invalid input data']);
        }

    }

    private function updatePizza() {
        $input = json_decode(file_get_contents("php://input"), true);

        if ($this->validatePizzaInput($input) && isset($input['id'])) {
            $this->pizzaModel->id = (int)$input['id'];
            $this->pizzaModel->name = $input['name'];
            $this->pizzaModel->selling_price = $input['selling_price'];
            $this->pizzaModel->image_url = $input['image_url'];

            try {
                if ($this->pizzaModel->update()) {
                    $this->sendResponse(200, ['message' => 'Pizza updated successfully']);
                } else {
                    $this->sendResponse(500, ['error' => 'Failed to update pizza']);
                }
            } catch (Exception $e) {
                $this->sendResponse(500, ['error' => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(400, ['error' => 'Invalid input data']);
        }

        

      
    }

    private function deletePizza() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['id'])) {
            try {
                if ($this->pizzaModel->delete($input['id'])) {
                    $this->sendResponse(200, ['message' => 'Pizza deleted successfully']);
                } else {
                    $this->sendResponse(500, ['error' => 'Failed to delete pizza']);
                }
            } catch (Exception $e) {
                $this->sendResponse(500, ['error' => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(400, ['error' => 'Invalid input data']);
        }
    }

    private function validatePizzaInput($input) {
        return isset($input['name']) && strlen($input['name']) >= 2 && strlen($input['name']) <= 50 &&
            !preg_match('/[\{\}\[\]"\!\.]/', $input['name']) &&
            isset($input['selling_price']) && is_numeric($input['selling_price']) &&
            isset($input['image_url']);
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}