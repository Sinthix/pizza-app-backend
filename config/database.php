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
            $this->coonection->exec("set names utf8");
        } catch (PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->connection;
    }
}