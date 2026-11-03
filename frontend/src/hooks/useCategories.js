import { useEffect, useState } from 'react'
import { createCategory, deleteCategory, listCategories } from '../api/categories'

export function useCategories() {
  const [categories, setCategories] = useState([])

  useEffect(() => {
    listCategories().then(setCategories).catch(() => setCategories([]))
  }, [])

  async function add(values) {
    const created = await createCategory(values)
    setCategories((current) => [...current, created].sort((a, b) => a.name.localeCompare(b.name)))
  }

  async function remove(id) {
    await deleteCategory(id) // 409 se a categoria tiver lançamentos
    setCategories((current) => current.filter((category) => category.id !== id))
  }

  return { categories, add, remove }
}
