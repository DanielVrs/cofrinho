// a sessão em si fica num cookie HttpOnly que o JavaScript não lê;
// aqui guardamos só o usuário, para a tela abrir logada sem esperar a API
const USER_KEY = 'cofrinho.user'

export function getStoredUser() {
  const raw = localStorage.getItem(USER_KEY)
  return raw ? JSON.parse(raw) : null
}

export function saveUser(user) {
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function clearUser() {
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
