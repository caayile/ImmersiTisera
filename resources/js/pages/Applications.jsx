import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { Badge, Button, Card, PageHeader } from '../components/ui'

export default function Applications() {
  const { user } = useAuth()
  const [items, setItems] = useState([])

  async function load() {
    const { data } = await api.get('/applications')
    setItems(data)
  }

  useEffect(() => { load() }, [])

  async function review(id, decision) {
    await api.post(`/applications/${id}/review`, { decision })
    load()
  }

  return (
    <div>
      <PageHeader kicker="Match & Commit" title={user.role === 'mentor' ? 'Review minat dosen' : 'Minat saya'} />
      <div className="space-y-4">
        {items.map((item) => (
          <Card key={item.id} className="flex flex-wrap items-start justify-between gap-4">
            <div>
              <Badge tone={item.status === 'approved' ? 'sage' : 'copper'}>{item.status}</Badge>
              <h2 className="mt-2 font-display text-2xl">{item.opportunity?.title}</h2>
              <p className="mt-1 text-sm text-moss/80">
                {item.dosen?.name} · {item.match_score}% {item.match_label} · {item.primary_activity}
                {item.supporting_activity ? ` + ${item.supporting_activity}` : ''}
              </p>
              <p className="mt-3 max-w-2xl text-sm">{item.proposed_shared_goal}</p>
            </div>
            <div className="flex flex-wrap gap-2">
              {user.role === 'mentor' && item.status === 'pending' && (
                <>
                  <Button onClick={() => review(item.id, 'approved')}>Approve</Button>
                  <Button variant="ghost" onClick={() => review(item.id, 'rejected')}>Reject</Button>
                </>
              )}
              {item.agreement && (
                <Link to={`/app/agreements/${item.agreement.id}`} className="rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-parchment">
                  Buka agreement
                </Link>
              )}
            </div>
          </Card>
        ))}
      </div>
    </div>
  )
}
