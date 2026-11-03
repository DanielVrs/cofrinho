import { useState, type ChangeEvent, type FormEvent } from 'react'
import FieldError from '../../components/FieldError'
import { ApiError } from '../../api/client'
import { centsToInput, parseReais } from '../../utils/money'
import type { Category, CategoryInput, FieldErrors } from '../../types'

interface FormValues {
  name: string
  color: string
  budget: string
}

const emptyValues: FormValues = { name: '', color: '#22c55e', budget: '' }

function toFormValues(category: Category | null): FormValues {
  if (!category) return emptyValues
  return {
    name: category.name,
    color: category.color,
    budget: category.budget_cents ? centsToInput(category.budget_cents) : '',
  }
}

interface CategoryFormProps {
  category?: Category | null
  onSubmit: (values: CategoryInput) => Promise<void>
  onCancel?: () => void
}

// serve para criar (sem category) e para editar (com category)
export default function CategoryForm({ category = null, onSubmit, onCancel }: CategoryFormProps) {
  const [values, setValues] = useState<FormValues>(() => toFormValues(category))
  const [errors, setErrors] = useState<FieldErrors>({})
  const [submitting, setSubmitting] = useState(false)

  function handleChange(event: ChangeEvent<HTMLInputElement>) {
    const { name, value } = event.target
    setValues((current) => ({ ...current, [name]: value }))
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
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
      else setErrors({ general: [err instanceof Error ? err.message : 'Erro inesperado.'] })
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
