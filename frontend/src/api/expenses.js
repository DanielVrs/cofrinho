import { request } from './client'

// monta ?month=2026-11&category_id=2&page=1 só com o que foi informado
export function listExpenses({ month, categoryId, page } = {}) {
  const params = new URLSearchParams()
  if (month) params.set('month', month)
  if (categoryId) params.set('category_id', categoryId)
  if (page) params.set('page', page)

  const query = params.toString()
  return request(`/expenses${query ? `?${query}` : ''}`) // devolve { data, meta, links }
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
