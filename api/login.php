<?php
// api/login.php
require_once '../config/cors.php';
require_once '../controllers/AuthController.php';

// Permite requisições de outras origens (CORS - Importante se o Front e Back estiverem em portas diferentes)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $email = $data->email ?? $_POST['email'] ?? '';
    $password = $data->password ?? $_POST['password'] ?? '';

    $auth = new AuthController();
    $response = $auth->login($email, $password);
    
    // 200 OK para sucesso, 401 Unauthorized para erro de credenciais
    http_response_code($response['status'] == 'success' ? 200 : 401);
    echo json_encode($response);
} else {
    // Método não permitido
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
}
?>