import { request } from './client'

export async function listCategories() {
  const { data } = await request('/categories')
  return data
}

export async function createCategory(values) {
  const { data } = await request('/categories', { method: 'POST', body: values })
  return data
}

export function deleteCategory(id) {
  return request(`/categories/${id}`, { method: 'DELETE' })
}
