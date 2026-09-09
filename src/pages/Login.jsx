import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../services/api'; // Importamos a nossa ponte Axios

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false); // Controla o estado de carregamento

  const navigate = useNavigate(); // Hook para redirecionar de página

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    // Validação básica de frontend
    if (!email || !password) {
      setError('Por favor, preencha todos os campos.');
      return;
    }

    setIsLoading(true); // Desativa o botão e mostra que está a carregar

    try {
      // Faz o POST para http://localhost/estelar-habits/api/login.php
      const response = await api.post('api/login.php', {
        email,
        password
      });

      // Se o PHP devolver sucesso
      if (response.data.status === 'success') {
        // Redireciona imediatamente para o Dashboard
        navigate('/dashboard');
      }

    } catch (err) {
      // Se o PHP devolver um erro (ex: 401 Unauthorized)
      if (err.response && err.response.data && err.response.data.message) {
        setError(err.response.data.message); // Exibe: "Credenciais inválidas."
      } else {
        setError('Erro de ligação ao servidor. Verifique se o backend está a correr.');
      }
    } finally {
      setIsLoading(false); // Volta a ativar o botão
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-950 text-gray-100 font-sans">
      <div className="max-w-md w-full p-8 bg-gray-900 rounded-2xl shadow-xl border border-gray-800">

        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-yellow-400">
            Estelar Habits
          </h1>
          <p className="text-gray-400 mt-2">Bem-vindo de volta! Continue a sua jornada.</p>
        </div>

        {error && (
          <div className="mb-4 p-3 bg-red-900/50 border border-red-500 rounded-lg text-red-200 text-sm text-center">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label className="block text-sm font-medium text-gray-400 mb-1" htmlFor="email">
              E-mail
            </label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              disabled={isLoading}
              className="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:opacity-50"
              placeholder="seu@email.com"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-400 mb-1" htmlFor="password">
              Palavra-passe
            </label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              disabled={isLoading}
              className="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:opacity-50"
              placeholder="••••••••"
            />
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-3 px-4 bg-gradient-to-r from-red-500 to-yellow-400 hover:from-red-600 hover:to-yellow-500 text-black font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all disabled:opacity-70 flex justify-center items-center"
          >
            {isLoading ? (
              <span className="animate-pulse">A verificar...</span>
            ) : (
              'Entrar'
            )}
          </button>
        </form>

        <div className="mt-6 text-center text-sm text-gray-400">
          Ainda não tem uma conta?{' '}
          <Link to="/register" className="text-red-400 hover:text-red-300 font-medium transition-colors">
            Cadastre-se aqui
          </Link>
        </div>

      </div>
    </div>
  );
}