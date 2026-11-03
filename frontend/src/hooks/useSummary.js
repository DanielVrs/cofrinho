import { useCallback, useEffect, useState } from 'react'
import { getSummary } from '../api/summary'

export function useSummary(month) {
  const [summary, setSummary] = useState(null)

  const reload = useCallback(() => {
    getSummary(month).then(setSummary).catch(() => setSummary(null))
  }, [month])

  useEffect(() => {
    reload()
  }, [reload])

  return { summary, reload }
}
