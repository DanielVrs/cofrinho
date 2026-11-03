import { request } from './client'
import type { Summary } from '../types'

export async function getSummary(month: string): Promise<Summary> {
  const { data } = await request<{ data: Summary }>(`/summary?month=${month}`)
  return data
}
