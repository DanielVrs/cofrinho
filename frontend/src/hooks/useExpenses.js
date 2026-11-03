import { useEffect, useState } from 'react'
import { createExpense, deleteExpense, listExpenses, updateExpense } from '../api/expenses'

export function useExpenses() {
  const [expenses, setExpenses] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  // roda uma vez, quando o componente aparece na tela ([] = sem dependências)
  useEffect(() => {
    let active = true

    async function load() {
      setLoading(true)
      setError(null)
      try {
        const data = await listExpenses()
        if (active) setExpenses(data)
      } catch (err) {
        if (active) setError(err.message)
      } finally {
        if (active) setLoading(false)
      }
    }

    load()

    // se o componente sair da tela antes da resposta, ignora o resultado
    return () => {
      active = false
    }
  }, [])

  // cada operação chama a API e, se deu certo, atualiza a lista local sem recarregar
  async function add(values) {
    const created = await createExpense(values)
    setExpenses((current) => [created, ...current])
  }

  async function update(id, values) {
    const updated = await updateExpense(id, values)
    setExpenses((current) => current.map((expense) => (expense.id === id ? updated : expense)))
  }

  async function remove(id) {
    await deleteExpense(id)
    setExpenses((current) => current.filter((expense) => expense.id !== id))
  }

  return { expenses, loading, error, add, update, remove }
}
