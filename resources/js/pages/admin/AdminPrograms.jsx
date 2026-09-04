import { useEffect, useState } from 'react'
import api from '../../api/client'
import { Badge, Card, PageHeader } from '../../components/ui'

export default function AdminPrograms() {
  const [items, setItems] = useState([])

  useEffect(() => {
    api.get('/admin/programs').then(({ data }) => setItems(data))
  }, [])

  return (
    <div>
      <PageHeader kicker="Monitoring" title="Semua program" description="Admin memantau keseluruhan. Mentor tetap pihak utama monitoring aktivitas industri." />
      <div className="space-y-3">
        {items.map((item) => (
          <Card key={item.id} className="flex flex-wrap items-center justify-between gap-4">
            <div>
              <Badge>{item.computed_phase}</Badge>
              <h2 className="mt-2 font-display text-2xl">{item.opportunity?.title}</h2>
              <p className="text-sm text-moss/70">{item.dosen?.name} × {item.mentor?.name} · minggu {item.current_week}</p>
            </div>
            <p className="text-sm">Score {item.collaboration_score || '—'} · {item.connect_decision || 'belum connect'}</p>
          </Card>
        ))}
      </div>
    </div>
  )
}
