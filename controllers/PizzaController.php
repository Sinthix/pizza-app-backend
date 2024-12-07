<?php
include_once __DIR__ . '/../models/Pizza.php';
include_once __DIR__ . '/../config/database.php';

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
                $this->getPizzas();
                break;
            case 'GET':
                $this->createPizza();
                break;            
            default:
                $this->notFoundResponse();
                break;
        }
    }

    private function getPizzas() {
        $input = json_decode(file_get_contents('php://input'), true);
        $pizza = new Pizza($this->db);

        $pizza->name = $input['name'];
        $pizza->selling_price = $input['selling_price'];
        $pizza->image_url = $input['image_url'];

        if($pizza->create()) {
            echo json_encode(['message' => 'Pizza created']);
        } else {
            echo json_encode(['message' => 'Pizza not created']);
        }
    }

    private function notFoundResponse() {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["message" => "Endpoint not found"]);
    }
}