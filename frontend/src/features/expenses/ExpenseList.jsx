import { useState } from 'react'
import ExpenseForm from './ExpenseForm'
import ExpenseItem from './ExpenseItem'
import { useCategories } from '../../hooks/useCategories'
import { useExpenses } from '../../hooks/useExpenses'

export default function ExpenseList() {
  const { expenses, loading, error, add, update, remove } = useExpenses()
  const { categories } = useCategories()
  const [editing, setEditing] = useState(null) // lançamento em edição, ou null

  async function handleUpdate(values) {
    await update(editing.id, values)
    setEditing(null)
  }

  async function handleRemove(expense) {
    if (!window.confirm(`Remover "${expense.description}"?`)) return
    try {
      await remove(expense.id)
    } catch (err) {
      window.alert(err.message)
    }
  }

  return (
    <>
      <h2>Novo lançamento</h2>
      <ExpenseForm categories={categories} onSubmit={add} />

      <h2>Lançamentos</h2>
      {loading && <p className="status">Carregando...</p>}
      {error && <p className="status error">{error}</p>}
      {!loading && !error && expenses.length === 0 && <p className="status">Nenhum lançamento.</p>}

      <ul className="expense-list">
        {expenses.map((expense) =>
          editing?.id === expense.id ? (
            <li key={expense.id} className="expense-item editing">
              <ExpenseForm
                categories={categories}
                expense={expense}
                onSubmit={handleUpdate}
                onCancel={() => setEditing(null)}
              />
            </li>
          ) : (
            <ExpenseItem key={expense.id} expense={expense} onEdit={setEditing} onRemove={handleRemove} />
          ),
        )}
      </ul>
    </>
  )
}
