import { createContext } from 'react'
import type { LoginInput, RegisterInput, User } from '../../types'

export interface AuthContextValue {
  user: User | null
  login: (values: LoginInput) => Promise<void>
  register: (values: RegisterInput) => Promise<void>
  logout: () => Promise<void>
}

// estado global simples: quem está logado, visível para qualquer componente
export const AuthContext = createContext<AuthContextValue | null>(null)
