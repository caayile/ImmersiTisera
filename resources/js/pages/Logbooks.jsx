import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/client'
import { formatDate } from '../lib/constants'
import { Badge, Button, Card, Field, PageHeader, inputClass } from '../components/ui'

const emptyForm = {
  entry_date: new Date().toISOString().slice(0, 10),
  what_did: '',
  what_learned: '',
  what_found: '',
  obstacles: '',
  output: '',
}

export default function Logbooks() {
  const [program, setProgram] = useState(null)
  const [loading, setLoading] = useState(true)
  const [form, setForm] = useState(emptyForm)
  const [saving, setSaving] = useState(false)

  async function load() {
    setLoading(true)
    try {
      const { data } = await api.get('/programs')
      const list = Array.isArray(data) ? data : []
      const pick = list.find((item) => item.status === 'active') || list[0]

      if (pick) {
        const { data: detail } = await api.get(`/programs/${pick.id}`)
        setProgram(detail)
      } else {
        setProgram(null)
      }
    } catch {
      setProgram(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load()
  }, [])

  async function submit(event) {
    event.preventDefault()
    if (!program) return

    setSaving(true)
    try {
      await api.post(`/programs/${program.id}/logbooks`, form)
      setForm({ ...emptyForm, entry_date: new Date().toISOString().slice(0, 10) })
      await load()
    } finally {
      setSaving(false)
    }
  }

  const logbooks = program?.logbooks || []
  const canWrite = program?.status === 'active'

  return (
    <div>
      <PageHeader
        kicker="Immersion · Rekam jejak"
        title="Logbook"
        description="Refleksi harian selama imersi. Diisi oleh peserta, lalu diverifikasi mentor."
        action={program ? <Badge tone="copper">{program.status}</Badge> : null}
      />

      {loading && <Card>Memuat logbook…</Card>}

      {!loading && !program && (
        <Card>
          <h3 className="font-semibold">Logbook terbuka setelah program aktif</h3>
          <p className="mt-1 text-sm text-moss/80">
            Belum ada program imersi untuk Anda. Lengkapi profil lalu ajukan minat ke unit bisnis.
          </p>
          <div className="mt-4 flex flex-wrap gap-3">
            <Link to="/app/opportunities" className="text-sm font-semibold text-copper">
              Cari opportunity →
            </Link>
            <Link to="/app/profile" className="text-sm font-semibold text-moss">
              Lengkapi profil
            </Link>
          </div>
        </Card>
      )}

      {!loading && program && (
        <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
          {canWrite ? (
            <Card>
              <h3 className="font-display text-2xl">Daily logbook</h3>
              <form className="mt-4 space-y-3" onSubmit={submit}>
                <Field label="Tanggal">
                  <input
                    className={inputClass}
                    type="date"
                    value={form.entry_date}
                    onChange={(e) => setForm({ ...form, entry_date: e.target.value })}
                  />
                </Field>
                <Field label="What I did">
                  <textarea className={inputClass} rows="2" value={form.what_did} onChange={(e) => setForm({ ...form, what_did: e.target.value })} required />
                </Field>
                <Field label="What I learned">
                  <textarea className={inputClass} rows="2" value={form.what_learned} onChange={(e) => setForm({ ...form, what_learned: e.target.value })} required />
                </Field>
                <Field label="What I found">
                  <textarea className={inputClass} rows="2" value={form.what_found} onChange={(e) => setForm({ ...form, what_found: e.target.value })} required />
                </Field>
                <Field label="Kendala">
                  <textarea className={inputClass} rows="2" value={form.obstacles} onChange={(e) => setForm({ ...form, obstacles: e.target.value })} />
                </Field>
                <Field label="Output hari ini">
                  <textarea className={inputClass} rows="2" value={form.output} onChange={(e) => setForm({ ...form, output: e.target.value })} />
                </Field>
                <Button disabled={saving}>{saving ? 'Mengirim…' : 'Submit'}</Button>
              </form>
            </Card>
          ) : (
            <Card>
              <h3 className="font-display text-2xl">Belum bisa diisi</h3>
              <p className="mt-2 text-sm text-moss/80">
                Logbook hanya terbuka saat program status <b>active</b>. Status saat ini: <b>{program.status}</b>.
              </p>
            </Card>
          )}

          <div className="space-y-3">
            {logbooks.length === 0 && (
              <Card>
                <p className="text-sm text-moss/80">Belum ada entri logbook.</p>
              </Card>
            )}
            {logbooks.map((item) => (
              <Card key={item.id}>
                <div className="flex items-center justify-between gap-3">
                  <p className="font-semibold">{formatDate(item.entry_date)}</p>
                  <Badge tone={item.mentor_verified ? 'sage' : 'copper'}>
                    {item.mentor_verified ? 'Terverifikasi' : item.status}
                  </Badge>
                </div>
                <p className="mt-2 text-sm">
                  <b>Did:</b> {item.what_did}
                </p>
                <p className="text-sm">
                  <b>Learned:</b> {item.what_learned}
                </p>
                <p className="text-sm">
                  <b>Found:</b> {item.what_found}
                </p>
              </Card>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
