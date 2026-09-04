import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import api from '../api/client'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  async function loadUser() {
    const token = localStorage.getItem('immersi_token')
    if (!token) {
      setUser(null)
      setLoading(false)
      return
    }
    try {
      const { data } = await api.get('/me')
      setUser(data)
    } catch {
      localStorage.removeItem('immersi_token')
      setUser(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadUser()
  }, [])

  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })
    localStorage.setItem('immersi_token', data.token)
    setUser(data.user)
    return data.user
  }

  async function register(payload) {
    const { data } = await api.post('/register', payload)
    localStorage.setItem('immersi_token', data.token)
    setUser(data.user)
    return data.user
  }

  function logout() {
    api.post('/logout').catch(() => {})
    localStorage.removeItem('immersi_token')
    setUser(null)
  }

  const value = useMemo(
    () => ({ user, setUser, loading, login, register, logout, refresh: loadUser }),
    [user, loading],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  return useContext(AuthContext)
}
