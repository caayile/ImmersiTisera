export function Badge({ children, tone = 'sage' }) {
  const tones = {
    sage: 'bg-sage/15 text-moss',
    copper: 'bg-copper/15 text-copper-dark',
    ink: 'bg-ink/10 text-ink',
    cream: 'bg-white/10 text-parchment',
  }

  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide uppercase ${tones[tone]}`}>
      {children}
    </span>
  )
}

export function Card({ children, className = '' }) {
  const hasBg = /\bbg-/.test(className)
  return (
    <div className={`rounded-2xl border border-clay/80 p-6 shadow-[0_10px_30px_rgba(22,53,44,0.04)] ${hasBg ? '' : 'bg-white'} ${className}`}>
      {children}
    </div>
  )
}

export function Field({ label, children }) {
  return (
    <label className="block space-y-2">
      <span className="text-xs font-semibold uppercase tracking-[0.16em] text-moss/80">{label}</span>
      {children}
    </label>
  )
}

export const inputClass =
  'w-full rounded-lg border border-clay bg-white px-4 py-3 text-sm outline-none transition focus:border-mint'

export function Button({ children, variant = 'primary', className = '', ...props }) {
  const variants = {
    primary: 'bg-mint text-white hover:bg-copper-dark',
    ink: 'bg-ink text-white hover:bg-ink-soft',
    ghost: 'bg-transparent border border-clay text-ink hover:bg-white',
    sage: 'bg-mint text-white hover:bg-copper-dark',
  }

  return (
    <button
      className={`inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-semibold transition disabled:opacity-50 ${variants[variant]} ${className}`}
      {...props}
    >
      {children}
    </button>
  )
}

export function ScoreRing({ score = 0, label }) {
  return (
    <div className="flex items-center gap-3">
      <div className="grid h-16 w-16 place-items-center rounded-full border-4 border-mint bg-cream text-xl font-semibold text-ink">
        {score}
      </div>
      <div>
        <p className="text-xs uppercase tracking-[0.16em] text-sage">Match</p>
        <p className="font-semibold text-ink">{label}</p>
      </div>
    </div>
  )
}

export function PageHeader({ kicker, title, description, action }) {
  return (
    <div className="mb-8 flex flex-wrap items-end justify-between gap-4">
      <div>
        {kicker && <p className="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-copper-dark">{kicker}</p>}
        <h1 className="text-3xl font-semibold text-ink">{title}</h1>
        {description && <p className="mt-2 max-w-2xl text-moss/80">{description}</p>}
      </div>
      {action}
    </div>
  )
}
