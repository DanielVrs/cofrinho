// guarda o token e o usuário no localStorage, para a sessão sobreviver ao recarregar
const TOKEN_KEY = 'cofrinho.token'
const USER_KEY = 'cofrinho.user'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function getStoredUser() {
  const raw = localStorage.getItem(USER_KEY)
  return raw ? JSON.parse(raw) : null
}

export function saveSession(token, user) {
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function clearSession() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

// quem quiser ser avisado de um 401 (o AuthProvider) registra uma função aqui
let unauthorizedHandler = () => {}

export function onUnauthorized(handler) {
  unauthorizedHandler = handler
}

export function notifyUnauthorized() {
  unauthorizedHandler()
}
