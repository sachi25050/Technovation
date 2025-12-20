<template>
  <div class="modern-sidebar" :class="{ 'collapsed': collapsed }">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <div class="logo">
        <div class="logo-container">
          <img src="/images/lankapay-logo.png" alt="LankaPay Logo" class="logo-image" />
          <div class="logo-text-container">
            <h2 class="logo-text">LankaPay</h2>
            <p class="logo-subtitle">Technnovation Awards</p>
          </div>
        </div>
      </div>
      <button class="collapse-btn" @click="toggleCollapse" :title="collapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
        <svg v-if="!collapsed" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M10 12L6 8L10 4" stroke="#2D3748" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M6 4L10 8L6 12" stroke="#2D3748" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <ul class="nav-list">
        <!-- Admin Navigation -->
        <template v-if="isAdmin">
          <li class="nav-item">
            <router-link to="/admin" class="nav-link" active-class="active" exact>
              <div class="nav-icon">
                <!-- Dashboard Icon - Grid Layout -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="currentColor"/>
                  <path d="M3 10C3 9.44771 3.44772 9 4 9H10C10.5523 9 11 9.44771 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="currentColor"/>
                  <path d="M14 9C13.4477 9 13 9.44771 13 10V16C13 16.5523 13.4477 17 14 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44771 16.5523 9 16 9H14Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Dashboard</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/admin/create-accounts" class="nav-link" active-class="active">
              <div class="nav-icon">
                <!-- Users Icon - Multiple People with Plus -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8 9C9.65685 9 11 7.65685 11 6C11 4.34315 9.65685 3 8 3C6.34315 3 5 4.34315 5 6C5 7.65685 6.34315 9 8 9Z" fill="currentColor"/>
                  <path d="M8 11C4.68629 11 2 13.6863 2 17H14C14 13.6863 11.3137 11 8 11Z" fill="currentColor"/>
                  <path d="M16 7C16 6.44772 15.5523 6 15 6C14.4477 6 14 6.44772 14 7V8H13C12.4477 8 12 8.44772 12 9C12 9.55228 12.4477 10 13 10H14V11C14 11.5523 14.4477 12 15 12C15.5523 12 16 11.5523 16 11V10H17C17.5523 10 18 9.55228 18 9C18 8.44772 17.5523 8 17 8H16V7Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Create Accounts</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/admin/add-awards" class="nav-link" active-class="active">
              <div class="nav-icon">
                <!-- Trophy/Award Icon -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10 2L12.09 6.26L17 6.97L13.5 10.36L14.18 15.24L10 13.05L5.82 15.24L6.5 10.36L3 6.97L7.91 6.26L10 2Z" fill="currentColor"/>
                  <path d="M7 16H13V17C13 17.5523 12.5523 18 12 18H8C7.44772 18 7 17.5523 7 17V16Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Add Awards</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/admin/add-institution" class="nav-link" active-class="active">
              <div class="nav-icon">
                <!-- Building/Institution Icon -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10 2L3 6V8H17V6L10 2Z" fill="currentColor"/>
                  <path d="M4 9V16H6V11H8V16H9V11H11V16H12V11H14V16H16V9H4Z" fill="currentColor"/>
                  <path d="M2 17H18V19H2V17Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Add Institution</span>
            </router-link>
          </li>
          
         
        </template>

        <!-- Judger Navigation -->
        <template v-if="isJudger">
          <li class="nav-item">
            <router-link to="/judger" class="nav-link" active-class="active" exact>
              <div class="nav-icon">
                <!-- Dashboard Icon - Grid Layout -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="currentColor"/>
                  <path d="M3 10C3 9.44771 3.44772 9 4 9H10C10.5523 9 11 9.44771 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="currentColor"/>
                  <path d="M14 9C13.4477 9 13 9.44771 13 10V16C13 16.5523 13.4477 17 14 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44771 16.5523 9 16 9H14Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Dashboard</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/judger/evaluate" class="nav-link" active-class="active">
              <div class="nav-icon">
                <!-- Clipboard Check Icon - Evaluate -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 2C8.44772 2 8 2.44772 8 3C8 3.55228 8.44772 4 9 4H11C11.5523 4 12 3.55228 12 3C12 2.44772 11.5523 2 11 2H9Z" fill="currentColor"/>
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M4 5C4 3.89543 4.89543 3 6 3C6 4.65685 7.34315 6 9 6H11C12.6569 6 14 4.65685 14 3C15.1046 3 16 3.89543 16 5V16C16 17.1046 15.1046 18 14 18H6C4.89543 18 4 17.1046 4 16V5ZM13.7071 9.70711C14.0976 9.31658 14.0976 8.68342 13.7071 8.29289C13.3166 7.90237 12.6834 7.90237 12.2929 8.29289L9 11.5858L7.70711 10.2929C7.31658 9.90237 6.68342 9.90237 6.29289 10.2929C5.90237 10.6834 5.90237 11.3166 6.29289 11.7071L8.29289 13.7071C8.68342 14.0976 9.31658 14.0976 9.70711 13.7071L13.7071 9.70711Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Evaluate Awards</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/judger/submit-evaluations" class="nav-link" active-class="active">
              <div class="nav-icon">
                <!-- Clock/History Icon - Submission History -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18ZM11 6C11 5.44772 10.5523 5 10 5C9.44772 5 9 5.44772 9 6V10C9 10.2652 9.10536 10.5196 9.29289 10.7071L12.1213 13.5355C12.5118 13.9261 13.145 13.9261 13.5355 13.5355C13.9261 13.145 13.9261 12.5118 13.5355 12.1213L11 9.58579V6Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Submission History</span>
            </router-link>
          </li>
        </template>

        <!-- Reporter Navigation -->
        <template v-if="isReporter">
          <li class="nav-item">
            <router-link to="/reporter" class="nav-link" active-class="active" exact>
              <div class="nav-icon">
                <!-- Dashboard Icon - Grid Layout -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="currentColor"/>
                  <path d="M3 10C3 9.44771 3.44772 9 4 9H10C10.5523 9 11 9.44771 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="currentColor"/>
                  <path d="M14 9C13.4477 9 13 9.44771 13 10V16C13 16.5523 13.4477 17 14 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44771 16.5523 9 16 9H14Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Dashboard</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/reporter/generate-reports" class="nav-link" active-class="active">
              <div class="nav-icon">
                <!-- Document/Report Icon -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M4 4C4 2.89543 4.89543 2 6 2H10.5858C11.1162 2 11.6249 2.21071 12 2.58579L15.4142 6C15.7893 6.37507 16 6.88378 16 7.41421V16C16 17.1046 15.1046 18 14 18H6C4.89543 18 4 17.1046 4 16V4ZM6 10C6 9.44772 6.44772 9 7 9H13C13.5523 9 14 9.44772 14 10C14 10.5523 13.5523 11 13 11H7C6.44772 11 6 10.5523 6 10ZM7 13C6.44772 13 6 13.4477 6 14C6 14.5523 6.44772 15 7 15H13C13.5523 15 14 14.5523 14 14C14 13.4477 13.5523 13 13 13H7Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Generate Reports</span>
            </router-link>
          </li>
        </template>
      </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <!-- User Profile Section for Judger -->
      <div v-if="isJudger" class="user-profile-section">
        <div class="user-profile-avatar" :class="{ 'has-image': currentUser.profile_image }">
          <img 
            v-if="currentUser.profile_image" 
            :src="getProfileImageUrl(currentUser.profile_image)" 
            :alt="currentUser.name || 'Judge'"
            @error="handleImageError"
          />
          <svg v-else width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
          </svg>
        </div>
        <div class="user-profile-info">
          <span class="user-welcome-text">WELCOME!</span>
          <span class="user-profile-name">{{ currentUser.name }}</span>
          <span class="user-profile-role">Judge</span>
        </div>
        <button class="user-logout-btn" @click="handleLogout" title="Logout">
          <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 3H4C3.44772 3 3 3.44772 3 4V16C3 16.5523 3.44772 17 4 17H7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M13 7L17 11L13 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17 11H7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <!-- Help Section for Non-Judger -->
      <div v-else class="help-section">
        <div class="help-icon">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18Z" stroke="currentColor" stroke-width="2"/>
            <path d="M7.5 7.5C7.5 6.11929 8.61929 5 10 5C11.3807 5 12.5 6.11929 12.5 7.5C12.5 8.88071 11.3807 10 10 10V12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="help-content">
          <h6>Need Help?</h6>
          <p>Check our documentation</p>
        </div>
      </div>
    </div>

    <!-- Floating Expand Button (shown when collapsed) -->
    <button 
      v-if="collapsed" 
      class="floating-expand-btn" 
      @click="toggleCollapse"
      title="Expand Sidebar"
    >
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>
</template>

<script>
import userRoleStore from '@/store/userRole'
import apiService from '@/services/api'

export default {
  name: 'ModernSidebar',
  props: {
    collapsed: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      currentUser: {
        name: 'Judge',
        email: '',
        profile_image: null
      }
    }
  },
  computed: {
    isAdmin() {
      return userRoleStore.isAdmin()
    },
    isJudger() {
      return userRoleStore.isJudger()
    },
    isReporter() {
      return userRoleStore.isReporter()
    }
  },
  methods: {
    toggleCollapse() {
      this.$emit('toggle-collapse')
    },
    getProfileImageUrl(imageUrl) {
      if (!imageUrl) return null;
      
      if (imageUrl.includes('/backend/uploads/')) {
        const pathMatch = imageUrl.match(/\/backend\/uploads\/(.+)$/);
        if (pathMatch && pathMatch[1]) {
          const apiBaseUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
          return `${apiBaseUrl}/uploads?path=${encodeURIComponent(pathMatch[1])}`;
        }
      }
      
      if (imageUrl.includes('localhost/') && !imageUrl.includes('localhost:')) {
        imageUrl = imageUrl.replace('http://localhost/', 'http://localhost:8000/');
        imageUrl = imageUrl.replace('https://localhost/', 'https://localhost:8000/');
      }
      
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
      event.target.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23e5e7eb' width='100' height='100'/%3E%3Ccircle cx='50' cy='35' r='20' fill='%239ca3af'/%3E%3Cpath d='M20 85c0-22 13-30 30-30s30 8 30 30' fill='%239ca3af'/%3E%3C/svg%3E";
      event.target.onerror = null;
    },
    async handleLogout() {
      try {
        await apiService.logout();
        userRoleStore.setRole('admin');
        this.$router.push('/sign-in');
      } catch (error) {
        console.error('Logout failed:', error);
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        userRoleStore.setRole('admin');
        this.$router.push('/sign-in');
      }
    },
    loadUserData() {
      const user = apiService.getCurrentUser();
      if (user) {
        this.currentUser = {
          name: user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.username || 'Judge',
          email: user.email || '',
          profile_image: user.profile_image || null
        };
      }
    }
  },
  mounted() {
    this.loadUserData();
  }
}
</script>

<style scoped>
.modern-sidebar {
  width: 280px;
  height: 100vh;
  background: #F9FAFB;
  border-right: 1px solid #E5E7EB;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  transition: width 0.3s ease;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.modern-sidebar.collapsed {
  width: 80px;
}

/* Sidebar Header */
.sidebar-header {
  padding: 24px 20px;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  flex: 1;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-image {
  width: 40px;
  height: 40px;
  object-fit: contain;
  flex-shrink: 0;
}

.logo-text-container {
  flex: 1;
}

.logo-text {
  font-size: 20px;
  font-weight: 700;
  color: #2D3748;
  margin: 0;
  line-height: 1.2;
}

.logo-subtitle {
  font-size: 12px;
  color: #6B7280;
  margin: 0;
  font-weight: 500;
}

.collapse-btn {
  background: none;
  border: none;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6B7280;
  display: flex;
  align-items: center;
  justify-content: center;
}

.collapse-btn:hover {
  background-color: #F1F5FF;
  color: #007BFF;
  transform: scale(1.05);
}

.collapse-btn:focus {
  outline: 2px solid #007BFF;
  outline-offset: 2px;
}

/* Navigation */
.sidebar-nav {
  flex: 1;
  padding: 16px 0;
}

.nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-item {
  margin: 0;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #2D3748;
  text-decoration: none;
  transition: all 0.2s ease;
  position: relative;
  font-weight: 500;
  font-size: 14px;
}

.nav-link:hover {
  background-color: #F1F5FF;
  color: #007BFF;
}

.nav-link.active {
  background-color: #EAF2FF;
  color: #007BFF;
  font-weight: 600;
  border-left: 3px solid #007BFF;
}

.nav-link.active .nav-icon {
  color: #007BFF;
}

.nav-icon {
  width: 20px;
  height: 20px;
  margin-right: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  transition: all 0.2s ease;
}

.nav-link:hover .nav-icon {
  color: #007BFF;
  transform: scale(1.05);
}

.nav-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Collapsed State */
.modern-sidebar.collapsed .logo-text,
.modern-sidebar.collapsed .logo-subtitle,
.modern-sidebar.collapsed .logo-text-container,
.modern-sidebar.collapsed .nav-label,
.modern-sidebar.collapsed .help-content {
  display: none;
}

.modern-sidebar.collapsed .logo-container {
  justify-content: center;
}

.modern-sidebar.collapsed .logo-image {
  width: 32px;
  height: 32px;
}

.modern-sidebar.collapsed .sidebar-header {
  justify-content: center;
  padding: 24px 16px;
}

.modern-sidebar.collapsed .nav-link {
  justify-content: center;
  padding: 12px 16px;
}

.modern-sidebar.collapsed .nav-icon {
  margin-right: 0;
}

.modern-sidebar.collapsed .help-section {
  justify-content: center;
  padding: 16px;
}

/* Sidebar Footer */
.sidebar-footer {
  padding: 20px;
  border-top: 1px solid #E5E7EB;
}

.help-section {
  display: flex;
  align-items: center;
  padding: 12px;
  background: #FFFFFF;
  border-radius: 8px;
  border: 1px solid #E5E7EB;
}

.help-icon {
  width: 32px;
  height: 32px;
  background: #F1F5FF;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: #007BFF;
}

.help-content h6 {
  font-size: 14px;
  font-weight: 600;
  color: #2D3748;
  margin: 0 0 2px 0;
}

.help-content p {
  font-size: 12px;
  color: #6B7280;
  margin: 0;
}

/* User Profile Section for Judger */
.user-profile-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px 16px;
  background: #ffffff;
  border-radius: 12px;
  gap: 12px;
  border: 1px solid #E5E7EB;
}

.user-profile-avatar {
  width: 120px;
  height: 120px;
  background: #F3F4F6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  overflow: hidden;
  flex-shrink: 0;
  border: 3px solid #3bb9df;
  box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.2);
}

.user-profile-avatar.has-image {
  background: transparent;
  border-color: #3bb9df;
}

.user-profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  display: block;
}

.user-profile-avatar svg {
  width: 40px;
  height: 40px;
  stroke: #6B7280;
}

.user-profile-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.user-welcome-text {
  font-size: 10px;
  font-weight: 500;
  color: #6B7280;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.user-profile-name {
  font-size: 18px;
  font-weight: 600;
  color: #2D3748;
  line-height: 1.3;
}

.user-profile-role {
  font-size: 12px;
  color: #6B7280;
  line-height: 1.2;
  margin-top: 2px;
}

.user-logout-btn {
  background: #F3F4F6;
  border: 1px solid #E5E7EB;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6B7280;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 8px;
}

.user-logout-btn:hover {
  background: #dc2626;
  border-color: #dc2626;
  color: #fff;
  transform: scale(1.05);
}

.user-logout-btn:focus {
  outline: 2px solid #007BFF;
  outline-offset: 2px;
}

.user-logout-btn svg {
  stroke: currentColor;
}

/* Collapsed state for user profile */
.modern-sidebar.collapsed .user-profile-section {
  padding: 16px 8px;
  gap: 8px;
}

.modern-sidebar.collapsed .user-profile-info {
  display: none;
}

.modern-sidebar.collapsed .user-profile-avatar {
  width: 56px;
  height: 56px;
}

.modern-sidebar.collapsed .user-logout-btn {
  padding: 8px;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .modern-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .modern-sidebar.open {
    transform: translateX(0);
  }
}

/* Tooltip for collapsed state */
.modern-sidebar.collapsed .nav-link {
  position: relative;
}

.modern-sidebar.collapsed .nav-link::after {
  content: attr(data-tooltip);
  position: absolute;
  left: 100%;
  top: 50%;
  transform: translateY(-50%);
  background: #2D3748;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease;
  z-index: 1001;
  margin-left: 8px;
}

.modern-sidebar.collapsed .nav-link:hover::after {
  opacity: 1;
  visibility: visible;
}

/* Floating Expand Button */
.floating-expand-btn {
  position: fixed;
  top: 20px;
  left: 20px;
  width: 40px;
  height: 40px;
  background: #007BFF;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  transition: all 0.3s ease;
  z-index: 1001;
}

.floating-expand-btn:hover {
  background: #0056B3;
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
}

.floating-expand-btn:active {
  transform: scale(0.95);
}

.floating-expand-btn:focus {
  outline: 2px solid #007BFF;
  outline-offset: 2px;
}

/* Animation for floating button */
.floating-expand-btn {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  }
  50% {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.5);
  }
  100% {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  }
}

/* Hide floating button on larger screens when sidebar is not collapsed */
@media (min-width: 1025px) {
  .floating-expand-btn {
    display: none;
  }
}
</style>
