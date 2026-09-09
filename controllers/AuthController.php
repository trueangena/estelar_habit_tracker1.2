<?php
require_once '../config/database.php';

class AuthController {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Método de Cadastro
    public function register($name, $email, $password) {
        if (empty($name) || empty($email) || empty($password)) {
            return ["status" => "error", "message" => "Todos os campos são obrigatórios."];
        }

        $queryCheck = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(":email", $email);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            return ["status" => "error", "message" => "Este e-mail já está em uso."];
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $queryInsert = "INSERT INTO " . $this->table_name . " (name, email, password_hash) VALUES (:name, :email, :password_hash)";
        $stmtInsert = $this->conn->prepare($queryInsert);

        $name = htmlspecialchars(strip_tags($name));
        $email = htmlspecialchars(strip_tags($email));

        $stmtInsert->bindParam(":name", $name);
        $stmtInsert->bindParam(":email", $email);
        $stmtInsert->bindParam(":password_hash", $password_hash);

        if ($stmtInsert->execute()) {
            return ["status" => "success", "message" => "Conta criada com sucesso!"];
        }

        return ["status" => "error", "message" => "Erro interno ao criar a conta."];
    }

    // Método de Login
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ["status" => "error", "message" => "E-mail e senha são obrigatórios."];
        }

        $query = "SELECT id, name, password_hash FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password_hash'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                return [
                    "status" => "success", 
                    "message" => "Login realizado com sucesso!",
                    "user" => [
                        "id" => $user['id'],
                        "name" => $user['name']
                    ]
                ];
            }
        }
        return ["status" => "error", "message" => "Credenciais inválidas."];
    }
}
?>