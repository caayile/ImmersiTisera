import { Link } from 'react-router-dom'

const stages = [
  ['Identify', 'Mempertemukan dosen, prodi, dan unit bisnis TSPM / TSIC.'],
  ['Match & Commit', 'Rekomendasi, pilih aktivitas, lalu Industry Immersion Agreement.'],
  ['Immersion', '60 hari: Discover, Understand, Contribute, Deliver.'],
  ['Impact', 'Output nyata, evaluasi, dan manfaat ke kampus serta industri.'],
  ['Integration', 'Close, follow-up, collaborate, develop, atau scale.'],
]

export default function Landing() {
  return (
    <div className="min-h-screen bg-cream text-ink">
      <header className="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
        <div>
          <p className="text-xl font-semibold">IMMERSI</p>
          <p className="text-[10px] font-medium uppercase tracking-[0.18em] text-moss/70">TSU Industry Immersion</p>
        </div>
        <div className="flex gap-3">
          <Link to="/login" className="rounded-lg px-4 py-2 text-sm font-medium text-moss hover:text-ink">Masuk</Link>
          <Link to="/register" className="rounded-lg bg-mint px-5 py-2 text-sm font-semibold text-white hover:bg-copper-dark">Daftar</Link>
        </div>
      </header>

      <section className="mx-auto grid max-w-6xl gap-12 px-6 py-14 lg:grid-cols-[1.15fr_0.85fr]">
        <div>
          <p className="mb-3 text-sm font-medium text-copper-dark">Dosen × Industri</p>
          <h1 className="max-w-3xl text-4xl font-semibold leading-tight md:text-5xl">
            Masuk industri, belajar, berkontribusi, lalu berkolaborasi.
          </h1>
          <p className="mt-5 max-w-xl text-base leading-7 text-moss/80">
            IMMERSI menuntun dosen ke unit bisnis TSPM dan TSIC — dari identifikasi kebutuhan sampai kemitraan berkelanjutan.
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link to="/register" className="rounded-lg bg-mint px-6 py-3 text-sm font-semibold text-white hover:bg-copper-dark">Mulai registrasi</Link>
            <Link to="/login" className="rounded-lg border border-clay bg-white px-6 py-3 text-sm font-medium text-ink">Lihat akun demo</Link>
          </div>
        </div>
        <aside className="rounded-2xl border border-clay bg-white p-6">
          <p className="text-xs font-medium uppercase tracking-[0.16em] text-copper-dark">Journey 60 hari</p>
          <ol className="mt-5 space-y-3">
            {['Discover', 'Understand', 'Contribute', 'Deliver', 'Connect'].map((item, index) => (
              <li key={item} className="flex items-center justify-between border-b border-clay pb-3 last:border-0">
                <span className="font-semibold">{item}</span>
                <span className="text-sm text-moss/70">Minggu {['1–2', '3–4', '5–7', '8', '60+'][index]}</span>
              </li>
            ))}
          </ol>
        </aside>
      </section>

      <section className="border-t border-clay bg-white">
        <div className="mx-auto grid max-w-6xl gap-4 px-6 py-14 md:grid-cols-5">
          {stages.map(([title, copy]) => (
            <article key={title} className="rounded-2xl border border-clay bg-cream p-5">
              <h3 className="text-lg font-semibold">{title}</h3>
              <p className="mt-2 text-sm leading-6 text-moss/80">{copy}</p>
            </article>
          ))}
        </div>
      </section>
    </div>
  )
}
