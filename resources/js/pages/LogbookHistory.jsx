import { useEffect, useMemo, useState } from 'react'
import api from '../api/client'
import { formatDate } from '../lib/constants'
import { Badge, Card, PageHeader } from '../components/ui'

function formatRange(start, end) {
  if (!start && !end) return 'Periode belum diatur'
  return `${formatDate(start)} — ${formatDate(end || start)}`
}

function logbookCount(program) {
  return (program.logbooks || []).length
}

function PeriodItem({ program, selected, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
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
    </button>
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
    <div>
      <PageHeader
        kicker="Arsip imersi"
        title="Riwayat Logbook"
        description="Daftar periode magang per tahun, termasuk penempatan unit bisnis tiap batch."
        action={<Badge tone="copper">{total} entri</Badge>}
      />

      {loading && <Card>Memuat riwayat logbook…</Card>}

      {!loading && programs.length === 0 && (
        <Card>
          <p className="text-sm text-moss/80">Belum ada riwayat logbook.</p>
        </Card>
      )}

      {!loading && programs.length > 0 && (
        <div className="grid gap-6 lg:grid-cols-[340px_1fr]">
          <div className="space-y-5">
            {years.map(([year, items]) => (
              <div key={year}>
                <p className="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-moss/70">Batch {year}</p>
                <div className="space-y-3">
                  {items.map((program) => (
                    <PeriodItem
                      key={program.id}
                      program={program}
                      selected={selected?.id === program.id}
                      onClick={() => setSelectedId(program.id)}
                    />
                  ))}
                </div>
              </div>
            ))}
          </div>

          <div>
            {selected && (
              <>
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
                    <div>
                      <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-moss/60">Periode</p>
                      <p className="mt-0.5 font-medium">{formatRange(selected.start_date, selected.end_date)}</p>
                    </div>
                    <div>
                      <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-moss/60">Mentor</p>
                      <p className="mt-0.5 font-medium">{selected.mentor || 'Belum ditugaskan'}</p>
                    </div>
                    <div>
                      <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-moss/60">Entri</p>
                      <p className="mt-0.5 font-medium">{logbookCount(selected)}</p>
                    </div>
                  </div>
                </Card>

                {logbookCount(selected) === 0 ? (
                  <Card>
                    <p className="text-sm text-moss/75">Belum ada entri logbook pada periode ini.</p>
                  </Card>
                ) : (
                  <div className="space-y-3">
                    {(selected.logbooks || []).map((item) => (
                      <Card key={item.id}>
                        <div className="flex items-center justify-between gap-3">
                          <p className="font-semibold">{formatDate(item.entry_date)}</p>
                          <Badge tone={item.mentor_verified ? 'sage' : 'copper'}>
                            {item.mentor_verified ? 'Terverifikasi' : item.status}
                          </Badge>
                        </div>
                        <p className="mt-2 text-sm">
                          <b>Did:</b> {item.what_did}
                        </p>
                        <p className="text-sm">
                          <b>Learned:</b> {item.what_learned}
                        </p>
                        <p className="text-sm">
                          <b>Found:</b> {item.what_found}
                        </p>
                      </Card>
                    ))}
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      )}
    </div>
  )
}
