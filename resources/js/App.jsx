import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import Layout from './components/Layout'
import Landing from './pages/Landing'
import Login from './pages/Login'
import Register from './pages/Register'
import ProfileSetup from './pages/ProfileSetup'
import Dashboard from './pages/Dashboard'
import Opportunities from './pages/Opportunities'
import OpportunityDetail from './pages/OpportunityDetail'
import Applications from './pages/Applications'
import AgreementPage from './pages/AgreementPage'
import ProgramList from './pages/ProgramList'
import ProgramPage from './pages/ProgramPage'
import MentorOpportunities from './pages/MentorOpportunities'
import AdminUsers from './pages/admin/AdminUsers'
import AdminNeeds from './pages/admin/AdminNeeds'
import AdminPrograms from './pages/admin/AdminPrograms'

function Guard({ children, roles, allowUnverified = false }) {
  const { user, loading } = useAuth()

  if (loading) {
    return <div className="grid min-h-screen place-items-center bg-cream text-moss">Memuat IMMERSI…</div>
  }

  if (!user) return <Navigate to="/login" replace />
  if (roles && !roles.includes(user.role)) return <Navigate to="/app" replace />
  if (!allowUnverified && user.role !== 'admin' && user.verification_status !== 'verified') {
    return <Navigate to="/app/profile" replace />
  }

  return children
}

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<Landing />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route
            path="/app"
            element={
              <Guard allowUnverified>
                <Layout />
              </Guard>
            }
          >
            <Route index element={<Guard><Dashboard /></Guard>} />
            <Route path="profile" element={<Guard allowUnverified><ProfileSetup /></Guard>} />
            <Route path="opportunities" element={<Guard roles={['user']}><Opportunities /></Guard>} />
            <Route path="opportunities/:id" element={<Guard roles={['user']}><OpportunityDetail /></Guard>} />
            <Route path="applications" element={<Guard roles={['user', 'mentor']}><Applications /></Guard>} />
            <Route path="agreements/:id" element={<Guard roles={['user', 'mentor', 'admin']}><AgreementPage /></Guard>} />
            <Route path="programs" element={<Guard roles={['user', 'mentor']}><ProgramList /></Guard>} />
            <Route path="programs/:id" element={<Guard roles={['user', 'mentor', 'admin']}><ProgramPage /></Guard>} />
            <Route path="mentor/opportunities" element={<Guard roles={['mentor']}><MentorOpportunities /></Guard>} />
            <Route path="admin/users" element={<Guard roles={['admin']}><AdminUsers /></Guard>} />
            <Route path="admin/needs" element={<Guard roles={['admin']}><AdminNeeds /></Guard>} />
            <Route path="admin/programs" element={<Guard roles={['admin']}><AdminPrograms /></Guard>} />
          </Route>
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  )
}
