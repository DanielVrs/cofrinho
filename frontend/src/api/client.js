import { getToken, notifyUnauthorized } from './session'

// única função que fala com a API; nenhum componente usa fetch direto
const BASE_URL = import.meta.env.VITE_API_URL

export class ApiError extends Error {
  constructor(message, status, errors = {}) {
    super(message)
    this.status = status
    this.errors = errors // erros de validação (422), por campo
  }
}

export async function request(path, { method = 'GET', body } = {}) {
  const headers = { Accept: 'application/json', 'Content-Type': 'application/json' }

  // se tem token guardado, vai em todas as chamadas
  const token = getToken()
  if (token) headers.Authorization = `Bearer ${token}`

  let response
  try {
    response = await fetch(`${BASE_URL}${path}`, {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
    })
  } catch {
    // fetch só rejeita quando nem chegou no servidor (desligado, sem rede, CORS)
    throw new ApiError('Não foi possível conectar à API.', 0)
  }

  if (response.status === 204) return null

  const data = await response.json()

  if (response.status === 401) {
    // token inválido ou expirado: avisa quem cuida da sessão, que volta para o login
    notifyUnauthorized()
    throw new ApiError('Sessão expirada. Entre novamente.', 401)
  }

  if (!response.ok) {
    throw new ApiError(data.message ?? 'Erro na API.', response.status, data.errors ?? {})
  }

  return data
}
