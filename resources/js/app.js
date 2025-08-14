import Alpine from 'alpinejs'

window.Alpine = Alpine

// Theme management
Alpine.data('theme', () => ({
    isDark: false,
    
    init() {
        // Check for saved theme preference or default to light mode
        this.isDark = localStorage.getItem('theme') === 'dark' || 
                     (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
        
        this.updateTheme()
    },
    
    toggleTheme() {
        this.isDark = !this.isDark
        localStorage.setItem('theme', this.isDark ? 'dark' : 'light')
        this.updateTheme()
    },
    
    updateTheme() {
        if (this.isDark) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    }
}))

// Loading bar management
Alpine.data('loadingBar', () => ({
    isLoading: false,
    progress: 0,
    interval: null,
    
    init() {
        // Listen for navigation events
        this.$watch('isLoading', (value) => {
            if (value) {
                this.startLoading()
            } else {
                this.stopLoading()
            }
        })
        
        // Listen for custom navigation events
        document.addEventListener('navigation-start', () => {
            this.isLoading = true
        })
        
        // Listen for page navigation events (Turbo)
        document.addEventListener('turbo:request-start', () => {
            this.isLoading = true
        })
        
        document.addEventListener('turbo:request-end', () => {
            this.isLoading = false
        })
        
        // Listen for page navigation events (Livewire)
        if (window.Livewire) {
            window.Livewire.on('loading', () => {
                this.isLoading = true
            })
            
            window.Livewire.on('loaded', () => {
                this.isLoading = false
            })
        }
        
        // Listen for all link clicks (fallback for regular navigation)
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a')
            if (link && link.href && !link.href.startsWith('javascript:') && !link.href.startsWith('#')) {
                // Check if it's a same-origin link
                if (link.hostname === window.location.hostname) {
                    this.isLoading = true
                }
            }
        })
        
        // Listen for browser navigation (back/forward buttons)
        window.addEventListener('popstate', () => {
            this.isLoading = true
        })
        
        // Fallback for regular navigation
        window.addEventListener('beforeunload', () => {
            this.isLoading = true
        })
        
        // Auto-hide loading bar after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.isLoading = false
            }, 500)
        })
        
        // Auto-hide loading bar when page is fully loaded
        if (document.readyState === 'complete') {
            setTimeout(() => {
                this.isLoading = false
            }, 500)
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    this.isLoading = false
                }, 500)
            })
        }
    },
    
    startLoading() {
        this.progress = 0
        this.interval = setInterval(() => {
            if (this.progress < 90) {
                this.progress += Math.random() * 15
            }
        }, 100)
    },
    
    stopLoading() {
        this.progress = 100
        setTimeout(() => {
            this.progress = 0
        }, 200)
        
        if (this.interval) {
            clearInterval(this.interval)
            this.interval = null
        }
    }
}))

Alpine.start()
