import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { Button, Field, inputClass } from '../components/ui'

export default function Register() {
  const { register } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    role: 'user',
    phone: '',
  })
  const [error, setError] = useState('')

  function set(key, value) {
    setForm((current) => ({ ...current, [key]: value }))
  }

  async function submit(event) {
    event.preventDefault()
    setError('')
    try {
      await register(form)
      navigate('/app/profile')
    } catch (err) {
      setError(err.response?.data?.message || 'Registrasi gagal.')
    }
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      <section className="hidden flex-col justify-between bg-ink px-12 py-12 text-white lg:flex">
        <div>
          <p className="text-2xl font-semibold">IMMERSI</p>
          <p className="mt-1 text-xs font-medium uppercase tracking-[0.2em] text-mint-light">TSU Industry Immersion</p>
        </div>
        <div>
          <h1 className="max-w-md text-4xl font-semibold leading-tight">Daftar sesuai peran Anda.</h1>
          <p className="mt-4 max-w-md text-sm leading-6 text-white/70">Dosen masuk ke unit bisnis TSPM atau TSIC. Mentor membuka opportunity dari departemennya.</p>
        </div>
        <p className="text-xs text-white/50">Tiga Serangkai · Industry Immersion</p>
      </section>
      <section className="flex items-center justify-center bg-cream px-6 py-12">
        <div className="w-full max-w-[480px]">
          <h2 className="text-2xl font-semibold text-ink">Buat akun</h2>
          <div className="mt-5 grid grid-cols-2 gap-3">
            {[
              ['user', 'Dosen', 'Masuk industri dan kolaborasi.'],
              ['mentor', 'Mentor', 'Buka opportunity unit bisnis.'],
            ].map(([value, label, hint]) => (
              <button
                key={value}
                type="button"
                onClick={() => set('role', value)}
                className={`rounded-xl border px-4 py-4 text-left ${
                  form.role === value ? 'border-mint bg-mint/10' : 'border-clay bg-white'
                }`}
              >
                <p className="font-semibold text-ink">{label}</p>
                <p className="mt-1 text-xs text-moss/70">{hint}</p>
              </button>
            ))}
          </div>
          <form className="mt-6 space-y-4" onSubmit={submit}>
            <Field label="Nama"><input className={inputClass} value={form.name} onChange={(e) => set('name', e.target.value)} required /></Field>
            <Field label="Email"><input className={inputClass} type="email" value={form.email} onChange={(e) => set('email', e.target.value)} required /></Field>
            <Field label="No. telepon"><input className={inputClass} value={form.phone} onChange={(e) => set('phone', e.target.value)} /></Field>
            <Field label="Kata sandi"><input className={inputClass} type="password" value={form.password} onChange={(e) => set('password', e.target.value)} required /></Field>
            {error && <p className="text-sm text-red-600">{error}</p>}
            <Button className="w-full">Lanjut lengkapi profil</Button>
          </form>
          <p className="mt-6 text-sm text-moss/80">
            Sudah punya akun? <Link to="/login" className="font-semibold text-copper-dark">Masuk</Link>
          </p>
        </div>
      </section>
    </div>
  )
}
