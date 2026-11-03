import { request, requestBlob } from './client'
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

export async function createExpense(values: ExpenseInput): Promise<Expense> {
  const { data } = await request<{ data: Expense }>('/expenses', { method: 'POST', body: values })
  return data
}

export async function updateExpense(id: number, values: ExpenseInput): Promise<Expense> {
  const { data } = await request<{ data: Expense }>(`/expenses/${id}`, { method: 'PUT', body: values })
  return data
}

export function deleteExpense(id: number): Promise<null> {
  return request<null>(`/expenses/${id}`, { method: 'DELETE' })
}

// CSV do mês, como Blob, para o navegador baixar
export function exportExpenses(month: string): Promise<Blob> {
  return requestBlob(`/expenses/export?month=${month}`)
}
