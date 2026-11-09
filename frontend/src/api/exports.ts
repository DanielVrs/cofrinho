import { request, requestBlob } from './client'
import type { Export } from '../types'

// pede a exportação: a API responde 202 e o worker gera o arquivo depois
export async function requestExport(month: string): Promise<Export> {
  const { data } = await request<{ data: Export }>('/exports', { method: 'POST', body: { month } })
  return data
}

// consulta o status: pending, done ou failed
export async function getExport(id: number): Promise<Export> {
  const { data } = await request<{ data: Export }>(`/exports/${id}`)
  return data
}

export function downloadExport(id: number): Promise<Blob> {
  return requestBlob(`/exports/${id}/download`)
}
