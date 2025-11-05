// User role management store
import Vue from 'vue';

// Simple store to manage user role based on URL
const UserRoleStore = {
  state: {
    role: 'admin', // Default to admin, will be set based on URL
  },
  
  setRole(role) {
    if (role === 'admin' || role === 'judger' || role === 'reporter') {
      this.state.role = role;
    }
  },
  
  setRoleFromPath(path) {
    if (path.startsWith('/admin')) {
      this.setRole('admin');
    } else if (path.startsWith('/judger')) {
      this.setRole('judger');
    } else if (path.startsWith('/reporter')) {
      this.setRole('reporter');
    }
  },
  
  getRole() {
    return this.state.role;
  },
  
  get role() {
    return this.state.role;
  },
  
  isAdmin() {
    return this.state.role === 'admin';
  },
  
  isJudger() {
    return this.state.role === 'judger';
  },
  
  isReporter() {
    return this.state.role === 'reporter';
  }
};

// Make it reactive
Vue.observable(UserRoleStore.state);

export default UserRoleStore;