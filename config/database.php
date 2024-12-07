<?php
class Database {
    private $host = "localhost";
    private $db_name = "pizza_management";
    private $username = "root";
    private $password = "";
    private $connection;

    public function getConnection() {
        $this->connection = null;
        
        try{
            $this->connection = new PDO("mysql:host=" . $this->host . ";dbname=" . $this.db_name, $this->username, $this->password);
            $this->coonection->setAttribute(PDO::ATTER_ERRMODE, PDO::ERRMODE_EXECPTION);
        } catch (PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->connection;
    }
}