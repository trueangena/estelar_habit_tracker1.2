<?php
require_once '../../config/cors.php';
require_once '../../middlewares/AuthMiddleware.php';
require_once '../../controllers/HabitController.php';


// 1. Protege a rota e pega o ID do usuário logado
AuthMiddleware::check();
$user_id = AuthMiddleware::getUserId();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $habitController = new HabitController();
    $response = $habitController->readAll($user_id);
    
    http_response_code(200); // OK
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
}
?>