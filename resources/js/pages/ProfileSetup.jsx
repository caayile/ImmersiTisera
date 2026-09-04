import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { PURPOSE, ORGANIZATIONS, unitsFor } from '../lib/constants'
import { Button, Card, Field, PageHeader, inputClass } from '../components/ui'

const emptyDosen = {
  nidn: '',
  prodi: '',
  department: '',
  expertise: '',
  interests: '',
  experience: '',
  purpose: 'riset',
  goals: '',
  competency_gap: '',
}

const emptyMentor = {
  company_name: 'Tiga Serangkai',
  industry_field: 'TSPM',
  business_unit: '',
  department_function: '',
  job_title: '',
  expertise: '',
  industry_needs: '',
  problems: '',
  opportunities: '',
  dosen_needs: '',
  availability: '',
}

function csv(value) {
  return String(value || '')
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
}

export default function ProfileSetup() {
  const { user, setUser } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState(user?.role === 'mentor' ? emptyMentor : emptyDosen)
  const [message, setMessage] = useState('')

  useEffect(() => {
    if (!user) return
    if (user.role === 'mentor') {
      const profile = user.mentor_profile || {}
      setForm({
        ...emptyMentor,
        ...profile,
        expertise: (profile.expertise || []).join(', '),
        industry_field: ['TSPM', 'TSIC'].includes(profile.industry_field) ? profile.industry_field : 'TSPM',
      })
    } else if (user.role === 'user') {
      const profile = user.dosen_profile || {}
      setForm({
        ...emptyDosen,
        ...profile,
        expertise: (profile.expertise || []).join(', '),
        interests: (profile.interests || []).join(', '),
      })
    }
  }, [user])

  function set(key, value) {
    setForm((current) => ({ ...current, [key]: value }))
  }

  async function submit(event) {
    event.preventDefault()
    const payload = user.role === 'mentor'
      ? { ...form, expertise: csv(form.expertise) }
      : { ...form, expertise: csv(form.expertise), interests: csv(form.interests) }

    const { data } = await api.put('/profile', payload)
    setUser(data)
    setMessage('Profil tersimpan. Menunggu verifikasi admin.')
    if (data.verification_status === 'verified') navigate('/app')
  }

  if (user?.role === 'admin') {
    return <p>Admin tidak perlu mengisi profil industri.</p>
  }

  return (
    <div>
      <PageHeader
        kicker="Identify"
        title="Lengkapi profil"
        description="Data ini menjadi dasar matching: kompetensi, tujuan, dan kebutuhan — bukan sekadar nama bidang."
      />
      {user?.verification_status === 'pending_verification' && (
        <Card className="mb-6 bg-copper/10">Akun menunggu verifikasi admin. Anda belum bisa masuk ke matching.</Card>
      )}
      {user?.verification_status === 'rejected' && (
        <Card className="mb-6 bg-copper/10">Ditolak: {user.rejection_reason}. Perbaiki profil lalu kirim ulang.</Card>
      )}
      <Card>
        <form className="grid gap-4 md:grid-cols-2" onSubmit={submit}>
          {user?.role === 'user' ? (
            <>
              <Field label="NIDN"><input className={inputClass} value={form.nidn} onChange={(e) => set('nidn', e.target.value)} /></Field>
              <Field label="Prodi"><input className={inputClass} value={form.prodi} onChange={(e) => set('prodi', e.target.value)} required /></Field>
              <Field label="Departemen"><input className={inputClass} value={form.department} onChange={(e) => set('department', e.target.value)} /></Field>
              <Field label="Tujuan">
                <select className={inputClass} value={form.purpose} onChange={(e) => set('purpose', e.target.value)}>
                  {PURPOSE.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                </select>
              </Field>
              <Field label="Keahlian (pisahkan koma)"><input className={inputClass} value={form.expertise} onChange={(e) => set('expertise', e.target.value)} required /></Field>
              <Field label="Minat"><input className={inputClass} value={form.interests} onChange={(e) => set('interests', e.target.value)} required /></Field>
              <div className="md:col-span-2"><Field label="Pengalaman"><textarea className={inputClass} rows="3" value={form.experience} onChange={(e) => set('experience', e.target.value)} required /></Field></div>
              <div className="md:col-span-2"><Field label="Tujuan mengikuti program"><textarea className={inputClass} rows="3" value={form.goals} onChange={(e) => set('goals', e.target.value)} required /></Field></div>
              <div className="md:col-span-2"><Field label="Kebutuhan / gap kompetensi"><textarea className={inputClass} rows="3" value={form.competency_gap} onChange={(e) => set('competency_gap', e.target.value)} required /></Field></div>
            </>
          ) : (
            <>
              <Field label="Perusahaan / institusi"><input className={inputClass} value={form.company_name} onChange={(e) => set('company_name', e.target.value)} required /></Field>
              <Field label="Departemen">
                <select
                  className={inputClass}
                  value={ORGANIZATIONS.includes(form.industry_field) ? form.industry_field : 'TSPM'}
                  onChange={(e) => setForm((current) => ({ ...current, industry_field: e.target.value, business_unit: '' }))}
                >
                  {ORGANIZATIONS.map((item) => <option key={item} value={item}>{item}</option>)}
                </select>
              </Field>
              <Field label="Unit bisnis">
                <select className={inputClass} value={form.business_unit} onChange={(e) => set('business_unit', e.target.value)} required>
                  <option value="">Pilih unit bisnis</option>
                  {unitsFor(ORGANIZATIONS.includes(form.industry_field) ? form.industry_field : 'TSPM').map((item) => (
                    <option key={item} value={item}>{item}</option>
                  ))}
                </select>
              </Field>
              <Field label="Fungsi / department"><input className={inputClass} value={form.department_function} onChange={(e) => set('department_function', e.target.value)} /></Field>
              <Field label="Jabatan"><input className={inputClass} value={form.job_title} onChange={(e) => set('job_title', e.target.value)} /></Field>
              <Field label="Ketersediaan"><input className={inputClass} value={form.availability} onChange={(e) => set('availability', e.target.value)} /></Field>
              <div className="md:col-span-2"><Field label="Expertise"><input className={inputClass} value={form.expertise} onChange={(e) => set('expertise', e.target.value)} required /></Field></div>
              <div className="md:col-span-2"><Field label="Kebutuhan industri"><textarea className={inputClass} rows="3" value={form.industry_needs} onChange={(e) => set('industry_needs', e.target.value)} required /></Field></div>
              <div className="md:col-span-2"><Field label="Problem"><textarea className={inputClass} rows="3" value={form.problems} onChange={(e) => set('problems', e.target.value)} required /></Field></div>
              <div className="md:col-span-2"><Field label="Opportunity"><textarea className={inputClass} rows="3" value={form.opportunities} onChange={(e) => set('opportunities', e.target.value)} required /></Field></div>
              <div className="md:col-span-2"><Field label="Kebutuhan terhadap dosen"><textarea className={inputClass} rows="3" value={form.dosen_needs} onChange={(e) => set('dosen_needs', e.target.value)} required /></Field></div>
            </>
          )}
          <div className="md:col-span-2">
            <Button>Simpan profil</Button>
            {message && <p className="mt-3 text-sm text-moss">{message}</p>}
          </div>
        </form>
      </Card>
    </div>
  )
}
