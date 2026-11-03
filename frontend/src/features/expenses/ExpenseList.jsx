import { useState } from 'react'
import ExpenseItem from './ExpenseItem'
import { mockExpenses } from './mockExpenses'

export default function ExpenseList() {
  // estado: a lista que aparece na tela. Mudar via setExpenses redesenha o componente.
  const [expenses, setExpenses] = useState(mockExpenses)

  function handleRemove(id) {
    // nunca altera o array atual; cria um novo sem o item removido
    setExpenses(expenses.filter((expense) => expense.id !== id))
  }

  if (expenses.length === 0) {
    return <p className="empty">Nenhum lançamento.</p>
  }

  return (
    <ul className="expense-list">
      {expenses.map((expense) => (
        // key: o React usa para saber qual <li> é qual quando a lista muda
        <ExpenseItem key={expense.id} expense={expense} onRemove={handleRemove} />
      ))}
    </ul>
  )
}
