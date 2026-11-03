import { request } from './client'

export async function getSummary(month) {
  const { data } = await request(`/summary?month=${month}`)
  return data
}
