import { request } from './client'

export async function listCategories() {
  const { data } = await request('/categories')
  return data
}
