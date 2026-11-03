import { useState } from 'react'
import { AuthProvider } from './features/auth/AuthProvider'
import LoginPage from './features/auth/LoginPage'
import RegisterPage from './features/auth/RegisterPage'
import CategoriesPage from './features/categories/CategoriesPage'
import ExpensesPage from './features/expenses/ExpensesPage'
import { useAuth } from './hooks/useAuth'
import './App.css'

// decide o que mostrar: login/cadastro ou o app
function Screen() {
  const { user, logout } = useAuth()
  const [screen, setScreen] = useState('login')
  const [page, setPage] = useState('expenses')

  if (!user) {
    return screen === 'login' ? (
      <LoginPage onSwitch={() => setScreen('register')} />
    ) : (
      <RegisterPage onSwitch={() => setScreen('login')} />
    )
  }

  return (
    <>
      <header className="user-bar">
        <span>Olá, {user.name}</span>
        <button type="button" onClick={logout}>
          Sair
        </button>
      </header>

      <nav className="tabs">
        <button type="button" className={page === 'expenses' ? 'active' : ''} onClick={() => setPage('expenses')}>
          Lançamentos
        </button>
        <button type="button" className={page === 'categories' ? 'active' : ''} onClick={() => setPage('categories')}>
          Categorias
        </button>
      </nav>

      {page === 'expenses' ? <ExpensesPage /> : <CategoriesPage />}
    </>
  )
}

function App() {
  return (
    <AuthProvider>
      <main className="app">
        <h1>Cofrinho</h1>
        <Screen />
      </main>
    </AuthProvider>
  )
}

export default App
