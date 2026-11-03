import { BrowserRouter, Navigate, Route, Routes } from 'react-router'
import Layout from './components/Layout'
import { AuthProvider } from './features/auth/AuthProvider'
import LoginPage from './features/auth/LoginPage'
import RegisterPage from './features/auth/RegisterPage'
import { GuestOnly, RequireAuth } from './features/auth/RequireAuth'
import CategoriesPage from './features/categories/CategoriesPage'
import ExpensesPage from './features/expenses/ExpensesPage'
import './App.css'

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <main className="app">
          <h1>Cofrinho</h1>
          <Routes>
            {/* públicas: só para quem não está logado */}
            <Route element={<GuestOnly />}>
              <Route path="/login" element={<LoginPage />} />
              <Route path="/register" element={<RegisterPage />} />
            </Route>

            {/* protegidas: exigem usuário, e usam o Layout com cabeçalho e menu */}
            <Route element={<RequireAuth />}>
              <Route element={<Layout />}>
                <Route path="/" element={<ExpensesPage />} />
                <Route path="/categories" element={<CategoriesPage />} />
              </Route>
            </Route>

            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </main>
      </BrowserRouter>
    </AuthProvider>
  )
}

export default App
