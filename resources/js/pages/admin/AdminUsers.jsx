import { useEffect, useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../../api/client'
import { Badge, Button, Card, PageHeader } from '../../components/ui'
import { pageVariants, staggerContainer, cardVariants } from '../../lib/motion'

const STATUSES = ['pending_verification', 'verified', 'rejected', 'pending_profile']

export default function AdminUsers() {
  const [items, setItems] = useState([])
  const [status, setStatus] = useState('pending_verification')

  async function load(nextStatus = status) {
    const { data } = await api.get('/admin/users', { params: { status: nextStatus } })
    setItems(data)
  }

  useEffect(() => { load() }, [])

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Verification" title="Verifikasi akun" description="Mentor diverifikasi lebih ketat karena mereka yang membuka opportunity." />

      {/* Filter tabs */}
      <motion.div
        className="mb-4 flex flex-wrap gap-2"
        initial={{ opacity: 0, y: -8 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1, duration: 0.35 }}
      >
        {STATUSES.map((item) => (
          <motion.button
            key={item}
            onClick={() => { setStatus(item); load(item) }}
            whileHover={{ scale: 1.04 }}
            whileTap={{ scale: 0.96 }}
            className={`rounded-full px-4 py-2 text-sm transition ${status === item ? 'bg-ink text-white' : 'bg-white'}`}
          >
            {item.replaceAll('_', ' ')}
          </motion.button>
        ))}
      </motion.div>

      {/* User list */}
      <motion.div
        className="space-y-3"
        variants={staggerContainer}
        initial="hidden"
        animate="visible"
      >
        <AnimatePresence mode="popLayout">
          {items.map((item) => (
            <motion.div
              key={item.id}
              variants={cardVariants}
              layout
              exit={{ opacity: 0, x: -20, transition: { duration: 0.25 } }}
            >
              <Card className="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <Badge>{item.role === 'user' ? 'Dosen' : item.role}</Badge>
                  <h2 className="mt-2 font-display text-2xl">{item.name}</h2>
                  <p className="text-sm text-moss/70">{item.email}</p>
                  {item.dosen_profile && <p className="mt-2 text-sm">{item.dosen_profile.prodi} · {item.dosen_profile.purpose}</p>}
                  {item.mentor_profile && <p className="mt-2 text-sm">{item.mentor_profile.company_name} · {item.mentor_profile.business_unit}</p>}
                </div>
                {item.verification_status !== 'verified' && item.role !== 'admin' && (
                  <div className="flex gap-2">
                    <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
                      <Button onClick={async () => { await api.post(`/admin/users/${item.id}/verify`); load() }}>Setujui</Button>
                    </motion.div>
                    <motion.div whileHover={{ scale: 1.04 }} whileTap={{ scale: 0.96 }}>
                      <Button variant="ghost" onClick={async () => {
                        const reason = prompt('Alasan penolakan')
                        if (!reason) return
                        await api.post(`/admin/users/${item.id}/reject`, { reason })
                        load()
                      }}>Tolak</Button>
                    </motion.div>
                  </div>
                )}
              </Card>
            </motion.div>
          ))}
        </AnimatePresence>
      </motion.div>
    </motion.div>
  )
}
