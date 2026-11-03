import { useEffect, useState } from 'react'
import { createCategory, deleteCategory, listCategories, updateCategory } from '../api/categories'
import type { Category, CategoryInput } from '../types'

const byName = (a: Category, b: Category) => a.name.localeCompare(b.name)

export function useCategories() {
  const [categories, setCategories] = useState<Category[]>([])

  useEffect(() => {
    listCategories().then(setCategories).catch(() => setCategories([]))
  }, [])

  async function add(values: CategoryInput) {
    const created = await createCategory(values)
    setCategories((current) => [...current, created].sort(byName))
  }

  async function update(id: number, values: CategoryInput) {
    const updated = await updateCategory(id, values)
    setCategories((current) => current.map((category) => (category.id === id ? updated : category)).sort(byName))
  }

  async function remove(id: number) {
    await deleteCategory(id) // 409 se a categoria tiver lançamentos
    setCategories((current) => current.filter((category) => category.id !== id))
  }

  return { categories, add, update, remove }
}
