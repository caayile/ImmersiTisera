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
                <div className="flex flex-wrap items-center gap-2">
                  <Badge>{item.purpose}</Badge>
                  <Badge tone={item.is_full || item.status !== 'open' ? 'copper' : 'sage'}>
                    {item.is_full ? `Kuota penuh (${item.applicants_count}/${item.quota})` : item.status === 'open' ? 'Lowongan dibuka' : 'Lowongan ditutup'}
                  </Badge>
                  {typeof item.applicants_count === 'number' && (
                    <Badge>Terisi {item.applicants_count}/{item.quota ?? 2}</Badge>
                  )}
                </div>
                <h2 className="mt-3 font-display text-3xl">{item.title}</h2>
                <p className="mt-2 text-sm text-moss/80">{item.mentor?.company} · {item.business_unit}</p>
              </div>
              <ScoreRing score={item.match_score} label={item.match_label} />
            </div>
            <p className="mt-4 text-sm">{item.problem}</p>
            <Link to={`/app/opportunities/${item.id}`} className="mt-5 inline-block font-semibold text-copper">
              {item.applied ? 'Lihat pengajuan' : item.is_full ? 'Kuota penuh – Lihat detail' : item.status === 'open' ? 'Lihat & apply' : 'Lihat detail'}
            </Link>
          </Card>
        ))}
      </div>
    </div>
  )
}
