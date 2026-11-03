// mostra a primeira mensagem de erro da API para um campo, se houver
export default function FieldError({ messages }) {
  if (!messages || messages.length === 0) return null
  return <small className="field-error">{messages[0]}</small>
}
