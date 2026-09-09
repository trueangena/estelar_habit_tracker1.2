<?php
// api/habits/complete.php
require_once '../../config/cors.php';
require_once '../../middlewares/AuthMiddleware.php';
require_once '../../controllers/HabitLogController.php';

// Protege a rota e obtém o ID do utilizador logado
AuthMiddleware::check();
$user_id = AuthMiddleware::getUserId();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $habit_id = $data->habit_id ?? $_POST['habit_id'] ?? null;
    $completed_date = $data->completed_date ?? $_POST['completed_date'] ?? date('Y-m-d');

    if (empty($habit_id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "O ID do hábito é obrigatório."]);
        exit();
    }

    $logController = new HabitLogController();
    $response = $logController->logHabit($user_id, $habit_id, $completed_date);
    
    // Devolve 201 (Created) se for sucesso, ou 400 (Bad Request) se já existir/falhar
    http_response_code($response['status'] == 'success' ? 201 : 400);
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
}
?>