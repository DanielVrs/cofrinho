// "2026-11-03" -> "03/11/2026"
export function formatDate(isoDate: string): string {
  const [year, month, day] = isoDate.split('-')
  return `${day}/${month}/${year}`
}

// hoje -> "2026-11"
export function currentMonth(): string {
  return new Date().toISOString().slice(0, 7)
}
