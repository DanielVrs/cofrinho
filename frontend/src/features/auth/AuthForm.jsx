import { useState } from 'react'
import FieldError from '../../components/FieldError'
import { ApiError } from '../../api/client'

// formulário controlado genérico: recebe os campos e a função de envio
export default function AuthForm({ fields, submitLabel, onSubmit }) {
  const [values, setValues] = useState(() => Object.fromEntries(fields.map((field) => [field.name, ''])))
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
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors)
      else setErrors({ general: [err.message] })
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form className="auth-form" onSubmit={handleSubmit} noValidate>
      {fields.map((field) => (
        <label key={field.name}>
          {field.label}
          <input name={field.name} type={field.type ?? 'text'} value={values[field.name]} onChange={handleChange} />
          <FieldError messages={errors[field.name]} />
        </label>
      ))}
      <FieldError messages={errors.general} />
      <button type="submit" disabled={submitting}>
        {submitting ? 'Aguarde...' : submitLabel}
      </button>
    </form>
  )
}
