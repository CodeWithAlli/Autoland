import { IconoCheck, IconoAlerta } from './Iconos'

export default function Mensaje({ tipo = 'ok', children }) {
  if (!children) return null
  const clase = tipo === 'ok' ? 'mensaje-ok' : tipo === 'warn' ? 'mensaje-warn' : 'mensaje-err'
  return (
    <div className={`mensaje ${clase}`}>
      {tipo === 'ok' ? <IconoCheck size={15} /> : <IconoAlerta size={15} />}
      <span>{children}</span>
    </div>
  )
}
