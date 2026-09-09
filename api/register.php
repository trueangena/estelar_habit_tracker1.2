<?php
// api/register.php
require_once '../config/cors.php';
require_once '../controllers/AuthController.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $name = $data->name ?? $_POST['name'] ?? '';
    $email = $data->email ?? $_POST['email'] ?? '';
    $password = $data->password ?? $_POST['password'] ?? '';

    $auth = new AuthController();
    $response = $auth->register($name, $email, $password);
    
    http_response_code($response['status'] == 'success' ? 201 : 400);
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
}
?>