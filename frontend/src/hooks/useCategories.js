import { useEffect, useState } from 'react'
import { listCategories } from '../api/categories'

export function useCategories() {
  const [categories, setCategories] = useState([])

  useEffect(() => {
    listCategories().then(setCategories).catch(() => setCategories([]))
  }, [])

  return { categories }
}
