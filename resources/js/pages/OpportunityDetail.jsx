import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import api from '../api/client'
import { ACTIVITIES } from '../lib/constants'
import { Badge, Button, Card, Field, PageHeader, ScoreRing, inputClass } from '../components/ui'

export default function OpportunityDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [item, setItem] = useState(null)
  const [form, setForm] = useState({
    primary_activity: 'riset',
    supporting_activity: 'observasi',
    proposed_shared_goal: '',
  })
  const [error, setError] = useState('')

  useEffect(() => {
    api.get(`/opportunities/${id}`).then(({ data }) => {
      setItem(data)
      setForm((current) => ({
        ...current,
        proposed_shared_goal: data.opportunity || '',
      }))
    })
  }, [id])

  async function apply(event) {
    event.preventDefault()
    setError('')
    try {
      await api.post('/applications', { ...form, opportunity_id: Number(id) })
      navigate('/app/applications')
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal mengajukan minat.')
    }
  }

  if (!item) return null

  return (
    <div>
      <PageHeader kicker="Match & Commit" title={item.title} description={`${item.mentor?.company} · Mentor ${item.mentor?.name}`} />
      <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <Card>
          <ScoreRing score={item.match_score} label={item.match_label} />
          <dl className="mt-6 space-y-4 text-sm">
            <div><dt className="text-xs uppercase tracking-widest text-sage">Problem</dt><dd className="mt-1">{item.problem}</dd></div>
            <div><dt className="text-xs uppercase tracking-widest text-sage">Opportunity</dt><dd className="mt-1">{item.opportunity}</dd></div>
            <div><dt className="text-xs uppercase tracking-widest text-sage">Output yang diharapkan</dt><dd className="mt-1">{item.expected_output}</dd></div>
            <div><dt className="text-xs uppercase tracking-widest text-sage">Kompetensi</dt><dd className="mt-2 flex flex-wrap gap-2">{(item.needed_expertise || []).map((tag) => <Badge key={tag}>{tag}</Badge>)}</dd></div>
          </dl>
        </Card>
        <Card>
          {item.application ? (
            <p>Minat sudah diajukan ({item.application.status}).</p>
          ) : (
            <form className="space-y-4" onSubmit={apply}>
              <Field label="Aktivitas utama">
                <select className={inputClass} value={form.primary_activity} onChange={(e) => setForm({ ...form, primary_activity: e.target.value })}>
                  {ACTIVITIES.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                </select>
              </Field>
              <Field label="Aktivitas pendukung">
                <select className={inputClass} value={form.supporting_activity} onChange={(e) => setForm({ ...form, supporting_activity: e.target.value })}>
                  <option value="">Tidak ada</option>
                  {ACTIVITIES.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                </select>
              </Field>
              <Field label="Usulan shared goal">
                <textarea className={inputClass} rows="5" value={form.proposed_shared_goal} onChange={(e) => setForm({ ...form, proposed_shared_goal: e.target.value })} required />
              </Field>
              {error && <p className="text-sm text-copper">{error}</p>}
              <Button>Apply / submit interest</Button>
            </form>
          )}
        </Card>
      </div>
    </div>
  )
}
