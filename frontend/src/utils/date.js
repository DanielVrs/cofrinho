// "2026-11-03" -> "03/11/2026"
export function formatDate(isoDate) {
  const [year, month, day] = isoDate.split('-')
  return `${day}/${month}/${year}`
}
