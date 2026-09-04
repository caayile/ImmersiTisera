import { useState } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
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

export default function Layout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const items = menus[user?.role] || menus.user
  const isDosen = user?.role === 'user'
  const [open, setOpen] = useState(false)

  function signOut() {
    logout()
    navigate('/')
  }

  if (isDosen) {
    return (
      <div className="min-h-screen bg-cream">
        <header className="sticky top-0 z-20 border-b border-clay bg-white/95 backdrop-blur">
          <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-3">
            <div className="flex items-center gap-8">
              <div>
                <p className="text-lg font-semibold text-ink">IMMERSI</p>
                <p className="text-[10px] font-medium uppercase tracking-[0.18em] text-moss/70">Industry Immersion</p>
              </div>
              <nav className="hidden items-center gap-1 md:flex">
                {items.map(([label, to]) => (
                  <NavLink
                    key={to}
                    to={to}
                    end={to === '/app'}
                    className={({ isActive }) =>
                      `rounded-lg px-3 py-2 text-sm font-medium transition ${
                        isActive ? 'bg-mint text-white' : 'text-moss hover:bg-mint-light/30 hover:text-ink'
                      }`
                    }
                  >
                    {label}
                  </NavLink>
                ))}
              </nav>
            </div>
            <div className="flex items-center gap-4">
              <div className="hidden text-right sm:block">
                <p className="text-sm font-medium text-ink">{user?.name}</p>
                <p className="text-[11px] text-moss/70">{roleLabel[user?.role]}</p>
              </div>
              <button type="button" className="text-sm font-medium text-moss hover:text-ink" onClick={signOut}>
                Keluar
              </button>
              <button type="button" className="rounded-lg border border-clay px-3 py-2 text-sm md:hidden" onClick={() => setOpen((value) => !value)}>
                Menu
              </button>
            </div>
          </div>
          {open && (
            <nav className="border-t border-clay px-5 py-3 md:hidden">
              {items.map(([label, to]) => (
                <NavLink
                  key={to}
                  to={to}
                  end={to === '/app'}
                  onClick={() => setOpen(false)}
                  className={({ isActive }) =>
                    `block rounded-lg px-3 py-2 text-sm ${isActive ? 'bg-mint text-white' : 'text-ink'}`
                  }
                >
                  {label}
                </NavLink>
              ))}
            </nav>
          )}
        </header>
        <main className="mx-auto max-w-6xl px-5 py-8">
          <Outlet />
        </main>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-cream lg:grid lg:grid-cols-[260px_1fr]">
      <aside className="bg-ink px-6 py-8 text-white">
        <div className="mb-10">
          <p className="text-2xl font-semibold">IMMERSI</p>
          <p className="mt-1 text-[10px] uppercase tracking-[0.18em] text-mint-light">Industry Immersion</p>
        </div>
        <nav className="space-y-1">
          {items.map(([label, to]) => (
            <NavLink
              key={to}
              to={to}
              end={to === '/app'}
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
        <div className="mt-12 rounded-2xl bg-ink-soft p-4">
          <p className="text-sm font-semibold">{user?.name}</p>
          <p className="mt-1 text-xs text-mint-light">{roleLabel[user?.role]}</p>
          <button className="mt-4 text-xs font-medium text-mint-light" onClick={signOut}>
            Keluar
          </button>
        </div>
      </aside>
      <main className="px-6 py-8 lg:px-10">
        <Outlet />
      </main>
    </div>
  )
}
