import { useState } from 'react'
import FieldError from '../../components/FieldError'
import { ApiError } from '../../api/client'

const emptyValues = { name: '', color: '#22c55e' }

export default function CategoryForm({ onSubmit }) {
  const [values, setValues] = useState(emptyValues)
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
      await onSubmit(values)
      setValues(emptyValues)
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
        Cor
        <input name="color" type="color" value={values.color} onChange={handleChange} />
        <FieldError messages={errors.color} />
      </label>
      <FieldError messages={errors.general} />
      <button type="submit" disabled={submitting}>
        {submitting ? 'Salvando...' : 'Adicionar'}
      </button>
    </form>
  )
}
