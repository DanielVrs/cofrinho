import type { Category, ExpenseFilters as Filters } from '../../types'

interface ExpenseFiltersProps {
  month: string
  categoryId: string
  categories: Category[]
  onChange: (change: Partial<Filters>) => void
}

export default function ExpenseFilters({ month, categoryId, categories, onChange }: ExpenseFiltersProps) {
  return (
    <div className="filters">
      <label>
        Mês
        <input type="month" value={month} onChange={(e) => onChange({ month: e.target.value })} />
      </label>
      <label>
        Categoria
        <select value={categoryId} onChange={(e) => onChange({ categoryId: e.target.value })}>
          <option value="">Todas</option>
          {categories.map((category) => (
            <option key={category.id} value={category.id}>
              {category.name}
            </option>
          ))}
        </select>
      </label>
    </div>
  )
}
