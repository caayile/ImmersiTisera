import { useEffect, useMemo, useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import api from '../api/client'
import { formatDate } from '../lib/constants'
import { Badge, Card, PageHeader } from '../components/ui'
import { pageVariants, staggerContainer, cardVariants, slideLeft, slideRight } from '../lib/motion'

function formatRange(start, end) {
  if (!start && !end) return 'Periode belum diatur'
  return `${formatDate(start)} — ${formatDate(end || start)}`
}

function logbookCount(program) {
  return (program.logbooks || []).length
}

function PeriodItem({ program, selected, onClick }) {
  return (
    <motion.button
      type="button"
      onClick={onClick}
      whileHover={{ scale: 1.02 }}
      whileTap={{ scale: 0.98 }}
      className={`w-full rounded-2xl border p-4 text-left transition ${
        selected ? 'border-mint bg-mint/10 shadow-sm' : 'border-clay/80 bg-white hover:border-mint/60'
      }`}
    >
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="truncate font-semibold">{program.department || program.opportunity || 'Program imersi'}</p>
          <p className="mt-0.5 truncate text-sm text-moss/75">{program.opportunity || 'Unit bisnis belum ditentukan'}</p>
        </div>
        <Badge tone={program.status === 'active' ? 'sage' : 'copper'}>{program.status}</Badge>
      </div>
      <p className="mt-2 text-xs text-moss/65">{formatRange(program.start_date, program.end_date)}</p>
      <p className="mt-1 text-xs text-moss/65">{logbookCount(program)} entri logbook</p>
    </motion.button>
  )
}

export default function LogbookHistory() {
  const [programs, setPrograms] = useState([])
  const [loading, setLoading] = useState(true)
  const [selectedId, setSelectedId] = useState(null)

  useEffect(() => {
    api
      .get('/logbooks/history')
      .then(({ data }) => setPrograms(Array.isArray(data) ? data : []))
      .catch(() => setPrograms([]))
      .finally(() => setLoading(false))
  }, [])

  const years = useMemo(() => {
    const map = new Map()
    programs.forEach((program) => {
      const stamp = program.start_date || program.end_date || ''
      const year = stamp ? stamp.slice(0, 4) : 'Tanpa periode'
      if (!map.has(year)) map.set(year, [])
      map.get(year).push(program)
    })
    return [...map.entries()].sort((a, b) => b[0].localeCompare(a[0]))
  }, [programs])

  useEffect(() => {
    if (selectedId == null && programs.length) {
      setSelectedId(programs[0].id)
    }
  }, [programs, selectedId])

  const selected = programs.find((program) => program.id === selectedId) || programs[0] || null
  const total = programs.reduce((sum, program) => sum + logbookCount(program), 0)

  return (
    <motion.div variants={pageVariants} initial="hidden" animate="visible">
      <PageHeader
        kicker="Arsip imersi"
        title="Riwayat Logbook"
        description="Daftar periode magang per tahun, termasuk penempatan unit bisnis tiap batch."
        action={<Badge tone="copper">{total} entri</Badge>}
      />

      {loading && (
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
          <Card>Memuat riwayat logbook…</Card>
        </motion.div>
      )}

      {!loading && programs.length === 0 && (
        <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}>
          <Card><p className="text-sm text-moss/80">Belum ada riwayat logbook.</p></Card>
        </motion.div>
      )}

      {!loading && programs.length > 0 && (
        <div className="grid gap-6 lg:grid-cols-[340px_1fr]">
          {/* Sidebar list */}
          <motion.div
            className="space-y-5"
            variants={slideLeft}
            initial="hidden"
            animate="visible"
          >
            {years.map(([year, items]) => (
              <motion.div key={year} variants={staggerContainer} initial="hidden" animate="visible">
                <p className="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-moss/70">Batch {year}</p>
                <div className="space-y-3">
                  {items.map((program) => (
                    <motion.div key={program.id} variants={cardVariants}>
                      <PeriodItem
                        program={program}
                        selected={selected?.id === program.id}
                        onClick={() => setSelectedId(program.id)}
                      />
                    </motion.div>
                  ))}
                </div>
              </motion.div>
            ))}
          </motion.div>

          {/* Detail panel */}
          <motion.div variants={slideRight} initial="hidden" animate="visible">
            <AnimatePresence mode="wait">
              {selected && (
                <motion.div
                  key={selected.id}
                  initial={{ opacity: 0, y: 12 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: -8 }}
                  transition={{ duration: 0.3 }}
                >
                  <Card className="mb-4">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-copper-dark">
                          Batch {String(selected.start_date || selected.end_date || '').slice(0, 4) || '—'}
                        </p>
                        <h2 className="mt-1 text-xl font-semibold">{selected.department || selected.opportunity || 'Program imersi'}</h2>
                        <p className="mt-1 text-sm text-moss/80">{selected.opportunity || 'Unit bisnis belum ditentukan'}</p>
                      </div>
                      <Badge tone={selected.status === 'active' ? 'sage' : 'copper'}>{selected.status}</Badge>
                    </div>
                    <div className="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-sm">
                      {[
                        ['Periode', formatRange(selected.start_date, selected.end_date)],
                        ['Mentor', selected.mentor || 'Belum ditugaskan'],
                        ['Entri', logbookCount(selected)],
                      ].map(([label, value]) => (
                        <div key={label}>
                          <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-moss/60">{label}</p>
                          <p className="mt-0.5 font-medium">{value}</p>
                        </div>
                      ))}
                    </div>
                  </Card>

                  {logbookCount(selected) === 0 ? (
                    <Card><p className="text-sm text-moss/75">Belum ada entri logbook pada periode ini.</p></Card>
                  ) : (
                    <motion.div
                      className="space-y-3"
                      variants={staggerContainer}
                      initial="hidden"
                      animate="visible"
                    >
                      {(selected.logbooks || []).map((item) => (
                        <motion.div key={item.id} variants={cardVariants}>
                          <Card>
                            <div className="flex items-center justify-between gap-3">
                              <p className="font-semibold">{formatDate(item.entry_date)}</p>
                              <Badge tone={item.mentor_verified ? 'sage' : 'copper'}>
                                {item.mentor_verified ? 'Terverifikasi' : item.status}
                              </Badge>
                            </div>
                            <p className="mt-2 text-sm"><b>Did:</b> {item.what_did}</p>
                            <p className="text-sm"><b>Learned:</b> {item.what_learned}</p>
                            <p className="text-sm"><b>Found:</b> {item.what_found}</p>
                          </Card>
                        </motion.div>
                      ))}
                    </motion.div>
                  )}
                </motion.div>
              )}
            </AnimatePresence>
          </motion.div>
        </div>
      )}
    </motion.div>
  )
}
