import { Link } from 'react-router'
import AuthForm, { type AuthField } from './AuthForm'
import { useAuth } from '../../hooks/useAuth'
import type { LoginInput } from '../../types'

const fields: AuthField<LoginInput>[] = [
  { name: 'email', label: 'E-mail', type: 'email' },
  { name: 'password', label: 'Senha', type: 'password' },
]

export default function LoginPage() {
  const { login } = useAuth()

  return (
    <section className="auth">
      <h2>Entrar</h2>
      <AuthForm<LoginInput> fields={fields} initialValues={{ email: '', password: '' }} submitLabel="Entrar" onSubmit={login} />
      <p>
        Não tem conta? <Link to="/register">Cadastre-se</Link>
      </p>
    </section>
  )
}
