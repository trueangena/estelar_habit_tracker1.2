<?php
// config/cors.php

// 1. Define exatamente qual frontend tem permissão (a porta do seu Vite)
$allowedOrigin = "http://localhost:5173";

// 2. Libera a origem específica
header("Access-Control-Allow-Origin: " . $allowedOrigin);

// 3. ESSENCIAL: Permite que o navegador envie o Cookie (PHPSESSID)
header("Access-Control-Allow-Credentials: true");

// 4. Define quais métodos HTTP são permitidos
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// 5. Define quais cabeçalhos o React pode enviar
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 6. O "Preflight Request": O navegador faz uma requisição OPTIONS escondida antes do POST.
// O PHP precisa de responder "OK" imediatamente para o navegador libertar o POST real.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>