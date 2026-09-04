import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { PHASES } from '../lib/constants'
import { Badge, Card, PageHeader, ScoreRing } from '../components/ui'

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
    <div>
      <PageHeader kicker="Identify → Immersion" title="Dasbor dosen" description="Rekomendasi matching hanya saran. Keputusan tetap di agreement bersama mentor." />
      {active && (
        <Card className="mb-6 bg-mint text-white">
          <p className="text-xs font-semibold uppercase tracking-[0.16em] text-white/80">Program aktif · Minggu {active.current_week}</p>
          <h2 className="mt-2 text-2xl font-semibold">{active.opportunity?.title}</h2>
          <div className="mt-6 grid gap-3 md:grid-cols-5">
            {PHASES.map((phase) => (
              <div key={phase.key} className={`rounded-xl p-3 ${active.computed_phase === phase.key ? 'bg-white text-ink' : 'bg-white/15'}`}>
                <p className="text-xs opacity-80">{phase.week}</p>
                <p className="font-semibold">{phase.title}</p>
              </div>
            ))}
          </div>
          <Link to={`/app/programs/${active.id}`} className="mt-5 inline-block text-sm font-semibold text-white">Buka ruang program →</Link>
        </Card>
      )}
      <div className="grid gap-4 md:grid-cols-3">
        {opps.map((item) => (
          <Card key={item.id}>
            <Badge>{item.purpose}</Badge>
            <h3 className="mt-3 font-display text-2xl">{item.title}</h3>
            <p className="mt-2 text-sm text-moss/80">{item.mentor?.company}</p>
            <div className="mt-4"><ScoreRing score={item.match_score} label={item.match_label} /></div>
            <Link to={`/app/opportunities/${item.id}`} className="mt-4 inline-block text-sm font-semibold text-copper">Lihat detail</Link>
          </Card>
        ))}
      </div>
    </div>
  )
}

function MentorHome() {
  const [apps, setApps] = useState([])
  const [programs, setPrograms] = useState([])

  useEffect(() => {
    api.get('/applications').then(({ data }) => setApps(data))
    api.get('/programs').then(({ data }) => setPrograms(data))
  }, [])

  return (
    <div>
      <PageHeader kicker="Match & Commit" title="Dasbor mentor" description="Review minat dosen, bentuk agreement, lalu dampingi 60 hari immersion." />
      <div className="grid gap-4 md:grid-cols-3">
        <Card><p className="text-xs uppercase tracking-widest text-sage">Minat masuk</p><p className="mt-2 font-display text-4xl">{apps.filter((item) => item.status === 'pending').length}</p></Card>
        <Card><p className="text-xs uppercase tracking-widest text-sage">Program aktif</p><p className="mt-2 font-display text-4xl">{programs.filter((item) => item.status === 'active').length}</p></Card>
        <Card><p className="text-xs uppercase tracking-widest text-sage">Total aplikasi</p><p className="mt-2 font-display text-4xl">{apps.length}</p></Card>
      </div>
      <div className="mt-6 space-y-3">
        {apps.slice(0, 4).map((item) => (
          <Card key={item.id} className="flex items-center justify-between">
            <div>
              <p className="font-semibold">{item.dosen?.name}</p>
              <p className="text-sm text-moss/70">{item.opportunity?.title} · {item.match_score}% {item.match_label}</p>
            </div>
            <Badge tone={item.status === 'pending' ? 'copper' : 'sage'}>{item.status}</Badge>
          </Card>
        ))}
      </div>
    </div>
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
    <div>
      <PageHeader kicker="Program Manager" title="Dasbor admin" description="Verifikasi akun, pantau matching, dan monitor seluruh perjalanan 60 hari." />
      <div className="grid gap-4 md:grid-cols-3">
        {tiles.map(([label, value]) => (
          <Card key={label}>
            <p className="text-xs uppercase tracking-[0.16em] text-sage">{label}</p>
            <p className="mt-2 font-display text-4xl">{value}</p>
          </Card>
        ))}
      </div>
    </div>
  )
}
