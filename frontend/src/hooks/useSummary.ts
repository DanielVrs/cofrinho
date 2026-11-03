import { useCallback, useEffect, useState } from 'react'
import { getSummary } from '../api/summary'
import type { Summary } from '../types'

export function useSummary(month: string) {
  const [summary, setSummary] = useState<Summary | null>(null)

  const reload = useCallback(() => {
    getSummary(month).then(setSummary).catch(() => setSummary(null))
  }, [month])

  useEffect(() => {
    reload()
  }, [reload])

  return { summary, reload }
}
