<?php
// middlewares/AuthMiddleware.php

class AuthMiddleware {
    
    /**
     * Verifica se o usuário está autenticado na sessão.
     * Se não estiver, encerra a execução (exit) e retorna JSON de erro.
     */
    public static function check() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se o ID do usuário existe na sessão
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            
            header('Content-Type: application/json');
            http_response_code(401); // 401 Unauthorized
            
            echo json_encode([
                "status" => "error",
                "message" => "Acesso negado. É necessário estar logado para acessar este recurso."
            ]);
            
            exit(); // Para a execução do script imediatamente
        }

        return true;
    }

    /**
     * Retorna o ID do usuário logado para ser usado nas queries do banco
     */
    public static function getUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }
}
?>