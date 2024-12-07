<?php
require_once './config/database.php';
require_once './controllers/PizzaController.php';

$database = new Database();
$db = $database->getConnection();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$pizzaController = new PizzaController($db, $requestMethod);
$pizzaController->processRequest();