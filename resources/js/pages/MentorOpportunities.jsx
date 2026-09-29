import { useEffect, useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../api/client'
import { PURPOSE, ORGANIZATIONS, unitsFor } from '../lib/constants'
import { Button, Card, Field, PageHeader, inputClass } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants, slideLeft, slideRight } from '../lib/motion'

const blank = {
  title: '',
  field: 'TSPM',
  business_unit: '',
  purpose: 'riset',
  problem: '',
  opportunity: '',
  needed_expertise: '',
  expected_output: '',
  allowed_activities: ['riset', 'observasi'],
}

export default function MentorOpportunities() {
  const [items, setItems] = useState([])
  const [form, setForm] = useState(blank)

  async function load() {
    const { data } = await api.get('/opportunities')
    setItems(data)
  }

  useEffect(() => { load() }, [])

  async function submit(event) {
    event.preventDefault()
    await api.post('/opportunities', {
      ...form,
      needed_expertise: form.needed_expertise.split(',').map((item) => item.trim()).filter(Boolean),
    })
    setForm(blank)
    load()
  }

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader kicker="Identify" title="Opportunity industri" description="Dosen dipertemukan dengan unit bisnis berdasarkan problem, opportunity, dan expertise." />

      <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        {/* Form */}
        <motion.div variants={slideLeft} initial="hidden" animate="visible">
          <Card>
            <form className="space-y-3" onSubmit={submit}>
              <Field label="Judul"><input className={inputClass} value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required /></Field>
              <Field label="Departemen">
                <select className={inputClass} value={form.field} onChange={(e) => setForm({ ...form, field: e.target.value, business_unit: '' })}>
                  {ORGANIZATIONS.map((item) => <option key={item} value={item}>{item}</option>)}
                </select>
              </Field>
              <Field label="Unit bisnis">
                <select className={inputClass} value={form.business_unit} onChange={(e) => setForm({ ...form, business_unit: e.target.value })} required>
                  <option value="">Pilih unit bisnis</option>
                  {unitsFor(form.field).map((item) => <option key={item} value={item}>{item}</option>)}
                </select>
              </Field>
              <Field label="Purpose">
                <select className={inputClass} value={form.purpose} onChange={(e) => setForm({ ...form, purpose: e.target.value })}>
                  {PURPOSE.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                </select>
              </Field>
              <Field label="Problem"><textarea className={inputClass} rows="3" value={form.problem} onChange={(e) => setForm({ ...form, problem: e.target.value })} required /></Field>
              <Field label="Opportunity"><textarea className={inputClass} rows="3" value={form.opportunity} onChange={(e) => setForm({ ...form, opportunity: e.target.value })} required /></Field>
              <Field label="Expertise dibutuhkan"><input className={inputClass} value={form.needed_expertise} onChange={(e) => setForm({ ...form, needed_expertise: e.target.value })} required /></Field>
              <Field label="Expected output"><textarea className={inputClass} rows="3" value={form.expected_output} onChange={(e) => setForm({ ...form, expected_output: e.target.value })} required /></Field>
              <motion.div whileHover={{ scale: 1.015 }} whileTap={{ scale: 0.985 }}>
                <Button>Publikasikan</Button>
              </motion.div>
            </form>
          </Card>
        </motion.div>

        {/* Opportunity list */}
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
                    <h3 className="font-display text-2xl">{item.title}</h3>
                    <p className="mt-2 text-sm text-moss/80">{item.problem}</p>
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
