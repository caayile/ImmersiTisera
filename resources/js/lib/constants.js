export const PURPOSE = [
  { value: 'riset', label: 'Riset' },
  { value: 'observasi', label: 'Observasi' },
  { value: 'pembelajaran', label: 'Pembelajaran' },
  { value: 'penugasan', label: 'Penugasan' },
]

export const ACTIVITIES = [
  { value: 'penugasan', label: 'Penugasan' },
  { value: 'observasi', label: 'Observasi' },
  { value: 'riset', label: 'Riset' },
]

export const PHASES = [
  { key: 'discover', week: '1–2', title: 'Discover', output: 'Industry Insight' },
  { key: 'understand', week: '3–4', title: 'Understand', output: 'Problem Statement' },
  { key: 'contribute', week: '5–7', title: 'Contribute', output: 'Try → Test → Develop' },
  { key: 'deliver', week: '8', title: 'Deliver', output: 'Final Output' },
  { key: 'connect', week: '60+', title: 'Connect', output: 'Kolaborasi lanjutan' },
]

export const CONNECT_OPTIONS = [
  { value: 'close', label: 'Close', hint: 'Tidak ada tindak lanjut' },
  { value: 'follow_up', label: 'Follow-up', hint: 'Masih perlu diskusi' },
  { value: 'collaborate', label: 'Collaborate', hint: 'Ada project bersama' },
  { value: 'develop', label: 'Develop', hint: 'Output dikembangkan' },
  { value: 'scale', label: 'Scale', hint: 'Kemitraan strategis' },
]

export const MATURITY = {
  0: 'Not Connected',
  1: 'Continue',
  2: 'Collaboration',
  3: 'Develop',
  4: 'Scale',
}

export const OUTPUT_CATEGORIES = [
  { value: 'research', label: 'Research Insight' },
  { value: 'learning', label: 'Learning Material' },
  { value: 'industry', label: 'Industry Improvement' },
]

export const ORGANIZATIONS = ['TSPM', 'TSIC']

export const BUSINESS_UNITS = {
  TSPM: [
    'School Book Publishing',
    'E-Publishing',
    'Production',
    'School Book Sales',
    'Digital Business',
    'General Trading',
    'Marketing',
    'Procurement',
    'SCM',
    'IQA',
    'Finance',
    'IT',
    'TAX',
    'HR & GA',
    'HSE',
  ],
  TSIC: [
    'Center Of Excellence',
    'People Development Center',
    'MTIS Planning and Development',
    'MTIS Perpuskita dan Tisera',
  ],
}

export function unitsFor(org) {
  return BUSINESS_UNITS[org] || [...BUSINESS_UNITS.TSPM, ...BUSINESS_UNITS.TSIC]
}

export function labelOf(list, value) {
  return list.find((item) => item.value === value)?.label || value || '—'
}

export function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}
