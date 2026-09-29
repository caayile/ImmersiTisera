import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import api from '../api/client'
import { Badge, Card, PageHeader, ScoreRing } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants } from '../lib/motion'

export default function Opportunities() {
  const [items, setItems] = useState([])

  useEffect(() => {
    api.get('/opportunities').then(({ data }) => setItems(data))
  }, [])

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Matching" title="Recommended opportunity" description="Skor hanya rekomendasi relevansi. Anda tetap memilih, lalu mentor meninjau." />

      <motion.div
        className="grid gap-4 lg:grid-cols-2"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {items.map((item) => (
          <motion.div
            key={item.id}
            variants={cardVariants}
            whileHover={{ y: -4, boxShadow: '0 10px 30px rgba(46,93,76,0.1)', transition: { duration: 0.2 } }}
          >
            <Card>
              <div className="flex items-start justify-between gap-4">
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <Badge>{item.purpose}</Badge>
                    <Badge tone={item.status === 'open' ? 'sage' : 'copper'}>
                      {item.status === 'open' ? 'Lowongan dibuka' : 'Lowongan ditutup'}
                    </Badge>
                  </div>
                  <h2 className="mt-3 font-display text-3xl">{item.title}</h2>
                  <p className="mt-2 text-sm text-moss/80">{item.mentor?.company} · {item.business_unit}</p>
                </div>
                <ScoreRing score={item.match_score} label={item.match_label} />
              </div>
              <p className="mt-4 text-sm">{item.problem}</p>
              <motion.div whileHover={{ x: 4 }} transition={{ duration: 0.15 }}>
                <Link to={`/app/opportunities/${item.id}`} className="mt-5 inline-block font-semibold text-copper">
                  {item.applied ? 'Lihat pengajuan' : item.status === 'open' ? 'Lihat & apply' : 'Lihat detail'}
                </Link>
              </motion.div>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </motion.div>
  )
}
