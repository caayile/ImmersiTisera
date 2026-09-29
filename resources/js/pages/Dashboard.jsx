import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { PHASES } from '../lib/constants'
import { Badge, Card, PageHeader, ScoreRing } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants, popIn } from '../lib/motion'

export default function Dashboard() {
  const { user } = useAuth()
  if (user.role === 'admin') return <AdminHome />
  if (user.role === 'mentor') return <MentorHome />
  return <DosenHome />
}

function DosenHome() {
  const [programs, setPrograms] = useState([])
  const [opps, setOpps] = useState([])

  useEffect(() => {
    api.get('/programs').then(({ data }) => setPrograms(data))
    api.get('/opportunities').then(({ data }) => setOpps(data.slice(0, 3)))
  }, [])

  const active = programs.find((item) => item.status === 'active')

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Identify → Immersion" title="Dasbor dosen" description="Rekomendasi matching hanya saran. Keputusan tetap di agreement bersama mentor." />

      {/* Active program banner */}
      {active && (
        <motion.div
          initial={{ opacity: 0, y: -12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4, delay: 0.1 }}
        >
          <Card className="mb-6 bg-mint text-white">
            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-white/80">Program aktif · Minggu {active.current_week}</p>
            <h2 className="mt-2 text-2xl font-semibold">{active.opportunity?.title}</h2>
            <div className="mt-6 grid gap-3 md:grid-cols-5">
              {PHASES.map((phase) => (
                <motion.div
                  key={phase.key}
                  whileHover={{ scale: 1.04 }}
                  className={`rounded-xl p-3 ${active.computed_phase === phase.key ? 'bg-white text-ink' : 'bg-white/15'}`}
                >
                  <p className="text-xs opacity-80">{phase.week}</p>
                  <p className="font-semibold">{phase.title}</p>
                </motion.div>
              ))}
            </div>
            <div className="mt-5 flex flex-wrap gap-4">
              <Link to={`/app/programs/${active.id}`} className="text-sm font-semibold text-white">Buka ruang program →</Link>
              <Link to="/app/logbooks" className="text-sm font-semibold text-white/90">Isi Logbook →</Link>
            </div>
          </Card>
        </motion.div>
      )}

      {/* Logbook shortcut */}
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4, delay: 0.15 }}
        whileHover={{ scale: 1.01 }}
        whileTap={{ scale: 0.99 }}
      >
        <Link to="/app/logbooks" className="mb-6 block rounded-2xl border border-mint/40 bg-white p-5 transition hover:border-mint hover:shadow-md">
          <div className="flex items-center justify-between gap-4">
            <div>
              <h3 className="font-semibold">Logbook</h3>
              <p className="mt-1 text-sm text-moss/80">
                {active ? `Isi refleksi harian program aktif · Minggu ${active.current_week}` : 'Refleksi harian selama program imersi berlangsung'}
              </p>
            </div>
            <span className="text-sm font-semibold text-copper">Buka logbook →</span>
          </div>
        </Link>
      </motion.div>

      {/* Opportunity cards */}
      <motion.div
        className="grid gap-4 md:grid-cols-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {opps.map((item) => (
          <motion.div key={item.id} variants={cardVariants} whileHover={{ y: -4, transition: { duration: 0.2 } }}>
            <Card>
              <Badge>{item.purpose}</Badge>
              <h3 className="mt-3 font-display text-2xl">{item.title}</h3>
              <p className="mt-2 text-sm text-moss/80">{item.mentor?.company}</p>
              <div className="mt-4"><ScoreRing score={item.match_score} label={item.match_label} /></div>
              <Link to={`/app/opportunities/${item.id}`} className="mt-4 inline-block text-sm font-semibold text-copper">Lihat detail</Link>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </motion.div>
  )
}

function MentorHome() {
  const [apps, setApps] = useState([])
  const [programs, setPrograms] = useState([])

  useEffect(() => {
    api.get('/applications').then(({ data }) => setApps(data))
    api.get('/programs').then(({ data }) => setPrograms(data))
  }, [])

  const stats = [
    ['Minat masuk', apps.filter((item) => item.status === 'waiting_mentor' || item.status === 'submitted').length],
    ['Program aktif', programs.filter((item) => item.status === 'active').length],
    ['Total aplikasi', apps.length],
  ]

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Match & Commit" title="Dasbor mentor" description="Review minat dosen, bentuk agreement, lalu dampingi 60 hari immersion." />

      {/* Stat tiles */}
      <motion.div
        className="grid gap-4 md:grid-cols-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {stats.map(([label, value]) => (
          <motion.div key={label} variants={cardVariants}>
            <Card>
              <p className="text-xs uppercase tracking-widest text-sage">{label}</p>
              <motion.p
                className="mt-2 font-display text-4xl"
                initial={{ opacity: 0, scale: 0.7 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ duration: 0.5, delay: 0.3, type: 'spring', stiffness: 200 }}
              >
                {value}
              </motion.p>
            </Card>
          </motion.div>
        ))}
      </motion.div>

      {/* Application list */}
      <motion.div
        className="mt-6 space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {apps.slice(0, 4).map((item) => (
          <motion.div key={item.id} variants={cardVariants} whileHover={{ x: 4, transition: { duration: 0.15 } }}>
            <Card className="flex items-center justify-between">
              <div>
                <p className="font-semibold">{item.dosen?.name}</p>
                <p className="text-sm text-moss/70">{item.opportunity?.title} · {item.match_score}% {item.match_label}</p>
              </div>
              <Badge tone={item.status === 'pending' ? 'copper' : 'sage'}>{item.status}</Badge>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </motion.div>
  )
}

function AdminHome() {
  const [overview, setOverview] = useState(null)

  useEffect(() => {
    api.get('/admin/overview').then(({ data }) => setOverview(data))
  }, [])

  if (!overview) return null

  const tiles = [
    ['Menunggu verifikasi', overview.pending_verification],
    ['Dosen terverifikasi', overview.dosen],
    ['Mentor terverifikasi', overview.mentors],
    ['Program aktif', overview.active_programs],
    ['Opportunity', overview.opportunities],
    ['Kebutuhan prodi', overview.department_needs],
  ]

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Program Manager" title="Dasbor admin" description="Verifikasi akun, pantau matching, dan monitor seluruh perjalanan 60 hari." />
      <motion.div
        className="grid gap-4 md:grid-cols-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {tiles.map(([label, value]) => (
          <motion.div key={label} variants={cardVariants} whileHover={{ y: -3, transition: { duration: 0.2 } }}>
            <Card>
              <p className="text-xs uppercase tracking-[0.16em] text-sage">{label}</p>
              <motion.p
                className="mt-2 font-display text-4xl"
                initial={{ opacity: 0, scale: 0.7 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ duration: 0.5, delay: 0.2, type: 'spring', stiffness: 180 }}
              >
                {value}
              </motion.p>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </motion.div>
  )
}
