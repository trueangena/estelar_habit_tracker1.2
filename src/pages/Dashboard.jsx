import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../services/api';

export default function Dashboard() {
  // Estados do Dashboard
  const [habits, setHabits] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');

  // Estados do Modal de Novo Hábito
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [newHabit, setNewHabit] = useState({ title: '', description: '', frequency: 'daily' });
  const [modalError, setModalError] = useState('');
  const [isCreating, setIsCreating] = useState(false);

  const navigate = useNavigate();

  // --- NOVA FUNÇÃO DE LOGOUT ---
  const handleLogout = async (e) => {
    e.preventDefault();
    try {
      // Chama a rota de logout para destruir a sessão no PHP
      await api.post('./api/logout.php');
    } catch (err) {
      console.error('Erro ao fazer logout:', err);
    } finally {
      // Independentemente de dar erro ou não na rede, mandamos o utilizador para o login
      navigate('/login');
    }
  };

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      setIsLoading(true);
      const response = await api.get('./api/dashboard/stats.php');
      if (response.data.status === 'success') {
        setHabits(response.data.data);
      }
    } catch (err) {
      console.error(err);
      setError('Não foi possível carregar as estatísticas.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleCompleteHabit = async (habitId) => {
    try {
      const response = await api.post('./api/habits/complete.php', { habit_id: habitId });
      if (response.status === 201 || response.data.status === 'success') {
        fetchStats();
      }
    } catch (err) {
      if (err.response && err.response.data && err.response.data.message) {
        alert(err.response.data.message);
      } else {
        alert('Erro ao concluir o hábito.');
      }
    }
  };

  // --- FUNÇÃO PARA EXCLUIR HÁBITO ---
  const handleDeleteHabit = async (habitId) => {
    // Confirmação de segurança nativa do navegador
    const confirmDelete = window.confirm("Tem a certeza que deseja excluir este hábito? O histórico será ocultado.");

    if (!confirmDelete) return; // Se cancelar, não faz nada

    try {
      // Usamos o método HTTP DELETE
      const response = await api.delete('./api/habits/delete.php', {
        data: { habit_id: habitId } // O Axios exige que o corpo do DELETE vá dentro de "data"
      });

      if (response.data.status === 'success') {
        // Hábito excluído! Recarrega a lista
        fetchStats();
      }
    } catch (err) {
      if (err.response && err.response.data && err.response.data.message) {
        alert(err.response.data.message);
      } else {
        alert('Erro ao excluir o hábito. Verifique o servidor.');
      }
    }
  };

  // --- FUNÇÃO PARA CRIAR NOVO HÁBITO ---
  const handleCreateHabit = async (e) => {
    e.preventDefault();
    setModalError('');

    if (!newHabit.title) {
      setModalError('O título do hábito é obrigatório.');
      return;
    }

    setIsCreating(true);

    try {
      const response = await api.post('./api/habits/create.php', newHabit);

      if (response.status === 201 || response.data.status === 'success') {
        // Fecha o modal, limpa o formulário e recarrega os hábitos na tela
        setIsModalOpen(false);
        setNewHabit({ title: '', description: '', frequency: 'daily' });
        fetchStats();
      }
    } catch (err) {
      if (err.response && err.response.data && err.response.data.message) {
        setModalError(err.response.data.message);
      } else {
        setModalError('Erro ao criar o hábito. Verifique o servidor.');
      }
    } finally {
      setIsCreating(false);
    }
  };

  if (isLoading && habits.length === 0) {
    return (
      <div className="min-h-screen bg-gray-950 flex items-center justify-center text-blue-400">
        <span className="animate-pulse text-xl font-semibold">A carregar o seu progresso...</span>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-950 text-gray-100 font-sans pb-12 relative">

      <nav className="bg-gray-900 border-b border-gray-800 px-6 py-4 flex justify-between items-center shadow-md">
        <h1 className="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-yellow-400">    
          Estelar Habits
        </h1>
        <div className="flex items-center gap-4">
          <button
            onClick={handleLogout}
            className="text-sm font-medium text-red-400 hover:text-red-300 transition-colors focus:outline-none">
            Sair
          </button>

        </div>
      </nav>

      <main className="max-w-5xl mx-auto px-6 mt-10">

        {error && (
          <div className="mb-6 p-4 bg-red-900/50 border border-red-500 rounded-lg text-red-200">
            {error}
          </div>
        )}

        <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
          <div>
            <h2 className="text-3xl font-bold text-white">Seu Progresso</h2>
            <p className="text-gray-400 mt-1">Mantenha a consistência para não quebrar suas ofensivas.</p>
          </div>
          {/* Botão que abre o Modal */}
          <button
            onClick={() => setIsModalOpen(true)}
            className="bg-gradient-to-r from-red-500 to-yellow-400 hover:from-red-600 hover:to-yellow-500 text-black font-semibold py-2 px-6 rounded-lg shadow-md transition-all"
          >
            + Novo Hábito
          </button>
        </div>

        {habits.length === 0 && !error && (
          <div className="text-center py-20 bg-gray-900 border border-gray-800 rounded-2xl">
            <h3 className="text-xl text-gray-300 font-semibold mb-2">Nenhum hábito encontrado</h3>
            <p className="text-gray-500 mb-6">Comece criando o seu primeiro hábito agora mesmo!</p>
            <button
              onClick={() => setIsModalOpen(true)}
              className="text-blue-400 hover:text-blue-300 font-medium underline"
            >
              Criar meu primeiro hábito
            </button>
          </div>
        )}

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {habits.map((habit) => (
            <div key={habit.habit_id} className="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-lg hover:border-gray-700 transition-colors flex flex-col justify-between">
              <div>
                <div className="flex justify-between items-start mb-4">
                  <h3 className="text-xl font-semibold text-gray-100 pr-4">{habit.title}</h3>
                  <button
                    onClick={() => handleDeleteHabit(habit.habit_id)}
                    className="text-gray-500 hover:text-red-500 transition-colors focus:outline-none"
                    title="Excluir hábito"
                  >
                    {/* Ícone de Lixo simples (SVG) */}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                  </button>
                </div>
                <div className="space-y-3 mb-6">
                  <div className="flex justify-between items-center text-sm">
                    <span className="text-gray-400 flex items-center gap-2">🔥 Ofensiva Atual</span>
                    <span className={`font-bold ${habit.current_streak > 0 ? 'text-orange-400' : 'text-gray-500'}`}>
                      {habit.current_streak} dias
                    </span>
                  </div>
                  <div className="flex justify-between items-center text-sm">
                    <span className="text-gray-400 flex items-center gap-2">🏆 Maior Recorde</span>
                    <span className="font-semibold text-yellow-400">{habit.best_streak} dias</span>
                  </div>
                  <div className="flex justify-between items-center text-sm">
                    <span className="text-gray-400">📊 Taxa (30 dias)</span>
                    <span className="font-semibold text-blue-400">{habit.completion_rate_30d}</span>
                  </div>
                </div>
              </div>
              <button
                onClick={() => handleCompleteHabit(habit.habit_id)}
                className="w-full py-3 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-blue-400 font-medium rounded-lg transition-colors focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                ✓ Concluir Hoje
              </button>
            </div>
          ))}
        </div>
      </main>

      {/* --- MODAL DE NOVO HÁBITO --- */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-md p-6 animate-fade-in">
            <h3 className="text-2xl font-bold text-white mb-4">Criar Novo Hábito</h3>

            {modalError && (
              <div className="mb-4 p-3 bg-red-900/50 border border-red-500 rounded-lg text-red-200 text-sm">
                {modalError}
              </div>
            )}

            <form onSubmit={handleCreateHabit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-400 mb-1">Título do Hábito</label>
                <input
                  type="text"
                  value={newHabit.title}
                  onChange={(e) => setNewHabit({ ...newHabit, title: e.target.value })}
                  placeholder="Ex: Ler 10 páginas"
                  className="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-100"
                  maxLength={50}
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-400 mb-1">Descrição (Opcional)</label>
                <textarea
                  value={newHabit.description}
                  onChange={(e) => setNewHabit({ ...newHabit, description: e.target.value })}
                  placeholder="Detalhes para manter o foco..."
                  className="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-100 resize-none h-24"
                  maxLength={255}
                />
              </div>

              <div className="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 text-gray-400 hover:text-white transition-colors"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  disabled={isCreating}
                  className="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg shadow-md transition-colors disabled:opacity-50"
                >
                  {isCreating ? 'Criando...' : 'Salvar Hábito'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}