import ExpenseForm from './ExpenseForm'
import ExpenseItem from './ExpenseItem'

// só desenha: recebe tudo por props, não busca nada
export default function ExpenseList({ expenses, categories, loading, error, editing, onEdit, onUpdate, onCancelEdit, onRemove }) {
  if (loading) return <p className="status">Carregando...</p>
  if (error) return <p className="status error">{error}</p>
  if (expenses.length === 0) return <p className="status">Nenhum lançamento.</p>

  return (
    <ul className="expense-list">
      {expenses.map((expense) =>
        editing?.id === expense.id ? (
          <li key={expense.id} className="expense-item editing">
            <ExpenseForm categories={categories} expense={expense} onSubmit={onUpdate} onCancel={onCancelEdit} />
          </li>
        ) : (
          <ExpenseItem key={expense.id} expense={expense} onEdit={onEdit} onRemove={onRemove} />
        ),
      )}
    </ul>
  )
}
