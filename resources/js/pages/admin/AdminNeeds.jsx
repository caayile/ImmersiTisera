import { useEffect, useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../../api/client'
import { PURPOSE } from '../../lib/constants'
import { Button, Card, Field, PageHeader, inputClass } from '../../components/ui'
import { pageVariants, staggerContainer, cardVariants, slideLeft, slideRight } from '../../lib/motion'

export default function AdminNeeds() {
  const [items, setItems] = useState([])
  const [form, setForm] = useState({
    prodi: 'Informatika',
    department: 'Fakultas Teknik',
    purpose: 'riset',
    academic_needs: '',
    problem: '',
    goal: '',
  })

  async function load() {
    const { data } = await api.get('/admin/department-needs')
    setItems(data)
  }

  useEffect(() => { load() }, [])

  async function submit(event) {
    event.preventDefault()
    await api.post('/admin/department-needs', form)
    setForm({ ...form, academic_needs: '', problem: '', goal: '' })
    load()
  }

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Identify" title="Kebutuhan departemen / prodi" description="Riset, observasi, dan pembelajaran adalah tujuan program — bukan level kolaborasi." />

      <div className="grid gap-6 lg:grid-cols-2">
        {/* Form */}
        <motion.div variants={slideLeft} initial="hidden" animate="visible">
          <Card>
            <form className="space-y-3" onSubmit={submit}>
              <Field label="Prodi"><input className={inputClass} value={form.prodi} onChange={(e) => setForm({ ...form, prodi: e.target.value })} /></Field>
              <Field label="Departemen"><input className={inputClass} value={form.department} onChange={(e) => setForm({ ...form, department: e.target.value })} /></Field>
              <Field label="Purpose">
                <select className={inputClass} value={form.purpose} onChange={(e) => setForm({ ...form, purpose: e.target.value })}>
                  {PURPOSE.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                </select>
              </Field>
              <Field label="Kebutuhan akademik"><textarea className={inputClass} rows="3" value={form.academic_needs} onChange={(e) => setForm({ ...form, academic_needs: e.target.value })} required /></Field>
              <Field label="Problem"><textarea className={inputClass} rows="3" value={form.problem} onChange={(e) => setForm({ ...form, problem: e.target.value })} /></Field>
              <Field label="Tujuan"><textarea className={inputClass} rows="3" value={form.goal} onChange={(e) => setForm({ ...form, goal: e.target.value })} /></Field>
              <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
                <Button>Simpan kebutuhan</Button>
              </motion.div>
            </form>
          </Card>
        </motion.div>

        {/* Needs list */}
        <motion.div
          variants={slideRight}
          initial="hidden"
          animate="visible"
        >
          <motion.div
            className="space-y-3"
            variants={staggerContainer}
            initial="hidden"
            animate="visible"
          >
            <AnimatePresence>
              {items.map((item) => (
                <motion.div
                  key={item.id}
                  variants={cardVariants}
                  layout
                  exit={{ opacity: 0, x: 20, transition: { duration: 0.2 } }}
                  whileHover={{ x: 4, transition: { duration: 0.15 } }}
                >
                  <Card>
                    <p className="text-xs uppercase tracking-widest text-copper">{item.purpose}</p>
                    <h3 className="font-display text-2xl">{item.prodi}</h3>
                    <p className="mt-2 text-sm">{item.academic_needs}</p>
                  </Card>
                </motion.div>
              ))}
            </AnimatePresence>
          </motion.div>
        </motion.div>
      </div>
    </motion.div>
  )
}
