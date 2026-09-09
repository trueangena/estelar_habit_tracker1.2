<?php
require_once '../../config/cors.php';
require_once '../../middlewares/AuthMiddleware.php';
require_once '../../controllers/HabitController.php';


// 1. Protege a rota e pega o ID do usuário logado
AuthMiddleware::check();
$user_id = AuthMiddleware::getUserId();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $title = $data->title ?? $_POST['title'] ?? '';
    $description = $data->description ?? $_POST['description'] ?? '';
    $frequency = $data->frequency ?? $_POST['frequency'] ?? 'daily'; // daily por padrão

    $habitController = new HabitController();
    $response = $habitController->create($user_id, $title, $description, $frequency);
    
    http_response_code($response['status'] == 'success' ? 201 : 400);
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
}
?>