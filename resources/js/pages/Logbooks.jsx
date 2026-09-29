import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../api/client'
import { formatDate } from '../lib/constants'
import { Badge, Button, Card, Field, PageHeader, inputClass } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants, slideLeft, slideRight } from '../lib/motion'

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

  useEffect(() => { load() }, [])

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
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader
        kicker="Immersion · Rekam jejak"
        title="Logbook"
        description="Refleksi harian selama magang dosen. Diisi oleh peserta, lalu diverifikasi mentor."
        action={program ? <Badge tone="copper">{program.status}</Badge> : null}
      />

      {loading && (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
          <Card>Memuat logbook…</Card>
        </motion.div>
      )}

      <AnimatePresence>
        {!loading && !program && (
          <motion.div
            key="no-program"
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0 }}
          >
            <Card>
              <h3 className="font-semibold">Logbook terbuka setelah program aktif</h3>
              <p className="mt-1 text-sm text-moss/80">
                Belum ada program magang dosen untuk Anda. Lengkapi profil lalu ajukan minat ke unit bisnis.
              </p>
              <div className="mt-4 flex flex-wrap gap-3">
                <Link to="/app/opportunities" className="text-sm font-semibold text-copper">Cari opportunity →</Link>
                <Link to="/app/profile" className="text-sm font-semibold text-moss">Lengkapi profil</Link>
              </div>
            </Card>
          </motion.div>
        )}
      </AnimatePresence>

      {!loading && program && (
        <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
          {/* Form panel */}
          <motion.div variants={slideLeft} initial="hidden" animate="visible">
            {canWrite ? (
              <Card>
                <h3 className="font-display text-2xl">Daily logbook</h3>
                <form className="mt-4 space-y-3" onSubmit={submit}>
                  <Field label="Tanggal">
                    <input className={inputClass} type="date" value={form.entry_date} onChange={(e) => setForm({ ...form, entry_date: e.target.value })} />
                  </Field>
                  {[
                    ['What I did', 'what_did'],
                    ['What I learned', 'what_learned'],
                    ['What I found', 'what_found'],
                    ['Kendala', 'obstacles'],
                    ['Output hari ini', 'output'],
                  ].map(([label, key]) => (
                    <Field key={key} label={label}>
                      <textarea className={inputClass} rows="2" value={form[key]} onChange={(e) => setForm({ ...form, [key]: e.target.value })} required={['what_did', 'what_learned', 'what_found'].includes(key)} />
                    </Field>
                  ))}
                  <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
                    <Button disabled={saving}>{saving ? 'Mengirim…' : 'Submit'}</Button>
                  </motion.div>
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
          </motion.div>

          {/* Logbook list */}
          <motion.div
            className="space-y-3"
            variants={slideRight}
            initial="hidden"
            animate="visible"
          >
            {logbooks.length === 0 && (
              <Card><p className="text-sm text-moss/80">Belum ada entri logbook.</p></Card>
            )}
            <motion.div
              variants={staggerContainer}
              initial="hidden"
              animate="visible"
            >
              <AnimatePresence>
                {logbooks.map((item) => (
                  <motion.div
                    key={item.id}
                    variants={cardVariants}
                    layout
                    exit={{ opacity: 0, x: 20, transition: { duration: 0.2 } }}
                    className="mb-3"
                  >
                    <Card>
                      <div className="flex items-center justify-between gap-3">
                        <p className="font-semibold">{formatDate(item.entry_date)}</p>
                        <Badge tone={item.mentor_verified ? 'sage' : 'copper'}>
                          {item.mentor_verified ? 'Terverifikasi' : item.status}
                        </Badge>
                      </div>
                      <p className="mt-2 text-sm"><b>Did:</b> {item.what_did}</p>
                      <p className="text-sm"><b>Learned:</b> {item.what_learned}</p>
                      <p className="text-sm"><b>Found:</b> {item.what_found}</p>
                    </Card>
                  </motion.div>
                ))}
              </AnimatePresence>
            </motion.div>
          </motion.div>
        </div>
      )}
    </motion.div>
  )
}
