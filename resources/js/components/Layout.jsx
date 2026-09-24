import { useState } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

const menus = {
  user: [
    ['Dasbor', '/app'],
    ['Opportunity', '/app/opportunities'],
    ['Minat saya', '/app/applications'],
    ['Program', '/app/programs'],
    ['Profil', '/app/profile'],
  ],
  mentor: [
    ['Dasbor', '/app'],
    ['Opportunity', '/app/mentor/opportunities'],
    ['Review minat', '/app/applications'],
    ['Program', '/app/programs'],
    ['Profil', '/app/profile'],
  ],
  admin: [
    ['Dasbor', '/app'],
    ['Verifikasi', '/app/admin/users'],
    ['Kebutuhan Prodi', '/app/admin/needs'],
    ['Program', '/app/admin/programs'],
  ],
}

const roleLabel = {
  user: 'Dosen',
  mentor: 'Mentor Industri',
  admin: 'Admin Program',
}

function initialsOf(name = '') {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('')
}

function DosenProfileCard({ user }) {
  const profile = user?.dosen_profile || {}
  const nidn = profile.nidn
  const prodi = profile.prodi
  const faculty = profile.department
  const profileReady = Boolean(prodi && faculty)
  const initials = initialsOf(user?.name)

  return (
    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
      <div className="flex flex-col items-center text-center">
        {user?.avatar ? (
          <img
            src={user.avatar}
            alt=""
            width="72"
            height="72"
            className="h-[72px] w-[72px] rounded-full object-cover ring-2 ring-white/15"
            referrerPolicy="no-referrer"
          />
        ) : (
          <span className="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-mint/20 text-lg font-semibold text-mint-light ring-2 ring-white/15">
            {initials}
          </span>
        )}
        <p className="mt-3 text-sm font-semibold leading-snug text-white">{user?.name}</p>
        <p className="mt-0.5 text-xs text-white/55">{nidn || 'NIDN belum diisi'}</p>
        <span
          className={`mt-2 inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${
            profileReady ? 'bg-mint/20 text-mint-light' : 'bg-amber-400/15 text-amber-200'
          }`}
        >
          {profileReady ? 'Profil lengkap' : 'Lengkapi profil'}
        </span>
      </div>

      <dl className="mt-4 space-y-3 border-t border-white/10 pt-4 text-left">
        <div>
          <dt className="text-[10px] font-semibold uppercase tracking-[0.12em] text-white/45">Prodi</dt>
          <dd className="mt-0.5 text-sm font-medium text-white/90">{prodi || 'Belum diisi'}</dd>
        </div>
        <div>
          <dt className="text-[10px] font-semibold uppercase tracking-[0.12em] text-white/45">Fakultas</dt>
          <dd className="mt-0.5 text-sm font-medium text-white/90">{faculty || 'Belum diisi'}</dd>
        </div>
      </dl>
    </div>
  )
}

export default function Layout() {
  const { user, logout } = useAuth()
  const items = menus[user?.role] || menus.user
  const isDosen = user?.role === 'user'
  const [open, setOpen] = useState(false)

  function signOut() {
    logout()
    window.location.href = '/'
  }

  return (
    <div className="min-h-screen bg-cream lg:grid lg:grid-cols-[280px_1fr]">
      <div
        className={`fixed inset-0 z-30 bg-black/30 lg:hidden ${open ? 'block' : 'hidden'}`}
        onClick={() => setOpen(false)}
        aria-hidden="true"
      />
      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-[280px] flex-col bg-ink px-6 py-8 text-white transition lg:static lg:translate-x-0 ${
          open ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="mb-6 shrink-0">
          <p className="text-2xl font-semibold">IMMERSI</p>
          <p className="mt-1 text-[10px] uppercase tracking-[0.18em] text-mint-light">Industry Immersion</p>
        </div>

        {isDosen && (
          <div className="mb-6 shrink-0">
            <DosenProfileCard user={user} />
          </div>
        )}

        <nav className="min-h-0 flex-1 space-y-1 overflow-y-auto">
          {items.map(([label, to]) => (
            <NavLink
              key={to}
              to={to}
              end={to === '/app'}
              onClick={() => setOpen(false)}
              className={({ isActive }) =>
                `block rounded-xl px-4 py-2.5 text-sm font-medium transition ${
                  isActive ? 'bg-mint text-white' : 'text-white/70 hover:bg-white/5 hover:text-white'
                }`
              }
            >
              {label}
            </NavLink>
          ))}
        </nav>

        {!isDosen && (
          <div className="mt-8 shrink-0 rounded-2xl bg-ink-soft p-4">
            <p className="text-sm font-semibold">{user?.name}</p>
            <p className="mt-1 text-xs text-mint-light">{roleLabel[user?.role]}</p>
            <button type="button" className="mt-4 text-xs font-medium text-mint-light" onClick={signOut}>
              Keluar
            </button>
          </div>
        )}

        {isDosen && (
          <button type="button" className="mt-6 shrink-0 text-left text-sm text-white/60 hover:text-white" onClick={signOut}>
            Keluar
          </button>
        )}
      </aside>

      <div className="min-w-0">
        <header className="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-clay bg-white/95 px-5 py-3 backdrop-blur lg:px-10">
          <button type="button" className="rounded-lg border border-clay px-3 py-2 text-sm lg:hidden" onClick={() => setOpen((value) => !value)}>
            Menu
          </button>
          <div className="ml-auto text-right">
            <p className="text-sm font-medium text-ink">{user?.name}</p>
            <p className="text-[11px] text-moss/70">{roleLabel[user?.role]}</p>
          </div>
        </header>
        <main className="px-5 py-8 lg:px-10">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
