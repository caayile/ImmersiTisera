import { useEffect, useState } from 'react'
import api from '../../api/client'
import { Badge, Button, Card, PageHeader } from '../../components/ui'

export default function AdminUsers() {
  const [items, setItems] = useState([])
  const [status, setStatus] = useState('pending_verification')

  async function load(nextStatus = status) {
    const { data } = await api.get('/admin/users', { params: { status: nextStatus } })
    setItems(data)
  }

  useEffect(() => { load() }, [])

  return (
    <div>
      <PageHeader kicker="Verification" title="Verifikasi akun" description="Mentor diverifikasi lebih ketat karena mereka yang membuka opportunity." />
      <div className="mb-4 flex gap-2">
        {['pending_verification', 'verified', 'rejected', 'pending_profile'].map((item) => (
          <button
            key={item}
            onClick={() => { setStatus(item); load(item) }}
            className={`rounded-full px-4 py-2 text-sm ${status === item ? 'bg-ink text-white' : 'bg-white'}`}
          >
            {item.replaceAll('_', ' ')}
          </button>
        ))}
      </div>
      <div className="space-y-3">
        {items.map((item) => (
          <Card key={item.id} className="flex flex-wrap items-start justify-between gap-4">
            <div>
              <Badge>{item.role === 'user' ? 'Dosen' : item.role}</Badge>
              <h2 className="mt-2 font-display text-2xl">{item.name}</h2>
              <p className="text-sm text-moss/70">{item.email}</p>
              {item.dosen_profile && <p className="mt-2 text-sm">{item.dosen_profile.prodi} · {item.dosen_profile.purpose}</p>}
              {item.mentor_profile && <p className="mt-2 text-sm">{item.mentor_profile.company_name} · {item.mentor_profile.business_unit}</p>}
            </div>
            {item.verification_status !== 'verified' && item.role !== 'admin' && (
              <div className="flex gap-2">
                <Button onClick={async () => { await api.post(`/admin/users/${item.id}/verify`); load() }}>Setujui</Button>
                <Button variant="ghost" onClick={async () => {
                  const reason = prompt('Alasan penolakan')
                  if (!reason) return
                  await api.post(`/admin/users/${item.id}/reject`, { reason })
                  load()
                }}>Tolak</Button>
              </div>
            )}
          </Card>
        ))}
      </div>
    </div>
  )
}
