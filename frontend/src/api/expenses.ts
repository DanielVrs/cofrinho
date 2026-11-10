import { request } from './client'
import type { Expense, ExpenseFilters, ExpenseInput, Paginated } from '../types'

// monta ?month=2026-11&category_id=2&page=1 só com o que foi informado
export function listExpenses({ month, categoryId, page }: Partial<ExpenseFilters> = {}): Promise<Paginated<Expense>> {
  const params = new URLSearchParams()
  if (month) params.set('month', month)
  if (categoryId) params.set('category_id', categoryId)
  if (page) params.set('page', String(page))

  const query = params.toString()
  return request<Paginated<Expense>>(`/expenses${query ? `?${query}` : ''}`)
}

// idempotencyKey: um UUID por tentativa de criar. Se a mesma tentativa chegar duas vezes
// na API (clique duplo, rede repetindo), ela devolve o lançamento já criado em vez de duplicar
export async function createExpense(values: ExpenseInput, idempotencyKey: string): Promise<Expense> {
  const { data } = await request<{ data: Expense }>('/expenses', {
    method: 'POST',
    body: values,
    headers: { 'Idempotency-Key': idempotencyKey },
  })
  return data
}

export async function updateExpense(id: number, values: ExpenseInput): Promise<Expense> {
  const { data } = await request<{ data: Expense }>(`/expenses/${id}`, { method: 'PUT', body: values })
  return data
}

export function deleteExpense(id: number): Promise<null> {
  return request<null>(`/expenses/${id}`, { method: 'DELETE' })
}
