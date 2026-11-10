import { useRef, useState, type ChangeEvent, type FormEvent } from 'react'
import FieldError from '../../components/FieldError'
import { ApiError } from '../../api/client'
import { centsToInput, parseReais } from '../../utils/money'
import type { Category, Expense, ExpenseInput, FieldErrors } from '../../types'

// o que fica nos inputs é sempre texto; a conversão acontece no envio
interface FormValues {
  description: string
  amount: string
  spent_on: string
  category_id: string
}

const emptyValues: FormValues = { description: '', amount: '', spent_on: '', category_id: '' }

// transforma um lançamento da API nos valores do formulário
function toFormValues(expense: Expense | null): FormValues {
  if (!expense) return emptyValues
  return {
    description: expense.description,
    amount: centsToInput(expense.amount_cents),
    spent_on: expense.spent_on,
    category_id: String(expense.category.id),
  }
}

interface ExpenseFormProps {
  categories: Category[]
  expense?: Expense | null
  // idempotencyKey: a mesma chave enquanto esta tentativa não der certo; só a criação usa
  onSubmit: (values: ExpenseInput, idempotencyKey: string) => Promise<void>
  onCancel?: () => void
}

export default function ExpenseForm({ categories, expense = null, onSubmit, onCancel }: ExpenseFormProps) {
  // formulário controlado: o valor de cada input vive no estado
  const [values, setValues] = useState<FormValues>(() => toFormValues(expense))
  const [errors, setErrors] = useState<FieldErrors>({})
  const [submitting, setSubmitting] = useState(false)
  // chave de idempotência da tentativa atual. useRef: mudar não redesenha e o valor sobrevive entre envios
  const idempotencyKey = useRef<string | null>(null)

  function handleChange(event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) {
    const { name, value } = event.target
    setValues((current) => ({ ...current, [name]: value }))
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault() // sem isso o navegador recarrega a página
    setSubmitting(true)
    setErrors({})

    // ??= : só gera um UUID novo se não houver um pendente. Se o envio anterior falhou
    // (rede caiu, API fora), o reenvio usa a mesma chave e a API não duplica o lançamento
    idempotencyKey.current ??= crypto.randomUUID()

    try {
      await onSubmit(
        {
          description: values.description,
          amount_cents: parseReais(values.amount), // reais -> centavos
          spent_on: values.spent_on || null,
          category_id: values.category_id ? Number(values.category_id) : null,
        },
        idempotencyKey.current,
      )
      idempotencyKey.current = null // deu certo: a próxima tentativa é outra operação, outra chave
      if (!expense) setValues(emptyValues) // criou: limpa o formulário
    } catch (err) {
      // 422: erros por campo; outro erro: mensagem geral
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors)
      else setErrors({ general: [err instanceof Error ? err.message : 'Erro inesperado.'] })
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
