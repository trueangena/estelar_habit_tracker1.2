// src/services/api.js
import axios from 'axios';

// Cria uma instância do Axios com configurações padrão
const api = axios.create({
  // Substitua pela URL base real da sua pasta PHP no servidor local (XAMPP, WAMP, etc.)
  baseURL: 'http://localhost/estelar_habit_tracker1.2/',
  
  // Isso é ABSOLUTAMENTE CRUCIAL para sistemas com sessão em PHP.
  // Permite que o navegador envie o cookie PHPSESSID junto com a requisição.
  withCredentials: true,
  
  // Define que vamos sempre enviar e receber JSON
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Interceptador de Resposta (Tratamento global de erros)
api.interceptors.response.use(
  (response) => {
    // Se a requisição deu certo, apenas devolve a resposta
    return response;
  },
  (error) => {
    // Se o backend devolver um 401 (Não Autorizado), significa que a sessão expirou
    // ou o usuário tentou acessar uma rota protegida sem fazer login.
    if (error.response && error.response.status === 401) {
      console.error("Sessão expirada ou acesso negado.");
      
      // Se não estivermos na tela de login, forçamos o redirecionamento
      if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
        window.location.href = '/login';
      }
    }
    
    // Repassa o erro para ser tratado no componente (ex: mostrar aviso vermelho)
    return Promise.reject(error);
  }
);

export default api;