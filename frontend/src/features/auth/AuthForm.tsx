import { useState, type ChangeEvent, type FormEvent } from 'react'
import FieldError from '../../components/FieldError'
import { ApiError } from '../../api/client'
import type { FieldErrors } from '../../types'

export interface AuthField<T> {
  name: keyof T & string
  label: string
  type?: string
}

interface AuthFormProps<T> {
  fields: AuthField<T>[]
  initialValues: T
  submitLabel: string
  onSubmit: (values: T) => Promise<void>
}

// formulário controlado genérico: T é o formato dos valores (LoginInput, RegisterInput...)
export default function AuthForm<T extends { [K in keyof T]: string }>({ fields, initialValues, submitLabel, onSubmit }: AuthFormProps<T>) {
  const [values, setValues] = useState<T>(initialValues)
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
      await onSubmit(values)
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors)
      else setErrors({ general: [err instanceof Error ? err.message : 'Erro inesperado.'] })
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
