import { request } from './client'

export async function listExpenses() {
  const { data } = await request('/expenses')
  return data
}
