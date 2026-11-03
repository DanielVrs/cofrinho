import { formatCents } from '../../utils/money'

export default function Summary({ summary, categories }) {
  if (!summary) return null

  // cor de cada categoria, vinda da lista de categorias
  const colorOf = (id) => categories.find((category) => category.id === id)?.color ?? '#9ca3af'

  return (
    <section className="summary">
      <p className="summary-total">
        Total do mês: <strong>{formatCents(summary.total_cents)}</strong>
      </p>

      {summary.by_category.map((row) => {
        // estado derivado: a porcentagem é calculada na hora, não fica em useState
        const percent = summary.total_cents ? (row.total_cents / summary.total_cents) * 100 : 0

        return (
          <div key={row.category_id} className="summary-row">
            <span className="summary-name">{row.name}</span>
            <div className="summary-bar">
              <div className="summary-fill" style={{ width: `${percent}%`, background: colorOf(row.category_id) }} />
            </div>
            <span className="summary-value">{formatCents(row.total_cents)}</span>
          </div>
        )
      })}
    </section>
  )
}
