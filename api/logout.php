<?php
// api/logout.php

// 1. O CORS TEM de ser a primeira coisa, sempre!
require_once '../config/cors.php';

// 2. Inicia a sessão (para o PHP saber qual sessão ele deve destruir)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Limpa todas as variáveis da sessão da memória
$_SESSION = array();

// 4. Invalida o cookie de sessão no navegador do utilizador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Finalmente, destrói a sessão no servidor
session_destroy();

// 6. Retorna a resposta de sucesso
http_response_code(200);
echo json_encode(["status" => "success", "message" => "Logout realizado com sucesso."]);
?>