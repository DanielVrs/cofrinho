import { useEffect, useState } from 'react'
import { downloadExport, getExport, requestExport } from '../api/exports'
import { downloadBlob } from '../utils/download'
import type { Export } from '../types'

const POLL_MS = 2000

// pede a exportação e fica perguntando o status a cada 2 segundos até ficar pronta
export function useExport() {
  const [job, setJob] = useState<Export | null>(null)
  const [error, setError] = useState<string | null>(null)

  const pending = job?.status === 'pending'

  useEffect(() => {
    if (!job || job.status !== 'pending') return

    const id = job.id
    const timer = setInterval(async () => {
      try {
        const current = await getExport(id)
        if (current.status === 'pending') return

        clearInterval(timer)
        setJob(current)

        if (current.status === 'done') {
          downloadBlob(await downloadExport(id), `lancamentos-${current.month}.csv`)
        } else {
          setError(current.error ?? 'A exportação falhou.')
        }
      } catch (err) {
        clearInterval(timer)
        setJob(null)
        setError(err instanceof Error ? err.message : 'Erro inesperado.')
      }
    }, POLL_MS)

    // ao sair da tela, para de perguntar
    return () => clearInterval(timer)
  }, [job])

  async function start(month: string) {
    setError(null)
    try {
      setJob(await requestExport(month))
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro inesperado.')
    }
  }

  return { start, pending, error }
}
