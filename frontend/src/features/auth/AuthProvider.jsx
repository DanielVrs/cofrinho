import { useEffect, useState } from 'react'
import { AuthContext } from './context'
import * as authApi from '../../api/auth'
import { clearSession, getStoredUser, getToken, onUnauthorized, saveSession } from '../../api/session'


export function AuthProvider({ children }) {
  // começa com o que está no localStorage, por isso a sessão sobrevive ao recarregar
  const [user, setUser] = useState(getStoredUser)

  function endSession() {
    clearSession()
    setUser(null)
  }

  useEffect(() => {
    // qualquer 401 vindo da API derruba a sessão e volta para o login
    onUnauthorized(endSession)

    // confere se o token guardado ainda vale; se não, a API responde 401
    if (getToken()) authApi.me().then(setUser).catch(() => {})
  }, [])

  async function startSession(response) {
    saveSession(response.token, response.user)
    setUser(response.user)
  }

  const value = {
    user,
    login: async (values) => startSession(await authApi.login(values)),
    register: async (values) => startSession(await authApi.register(values)),
    logout: async () => {
      await authApi.logout().catch(() => {}) // se o token já não vale, tudo bem
      endSession()
    },
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
