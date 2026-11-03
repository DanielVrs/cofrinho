import { Link } from 'react-router'
import AuthForm, { type AuthField } from './AuthForm'
import { useAuth } from '../../hooks/useAuth'
import type { RegisterInput } from '../../types'

const fields: AuthField<RegisterInput>[] = [
  { name: 'name', label: 'Nome' },
  { name: 'email', label: 'E-mail', type: 'email' },
  { name: 'password', label: 'Senha', type: 'password' },
  { name: 'password_confirmation', label: 'Confirme a senha', type: 'password' },
]

const initialValues: RegisterInput = { name: '', email: '', password: '', password_confirmation: '' }

export default function RegisterPage() {
  const { register } = useAuth()

  return (
    <section className="auth">
      <h2>Criar conta</h2>
      <AuthForm<RegisterInput> fields={fields} initialValues={initialValues} submitLabel="Cadastrar" onSubmit={register} />
      <p>
        Já tem conta? <Link to="/login">Entrar</Link>
      </p>
    </section>
  )
}
