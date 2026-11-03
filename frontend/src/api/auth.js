import { request } from './client'

export function register(values) {
  return request('/register', { method: 'POST', body: values }) // { user, token }
}

export function login(values) {
  return request('/login', { method: 'POST', body: values }) // { user, token }
}

export function logout() {
  return request('/logout', { method: 'POST' })
}

export async function me() {
  const { data } = await request('/user')
  return data
}
