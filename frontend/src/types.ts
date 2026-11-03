// os formatos que a API devolve e recebe, num lugar só

export interface User {
  id: number
  name: string
  email: string
}

export interface Category {
  id: number
  name: string
  color: string
  budget_cents: number | null
}

export interface CategoryInput {
  name: string
  color: string
  budget_cents: number | null
}

export interface Expense {
  id: number
  description: string
  amount_cents: number
  spent_on: string // "2026-11-03"
  category: Category
}

export interface ExpenseInput {
  description: string
  amount_cents: number | null
  spent_on: string | null
  category_id: number | null
}

export interface ExpenseFilters {
  month: string // "2026-11"
  categoryId: string // "" = todas
  page: number
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface Paginated<T> {
  data: T[]
  meta: PaginationMeta
}

export interface SummaryRow {
  category_id: number
  name: string
  total_cents: number
  budget_cents: number | null
  over_budget: boolean
}

export interface Summary {
  month: string
  total_cents: number
  by_category: SummaryRow[]
}

export interface LoginInput {
  email: string
  password: string
}

export interface RegisterInput {
  name: string
  email: string
  password: string
  password_confirmation: string
}

// erros de validação (422): campo -> mensagens
export type FieldErrors = Record<string, string[] | undefined>
