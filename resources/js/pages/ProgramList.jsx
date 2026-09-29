import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import api from '../api/client'
import { PHASES } from '../lib/constants'
import { Badge, Card, PageHeader } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants } from '../lib/motion'

export default function ProgramList() {
  const [items, setItems] = useState([])

  useEffect(() => {
    api.get('/programs').then(({ data }) => setItems(data))
  }, [])

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="60 Days" title="Program immersion" />

      {/* Program list */}
      <motion.div
        className="space-y-4"
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
                <p className="text-sm text-moss/70">Minggu {item.current_week} · {item.dosen?.name} × {item.mentor?.name}</p>
              </div>
              <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
                <Link to={`/app/programs/${item.id}`} className="rounded-full bg-copper px-5 py-2.5 text-sm font-semibold text-white">Buka</Link>
              </motion.div>
            </Card>
          </motion.div>
        ))}
        {items.length === 0 && (
          <motion.div variants={cardVariants}>
            <Card>Belum ada program aktif.</Card>
          </motion.div>
        )}
      </motion.div>

      {/* Phase reference grid */}
      <motion.div
        className="mt-8 grid gap-3 md:grid-cols-5"
        variants={staggerContainer}
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, margin: '-40px' }}
      >
        {PHASES.map((phase) => (
          <motion.div
            key={phase.key}
            variants={cardVariants}
            whileHover={{ y: -4, boxShadow: '0 8px 20px rgba(46,93,76,0.08)', transition: { duration: 0.2 } }}
          >
            <Card>
              <p className="text-xs text-sage">{phase.week}</p>
              <p className="font-display text-xl">{phase.title}</p>
              <p className="mt-2 text-sm text-moss/70">{phase.output}</p>
            </Card>
          </motion.div>
        ))}
      </motion.div>
    </motion.div>
  )
}
