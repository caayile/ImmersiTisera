import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { Badge, Card, PageHeader } from '../components/ui'

export default function Dashboard() {
  const { user } = useAuth()
  if (user.role === 'admin') return <AdminHome />
  if (user.role === 'mentor') return <MentorHome />
  return <DosenHome />
}

const APPLICATION_STATUS_LABELS = {
  draft: 'Draf',
  submitted: 'Menunggu admin',
  waiting_mentor: 'Menunggu mentor',
  waiting_admin: 'Menunggu pengesahan',
  approved: 'Disetujui',
  revision: 'Revisi',
  rejected: 'Ditolak',
}

function DosenHome() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api.get('/applications')
      .then(({ data }) => setItems(Array.isArray(data) ? data : []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }, [])

  return (
    <div>
      <PageHeader
        title="Program / Pendaftaran"
        description="Form pendaftaran dan surat persetujuan: dosen → admin → mentor → admin → dosen."
        action={
          <Link to="/app/opportunities" className="rounded-lg bg-mint px-4 py-2 text-sm font-semibold text-white transition hover:bg-copper-dark">
            Daftar program
          </Link>
        }
      />
      <div className="grid gap-4 sm:grid-cols-2">
        <Link to="/app/logbooks" className="block rounded-2xl border border-clay/80 bg-white p-5 shadow-[0_10px_30px_rgba(22,53,44,0.04)] transition hover:-translate-y-0.5 hover:shadow-md">
          <p className="font-semibold text-ink">Logbook</p>
          <p className="mt-1 text-sm text-moss/80">Isi dan kelola refleksi harian program magang dosen Anda.</p>
        </Link>
        <Link to="/app/logbooks/history" className="block rounded-2xl border border-clay/80 bg-white p-5 shadow-[0_10px_30px_rgba(22,53,44,0.04)] transition hover:-translate-y-0.5 hover:shadow-md">
          <p className="font-semibold text-ink">Riwayat Logbook</p>
          <p className="mt-1 text-sm text-moss/80">Daftar periode magang dan entri logbook per tahun.</p>
        </Link>
      </div>
      <div className="mt-6 overflow-x-auto rounded-2xl border border-clay/80 bg-white">
        <table className="min-w-full text-left text-sm">
          <thead>
            <tr className="bg-cream text-xs uppercase tracking-wide text-moss/70">
              <th className="px-4 py-3">Nomor surat</th>
              <th className="px-4 py-3">Unit Bisnis</th>
              <th className="px-4 py-3">Departemen</th>
              <th className="px-4 py-3">Mentor</th>
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3"><span className="sr-only">Aksi</span></th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan="6" className="px-4 py-10 text-center text-moss/70">Memuat pendaftaran…</td></tr>
            ) : items.length === 0 ? (
              <tr><td colSpan="6" className="px-4 py-10 text-center text-moss/70">Belum ada pengajuan.</td></tr>
            ) : items.map((item) => (
              <tr key={item.id} className="border-t border-clay/60">
                <td className="px-4 py-3">{item.letter_number || '—'}</td>
                <td className="px-4 py-3">{item.department?.name || '—'}</td>
                <td className="px-4 py-3">{item.business_unit?.name || '—'}</td>
                <td className="px-4 py-3">{item.mentor?.name || '—'}</td>
                <td className="px-4 py-3">
                  <Badge tone={item.status === 'approved' ? 'sage' : 'copper'}>
                    {APPLICATION_STATUS_LABELS[item.status] || item.status}
                  </Badge>
                </td>
                <td className="px-4 py-3">
                  <Link to={item.agreement ? `/app/agreements/${item.agreement.id}` : '/app/applications'} className="font-semibold text-copper-dark">
                    Lihat surat
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
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
        <Card><p className="text-xs uppercase tracking-widest text-sage">Minat masuk</p><p className="mt-2 font-display text-4xl">{apps.filter((item) => item.status === 'waiting_mentor' || item.status === 'submitted').length}</p></Card>
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
