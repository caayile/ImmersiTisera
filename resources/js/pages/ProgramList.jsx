import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/client'
import { PHASES } from '../lib/constants'
import { Badge, Card, PageHeader } from '../components/ui'

export default function ProgramList() {
  const [items, setItems] = useState([])

  useEffect(() => {
    api.get('/programs').then(({ data }) => setItems(data))
  }, [])

  return (
    <div>
      <PageHeader kicker="60 Days" title="Program immersion" />
      <div className="space-y-4">
        {items.map((item) => (
          <Card key={item.id} className="flex flex-wrap items-center justify-between gap-4">
            <div>
              <Badge>{item.computed_phase}</Badge>
              <h2 className="mt-2 font-display text-2xl">{item.opportunity?.title}</h2>
              <p className="text-sm text-moss/70">Minggu {item.current_week} · {item.dosen?.name} × {item.mentor?.name}</p>
            </div>
            <Link to={`/app/programs/${item.id}`} className="rounded-full bg-copper px-5 py-2.5 text-sm font-semibold text-white">Buka</Link>
          </Card>
        ))}
        {items.length === 0 && <Card>Belum ada program aktif.</Card>}
      </div>
      <div className="mt-8 grid gap-3 md:grid-cols-5">
        {PHASES.map((phase) => (
          <Card key={phase.key}>
            <p className="text-xs text-sage">{phase.week}</p>
            <p className="font-display text-xl">{phase.title}</p>
            <p className="mt-2 text-sm text-moss/70">{phase.output}</p>
          </Card>
        ))}
      </div>
    </div>
  )
}
