import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { ACTIVITIES } from '../lib/constants'
import { Badge, Button, Card, Field, PageHeader, inputClass } from '../components/ui'

export default function AgreementPage() {
  const { id } = useParams()
  const { user } = useAuth()
  const [item, setItem] = useState(null)
  const [form, setForm] = useState({})
  const [message, setMessage] = useState('')

  async function load() {
    const { data } = await api.get(`/agreements/${id}`)
    setItem(data)
    setForm(data)
  }

  useEffect(() => { load() }, [id])

  function set(key, value) {
    setForm((current) => ({ ...current, [key]: value }))
  }

  async function save(event) {
    event.preventDefault()
    await api.put(`/agreements/${id}`, {
      shared_goal: form.shared_goal,
      problem_opportunity: form.problem_opportunity,
      primary_activity: form.primary_activity,
      supporting_activity: form.supporting_activity || null,
      promised_output: form.promised_output,
      benefit_dosen: form.benefit_dosen,
      benefit_industry: form.benefit_industry,
      success_indicator: form.success_indicator,
      potential_collaboration: form.potential_collaboration,
    })
    setMessage('Draft disimpan. Persetujuan direset sampai kedua pihak approve lagi.')
    load()
  }

  async function approve() {
    const { data } = await api.post(`/agreements/${id}/approve`)
    setItem(data)
    setMessage(data.program ? 'Agreement confirmed. Program aktif.' : 'Persetujuan tercatat.')
  }

  if (!item) return null

  return (
    <div>
      <PageHeader
        kicker="Industry Immersion Agreement"
        title="Single source of truth"
        description="Shared goals, timeline, output, dan komitmen kedua pihak. Program tidak aktif sebelum ini AGREED."
        action={<Badge tone="copper">{item.status}</Badge>}
      />
      <form className="grid gap-4" onSubmit={save}>
        <Card className="grid gap-4 md:grid-cols-2">
          <p className="md:col-span-2 text-sm text-moss/80">Dosen {item.dosen?.name} × Mentor {item.mentor?.name} · {item.business_unit}</p>
          <div className="md:col-span-2"><Field label="Shared goal"><textarea className={inputClass} rows="3" value={form.shared_goal || ''} onChange={(e) => set('shared_goal', e.target.value)} /></Field></div>
          <div className="md:col-span-2"><Field label="Problem / opportunity"><textarea className={inputClass} rows="3" value={form.problem_opportunity || ''} onChange={(e) => set('problem_opportunity', e.target.value)} /></Field></div>
          <Field label="Aktivitas utama">
            <select className={inputClass} value={form.primary_activity || 'riset'} onChange={(e) => set('primary_activity', e.target.value)}>
              {ACTIVITIES.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
            </select>
          </Field>
          <Field label="Aktivitas pendukung">
            <select className={inputClass} value={form.supporting_activity || ''} onChange={(e) => set('supporting_activity', e.target.value)}>
              <option value="">Tidak ada</option>
              {ACTIVITIES.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
            </select>
          </Field>
          <div className="md:col-span-2"><Field label="Output yang dijanjikan"><textarea className={inputClass} rows="3" value={form.promised_output || ''} onChange={(e) => set('promised_output', e.target.value)} /></Field></div>
          <Field label="Manfaat dosen / TSU"><textarea className={inputClass} rows="3" value={form.benefit_dosen || ''} onChange={(e) => set('benefit_dosen', e.target.value)} /></Field>
          <Field label="Manfaat industri"><textarea className={inputClass} rows="3" value={form.benefit_industry || ''} onChange={(e) => set('benefit_industry', e.target.value)} /></Field>
          <div className="md:col-span-2"><Field label="Success indicator"><textarea className={inputClass} rows="2" value={form.success_indicator || ''} onChange={(e) => set('success_indicator', e.target.value)} /></Field></div>
          <div className="md:col-span-2"><Field label="Potensi kolaborasi"><textarea className={inputClass} rows="2" value={form.potential_collaboration || ''} onChange={(e) => set('potential_collaboration', e.target.value)} /></Field></div>
        </Card>
        <div className="flex flex-wrap gap-3">
          <Button variant="ghost">Simpan draft</Button>
          {user.role === 'mentor' && <Button type="button" onClick={approve}>Mentor approve</Button>}
          {user.role === 'user' && <Button type="button" onClick={approve}>Dosen approve</Button>}
          {item.program && <Link to={`/app/programs/${item.program.id}`} className="rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-parchment">Masuk program</Link>}
        </div>
        {message && <p className="text-sm text-moss">{message}</p>}
      </form>
    </div>
  )
}
