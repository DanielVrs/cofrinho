import { request } from './client'

export async function listExpenses() {
  const { data } = await request('/expenses')
  return data
}

export async function createExpense(values) {
  const { data } = await request('/expenses', { method: 'POST', body: values })
  return data
}

export async function updateExpense(id, values) {
  const { data } = await request(`/expenses/${id}`, { method: 'PUT', body: values })
  return data
}

export function deleteExpense(id) {
  return request(`/expenses/${id}`, { method: 'DELETE' })
}
