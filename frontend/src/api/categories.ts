import { request } from './client'
import type { Category, CategoryInput } from '../types'

export async function listCategories(): Promise<Category[]> {
  const { data } = await request<{ data: Category[] }>('/categories')
  return data
}

export async function createCategory(values: CategoryInput): Promise<Category> {
  const { data } = await request<{ data: Category }>('/categories', { method: 'POST', body: values })
  return data
}

export async function updateCategory(id: number, values: CategoryInput): Promise<Category> {
  const { data } = await request<{ data: Category }>(`/categories/${id}`, { method: 'PUT', body: values })
  return data
}

export function deleteCategory(id: number): Promise<null> {
  return request<null>(`/categories/${id}`, { method: 'DELETE' })
}
