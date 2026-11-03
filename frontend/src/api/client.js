import { notifyUnauthorized } from './session'

// única função que fala com a API; nenhum componente usa fetch direto
const BASE_URL = import.meta.env.VITE_API_URL
const ORIGIN = new URL(BASE_URL).origin // http://localhost:8000

export class ApiError extends Error {
  constructor(message, status, errors = {}) {
    super(message)
    this.status = status
    this.errors = errors // erros de validação (422), por campo
  }
}

function readCookie(name) {
  const row = document.cookie.split('; ').find((item) => item.startsWith(`${name}=`))
  return row ? decodeURIComponent(row.slice(name.length + 1)) : null
}

// o Laravel manda o token CSRF no cookie XSRF-TOKEN; esta rota só serve para criá-lo
async function fetchCsrfCookie() {
  await fetch(`${ORIGIN}/sanctum/csrf-cookie`, { credentials: 'include' })
}

async function send(path, { method = 'GET', body } = {}, retrying = false) {
  const headers = { Accept: 'application/json', 'Content-Type': 'application/json' }

  let response
  try {
    if (method !== 'GET') {
      // POST, PUT e DELETE precisam devolver o token CSRF num cabeçalho
      if (!readCookie('XSRF-TOKEN')) await fetchCsrfCookie()
      headers['X-XSRF-TOKEN'] = readCookie('XSRF-TOKEN')
    }

    response = await fetch(`${BASE_URL}${path}`, {
      method,
      headers,
      credentials: 'include', // manda e recebe os cookies de sessão
      body: body ? JSON.stringify(body) : undefined,
    })
  } catch {
    // fetch só rejeita quando nem chegou no servidor (desligado, sem rede, CORS)
    throw new ApiError('Não foi possível conectar à API.', 0)
  }

  // 419: token CSRF velho (sessão expirou). Pega um novo e tenta uma vez
  if (response.status === 419 && !retrying) {
    await fetchCsrfCookie()
    return send(path, { method, body }, true)
  }

  if (response.status === 401) {
    // sessão inválida ou expirada: avisa quem cuida da sessão, que volta para o login
    notifyUnauthorized()
    throw new ApiError('Sessão expirada. Entre novamente.', 401)
  }

  return response
}

async function throwIfFailed(response) {
  if (response.ok) return
  const data = await response.json().catch(() => ({}))
  throw new ApiError(data.message ?? 'Erro na API.', response.status, data.errors ?? {})
}

export async function request(path, options) {
  const response = await send(path, options)
  await throwIfFailed(response)
  return response.status === 204 ? null : response.json()
}

// para downloads (CSV): devolve o conteúdo como Blob em vez de JSON
export async function requestBlob(path) {
  const response = await send(path)
  await throwIfFailed(response)
  return response.blob()
}
