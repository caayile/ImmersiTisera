import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/client'
import { Badge, Card, PageHeader, ScoreRing } from '../components/ui'

export default function Opportunities() {
  const [items, setItems] = useState([])

  useEffect(() => {
    api.get('/opportunities').then(({ data }) => setItems(data))
  }, [])

  return (
    <div>
      <PageHeader kicker="Matching" title="Recommended opportunity" description="Skor hanya rekomendasi relevansi. Anda tetap memilih, lalu mentor meninjau." />
      <div className="grid gap-4 lg:grid-cols-2">
        {items.map((item) => (
          <Card key={item.id}>
            <div className="flex items-start justify-between gap-4">
              <div>
                <Badge>{item.purpose}</Badge>
                <h2 className="mt-3 font-display text-3xl">{item.title}</h2>
                <p className="mt-2 text-sm text-moss/80">{item.mentor?.company} · {item.business_unit}</p>
              </div>
              <ScoreRing score={item.match_score} label={item.match_label} />
            </div>
            <p className="mt-4 text-sm">{item.problem}</p>
            <Link to={`/app/opportunities/${item.id}`} className="mt-5 inline-block font-semibold text-copper">
              {item.applied ? 'Lihat pengajuan' : 'Lihat & apply'}
            </Link>
          </Card>
        ))}
      </div>
    </div>
  )
}
