/// <reference types="vite/client" />

// tipa as variáveis VITE_* do .env
interface ImportMetaEnv {
  readonly VITE_API_URL: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
