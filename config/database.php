<?php
class Database {
    // Configurações do seu banco de dados
    private $host = "localhost";
    private $db_name = "habit_tracker"; // Nome do banco que você vai criar
    private $username = "root";
    private $password = "Feves@34992"; // Insira a senha se houver
    public $conn;

    // Método para obter a conexão
    public function getConnection() {
        $this->conn = null;

        try {
            // Criação da instância PDO
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            
            // Define o modo de erro do PDO para lançar exceções (facilita o debug)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            // Em produção, você logaria esse erro num arquivo, não na tela do usuário
            echo "Erro na conexão: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>