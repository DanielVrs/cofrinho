import { request } from './client'

export async function register(values) {
  const { data } = await request('/register', { method: 'POST', body: values })
  return data // usuário; a sessão fica no cookie
}

export async function login(values) {
  const { data } = await request('/login', { method: 'POST', body: values })
  return data
}

export function logout() {
  return request('/logout', { method: 'POST' })
}

export async function me() {
  const { data } = await request('/user')
  return data
}
