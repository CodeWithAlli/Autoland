// AUTOLAND — Íconos SVG simples, sin dependencias externas.
// Todos reciben size y className opcionales.

const base = (size) => ({
  width: size,
  height: size,
  viewBox: '0 0 24 24',
  fill: 'none',
  stroke: 'currentColor',
  strokeWidth: 1.8,
  strokeLinecap: 'round',
  strokeLinejoin: 'round',
})

export const IconoPanel = ({ size = 18, className }) => (
  <svg {...base(size)} className={className}>
    <rect x="3" y="3" width="7" height="9" rx="1.5" />
    <rect x="14" y="3" width="7" height="5" rx="1.5" />
    <rect x="14" y="12" width="7" height="9" rx="1.5" />
    <rect x="3" y="16" width="7" height="5" rx="1.5" />
  </svg>
)

export const IconoAuto = ({ size = 18, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M3 13l1.5-4.5A2 2 0 0 1 6.4 7h11.2a2 2 0 0 1 1.9 1.5L21 13" />
    <rect x="2.5" y="13" width="19" height="5.5" rx="1.5" />
    <circle cx="7" cy="18.5" r="1.6" />
    <circle cx="17" cy="18.5" r="1.6" />
  </svg>
)

export const IconoPersonas = ({ size = 18, className }) => (
  <svg {...base(size)} className={className}>
    <circle cx="9" cy="8" r="3.2" />
    <path d="M3.5 20c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5" />
    <circle cx="17" cy="8.5" r="2.4" />
    <path d="M15.8 14.7c2.2.4 3.7 2.1 3.7 4.3" />
  </svg>
)

export const IconoUsuarios = ({ size = 18, className }) => (
  <svg {...base(size)} className={className}>
    <circle cx="12" cy="8" r="3.5" />
    <path d="M4.5 20c0-4 3.4-6.5 7.5-6.5s7.5 2.5 7.5 6.5" />
  </svg>
)

export const IconoBuscar = ({ size = 16, className }) => (
  <svg {...base(size)} className={className}>
    <circle cx="10.5" cy="10.5" r="6.5" />
    <path d="M20 20l-4.3-4.3" />
  </svg>
)

export const IconoMas = ({ size = 16, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M12 5v14M5 12h14" />
  </svg>
)

export const IconoLapiz = ({ size = 15, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M4 20l.9-3.6L17.3 4 20 6.7 7.6 19.1 4 20z" />
    <path d="M14.5 6.5L17.5 9.5" />
  </svg>
)

export const IconoBasura = ({ size = 15, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M4 7h16" />
    <path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
    <path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13" />
    <path d="M10 11v6M14 11v6" />
  </svg>
)

export const IconoSalir = ({ size = 17, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3" />
    <path d="M15 16l4-4-4-4" />
    <path d="M19 12H9" />
  </svg>
)

export const IconoCandado = ({ size = 17, className }) => (
  <svg {...base(size)} className={className}>
    <rect x="5" y="11" width="14" height="9" rx="2" />
    <path d="M8 11V7a4 4 0 0 1 8 0v4" />
  </svg>
)

export const IconoAlerta = ({ size = 16, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M12 3l9.5 17H2.5L12 3z" />
    <path d="M12 10v4" />
    <circle cx="12" cy="17" r="0.6" fill="currentColor" />
  </svg>
)

export const IconoCheck = ({ size = 16, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M4 12.5l5 5L20 7" />
  </svg>
)

export const IconoMoneda = ({ size = 18, className }) => (
  <svg {...base(size)} className={className}>
    <circle cx="12" cy="12" r="9" />
    <path d="M12 7v10M9.5 9.5h3.2a1.8 1.8 0 0 1 0 3.6H10a1.8 1.8 0 0 0 0 3.6h3.5" />
  </svg>
)

export const IconoGrafico = ({ size = 18, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M4 19V11M10 19V5M16 19v-7M21 19H3" />
  </svg>
)

export const IconoX = ({ size = 18, className }) => (
  <svg {...base(size)} className={className}>
    <path d="M6 6l12 12M18 6L6 18" />
  </svg>
)

export const IconoVentas = ({ size = 18, className }) => {
  const base = {
    width: size,
    height: size,
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 1.8,
    strokeLinecap: 'round',
    strokeLinejoin: 'round',
  }
  return (
    <svg {...base} className={className}>
      <path d="M3 9l4-5h10l4 5" />
      <path d="M3 9h18" />
      <path d="M5 9v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9" />
      <path d="M9 13h6" />
    </svg>
  )
}