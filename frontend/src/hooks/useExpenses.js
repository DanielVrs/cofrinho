import { useEffect, useState } from 'react'
import { listExpenses } from '../api/expenses'

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

  return { expenses, setExpenses, loading, error }
}
