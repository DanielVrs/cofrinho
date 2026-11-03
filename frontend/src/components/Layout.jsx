import { NavLink, Outlet } from 'react-router'
import { useAuth } from '../hooks/useAuth'

// cabeçalho e navegação; <Outlet> é onde a rota filha aparece
export default function Layout() {
  const { user, logout } = useAuth()

  return (
    <>
      <header className="user-bar">
        <span>Olá, {user.name}</span>
        <button type="button" onClick={logout}>
          Sair
        </button>
      </header>

      <nav className="tabs">
        <NavLink to="/" end>
          Lançamentos
        </NavLink>
        <NavLink to="/categories">Categorias</NavLink>
      </nav>

      <Outlet />
    </>
  )
}
