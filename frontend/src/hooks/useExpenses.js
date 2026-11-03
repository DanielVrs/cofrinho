import { useEffect, useState } from 'react'
import { createExpense, deleteExpense, listExpenses, updateExpense } from '../api/expenses'

// recebe os filtros; sempre que mudarem, busca de novo
export function useExpenses({ month, categoryId, page }) {
  // version muda depois de criar, editar ou remover, para forçar uma nova busca
  const [version, setVersion] = useState(0)
  // o que foi carregado por último e para qual combinação de filtros
  const [loaded, setLoaded] = useState({ key: null, expenses: [], meta: null, error: null })

  const key = `${month}|${categoryId}|${page}|${version}`

  // dependências: o efeito roda de novo quando month, categoryId, page ou version mudam
  useEffect(() => {
    let active = true

    listExpenses({ month, categoryId, page })
      .then((response) => {
        if (active) setLoaded({ key, expenses: response.data, meta: response.meta, error: null })
      })
      .catch((err) => {
        if (active) setLoaded({ key, expenses: [], meta: null, error: err.message })
      })

    // se os filtros mudarem antes da resposta chegar, ignora a resposta antiga
    return () => {
      active = false
    }
  }, [key, month, categoryId, page])

  // estado derivado: está carregando se o que foi pedido ainda não é o que está carregado
  const loading = loaded.key !== key

  const reload = () => setVersion((current) => current + 1)

  async function add(values) {
    await createExpense(values)
    reload()
  }

  async function update(id, values) {
    await updateExpense(id, values)
    reload()
  }

  async function remove(id) {
    await deleteExpense(id)
    reload()
  }

  return { expenses: loaded.expenses, meta: loaded.meta, loading, error: loaded.error, add, update, remove }
}
