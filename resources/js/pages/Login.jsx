import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { Button, Field, inputClass } from '../components/ui'

const demos = [
  ['Dosen', 'dosen@immersi.id'],
  ['Dosen 2', 'dosen2@immersi.id'],
  ['Mentor', 'mentor@immersi.id'],
  ['Admin', 'admin@immersi.id'],
]

export default function Login() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [email, setEmail] = useState('dosen@immersi.id')
  const [password, setPassword] = useState('password')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function submit(event) {
    event.preventDefault()
    setError('')
    setLoading(true)
    try {
      const user = await login(email, password)
      navigate(user.verification_status === 'pending_profile' ? '/app/profile' : '/app')
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal masuk.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      <section className="relative hidden flex-col justify-between bg-ink px-12 py-12 text-white lg:flex">
        <div>
          <p className="text-2xl font-semibold">IMMERSI</p>
          <p className="mt-1 text-xs font-medium uppercase tracking-[0.2em] text-mint-light">TSU Industry Immersion</p>
        </div>
        <div>
          <p className="text-sm font-medium text-mint">Dosen × Industri</p>
          <h1 className="mt-4 max-w-md text-4xl font-semibold leading-tight">
            Masuk untuk melanjutkan program immersion.
          </h1>
          <p className="mt-4 max-w-md text-sm leading-6 text-white/70">
            Identify, match, dan berkolaborasi dengan unit bisnis TSPM dan TSIC selama 60 hari.
          </p>
        </div>
        <p className="text-xs text-white/50">Tiga Serangkai · Industry Immersion</p>
      </section>

      <section className="flex items-center justify-center bg-cream px-6 py-12">
        <div className="w-full max-w-[420px]">
          <div className="mb-8 lg:hidden">
            <p className="text-xl font-semibold text-ink">IMMERSI</p>
            <p className="text-xs text-moss/70">TSU Industry Immersion</p>
          </div>
          <h2 className="text-2xl font-semibold text-ink">Masuk ke akun</h2>
          <p className="mt-2 text-sm text-moss/80">Gunakan email dan kata sandi yang sudah terdaftar.</p>

          <form className="mt-8 space-y-4" onSubmit={submit}>
            <Field label="Email">
              <input className={inputClass} type="email" value={email} onChange={(e) => setEmail(e.target.value)} autoComplete="username" />
            </Field>
            <Field label="Kata sandi">
              <input className={inputClass} type="password" value={password} onChange={(e) => setPassword(e.target.value)} autoComplete="current-password" />
            </Field>
            {error && <p className="text-sm text-red-600">{error}</p>}
            <Button className="w-full" disabled={loading}>{loading ? 'Memproses…' : 'Masuk'}</Button>
          </form>

          <div className="mt-8">
            <p className="mb-3 text-xs font-medium text-moss/70">Akun demo</p>
            <div className="grid grid-cols-2 gap-2">
              {demos.map(([label, value]) => (
                <button
                  key={value}
                  type="button"
                  className={`rounded-lg border px-3 py-2 text-sm ${
                    email === value ? 'border-mint bg-mint/10 font-medium text-ink' : 'border-clay text-moss hover:border-mint'
                  }`}
                  onClick={() => setEmail(value)}
                >
                  {label}
                </button>
              ))}
            </div>
          </div>

          <p className="mt-8 text-sm text-moss/80">
            Belum punya akun? <Link to="/register" className="font-semibold text-copper-dark">Daftar</Link>
          </p>
        </div>
      </section>
    </div>
  )
}
