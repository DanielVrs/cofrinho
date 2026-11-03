import CategoryForm from './CategoryForm'
import { useCategories } from '../../hooks/useCategories'

export default function CategoriesPage() {
  const { categories, add, remove } = useCategories()

  async function handleRemove(category) {
    if (!window.confirm(`Remover "${category.name}"?`)) return
    try {
      await remove(category.id)
    } catch (err) {
      window.alert(err.message) // ex.: 409, categoria com lançamentos
    }
  }

  return (
    <>
      <h2>Nova categoria</h2>
      <CategoryForm onSubmit={add} />

      <h2>Categorias</h2>
      {categories.length === 0 && <p className="status">Nenhuma categoria. Crie a primeira acima.</p>}
      <ul className="expense-list">
        {categories.map((category) => (
          <li key={category.id} className="expense-item">
            <span className="expense-color" style={{ background: category.color }} />
            <div className="expense-info">
              <strong>{category.name}</strong>
            </div>
            <button type="button" onClick={() => handleRemove(category)}>
              Remover
            </button>
          </li>
        ))}
      </ul>
    </>
  )
}
