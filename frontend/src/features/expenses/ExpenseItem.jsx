import { formatCents } from '../../utils/money'
import { formatDate } from '../../utils/date'

// recebe um lançamento por props e desenha uma linha
export default function ExpenseItem({ expense, onEdit, onRemove }) {
  return (
    <li className="expense-item">
      <span className="expense-color" style={{ background: expense.category.color }} />
      <div className="expense-info">
        <strong>{expense.description}</strong>
        <small>
          {expense.category.name} · {formatDate(expense.spent_on)}
        </small>
      </div>
      <span className="expense-amount">{formatCents(expense.amount_cents)}</span>
      <button type="button" onClick={() => onEdit(expense)}>
        Editar
      </button>
      <button type="button" onClick={() => onRemove(expense)}>
        Remover
      </button>
    </li>
  )
}
