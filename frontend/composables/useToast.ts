export const useToast = () => {
  const toast = (message: string, type: 'success' | 'error') => {
    const toastEl = document.createElement('div')
    toastEl.className = `
      fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg
      ${type === 'success' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}
      transform transition-all duration-300 ease-out
    `
    toastEl.textContent = message
    document.body.appendChild(toastEl)

    // Animate in
    setTimeout(() => {
      toastEl.style.transform = 'translateY(10px)'
    }, 0)

    // Remove after delay
    setTimeout(() => {
      toastEl.style.transform = 'translateY(-10px)'
      toastEl.style.opacity = '0'
      setTimeout(() => {
        document.body.removeChild(toastEl)
      }, 300)
    }, 3000)
  }

  return {
    success: (message: string) => toast(message, 'success'),
    error: (message: string) => toast(message, 'error')
  }
} 