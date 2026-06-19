import { IconoX } from './Iconos'

export default function Modal({ titulo, onCerrar, children, footer, ancho }) {
  return (
    <div className="modal-fondo" onMouseDown={(e) => { if (e.target === e.currentTarget) onCerrar() }}>
      <div className="modal" style={ancho ? { maxWidth: ancho } : undefined}>
        <div className="modal-header">
          <div className="modal-titulo">{titulo}</div>
          <button className="btn btn-fantasma btn-icono" onClick={onCerrar} aria-label="Cerrar">
            <IconoX size={17} />
          </button>
        </div>
        <div className="modal-body">{children}</div>
        {footer && <div className="modal-footer">{footer}</div>}
      </div>
    </div>
  )
}
