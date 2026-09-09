<?php
require_once(__DIR__ . '/../config/database.php');

class DashboardController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getStats($user_id) {
        // 1. Buscar todos os hábitos ativos do usuário
        $queryHabits = "SELECT id, title, frequency FROM habits WHERE user_id = :user_id AND is_active = 1";
        $stmtHabits = $this->conn->prepare($queryHabits);
        $stmtHabits->bindParam(":user_id", $user_id);
        $stmtHabits->execute();
        $habits = $stmtHabits->fetchAll(PDO::FETCH_ASSOC);

        $dashboardData = [];

        foreach ($habits as $habit) {
            $habitId = $habit['id'];

            // 2. Buscar todas as datas de conclusão deste hábito (ordenado da mais recente para a mais antiga)
            $queryLogs = "SELECT completed_date FROM habit_logs WHERE habit_id = :habit_id ORDER BY completed_date DESC";
            $stmtLogs = $this->conn->prepare($queryLogs);
            $stmtLogs->bindParam(":habit_id", $habitId);
            $stmtLogs->execute();
            
            // Traz apenas um array simples com as datas: ['2023-10-25', '2023-10-24', ...]
            $logs = $stmtLogs->fetchAll(PDO::FETCH_COLUMN); 

            // 3. Processar o algoritmo de sequências (Streaks)
            $stats = $this->calculateStreaks($logs);

            // 4. Calcular a taxa de conclusão dos últimos 30 dias
            $queryRate = "SELECT COUNT(*) FROM habit_logs WHERE habit_id = :habit_id AND completed_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $stmtRate = $this->conn->prepare($queryRate);
            $stmtRate->bindParam(":habit_id", $habitId);
            $stmtRate->execute();
            $completionsLast30Days = $stmtRate->fetchColumn();
            
            // Cálculo simples: (dias cumpridos nos últimos 30 dias / 30) * 100
            $completionRate = round(($completionsLast30Days / 30) * 100);

            // 5. Montar o objeto de resposta para este hábito
            $dashboardData[] = [
                "habit_id" => $habitId,
                "title" => $habit['title'],
                "current_streak" => $stats['current_streak'],
                "best_streak" => $stats['best_streak'],
                "completion_rate_30d" => $completionRate . "%",
                "total_completions" => count($logs)
            ];
        }

        return ["status" => "success", "data" => $dashboardData];
    }

    /**
     * Algoritmo para calcular a Sequência Atual e a Melhor Sequência Histórica
     */
    private function calculateStreaks($logs) {
        if (empty($logs)) {
            return ['current_streak' => 0, 'best_streak' => 0];
        }

        $currentStreak = 0;
        $bestStreak = 0;
        
        $today = new DateTime('today');
        $yesterday = new DateTime('yesterday');
        
        // --- LÓGICA DA SEQUÊNCIA ATUAL ---
        $lastLogDate = new DateTime($logs[0]); // A data mais recente
        
        // Se o último log não foi hoje e nem ontem, a sequência atual já foi quebrada (é 0)
        if ($lastLogDate != $today && $lastLogDate != $yesterday) {
            $currentStreak = 0;
        } else {
            $currentStreak = 1;
            // Percorre o array comparando o dia atual do loop com o próximo
            for ($i = 0; $i < count($logs) - 1; $i++) {
                $date1 = new DateTime($logs[$i]);
                $date2 = new DateTime($logs[$i+1]);
                $interval = $date1->diff($date2)->days;

                if ($interval == 1) { // Se a diferença for de exatamente 1 dia
                    $currentStreak++;
                } else {
                    break; // Buraco encontrado, a sequência atual para aqui
                }
            }
        }

        // --- LÓGICA DA MELHOR SEQUÊNCIA (Recorde) ---
        $tempStreak = 1;
        for ($i = 0; $i < count($logs) - 1; $i++) {
            $date1 = new DateTime($logs[$i]);
            $date2 = new DateTime($logs[$i+1]);
            $interval = $date1->diff($date2)->days;

            if ($interval == 1) {
                $tempStreak++;
            } else {
                if ($tempStreak > $bestStreak) {
                    $bestStreak = $tempStreak;
                }
                $tempStreak = 1; // Reseta o contador temporário após o buraco
            }
        }
        // Verifica uma última vez ao final do loop
        if ($tempStreak > $bestStreak) {
            $bestStreak = $tempStreak;
        }

        // O recorde não pode ser menor que a sequência atual
        if ($currentStreak > $bestStreak) {
            $bestStreak = $currentStreak;
        }

        return ['current_streak' => $currentStreak, 'best_streak' => $bestStreak];
    }
}
?>