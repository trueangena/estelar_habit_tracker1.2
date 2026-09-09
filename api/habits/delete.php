<?php
// api/habits/delete.php

// 1. CORS no topo!
require_once '../../config/cors.php';
require_once '../../middlewares/AuthMiddleware.php';
require_once '../../controllers/HabitController.php';

// 2. Proteção e ID do utilizador
AuthMiddleware::check();
$user_id = AuthMiddleware::getUserId();

// Aceitamos DELETE ou POST (para facilitar a integração com o Axios)
if ($_SERVER["REQUEST_METHOD"] == "DELETE" || $_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    
    $habit_id = $data->habit_id ?? null;

    if (empty($habit_id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "O ID do hábito é obrigatório."]);
        exit();
    }

    $habitController = new HabitController();
    $response = $habitController->delete($habit_id, $user_id);
    
    http_response_code($response['status'] == 'success' ? 200 : 400);
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
}
?>