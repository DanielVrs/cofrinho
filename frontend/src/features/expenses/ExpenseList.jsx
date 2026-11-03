import ExpenseItem from './ExpenseItem'
import { useExpenses } from '../../hooks/useExpenses'

export default function ExpenseList() {
  const { expenses, setExpenses, loading, error } = useExpenses()

  function handleRemove(id) {
    setExpenses(expenses.filter((expense) => expense.id !== id))
  }

  // três estados antes da lista: carregando, erro e vazia
  if (loading) return <p className="status">Carregando...</p>
  if (error) return <p className="status error">{error}</p>
  if (expenses.length === 0) return <p className="status">Nenhum lançamento.</p>

  return (
    <ul className="expense-list">
      {expenses.map((expense) => (
        <ExpenseItem key={expense.id} expense={expense} onRemove={handleRemove} />
      ))}
    </ul>
  )
}
