import AuthForm from './AuthForm'
import { useAuth } from '../../hooks/useAuth'

const fields = [
  { name: 'name', label: 'Nome' },
  { name: 'email', label: 'E-mail', type: 'email' },
  { name: 'password', label: 'Senha', type: 'password' },
  { name: 'password_confirmation', label: 'Confirme a senha', type: 'password' },
]

export default function RegisterPage({ onSwitch }) {
  const { register } = useAuth()

  return (
    <section className="auth">
      <h2>Criar conta</h2>
      <AuthForm fields={fields} submitLabel="Cadastrar" onSubmit={register} />
      <p>
        Já tem conta?{' '}
        <button type="button" className="link" onClick={onSwitch}>
          Entrar
        </button>
      </p>
    </section>
  )
}
