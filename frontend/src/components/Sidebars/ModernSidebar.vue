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
            <router-link to="/admin" class="nav-link" active-class="active">
              <div class="nav-icon">
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
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 6C9 7.65685 7.65685 9 6 9C4.34315 9 3 7.65685 3 6C3 4.34315 4.34315 3 6 3C7.65685 3 9 4.34315 9 6Z" fill="currentColor"/>
                  <path d="M17 6C17 7.65685 15.6569 9 14 9C12.3431 9 11 7.65685 11 6C11 4.34315 12.3431 3 14 3C15.6569 3 17 4.34315 17 6Z" fill="currentColor"/>
                  <path d="M12.9291 17C12.9758 16.6734 13 16.3395 13 16C13 14.3648 12.4393 12.8606 11.4998 11.6691C12.2352 11.2435 13.0892 11 14 11C16.7614 11 19 13.2386 19 16V17H12.9291Z" fill="currentColor"/>
                  <path d="M6 11C8.76142 11 11 13.2386 11 16V17H1V16C1 13.2386 3.23858 11 6 11Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Create Accounts</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/admin/add-institution" class="nav-link" active-class="active">
              <div class="nav-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C4.73367 3.60948 7.26633 3.60948 8.82843 5.17157L10 6.34315L11.1716 5.17157C12.7337 3.60948 15.2663 3.60948 16.8284 5.17157C18.3905 6.73367 18.3905 9.26633 16.8284 10.8284L10 17.6569L3.17157 10.8284C1.60948 9.26633 1.60948 6.73367 3.17157 5.17157Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Add Institution</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/admin/add-awards" class="nav-link" active-class="active">
              <div class="nav-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8.43338 7.41784C8.58818 7.31464 8.77939 7.2224 9 7.15101L9.00001 8.84899C8.77939 8.7776 8.58818 8.68536 8.43338 8.58216C8.06927 8.33942 8 8.1139 8 8C8 7.8861 8.06927 7.66058 8.43338 7.41784Z" fill="currentColor"/>
                  <path d="M11 12.849L11 11.151C11.2206 11.2224 11.4118 11.3146 11.5666 11.4178C11.9308 11.6606 12 11.8861 12 12C12 12.1139 11.9308 12.3394 11.5666 12.5822C11.4118 12.6854 11.2206 12.7776 11 12.849Z" fill="currentColor"/>
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18ZM11 5C11 4.44772 10.5523 4 10 4C9.44772 4 9 4.44772 9 5V5.09199C8.3784 5.20873 7.80348 5.43407 7.32398 5.75374C6.6023 6.23485 6 7.00933 6 8C6 8.99067 6.6023 9.76515 7.32398 10.2463C7.80348 10.5659 8.37841 10.7913 9.00001 10.908L9.00002 12.8492C8.60902 12.7223 8.31917 12.5319 8.15667 12.3446C7.79471 11.9275 7.16313 11.8827 6.74599 12.2447C6.32885 12.6067 6.28411 13.2382 6.64607 13.6554C7.20855 14.3036 8.05956 14.7308 9 14.9076L9 15C8.99999 15.5523 9.44769 16 9.99998 16C10.5523 16 11 15.5523 11 15L11 14.908C11.6216 14.7913 12.1965 14.5659 12.676 14.2463C13.3977 13.7651 14 12.9907 14 12C14 11.0093 13.3977 10.2348 12.676 9.75373C12.1965 9.43407 11.6216 9.20873 11 9.09199L11 7.15075C11.391 7.27771 11.6808 7.4681 11.8434 7.65538C12.2053 8.07252 12.8369 8.11726 13.254 7.7553C13.6712 7.39335 13.7159 6.76176 13.354 6.34462C12.7915 5.69637 11.9405 5.26915 11 5.09236V5Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Add Awards</span>
            </router-link>
          </li>
        </template>

        <!-- Judger Navigation -->
        <template v-if="isJudger">
          <li class="nav-item">
            <router-link to="/judger" class="nav-link" active-class="active">
              <div class="nav-icon">
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
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 2C8.44772 2 8 2.44772 8 3C8 3.55228 8.44772 4 9 4H11C11.5523 4 12 3.55228 12 3C12 2.44772 11.5523 2 11 2H9Z" fill="currentColor"/>
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M4 5C4 3.89543 4.89543 3 6 3C6 4.65685 7.34315 6 9 6H11C12.6569 6 14 4.65685 14 3C15.1046 3 16 3.89543 16 5V16C16 17.1046 15.1046 18 14 18H6C4.89543 18 4 17.1046 4 16V5ZM7 9C6.44772 9 6 9.44772 6 10C6 10.5523 6.44772 11 7 11H7.01C7.56228 11 8.01 10.5523 8.01 10C8.01 9.44772 7.56228 9 7.01 9H7ZM10 9C9.44772 9 9 9.44772 9 10C9 10.5523 9.44772 11 10 11H13C13.5523 11 14 10.5523 14 10C14 9.44772 13.5523 9 13 9H10ZM7 13C6.44772 13 6 13.4477 6 14C6 14.5523 6.44772 15 7 15H7.01C7.56228 15 8.01 14.5523 8.01 14C8.01 13.4477 7.56228 13 7.01 13H7ZM10 13C9.44772 13 9 13.4477 9 14C9 14.5523 9.44772 15 10 15H13C13.5523 15 14 14.5523 14 14C14 13.4477 13.5523 13 13 13H10Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Evaluate Awards</span>
            </router-link>
          </li>
          
          <li class="nav-item">
            <router-link to="/judger/submit-evaluations" class="nav-link" active-class="active">
              <div class="nav-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10 2L12.5 7.5L18 8L14 12L15 18L10 15L5 18L6 12L2 8L7.5 7.5L10 2Z" fill="currentColor"/>
                </svg>
              </div>
              <span class="nav-label">Submission History</span>
            </router-link>
          </li>
        </template>

        <!-- Reporter Navigation -->
        <template v-if="isReporter">
          <li class="nav-item">
            <router-link to="/reporter" class="nav-link" active-class="active">
              <div class="nav-icon">
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
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 10C2 5.58172 5.58172 2 10 2V10H18C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10Z" fill="currentColor"/>
                  <path d="M12 2.25195C14.8113 2.97552 17.0245 5.18877 17.748 8.00004H12V2.25195Z" fill="currentColor"/>
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
      <div class="help-section">
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

export default {
  name: 'ModernSidebar',
  props: {
    collapsed: {
      type: Boolean,
      default: false
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
    }
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
