import { useState } from 'react'
import FieldError from '../../components/FieldError'
import { ApiError } from '../../api/client'
import { centsToInput, parseReais } from '../../utils/money'

const emptyValues = { description: '', amount: '', spent_on: '', category_id: '' }

// transforma um lançamento da API nos valores do formulário
function toFormValues(expense) {
  if (!expense) return emptyValues
  return {
    description: expense.description,
    amount: centsToInput(expense.amount_cents),
    spent_on: expense.spent_on,
    category_id: String(expense.category.id),
  }
}

export default function ExpenseForm({ categories, expense = null, onSubmit, onCancel }) {
  // formulário controlado: o valor de cada input vive no estado
  const [values, setValues] = useState(() => toFormValues(expense))
  const [errors, setErrors] = useState({})
  const [submitting, setSubmitting] = useState(false)

  function handleChange(event) {
    const { name, value } = event.target
    setValues((current) => ({ ...current, [name]: value }))
  }

  async function handleSubmit(event) {
    event.preventDefault() // sem isso o navegador recarrega a página
    setSubmitting(true)
    setErrors({})

    try {
      await onSubmit({
        description: values.description,
        amount_cents: parseReais(values.amount), // reais -> centavos
        spent_on: values.spent_on || null,
        category_id: values.category_id ? Number(values.category_id) : null,
      })
      if (!expense) setValues(emptyValues) // criou: limpa o formulário
    } catch (err) {
      // 422: erros por campo; outro erro: mensagem geral
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors)
      else setErrors({ general: [err.message] })
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form className="expense-form" onSubmit={handleSubmit} noValidate>
      <label>
        Descrição
        <input name="description" value={values.description} onChange={handleChange} />
        <FieldError messages={errors.description} />
      </label>

      <label>
        Valor (R$)
        <input name="amount" inputMode="decimal" placeholder="12,50" value={values.amount} onChange={handleChange} />
        <FieldError messages={errors.amount_cents} />
      </label>

      <label>
        Data
        <input name="spent_on" type="date" value={values.spent_on} onChange={handleChange} />
        <FieldError messages={errors.spent_on} />
      </label>

      <label>
        Categoria
        <select name="category_id" value={values.category_id} onChange={handleChange}>
          <option value="">Selecione</option>
          {categories.map((category) => (
            <option key={category.id} value={category.id}>
              {category.name}
            </option>
          ))}
        </select>
        <FieldError messages={errors.category_id} />
      </label>

      <FieldError messages={errors.general} />

      <div className="form-actions">
        <button type="submit" disabled={submitting}>
          {submitting ? 'Salvando...' : 'Salvar'}
        </button>
        {onCancel && (
          <button type="button" onClick={onCancel} disabled={submitting}>
            Cancelar
          </button>
        )}
      </div>
    </form>
  )
}
