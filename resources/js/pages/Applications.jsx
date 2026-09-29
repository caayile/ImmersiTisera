import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../api/client'
import { useAuth } from '../context/AuthContext'
import { Badge, Button, Card, PageHeader } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants } from '../lib/motion'

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
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Match & Commit" title={user.role === 'mentor' ? 'Review minat dosen' : 'Minat saya'} />

      <motion.div
        className="space-y-4"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        <AnimatePresence>
          {items.map((item) => (
            <motion.div
              key={item.id}
              variants={cardVariants}
              exit={{ opacity: 0, x: -20, transition: { duration: 0.25 } }}
              layout
            >
              <Card className="flex flex-wrap items-start justify-between gap-4">
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
                  {user.role === 'mentor' && item.status === 'waiting_mentor' && (
                    <>
                      <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
                        <Button onClick={() => review(item.id, 'approved')}>Approve</Button>
                      </motion.div>
                      <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
                        <Button variant="ghost" onClick={() => review(item.id, 'rejected')}>Reject</Button>
                      </motion.div>
                    </>
                  )}
                  {item.agreement && (
                    <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
                      <Link to={`/app/agreements/${item.agreement.id}`} className="rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-parchment">
                        Buka agreement
                      </Link>
                    </motion.div>
                  )}
                </div>
              </Card>
            </motion.div>
          ))}
        </AnimatePresence>
      </motion.div>
    </motion.div>
  )
}
