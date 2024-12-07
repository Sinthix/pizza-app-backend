<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once './config/database.php';
require_once './controllers/PizzaController.php';
require_once './controllers/IngredientController.php';

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