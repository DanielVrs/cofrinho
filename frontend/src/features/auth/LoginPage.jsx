import AuthForm from './AuthForm'
import { useAuth } from '../../hooks/useAuth'

const fields = [
  { name: 'email', label: 'E-mail', type: 'email' },
  { name: 'password', label: 'Senha', type: 'password' },
]

export default function LoginPage({ onSwitch }) {
  const { login } = useAuth()

  return (
    <section className="auth">
      <h2>Entrar</h2>
      <AuthForm fields={fields} submitLabel="Entrar" onSubmit={login} />
      <p>
        Não tem conta?{' '}
        <button type="button" className="link" onClick={onSwitch}>
          Cadastre-se
        </button>
      </p>
    </section>
  )
}
