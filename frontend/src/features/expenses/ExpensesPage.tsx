import { useState } from 'react'
import Pagination from '../../components/Pagination'
import Summary from '../summary/Summary'
import ExpenseFilters from './ExpenseFilters'
import ExpenseForm from './ExpenseForm'
import ExpenseList from './ExpenseList'
import { useCategories } from '../../hooks/useCategories'
import { useExpenses } from '../../hooks/useExpenses'
import { useSummary } from '../../hooks/useSummary'
import { currentMonth } from '../../utils/date'
import { downloadBlob } from '../../utils/download'
import { exportExpenses } from '../../api/expenses'
import type { Expense, ExpenseFilters as Filters, ExpenseInput } from '../../types'

// a tela: junta os hooks e distribui para os componentes
export default function ExpensesPage() {
  const [filters, setFilters] = useState<Filters>({ month: currentMonth(), categoryId: '', page: 1 })
  const [editing, setEditing] = useState<Expense | null>(null)

  const { categories } = useCategories()
  const { expenses, meta, loading, error, add, update, remove } = useExpenses(filters)
  const { summary, reload: reloadSummary } = useSummary(filters.month)

  // mudar mês ou categoria volta para a página 1
  function handleFilterChange(change: Partial<Filters>) {
    setFilters((current) => ({ ...current, ...change, page: 1 }))
  }

  async function handleAdd(values: ExpenseInput) {
    await add(values)
    reloadSummary()
  }

  async function handleUpdate(values: ExpenseInput) {
    if (!editing) return
    await update(editing.id, values)
    setEditing(null)
    reloadSummary()
  }

  async function handleExport() {
    try {
      downloadBlob(await exportExpenses(filters.month), `lancamentos-${filters.month}.csv`)
    } catch (err) {
      window.alert(err instanceof Error ? err.message : 'Erro inesperado.')
    }
  }

  async function handleRemove(expense: Expense) {
    if (!window.confirm(`Remover "${expense.description}"?`)) return
    try {
      await remove(expense.id)
      reloadSummary()
    } catch (err) {
      window.alert(err instanceof Error ? err.message : 'Erro inesperado.')
    }
  }

  return (
    <>
      <h2>Novo lançamento</h2>
      <ExpenseForm categories={categories} onSubmit={handleAdd} />

      <h2>Resumo</h2>
      <ExpenseFilters
        month={filters.month}
        categoryId={filters.categoryId}
        categories={categories}
        onChange={handleFilterChange}
      />
      <Summary summary={summary} categories={categories} />

      <div className="section-head">
        <h2>Lançamentos</h2>
        <button type="button" onClick={handleExport}>
          Exportar CSV
        </button>
      </div>
      <ExpenseList
        expenses={expenses}
        categories={categories}
        loading={loading}
        error={error}
        editing={editing}
        onEdit={setEditing}
        onUpdate={handleUpdate}
        onCancelEdit={() => setEditing(null)}
        onRemove={handleRemove}
      />
      <Pagination meta={meta} onChange={(page) => setFilters((current) => ({ ...current, page }))} />
    </>
  )
}
