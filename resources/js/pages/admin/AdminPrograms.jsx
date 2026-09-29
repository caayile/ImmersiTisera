import { useEffect, useState } from 'react'
import { motion } from 'framer-motion'
import api from '../../api/client'
import { Badge, Card, PageHeader } from '../../components/ui'
import { pageVariants, staggerContainer, cardVariants } from '../../lib/motion'

export default function AdminPrograms() {
  const [items, setItems] = useState([])

  useEffect(() => {
    api.get('/admin/programs').then(({ data }) => setItems(data))
  }, [])

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Monitoring" title="Semua program" description="Admin memantau keseluruhan. Mentor tetap pihak utama monitoring aktivitas industri." />

      <motion.div
        className="space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        {items.map((item) => (
          <motion.div
            key={item.id}
            variants={cardVariants}
            whileHover={{ x: 4, transition: { duration: 0.15 } }}
          >
            <Card className="flex flex-wrap items-center justify-between gap-4">
              <div>
                <Badge>{item.computed_phase}</Badge>
                <h2 className="mt-2 font-display text-2xl">{item.opportunity?.title}</h2>
                <p className="text-sm text-moss/70">{item.dosen?.name} × {item.mentor?.name} · minggu {item.current_week}</p>
              </div>
              <p className="text-sm">Score {item.collaboration_score || '—'} · {item.connect_decision || 'belum connect'}</p>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </motion.div>
  )
}
