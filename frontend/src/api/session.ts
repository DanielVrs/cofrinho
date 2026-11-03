import type { User } from '../types'

// a sessão em si fica num cookie HttpOnly que o JavaScript não lê;
// aqui guardamos só o usuário, para a tela abrir logada sem esperar a API
const USER_KEY = 'cofrinho.user'

export function getStoredUser(): User | null {
  const raw = localStorage.getItem(USER_KEY)
  return raw ? (JSON.parse(raw) as User) : null
}

export function saveUser(user: User): void {
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function clearUser(): void {
  localStorage.removeItem(USER_KEY)
}

// quem quiser ser avisado de um 401 (o AuthProvider) registra uma função aqui
let unauthorizedHandler: () => void = () => {}

export function onUnauthorized(handler: () => void): void {
  unauthorizedHandler = handler
}

export function notifyUnauthorized(): void {
  unauthorizedHandler()
}
