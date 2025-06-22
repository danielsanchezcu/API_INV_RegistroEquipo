<?php

class Conexion extends PDO {

    private $server = "localhost";
    private $user = "root";
    private $pass = "";    
    private $database = "inventrabd";  
    
    public function __construct() {
        try {
            parent::__construct("mysql:host={$this->server};dbname={$this->database}", $this->user, $this->pass);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }
}
?>
