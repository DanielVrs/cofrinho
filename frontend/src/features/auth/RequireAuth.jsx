import { Navigate, Outlet } from 'react-router'
import { useAuth } from '../../hooks/useAuth'

// rota protegida: sem usuário, manda para o login
export function RequireAuth() {
  const { user } = useAuth()
  return user ? <Outlet /> : <Navigate to="/login" replace />
}

// login e cadastro: quem já está logado vai para o app
export function GuestOnly() {
  const { user } = useAuth()
  return user ? <Navigate to="/" replace /> : <Outlet />
}
