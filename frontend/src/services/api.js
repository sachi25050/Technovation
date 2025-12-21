/**
 * API Service
 * Handles all HTTP requests to the backend API
 */

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';

class ApiService {
  /**
   * Make HTTP request
   */
  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json',
      },
    };

    // Add authorization token if available
    const token = localStorage.getItem('auth_token');
    if (token) {
      defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }

    const config = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...(options.headers || {}),
      },
    };

    try {
      const response = await fetch(url, config);
      
      // Read response as text first (we can only read the body once)
      const text = await response.text();
      let data;
      
      // Try to parse as JSON
      try {
        data = JSON.parse(text);
      } catch (e) {
        // If parsing fails, it's likely HTML or plain text error
        // Check if it's an error response
        if (!response.ok) {
          const error = new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
          error.status = response.status;
          throw error;
        }
        // If response is ok but not JSON, that's unexpected
        throw new Error(`Server returned non-JSON response: ${text.substring(0, 200)}`);
      }

      if (!response.ok) {
        // Handle error responses
        const error = new Error(data.message || data.error || 'An error occurred');
        error.status = response.status;
        error.data = data;
        throw error;
      }

      return data;
    } catch (error) {
      // Handle network errors
      if (error.name === 'TypeError' && error.message.includes('fetch')) {
        throw new Error('Network error. Please check your connection.');
      }
      // Re-throw if it's already our custom error
      if (error.message && error.status) {
        throw error;
      }
      // Otherwise wrap it
      throw new Error(error.message || 'An error occurred');
    }
  }

  /**
   * GET request
   */
  async get(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;
    return this.request(url, { method: 'GET' });
  }

  /**
   * POST request
   */
  async post(endpoint, data = {}) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  /**
   * PUT request
   */
  async put(endpoint, data = {}) {
    return this.request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  /**
   * DELETE request
   */
  async delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' });
  }

  /**
   * Login
   */
  async login(username, password) {
    const response = await this.post('/auth/login', { username, password });
    if (response.success && response.data) {
      // Store token and user data
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      return response.data;
    }
    throw new Error(response.message || 'Login failed');
  }

  /**
   * Logout
   */
  async logout() {
    try {
      await this.post('/auth/logout');
    } catch (error) {
      // Continue with logout even if API call fails
      console.error('Logout API error:', error);
    } finally {
      // Clear local storage
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
    }
  }

  /**
   * Get current user from localStorage
   */
  getCurrentUser() {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return !!localStorage.getItem('auth_token');
  }

  /**
   * Create a new user account
   * @param {Object} userData - User data (username, email, password, first_name, last_name, role)
   * @param {File} imageFile - Optional profile image file
   * @returns {Promise} - API response
   */
  async createUser(userData, imageFile = null) {
    // If image file is provided, use FormData
    if (imageFile) {
      const formData = new FormData();
      
      // Add all user data fields
      Object.keys(userData).forEach(key => {
        if (userData[key] !== null && userData[key] !== undefined) {
          formData.append(key, userData[key]);
        }
      });
      
      // Add image file
      formData.append('profileImage', imageFile);
      
      // Make request with FormData
      const url = `${API_BASE_URL}/admin/accounts`;
      const token = localStorage.getItem('auth_token');
      const headers = {};
      
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }
      // Don't set Content-Type for FormData, browser will set it with boundary
      
      const response = await fetch(url, {
        method: 'POST',
        headers: headers,
        body: formData,
      });
      
      // Read response as text first (we can only read the body once)
      const text = await response.text();
      let data;
      
      // Try to parse as JSON
      try {
        data = JSON.parse(text);
      } catch (e) {
        // If parsing fails, it's likely HTML or plain text error
        if (!response.ok) {
          const error = new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
          error.status = response.status;
          throw error;
        }
        throw new Error(`Server returned non-JSON response: ${text.substring(0, 200)}`);
      }
      
      if (!response.ok) {
        const error = new Error(data.message || data.error || 'An error occurred');
        error.status = response.status;
        error.data = data;
        throw error;
      }
      
      return data;
    } else {
      // No image, use regular JSON POST
      return this.post('/admin/accounts', userData);
    }
  }

  /**
   * Update a user account
   * @param {number} userId - User ID
   * @param {Object} userData - User data to update
   * @param {File} imageFile - Optional profile image file
   * @returns {Promise} - API response
   */
  async updateUser(userId, userData, imageFile = null) {
    // If image file is provided, use FormData
    if (imageFile) {
      const formData = new FormData();
      
      // Add all user data fields
      Object.keys(userData).forEach(key => {
        if (userData[key] !== null && userData[key] !== undefined) {
          formData.append(key, userData[key]);
        }
      });
      
      // Add image file
      formData.append('profileImage', imageFile);
      
      // Make request with FormData
      const url = `${API_BASE_URL}/admin/accounts/${userId}`;
      const token = localStorage.getItem('auth_token');
      const headers = {};
      
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }
      // Don't set Content-Type for FormData, browser will set it with boundary
      
      const response = await fetch(url, {
        method: 'PUT',
        headers: headers,
        body: formData
      });
      
      const text = await response.text();
      let data;
      
      try {
        data = JSON.parse(text);
      } catch (e) {
        if (!response.ok) {
          const error = new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
          error.status = response.status;
          throw error;
        }
        throw new Error(`Server returned non-JSON response: ${text.substring(0, 200)}`);
      }
      
      if (!response.ok) {
        throw new Error(data.message || 'Failed to update user');
      }
      
      return data;
    } else {
      // No image file, send as JSON
      return this.put(`/admin/accounts/${userId}`, userData);
    }
  }

  /**
   * Delete a user account
   * @param {number} userId - User ID
   * @returns {Promise} - API response
   */
  async deleteUser(userId) {
    return this.delete(`/admin/accounts/${userId}`);
  }

  /**
   * Get all user accounts with pagination
   * @param {Object} params - Query parameters (page, limit, role)
   * @returns {Promise} - API response
   */
  async getUsers(params = {}) {
    return this.get('/admin/accounts', params);
  }

  /**
   * Get a single user account
   * @param {number} userId - User ID
   * @returns {Promise} - API response
   */
  async getUser(userId) {
    return this.get(`/admin/accounts/${userId}`);
  }

  /**
   * Create a new institution
   * @param {Object} institutionData - Institution data (name, address, contact_number, email, etc.)
   * @param {File} imageFile - Optional image file
   * @returns {Promise} - API response
   */
  async createInstitution(institutionData, imageFile = null) {
    // If image file is provided, use FormData
    if (imageFile) {
      const formData = new FormData();
      
      // Add all institution data fields
      Object.keys(institutionData).forEach(key => {
        if (key === 'awardCategories' && Array.isArray(institutionData[key])) {
          // Send award categories as JSON string for easier parsing on backend
          formData.append('awardCategories', JSON.stringify(institutionData[key]));
        } else if (institutionData[key] !== null && institutionData[key] !== undefined) {
          formData.append(key, institutionData[key]);
        }
      });
      
      // Add image file
      formData.append('instituteImage', imageFile);
      
      // Make request with FormData
      const url = `${API_BASE_URL}/admin/institutions`;
      const token = localStorage.getItem('auth_token');
      const headers = {};
      
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }
      // Don't set Content-Type for FormData, browser will set it with boundary
      
      const response = await fetch(url, {
        method: 'POST',
        headers: headers,
        body: formData,
      });
      
      // Read response as text first (we can only read the body once)
      const text = await response.text();
      let data;
      
      // Try to parse as JSON
      try {
        data = JSON.parse(text);
      } catch (e) {
        // If parsing fails, it's likely HTML or plain text error
        // Check if it's an error response
        if (!response.ok) {
          const error = new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
          error.status = response.status;
          throw error;
        }
        // If response is ok but not JSON, that's unexpected
        throw new Error(`Server returned non-JSON response: ${text.substring(0, 200)}`);
      }
      
      if (!response.ok) {
        const error = new Error(data.message || data.error || 'An error occurred');
        error.status = response.status;
        error.data = data;
        throw error;
      }
      
      return data;
    } else {
      // No image, use regular JSON POST
      return this.post('/admin/institutions', institutionData);
    }
  }

  /**
   * Get all institutions
   * @param {Object} params - Query parameters (page, limit)
   * @returns {Promise} - API response
   */
  async getInstitutions(params = {}) {
    return this.get('/admin/institutions', params);
  }

  /**
   * Get a single institution
   * @param {number} institutionId - Institution ID
   * @returns {Promise} - API response
   */
  async getInstitution(institutionId) {
    return this.get(`/admin/institutions/${institutionId}`);
  }

  /**
   * Update an institution
   * @param {number} institutionId - Institution ID
   * @param {Object} institutionData - Institution data to update
   * @param {File} imageFile - Optional image file to update
   * @returns {Promise} - API response
   */
  async updateInstitution(institutionId, institutionData, imageFile = null) {
    // If image file is provided, use FormData (similar to create)
    if (imageFile) {
      const formData = new FormData();
      
      // Add _method field to indicate this is a PUT request (needed because PHP doesn't populate $_FILES for PUT)
      formData.append('_method', 'PUT');
      
      // Add all institution data fields
      Object.keys(institutionData).forEach(key => {
        if (key === 'awardCategories' && Array.isArray(institutionData[key])) {
          // Send award categories as JSON string for easier parsing on backend
          formData.append('awardCategories', JSON.stringify(institutionData[key]));
        } else if (institutionData[key] !== null && institutionData[key] !== undefined) {
          formData.append(key, institutionData[key]);
        }
      });
      
      // Add image file
      formData.append('instituteImage', imageFile);
      
      // Make request with FormData - use POST because PHP doesn't populate $_FILES for PUT requests
      const url = `${API_BASE_URL}/admin/institutions/${institutionId}`;
      const token = localStorage.getItem('auth_token');
      const headers = {};
      
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }
      // Don't set Content-Type for FormData, browser will set it with boundary
      
      const response = await fetch(url, {
        method: 'POST',
        headers: headers,
        body: formData
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Failed to update institution');
      }
      
      return data;
    } else {
      // No image file, send as JSON
      const dataToSend = { ...institutionData };
      
      if (dataToSend.awardCategories && Array.isArray(dataToSend.awardCategories)) {
        dataToSend.awardCategories = JSON.stringify(dataToSend.awardCategories);
      }
      
      return this.put(`/admin/institutions/${institutionId}`, dataToSend);
    }
  }

  /**
   * Delete an institution
   * @param {number} institutionId - Institution ID
   * @returns {Promise} - API response
   */
  async deleteInstitution(institutionId) {
    return this.delete(`/admin/institutions/${institutionId}`);
  }

  /**
   * Create a new award
   * @param {Object} awardData - Award data (awardCategory, awardDescription, presentationWeightage, preliminaryWeightage)
   * @returns {Promise} - API response
   */
  async createAward(awardData) {
    return this.post('/admin/awards', awardData);
  }

  /**
   * Get all awards
   * @param {Object} params - Query parameters (page, limit)
   * @returns {Promise} - API response
   */
  async getAwards(params = {}) {
    return this.get('/admin/awards', params);
  }

  /**
   * Get a single award
   * @param {number} awardId - Award ID
   * @returns {Promise} - API response
   */
  async getAward(awardId) {
    return this.get(`/admin/awards/${awardId}`);
  }

  /**
   * Update an award
   * @param {number} awardId - Award ID
   * @param {Object} awardData - Award data to update
   * @returns {Promise} - API response
   */
  async updateAward(awardId, awardData) {
    return this.put(`/admin/awards/${awardId}`, awardData);
  }

  /**
   * Delete an award
   * @param {number} awardId - Award ID
   * @returns {Promise} - API response
   */
  async deleteAward(awardId) {
    return this.delete(`/admin/awards/${awardId}`);
  }

  /**
   * Get admin dashboard data with statistics and institution performance
   * @returns {Promise} - API response with stats, activities, and institution performance
   */
  async getDashboard() {
    return this.get('/admin/dashboard');
  }

  // ============================================
  // Reporter API Methods
  // ============================================

  /**
   * Generate a report
   * @param {Object} reportData - Report configuration (reportType, reportFormat, title, filters)
   * @returns {Promise} - API response with generated report info
   */
  async generateReport(reportData) {
    return this.post('/reporter/reports', reportData);
  }

  /**
   * Get all reports for the current reporter
   * @param {Object} params - Query parameters (page, limit)
   * @returns {Promise} - API response with reports list
   */
  async getReports(params = {}) {
    return this.get('/reporter/reports', params);
  }

  /**
   * Get a single report
   * @param {number} reportId - Report ID
   * @returns {Promise} - API response with report details
   */
  async getReport(reportId) {
    return this.get(`/reporter/reports/${reportId}`);
  }

  /**
   * Download a report file
   * @param {number} reportId - Report ID
   * @returns {Promise<Blob>} - File blob for download
   */
  async downloadReport(reportId) {
    const url = `${API_BASE_URL}/reporter/reports/${reportId}/download`;
    const token = localStorage.getItem('auth_token');
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    if (!response.ok) {
      throw new Error('Failed to download report');
    }
    
    return response.blob();
  }

  /**
   * Get reporter dashboard data
   * @returns {Promise} - API response with reporter statistics
   */
  async getReporterData() {
    return this.get('/reporter/data');
  }
}

export default new ApiService();

