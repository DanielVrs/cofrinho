import { useState } from 'react'
import FieldError from '../../components/FieldError'
import { ApiError } from '../../api/client'
import { centsToInput, parseReais } from '../../utils/money'

const emptyValues = { name: '', color: '#22c55e', budget: '' }

function toFormValues(category) {
  if (!category) return emptyValues
  return {
    name: category.name,
    color: category.color,
    budget: category.budget_cents ? centsToInput(category.budget_cents) : '',
  }
}

// serve para criar (sem category) e para editar (com category)
export default function CategoryForm({ category = null, onSubmit, onCancel }) {
  const [values, setValues] = useState(() => toFormValues(category))
  const [errors, setErrors] = useState({})
  const [submitting, setSubmitting] = useState(false)

  function handleChange(event) {
    const { name, value } = event.target
    setValues((current) => ({ ...current, [name]: value }))
  }

  async function handleSubmit(event) {
    event.preventDefault()
    setSubmitting(true)
    setErrors({})
    try {
      await onSubmit({
        name: values.name,
        color: values.color,
        budget_cents: parseReais(values.budget), // vazio -> null = sem limite
      })
      if (!category) setValues(emptyValues)
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors)
      else setErrors({ general: [err.message] })
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form className="category-form" onSubmit={handleSubmit} noValidate>
      <label>
        Nome
        <input name="name" value={values.name} onChange={handleChange} />
        <FieldError messages={errors.name} />
      </label>
      <label>
        Limite mensal (R$)
        <input name="budget" inputMode="decimal" placeholder="opcional" value={values.budget} onChange={handleChange} />
        <FieldError messages={errors.budget_cents} />
      </label>
      <label>
        Cor
        <input name="color" type="color" value={values.color} onChange={handleChange} />
        <FieldError messages={errors.color} />
      </label>
      <FieldError messages={errors.general} />
      <div className="form-actions">
        <button type="submit" disabled={submitting}>
          {submitting ? 'Salvando...' : category ? 'Salvar' : 'Adicionar'}
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
