import { useEffect, useState, type ReactNode } from 'react'
import { AuthContext, type AuthContextValue } from './context'
import * as authApi from '../../api/auth'
import { clearUser, getStoredUser, onUnauthorized, saveUser } from '../../api/session'
import type { User } from '../../types'

export function AuthProvider({ children }: { children: ReactNode }) {
  // começa com o que está no localStorage, por isso a tela já abre logada ao recarregar
  const [user, setUser] = useState<User | null>(getStoredUser)

  function endSession() {
    clearUser()
    setUser(null)
  }

  function startSession(loggedUser: User) {
    saveUser(loggedUser)
    setUser(loggedUser)
  }

  useEffect(() => {
    // qualquer 401 vindo da API derruba a sessão e volta para o login
    onUnauthorized(endSession)

    // confere se o cookie de sessão ainda vale; se não, a API responde 401
    if (getStoredUser()) authApi.me().then(startSession).catch(() => {})
  }, [])

  const value: AuthContextValue = {
    user,
    login: async (values) => startSession(await authApi.login(values)),
    register: async (values) => startSession(await authApi.register(values)),
    logout: async () => {
      await authApi.logout().catch(() => {}) // se a sessão já caiu, tudo bem
      endSession()
    },
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
