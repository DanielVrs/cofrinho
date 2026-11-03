import type { PaginationMeta } from '../types'

interface PaginationProps {
  meta: PaginationMeta | null
  onChange: (page: number) => void
}

export default function Pagination({ meta, onChange }: PaginationProps) {
  if (!meta || meta.last_page <= 1) return null

  return (
    <div className="pagination">
      <button type="button" disabled={meta.current_page <= 1} onClick={() => onChange(meta.current_page - 1)}>
        Anterior
      </button>
      <span>
        Página {meta.current_page} de {meta.last_page}
      </span>
      <button
        type="button"
        disabled={meta.current_page >= meta.last_page}
        onClick={() => onChange(meta.current_page + 1)}
      >
        Próxima
      </button>
    </div>
  )
}
