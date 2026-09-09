<?php
require_once(__DIR__ . '/../config/database.php');

class HabitController {
    private $conn;
    private $table_name = "habits";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // 1. CREATE: Criar um novo hábito
    public function create($user_id, $title, $description, $frequency) {
        if (empty($title) || empty($frequency)) {
            return ["status" => "error", "message" => "Título e frequência são obrigatórios."];
        }

        $query = "INSERT INTO " . $this->table_name . " (user_id, title, description, frequency) VALUES (:user_id, :title, :description, :frequency)";
        $stmt = $this->conn->prepare($query);

        // Higienização
        $title = htmlspecialchars(strip_tags($title));
        $description = htmlspecialchars(strip_tags($description));
        $frequency = htmlspecialchars(strip_tags($frequency));

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":frequency", $frequency);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Hábito criado com sucesso!"];
        }
        return ["status" => "error", "message" => "Erro ao criar o hábito."];
    }

    // 2. READ: Listar todos os hábitos ATIVOS do usuário logado
    public function readAll($user_id) {
        // RN06: is_active = 1 garante que não vamos trazer hábitos "deletados"
        $query = "SELECT id, title, description, frequency, created_at FROM " . $this->table_name . " WHERE user_id = :user_id AND is_active = 1 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $habits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ["status" => "success", "data" => $habits];
    }

    // 3. UPDATE: Atualizar um hábito
    public function update($id, $user_id, $title, $description, $frequency) {
        // A cláusula user_id = :user_id garante que um usuário não altere o hábito de outro
        $query = "UPDATE " . $this->table_name . " SET title = :title, description = :description, frequency = :frequency WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $title = htmlspecialchars(strip_tags($title));
        $description = htmlspecialchars(strip_tags($description));
        $frequency = htmlspecialchars(strip_tags($frequency));

        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":frequency", $frequency);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);

        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return ["status" => "success", "message" => "Hábito atualizado!"];
        }
        return ["status" => "error", "message" => "Hábito não encontrado ou nenhuma alteração realizada."];
    }

    // 4. DELETE: Soft Delete (RN06)
    public function delete($id, $user_id) {
        // Em vez de DELETE FROM, fazemos um UPDATE mudando o is_active para 0
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);

        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return ["status" => "success", "message" => "Hábito excluído com sucesso."];
        }
        return ["status" => "error", "message" => "Não foi possível excluir o hábito."];
    }
}
?>