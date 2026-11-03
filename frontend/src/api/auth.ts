import { request } from './client'
import type { LoginInput, RegisterInput, User } from '../types'

export async function register(values: RegisterInput): Promise<User> {
  const { data } = await request<{ data: User }>('/register', { method: 'POST', body: values })
  return data // usuário; a sessão fica no cookie
}

export async function login(values: LoginInput): Promise<User> {
  const { data } = await request<{ data: User }>('/login', { method: 'POST', body: values })
  return data
}

export function logout(): Promise<null> {
  return request<null>('/logout', { method: 'POST' })
}

export async function me(): Promise<User> {
  const { data } = await request<{ data: User }>('/user')
  return data
}
