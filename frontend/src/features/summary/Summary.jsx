import { formatCents } from '../../utils/money'

export default function Summary({ summary, categories }) {
  if (!summary) return null

  // cor de cada categoria, vinda da lista de categorias
  const colorOf = (id) => categories.find((category) => category.id === id)?.color ?? '#9ca3af'

  const overBudget = summary.by_category.filter((row) => row.over_budget)

  return (
    <section className="summary">
      <p className="summary-total">
        Total do mês: <strong>{formatCents(summary.total_cents)}</strong>
      </p>

      {overBudget.length > 0 && (
        <p className="alert">
          Limite ultrapassado em: {overBudget.map((row) => row.name).join(', ')}
        </p>
      )}

      {summary.by_category.map((row) => {
        // estado derivado: a porcentagem é calculada na hora, não fica em useState
        const percent = summary.total_cents ? (row.total_cents / summary.total_cents) * 100 : 0

        return (
          <div key={row.category_id} className={`summary-row${row.over_budget ? ' over' : ''}`}>
            <span className="summary-name">{row.name}</span>
            <div className="summary-bar">
              <div className="summary-fill" style={{ width: `${percent}%`, background: colorOf(row.category_id) }} />
            </div>
            <span className="summary-value">
              {formatCents(row.total_cents)}
              {row.budget_cents && <small> / {formatCents(row.budget_cents)}</small>}
            </span>
          </div>
        )
      })}
    </section>
  )
}
