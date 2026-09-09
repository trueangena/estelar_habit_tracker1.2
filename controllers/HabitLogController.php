<?php
require_once(__DIR__ . '/../config/database.php');

class HabitLogController {
    private $conn;
    private $table_logs = "habit_logs";
    private $table_habits = "habits";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function logHabit($user_id, $habit_id, $completed_date = null) {
        // Se a data não for fornecida, assume o dia de hoje
        if (!$completed_date) {
            $completed_date = date('Y-m-d');
        }

        // 1. Verificar se o hábito pertence ao utilizador e se está ativo
        $queryCheck = "SELECT id FROM " . $this->table_habits . " WHERE id = :habit_id AND user_id = :user_id AND is_active = 1 LIMIT 1";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(":habit_id", $habit_id);
        $stmtCheck->bindParam(":user_id", $user_id);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() === 0) {
            return ["status" => "error", "message" => "Hábito não encontrado ou não pertence a este utilizador."];
        }

        // 2. Tentar inserir o log
        try {
            $queryInsert = "INSERT INTO " . $this->table_logs . " (habit_id, completed_date) VALUES (:habit_id, :completed_date)";
            $stmtInsert = $this->conn->prepare($queryInsert);
            
            $stmtInsert->bindParam(":habit_id", $habit_id);
            $stmtInsert->bindParam(":completed_date", $completed_date);
            
            $stmtInsert->execute();
            
            return ["status" => "success", "message" => "Hábito marcado como concluído!"];
            
        } catch (PDOException $e) {
            // O código 23000 do PDO significa "Integrity constraint violation" (Violação de chave única)
            if ($e->getCode() == 23000) {
                return ["status" => "error", "message" => "Este hábito já foi marcado como concluído no dia de hoje."];
            }
            // Para outros erros de base de dados
            return ["status" => "error", "message" => "Erro ao registar o hábito: " . $e->getMessage()];
        }
    }
}
?>