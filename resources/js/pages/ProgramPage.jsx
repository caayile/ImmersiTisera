import { useEffect, useState } from 'react'
import { useParams, useSearchParams } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { CONNECT_OPTIONS, MATURITY, OUTPUT_CATEGORIES, PHASES, formatDate } from '../lib/constants'
import { Badge, Button, Card, Field, PageHeader, inputClass } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants, slideLeft, slideRight, fadeIn } from '../lib/motion'

const tabs = ['Perjalanan', 'Logbook', 'Mentoring', 'Checkpoint', 'Evaluasi', 'Laporan', 'Output', 'Connect']

export default function ProgramPage() {
  const { id } = useParams()
  const { user } = useAuth()
  const [searchParams, setSearchParams] = useSearchParams()
  const [program, setProgram] = useState(null)
  const [tab, setTab] = useState(searchParams.get('tab') || 'Perjalanan')
  const [message, setMessage] = useState('')

  async function load() {
    const { data } = await api.get(`/programs/${id}`)
    setProgram(data)
  }

  useEffect(() => { load() }, [id])

  function chooseTab(item) {
    setTab(item)
    setSearchParams(item === 'Perjalanan' ? {} : { tab: item }, { replace: true })
  }

  if (!program) return null

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader
        kicker={`Minggu ${program.current_week} · ${program.computed_phase}`}
        title={program.opportunity?.title || 'Program'}
        description={program.agreement?.shared_goal}
        action={<Badge tone="copper">{program.status}</Badge>}
      />

      {/* Tab bar */}
      <motion.div
        className="mb-6 flex flex-wrap gap-2"
        initial={{ opacity: 0, y: -8 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1, duration: 0.35 }}
      >
        {tabs.map((item) => (
          <motion.button
            key={item}
            onClick={() => chooseTab(item)}
            whileHover={{ scale: 1.04 }}
            whileTap={{ scale: 0.96 }}
            className={`rounded-lg px-4 py-2 text-sm font-medium transition ${tab === item ? 'bg-mint text-white' : 'bg-white text-ink'}`}
          >
            {item}
          </motion.button>
        ))}
      </motion.div>

      {/* Global message */}
      <AnimatePresence>
        {message && (
          <motion.p
            key="msg"
            className="mb-4 text-sm text-moss"
            initial={{ opacity: 0, y: -6 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -6 }}
            transition={{ duration: 0.25 }}
          >
            {message}
          </motion.p>
        )}
      </AnimatePresence>

      {/* Tab content — crossfade on switch */}
      <AnimatePresence mode="wait">
        <motion.div
          key={tab}
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -8 }}
          transition={{ duration: 0.28, ease: 'easeOut' }}
        >
          {tab === 'Perjalanan' && <Journey program={program} onSaved={load} setMessage={setMessage} />}
          {tab === 'Logbook'   && <LogbookTab program={program} user={user} onSaved={load} />}
          {tab === 'Mentoring' && <MentoringTab program={program} onSaved={load} />}
          {tab === 'Checkpoint'&& <CheckpointTab program={program} user={user} onSaved={load} />}
          {tab === 'Evaluasi'  && <EvalTab program={program} onSaved={load} />}
          {tab === 'Laporan'   && <ReportTab program={program} onSaved={load} />}
          {tab === 'Output'    && <OutputTab program={program} onSaved={load} />}
          {tab === 'Connect'   && <ConnectTab program={program} onSaved={load} />}
        </motion.div>
      </AnimatePresence>
    </motion.div>
  )
}

/* ─── Journey ───────────────────────────────────────────────── */
function Journey({ program, onSaved, setMessage }) {
  const [form, setForm] = useState({
    industry_insight: program.industry_insight || '',
    problem_statement: program.problem_statement || '',
    contribution_notes: program.contribution_notes || '',
  })

  async function save(event) {
    event.preventDefault()
    await api.put(`/programs/${program.id}/notes`, form)
    setMessage('Catatan fase tersimpan.')
    onSaved()
  }

  return (
    <div className="grid gap-4">
      {/* Phase timeline */}
      <motion.div
        className="grid gap-3 md:grid-cols-5"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {PHASES.map((phase) => (
          <motion.div
            key={phase.key}
            variants={cardVariants}
            whileHover={{ y: -4, transition: { duration: 0.2 } }}
          >
            <Card className={program.computed_phase === phase.key ? 'bg-mint text-white' : ''}>
              <p className="text-xs opacity-70">{phase.week}</p>
              <p className="font-display text-2xl">{phase.title}</p>
              <p className="mt-2 text-sm opacity-80">{phase.output}</p>
            </Card>
          </motion.div>
        ))}
      </motion.div>

      {/* Notes form */}
      <motion.form
        className="grid gap-4"
        onSubmit={save}
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2, duration: 0.4 }}
      >
        <Field label="Discover · Industry Insight">
          <textarea className={inputClass} rows="4" value={form.industry_insight} onChange={(e) => setForm({ ...form, industry_insight: e.target.value })} />
        </Field>
        <Field label="Understand · Problem Statement">
          <textarea className={inputClass} rows="4" value={form.problem_statement} onChange={(e) => setForm({ ...form, problem_statement: e.target.value })} />
        </Field>
        <Field label="Contribute · Try / Test / Develop">
          <textarea className={inputClass} rows="4" value={form.contribution_notes} onChange={(e) => setForm({ ...form, contribution_notes: e.target.value })} />
        </Field>
        <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
          <Button>Simpan catatan fase</Button>
        </motion.div>
      </motion.form>
    </div>
  )
}

/* ─── Logbook Tab ────────────────────────────────────────────── */
function LogbookTab({ program, user, onSaved }) {
  const [form, setForm] = useState({
    entry_date: new Date().toISOString().slice(0, 10),
    what_did: '',
    what_learned: '',
    what_found: '',
    obstacles: '',
    output: '',
  })

  async function submit(event) {
    event.preventDefault()
    await api.post(`/programs/${program.id}/logbooks`, form)
    setForm({ ...form, what_did: '', what_learned: '', what_found: '', obstacles: '', output: '' })
    onSaved()
  }

  return (
    <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
      {user.role === 'user' && (
        <motion.div variants={slideLeft} initial="hidden" animate="visible">
          <Card>
            <h3 className="font-display text-2xl">Daily logbook</h3>
            <form className="mt-4 space-y-3" onSubmit={submit}>
              <Field label="Tanggal"><input className={inputClass} type="date" value={form.entry_date} onChange={(e) => setForm({ ...form, entry_date: e.target.value })} /></Field>
              <Field label="What I did"><textarea className={inputClass} rows="2" value={form.what_did} onChange={(e) => setForm({ ...form, what_did: e.target.value })} required /></Field>
              <Field label="What I learned"><textarea className={inputClass} rows="2" value={form.what_learned} onChange={(e) => setForm({ ...form, what_learned: e.target.value })} required /></Field>
              <Field label="What I found"><textarea className={inputClass} rows="2" value={form.what_found} onChange={(e) => setForm({ ...form, what_found: e.target.value })} required /></Field>
              <Field label="Kendala"><textarea className={inputClass} rows="2" value={form.obstacles} onChange={(e) => setForm({ ...form, obstacles: e.target.value })} /></Field>
              <Field label="Output hari ini"><textarea className={inputClass} rows="2" value={form.output} onChange={(e) => setForm({ ...form, output: e.target.value })} /></Field>
              <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
                <Button>Submit</Button>
              </motion.div>
            </form>
          </Card>
        </motion.div>
      )}

      <motion.div
        className="space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {(program.logbooks || []).map((item) => (
          <motion.div key={item.id} variants={cardVariants}>
            <Card>
              <div className="flex items-center justify-between">
                <p className="font-semibold">{formatDate(item.entry_date)}</p>
                {item.mentor_verified
                  ? <Badge>Terverifikasi</Badge>
                  : user.role === 'mentor' && (
                    <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
                      <Button variant="ghost" onClick={async () => { await api.post(`/programs/${program.id}/logbooks/${item.id}/verify`); onSaved() }}>
                        Verifikasi
                      </Button>
                    </motion.div>
                  )
                }
              </div>
              <p className="mt-2 text-sm"><b>Did:</b> {item.what_did}</p>
              <p className="text-sm"><b>Learned:</b> {item.what_learned}</p>
              <p className="text-sm"><b>Found:</b> {item.what_found}</p>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </div>
  )
}

/* ─── Mentoring Tab ──────────────────────────────────────────── */
function MentoringTab({ program, onSaved }) {
  const [form, setForm] = useState({
    week: program.current_week || 1,
    found: '',
    working_on: '',
    next_action: '',
    mentor_feedback: '',
  })

  async function submit(event) {
    event.preventDefault()
    await api.post(`/programs/${program.id}/mentorings`, form)
    onSaved()
  }

  return (
    <div className="grid gap-6 lg:grid-cols-2">
      <motion.div variants={slideLeft} initial="hidden" animate="visible">
        <Card>
          <h3 className="font-display text-2xl">30-minute mentor conversation</h3>
          <form className="mt-4 space-y-3" onSubmit={submit}>
            <Field label="Minggu"><input className={inputClass} type="number" min="1" max="8" value={form.week} onChange={(e) => setForm({ ...form, week: Number(e.target.value) })} /></Field>
            <Field label="Apa yang sudah kamu temukan?"><textarea className={inputClass} rows="3" value={form.found} onChange={(e) => setForm({ ...form, found: e.target.value })} /></Field>
            <Field label="Apa yang sedang kamu kerjakan?"><textarea className={inputClass} rows="3" value={form.working_on} onChange={(e) => setForm({ ...form, working_on: e.target.value })} /></Field>
            <Field label="Apa berikutnya?"><textarea className={inputClass} rows="3" value={form.next_action} onChange={(e) => setForm({ ...form, next_action: e.target.value })} /></Field>
            <Field label="Feedback + next action mentor"><textarea className={inputClass} rows="3" value={form.mentor_feedback} onChange={(e) => setForm({ ...form, mentor_feedback: e.target.value })} /></Field>
            <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
              <Button>Simpan sesi</Button>
            </motion.div>
          </form>
        </Card>
      </motion.div>

      <motion.div
        className="space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {(program.mentorings || []).map((item) => (
          <motion.div key={item.id} variants={cardVariants} whileHover={{ x: 4, transition: { duration: 0.15 } }}>
            <Card>
              <p className="text-xs uppercase tracking-widest text-sage">Minggu {item.week}</p>
              <p className="mt-2 text-sm">{item.found}</p>
              <p className="mt-2 text-sm text-moss">{item.mentor_feedback}</p>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </div>
  )
}

/* ─── Checkpoint Tab ─────────────────────────────────────────── */
function CheckpointTab({ program, user, onSaved }) {
  const [form, setForm] = useState({
    week: program.current_week || 1,
    dosen_progress: '',
    mentor_status: 'agree',
    mentor_notes: '',
  })

  async function submit(event) {
    event.preventDefault()
    await api.post(`/programs/${program.id}/checkpoints`, form)
    onSaved()
  }

  return (
    <div>
      <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.4 }}>
        <Card>
          <form className="grid gap-4 md:grid-cols-2" onSubmit={submit}>
            <Field label="Minggu"><input className={inputClass} type="number" min="1" max="8" value={form.week} onChange={(e) => setForm({ ...form, week: Number(e.target.value) })} /></Field>
            <Field label="Progress dosen"><textarea className={inputClass} rows="3" value={form.dosen_progress} onChange={(e) => setForm({ ...form, dosen_progress: e.target.value })} /></Field>
            {user.role !== 'user' && (
              <>
                <Field label="Status mentor">
                  <select className={inputClass} value={form.mentor_status} onChange={(e) => setForm({ ...form, mentor_status: e.target.value })}>
                    <option value="agree">Agree</option>
                    <option value="need_improvement">Need improvement</option>
                  </select>
                </Field>
                <Field label="Catatan mentor"><textarea className={inputClass} rows="3" value={form.mentor_notes} onChange={(e) => setForm({ ...form, mentor_notes: e.target.value })} /></Field>
              </>
            )}
            <div className="md:col-span-2">
              <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
                <Button>Simpan checkpoint</Button>
              </motion.div>
            </div>
          </form>
        </Card>
      </motion.div>

      <motion.div
        className="mt-4 space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {(program.checkpoints || []).map((item) => (
          <motion.div key={item.id} variants={cardVariants}>
            <Card>Minggu {item.week} · {item.mentor_status || 'menunggu mentor'} · {item.dosen_progress}</Card>
          </motion.div>
        ))}
      </motion.div>
    </div>
  )
}

/* ─── Evaluasi Tab ───────────────────────────────────────────── */
function EvalTab({ program, onSaved }) {
  const [form, setForm] = useState({
    industry_understanding: 4,
    relationship: 4,
    output_quality: 4,
    mutual_benefit: 4,
    collaboration_potential: 4,
    comments: '',
  })

  async function submit(event) {
    event.preventDefault()
    await api.post(`/programs/${program.id}/evaluations`, form)
    onSaved()
  }

  return (
    <div className="grid gap-6 lg:grid-cols-2">
      <motion.div variants={slideLeft} initial="hidden" animate="visible">
        <Card>
          <p className="text-xs uppercase tracking-widest text-sage">Collaboration score</p>
          <motion.p
            className="font-display text-5xl"
            initial={{ opacity: 0, scale: 0.7 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.5, delay: 0.15, type: 'spring', stiffness: 200 }}
          >
            {program.collaboration_score || '—'}<span className="text-2xl"> / 5</span>
          </motion.p>
          <form className="mt-6 space-y-3" onSubmit={submit}>
            {['industry_understanding', 'relationship', 'output_quality', 'mutual_benefit', 'collaboration_potential'].map((key) => (
              <Field key={key} label={key.replaceAll('_', ' ')}>
                <input className={inputClass} type="number" min="1" max="5" value={form[key]} onChange={(e) => setForm({ ...form, [key]: Number(e.target.value) })} />
              </Field>
            ))}
            <Field label="Komentar"><textarea className={inputClass} rows="3" value={form.comments} onChange={(e) => setForm({ ...form, comments: e.target.value })} /></Field>
            <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
              <Button>Kirim evaluasi</Button>
            </motion.div>
          </form>
        </Card>
      </motion.div>

      <motion.div
        className="space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {(program.evaluations || []).map((item) => (
          <motion.div key={item.id} variants={cardVariants}>
            <Card>{item.user?.name} · {item.type}</Card>
          </motion.div>
        ))}
      </motion.div>
    </div>
  )
}

/* ─── Laporan Tab ────────────────────────────────────────────── */
function ReportTab({ program, onSaved }) {
  async function generate() {
    await api.post(`/programs/${program.id}/report/generate`)
    onSaved()
  }

  const content = program.report?.content || {}

  return (
    <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.4 }}>
      <Card>
        <div className="flex items-center justify-between">
          <h3 className="font-display text-2xl">Final report</h3>
          <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
            <Button onClick={generate}>Generate dari data program</Button>
          </motion.div>
        </div>
        <p className="mt-2 text-sm text-moss/70">Report adalah dokumentasi. Output tetap hasil nyata di tab Output.</p>
        <motion.dl
          className="mt-6 space-y-4 text-sm"
          variants={staggerContainer}
          initial="hidden"
          animate="visible"
        >
          {Object.entries(content).map(([key, value]) => (
            <motion.div key={key} variants={cardVariants}>
              <dt className="text-xs uppercase tracking-widest text-sage">{key.replaceAll('_', ' ')}</dt>
              <dd className="mt-1 whitespace-pre-wrap">{typeof value === 'string' ? value : JSON.stringify(value, null, 2)}</dd>
            </motion.div>
          ))}
        </motion.dl>
      </Card>
    </motion.div>
  )
}

/* ─── Output Tab ─────────────────────────────────────────────── */
function OutputTab({ program, onSaved }) {
  const [form, setForm] = useState({ category: 'research', title: '', description: '' })

  async function submit(event) {
    event.preventDefault()
    await api.post(`/programs/${program.id}/outputs`, form)
    setForm({ ...form, title: '', description: '' })
    onSaved()
  }

  return (
    <div className="grid gap-6 lg:grid-cols-2">
      <motion.div variants={slideLeft} initial="hidden" animate="visible">
        <Card>
          <form className="space-y-3" onSubmit={submit}>
            <Field label="Kategori">
              <select className={inputClass} value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })}>
                {OUTPUT_CATEGORIES.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
              </select>
            </Field>
            <Field label="Judul"><input className={inputClass} value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required /></Field>
            <Field label="Deskripsi"><textarea className={inputClass} rows="4" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} /></Field>
            <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
              <Button>Tambah output</Button>
            </motion.div>
          </form>
        </Card>
      </motion.div>

      <motion.div
        className="space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        <AnimatePresence>
          {(program.outputs || []).map((item) => (
            <motion.div
              key={item.id}
              variants={cardVariants}
              layout
              exit={{ opacity: 0, x: 20, transition: { duration: 0.2 } }}
            >
              <Card>
                <Badge>{item.category}</Badge>
                <h3 className="mt-2 font-display text-2xl">{item.title}</h3>
                <p className="mt-2 text-sm">{item.description}</p>
              </Card>
            </motion.div>
          ))}
        </AnimatePresence>
      </motion.div>
    </div>
  )
}

/* ─── Connect Tab ────────────────────────────────────────────── */
function ConnectTab({ program, onSaved }) {
  const [form, setForm] = useState({
    connect_decision: program.connect_decision || 'collaborate',
    connect_notes: program.connect_notes || '',
  })

  async function submit(event) {
    event.preventDefault()
    await api.post(`/programs/${program.id}/connect`, form)
    onSaved()
  }

  return (
    <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.4 }}>
      <Card>
        <p className="text-sm text-moss/80">Program tidak otomatis tertutup di hari ke-60. Pilih tindak lanjut.</p>
        <p className="mt-2 text-sm">Maturity saat ini: Level {program.maturity_level} — {MATURITY[program.maturity_level]}</p>

        <form className="mt-6 grid gap-3 md:grid-cols-2" onSubmit={submit}>
          {CONNECT_OPTIONS.map((item) => (
            <motion.button
              type="button"
              key={item.value}
              onClick={() => setForm({ ...form, connect_decision: item.value })}
              whileHover={{ scale: 1.03 }}
              whileTap={{ scale: 0.97 }}
              className={`rounded-2xl border p-4 text-left transition ${
                form.connect_decision === item.value ? 'border-copper bg-copper/10' : 'border-clay'
              }`}
            >
              <p className="font-semibold">{item.label}</p>
              <p className="text-sm text-moss/70">{item.hint}</p>
            </motion.button>
          ))}
          <div className="md:col-span-2">
            <Field label="Catatan follow-up">
              <textarea className={inputClass} rows="4" value={form.connect_notes} onChange={(e) => setForm({ ...form, connect_notes: e.target.value })} />
            </Field>
          </div>
          <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
            <Button>Simpan keputusan Connect</Button>
          </motion.div>
        </form>
      </Card>
    </motion.div>
  )
}
