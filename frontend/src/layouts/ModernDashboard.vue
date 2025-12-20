<template>
  <div class="modern-dashboard">
    <!-- Modern Sidebar -->
    <ModernSidebar
      :collapsed="sidebarCollapsed"
      @toggle-collapse="toggleSidebar"
    />

    <!-- Main Content Area -->
    <div class="main-content" :class="{ 'sidebar-collapsed': sidebarCollapsed, 'no-header': isJudgerRoute }">
      <!-- Header (hidden for judger routes) -->
      <header v-if="!isJudgerRoute" class="dashboard-header">
        <div class="header-left">
          <button class="sidebar-toggle" @click="toggleSidebar" :title="`${sidebarCollapsed ? 'Expand' : 'Collapse'} Sidebar (Ctrl+B)`">
            <svg v-if="!sidebarCollapsed" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 6H17M3 10H17M3 14H17" stroke="#2D3748" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg v-else width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M4 6H16M4 10H16M4 14H16" stroke="#2D3748" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="keyboard-shortcut">⌘B</span>
          </button>
          <div class="breadcrumb">
            <span class="current-role">{{ currentRoleTitle }}</span>
            <span class="separator">/</span>
            <span class="current-page">{{ currentPageTitle }}</span>
          </div>
        </div>
        
        <div class="header-right">
          <!-- Notifications Bell Icon -->
          <button class="header-icon-btn" @click="toggleNotifications">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 2C6.68632 2 4.00003 4.68629 4.00003 8V11.5858L3.29292 12.2929C3.00692 12.5789 2.92137 13.009 3.07615 13.3827C3.23093 13.7564 3.59557 14 4.00003 14H16C16.4045 14 16.7691 13.7564 16.9239 13.3827C17.0787 13.009 16.9931 12.5789 16.7071 12.2929L16 11.5858V8C16 4.68629 13.3137 2 10 2Z" fill="#111827"/>
              <path d="M10 18C8.34315 18 7 16.6569 7 15H13C13 16.6569 11.6569 18 10 18Z" fill="#111827"/>
            </svg>
          </button>
          
          <!-- Heart Icon -->
          <button class="header-icon-btn" @click="toggleFavorites">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C4.73367 3.60948 7.26633 3.60948 8.82843 5.17157L10 6.34315L11.1716 5.17157C12.7337 3.60948 15.2663 3.60948 16.8284 5.17157C18.3905 6.73367 18.3905 9.26633 16.8284 10.8284L10 17.6569L3.17157 10.8284C1.60948 9.26633 1.60948 6.73367 3.17157 5.17157Z" fill="#111827"/>
            </svg>
          </button>
          
          <!-- Logout Button -->
          <button class="logout-btn" @click="handleLogout" title="Logout">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M7 3H4C3.44772 3 3 3.44772 3 4V16C3 16.5523 3.44772 17 4 17H7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M13 7L17 11L13 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M17 11H7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Logout</span>
          </button>
          
          <!-- User Info -->
          <div class="user-info">
            <div class="user-avatar" :class="{ 'has-image': currentUser.profile_image }">
              <img 
                v-if="currentUser.profile_image" 
                :src="getProfileImageUrl(currentUser.profile_image)" 
                :alt="currentUser.name || 'User'"
                @error="handleImageError"
              />
              <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="user-details">
              <span class="user-name">{{ currentUser.name }}</span>
              <span class="user-role">{{ currentRoleTitle }}</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content" :class="{ 'full-height': isJudgerRoute }">
        <router-view />
      </main>

      <!-- Footer -->
      <footer class="dashboard-footer">
        <div class="footer-content">
          <p>&copy; 2024 LankaPay Technnovation Awards. All rights reserved.</p>
        </div>
      </footer>
    </div>

    <!-- Mobile Overlay -->
    <div 
      class="mobile-overlay" 
      v-if="!sidebarCollapsed" 
      @click="toggleSidebar"
    ></div>
  </div>
</template>

<script>
import ModernSidebar from '../components/Sidebars/ModernSidebar.vue'
import userRoleStore from '@/store/userRole'
import apiService from '@/services/api'

export default {
  name: 'ModernDashboard',
  components: {
    ModernSidebar
  },
  data() {
    return {
      sidebarCollapsed: false,
      currentUser: {
        name: 'John Doe',
        email: 'john.doe@lankapay.com',
        profile_image: null
      }
    }
  },
  computed: {
    currentRole() {
      return userRoleStore.getRole()
    },
    currentRoleTitle() {
      const roleTitles = {
        admin: 'Administrator',
        judger: 'Judge',
        reporter: 'Reporter'
      }
      return roleTitles[this.currentRole] || 'User'
    },
    currentPageTitle() {
      const route = this.$route
      const pathSegments = route.path.split('/').filter(segment => segment)
      
      if (pathSegments.length <= 1) {
        return 'Dashboard'
      }
      
      const lastSegment = pathSegments[pathSegments.length - 1]
      return lastSegment
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
    },
    isJudgerRoute() {
      return this.$route.path.startsWith('/judger')
    }
  },
  methods: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },
    handleKeydown(event) {
      // Toggle sidebar with Ctrl/Cmd + B
      if ((event.ctrlKey || event.metaKey) && event.key === 'b') {
        event.preventDefault()
        this.toggleSidebar()
      }
    },
    toggleNotifications() {
      // TODO: Implement notifications dropdown
      console.log('Notifications clicked')
    },
    toggleFavorites() {
      // TODO: Implement favorites dropdown
      console.log('Favorites clicked')
    },
    async handleLogout() {
      try {
        await apiService.logout()
        // Reset role to default and redirect to sign-in
        userRoleStore.setRole('admin')
        this.$router.push('/sign-in')
      } catch (error) {
        console.error('Logout failed:', error)
        // Even if API call fails, clear local storage and redirect
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        userRoleStore.setRole('admin')
        this.$router.push('/sign-in')
      }
    },
    getProfileImageUrl(imageUrl) {
      if (!imageUrl) return null;
      
      // Convert to API endpoint URL if it's a backend/uploads path
      if (imageUrl.includes('/backend/uploads/')) {
        const pathMatch = imageUrl.match(/\/backend\/uploads\/(.+)$/);
        if (pathMatch && pathMatch[1]) {
          const apiBaseUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
          return `${apiBaseUrl}/uploads?path=${encodeURIComponent(pathMatch[1])}`;
        }
      }
      
      // Fix URL if it's missing the port
      if (imageUrl.includes('localhost/') && !imageUrl.includes('localhost:')) {
        imageUrl = imageUrl.replace('http://localhost/', 'http://localhost:8000/');
        imageUrl = imageUrl.replace('https://localhost/', 'https://localhost:8000/');
      }
      
      // Ensure the URL is absolute
      if (!imageUrl.startsWith('http://') && !imageUrl.startsWith('https://') && !imageUrl.startsWith('blob:')) {
        if (imageUrl.startsWith('//')) {
          imageUrl = window.location.protocol + imageUrl;
        } else if (imageUrl.startsWith('/')) {
          imageUrl = 'http://localhost:8000' + imageUrl;
        } else {
          imageUrl = 'http://localhost:8000/' + imageUrl;
        }
      }
      
      return imageUrl;
    },
    handleImageError(event) {
      // Show default user avatar on error
      event.target.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23e5e7eb' width='100' height='100'/%3E%3Ccircle cx='50' cy='35' r='20' fill='%239ca3af'/%3E%3Cpath d='M20 85c0-22 13-30 30-30s30 8 30 30' fill='%239ca3af'/%3E%3C/svg%3E";
      event.target.onerror = null; // Prevent infinite loop
    }
  },
  mounted() {
    // Set initial role based on current route
    userRoleStore.setRoleFromPath(this.$route.path)
    
    // Get current user from API service if available
    const user = apiService.getCurrentUser()
    if (user) {
      this.currentUser = {
        name: user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.username || 'John Doe',
        email: user.email || 'john.doe@lankapay.com',
        profile_image: user.profile_image || null
      }
    }
    
    // Listen for route changes to update role
    this.$router.beforeEach((to, from, next) => {
      userRoleStore.setRoleFromPath(to.path)
      next()
    })
    
    // Add keyboard event listener
    document.addEventListener('keydown', this.handleKeydown)
  },
  beforeDestroy() {
    // Remove keyboard event listener
    document.removeEventListener('keydown', this.handleKeydown)
  }
}
</script>

<style scoped>
.modern-dashboard {
  display: flex;
  min-height: 100vh;
  width: 100%;
  max-width: 100vw;
  background: #FFFFFF;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  overflow-x: hidden;
}

.main-content {
  flex: 1;
  margin-left: 280px;
  display: flex;
  flex-direction: column;
  min-width: 0;
  width: calc(100% - 280px);
  transition: margin-left 0.3s ease, width 0.3s ease;
}

.main-content.sidebar-collapsed {
  margin-left: 80px;
  width: calc(100% - 80px);
}

/* Header */
.dashboard-header {
  background: #FFFFFF;
  border-bottom: 1px solid #E5E7EB;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 100;
  width: 100%;
  box-sizing: border-box;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.sidebar-toggle {
  background: none;
  border: none;
  padding: 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6B7280;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-toggle:hover {
  background-color: #F1F5FF;
  color: #007BFF;
  transform: scale(1.05);
}

.sidebar-toggle:focus {
  outline: 2px solid #007BFF;
  outline-offset: 2px;
}

.keyboard-shortcut {
  font-size: 10px;
  color: #9CA3AF;
  margin-left: 4px;
  font-weight: 500;
  opacity: 0.7;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #6B7280;
}

.current-role {
  font-weight: 600;
  color: #007BFF;
}

.separator {
  color: #D1D5DB;
}

.current-page {
  color: #2D3748;
  font-weight: 500;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon-btn {
  background: none;
  border: none;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6B7280;
  display: flex;
  align-items: center;
  justify-content: center;
}

.header-icon-btn:hover {
  background-color: #F1F5FF;
  color: #007BFF;
  transform: scale(1.05);
}

.header-icon-btn:focus {
  outline: 2px solid #007BFF;
  outline-offset: 2px;
}

.logout-btn {
  background: none;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6B7280;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 500;
}

.logout-btn:hover {
  background-color: #FEF2F2;
  color: #DC2626;
  transform: scale(1.05);
}

.logout-btn:focus {
  outline: 2px solid #DC2626;
  outline-offset: 2px;
}

.logout-btn svg {
  stroke: currentColor;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
}

.user-info:hover {
  background-color: #F9FAFB;
}

.user-avatar {
  width: 50px;
  height: 50px;
  background: #F1F5FF;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #007BFF;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid #E5E7EB;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  
  &.has-image {
    background: transparent;
    padding: 0;
    border-color: #3b82f6;
  }
  
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
  }
  
  svg {
    width: 24px;
    height: 24px;
  }
}

.user-details {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #2D3748;
  line-height: 1.2;
}

.user-role {
  font-size: 12px;
  color: #6B7280;
  line-height: 1.2;
}

/* Page Content */
.page-content {
  flex: 1;
  padding: 24px;
  background: #F9FAFB;
  min-height: calc(100vh - 140px);
  width: 100%;
  box-sizing: border-box;
}

/* Footer */
.dashboard-footer {
  background: #FFFFFF;
  border-top: 1px solid #E5E7EB;
  padding: 16px 24px;
  width: 100%;
  box-sizing: border-box;
}

.footer-content {
  text-align: center;
}

.footer-content p {
  margin: 0;
  font-size: 12px;
  color: #6B7280;
}

/* No header layout adjustments */
.main-content.no-header .page-content {
  min-height: calc(100vh - 80px);
}

.page-content.full-height {
  padding-top: 16px;
}

/* Mobile Overlay */
.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: none;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .main-content {
    margin-left: 0;
    width: 100%;
  }
  
  .main-content.sidebar-collapsed {
    margin-left: 0;
    width: 100%;
  }
  
  .mobile-overlay {
    display: block;
  }
  
  .dashboard-header {
    padding: 16px 20px;
    width: 100%;
  }
  
  .page-content {
    padding: 20px;
  }
  
  .user-details {
    display: none;
  }
  
  .logout-btn span {
    display: none;
  }
  
  .logout-btn {
    padding: 8px;
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    padding: 12px 16px;
  }
  
  .page-content {
    padding: 16px;
  }
  
  .breadcrumb {
    font-size: 13px;
  }
}

/* Smooth transitions */
* {
  transition: all 0.2s ease;
}

/* Focus states for accessibility */
.sidebar-toggle:focus,
.user-info:focus {
  outline: 2px solid #007BFF;
  outline-offset: 2px;
}

/* Loading state */
.page-content {
  position: relative;
}

.page-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 10;
}

.page-content.loading::before {
  opacity: 1;
  visibility: visible;
}
</style>
