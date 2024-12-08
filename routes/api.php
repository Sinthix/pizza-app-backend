<?php
header('Access-Control-Allow-Origin: http://localhost:4200');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/PizzaController.php';
require_once __DIR__ . '/../controllers/IngredientController.php';

// Db connection
$database = new Database();
$db = $database->getConnection();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = $_SERVER["REQUEST_URI"];

// Route requests based on URI
if(strpos($requestUri, '/ingredients') !== false) {
    $controller = new IngredientController($db, $requestMethod);
} elseif(strpos($requestUri, '/pizzas') !== false) {
    $controller = new PizzaController($db, $requestMethod);
} else {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(["message" => "Endpoint not found."]);
    exit();
}

// Process the request
$controller->processRequest();