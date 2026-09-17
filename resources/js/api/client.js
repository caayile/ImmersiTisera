import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('immersi_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (csrf) {
    config.headers['X-CSRF-TOKEN'] = csrf
  }

  return config
})

export default api
