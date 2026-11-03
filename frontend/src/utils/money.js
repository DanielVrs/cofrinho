// 1250 -> "R$ 12,50"
const formatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

export function formatCents(cents) {
  return formatter.format(cents / 100)
}

// "12,50" ou "1.250,00" ou "12.50" -> centavos. Vazio ou inválido -> null
export function parseReais(text) {
  const clean = String(text).trim()
  if (clean === '') return null

  // com vírgula: ponto é separador de milhar. Sem vírgula: ponto é decimal
  const normalized = clean.includes(',') ? clean.replace(/\./g, '').replace(',', '.') : clean
  const value = Number(normalized)

  return Number.isNaN(value) ? null : Math.round(value * 100)
}

// 1250 -> "12,50" (para preencher o input na edição)
export function centsToInput(cents) {
  return (cents / 100).toFixed(2).replace('.', ',')
}
