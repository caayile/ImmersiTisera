import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../api/client'
import { ACTIVITIES } from '../lib/constants'
import { Badge, Button, Card, Field, PageHeader, ScoreRing, inputClass } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants, slideLeft, slideRight } from '../lib/motion'

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
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Match & Commit" title={item.title} description={`${item.mentor?.company} · Mentor ${item.mentor?.name}`} />

      <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        {/* Detail card */}
        <motion.div variants={slideLeft} initial="hidden" animate="visible">
          <Card>
            <ScoreRing score={item.match_score} label={item.match_label} />
            <motion.dl
              className="mt-6 space-y-4 text-sm"
              variants={staggerContainer}
              initial="hidden"
              animate="visible"
            >
              {[
                ['Problem', item.problem],
                ['Opportunity', item.opportunity],
                ['Output yang diharapkan', item.expected_output],
              ].map(([label, value]) => (
                <motion.div key={label} variants={cardVariants}>
                  <dt className="text-xs uppercase tracking-widest text-sage">{label}</dt>
                  <dd className="mt-1">{value}</dd>
                </motion.div>
              ))}
              <motion.div variants={cardVariants}>
                <dt className="text-xs uppercase tracking-widest text-sage">Kompetensi</dt>
                <dd className="mt-2 flex flex-wrap gap-2">
                  {(item.needed_expertise || []).map((tag) => (
                    <motion.span key={tag} whileHover={{ scale: 1.08 }}>
                      <Badge>{tag}</Badge>
                    </motion.span>
                  ))}
                </dd>
              </motion.div>
            </motion.dl>
          </Card>
        </motion.div>

        {/* Apply card */}
        <motion.div variants={slideRight} initial="hidden" animate="visible">
          <Card>
            <div className="mb-4">
              <Badge tone={item.status === 'open' ? 'sage' : 'copper'}>
                {item.status === 'open' ? 'Lowongan dibuka' : 'Lowongan ditutup'}
              </Badge>
            </div>
            {item.application ? (
              <p>Minat sudah diajukan ({item.application.status}).</p>
            ) : item.status !== 'open' ? (
              <p className="text-sm text-moss/80">Pendaftaran pada departemen ini sedang ditutup. Silakan lihat lowongan lain.</p>
            ) : (
              <motion.form
                className="space-y-4"
                onSubmit={apply}
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.2 }}
              >
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
                <AnimatePresence>
                  {error && (
                    <motion.p
                      key="err"
                      className="text-sm text-copper"
                      initial={{ opacity: 0, y: -6 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: -6 }}
                    >
                      {error}
                    </motion.p>
                  )}
                </AnimatePresence>
                <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
                  <Button>Apply / submit interest</Button>
                </motion.div>
              </motion.form>
            )}
          </Card>
        </motion.div>
      </div>
    </motion.div>
  )
}
