// 1250 -> "R$ 12,50"
const formatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

export function formatCents(cents) {
  return formatter.format(cents / 100)
}
