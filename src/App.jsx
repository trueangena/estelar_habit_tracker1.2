import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login.jsx';
import Register from './pages/register.jsx';
import Dashboard from './pages/Dashboard.jsx';

export default function App() {
  return (
    <Router>
      <Routes>
        {/* Se o usuário acessar a raiz (/), redirecionamos para o login */}
        <Route path="/" element={<Navigate to="/login" replace />} />
        
        {/* Nossas rotas de autenticação */}
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/dashboard" element={<Dashboard />} />
        {/* Futuramente, adicionaremos aqui a rota do Dashboard protegida */}
      </Routes>
    </Router>
  );
}