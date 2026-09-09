<?php
// api/dashboard/stats.php
require_once '../../config/cors.php';
require_once '../../middlewares/AuthMiddleware.php';
require_once '../../controllers/DashboardController.php';

// Bloqueia quem não está logado
AuthMiddleware::check();
$user_id = AuthMiddleware::getUserId();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $dashboardController = new DashboardController();
    $response = $dashboardController->getStats($user_id);
    
    http_response_code(200);
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
}
?>