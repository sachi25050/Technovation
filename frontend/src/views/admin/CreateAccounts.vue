<!-- 
	Create Accounts - Form to create new users
 -->

<template>
	<div>
		<!-- Page Header -->
		<div class="page-header">
			<h1>Create New Account</h1>
			<p>Add new users to the system</p>
		</div>

		<a-row :gutter="24">
			<a-col :span="24" :lg="16">
				<a-card title="User Information" class="mb-24">
					<a-form
						:form="form"
						@submit="handleSubmit"
						layout="vertical"
					>
						<!-- Row 1: Personal Information -->
						<a-row :gutter="16">
							<a-col :span="24" :md="6">
								<a-form-item label="Title">
								<a-select
									v-decorator="['title']"
									placeholder="Select title"
									:getPopupContainer="triggerNode => triggerNode.parentNode"
								>
									<a-select-option value="Mr">Mr</a-select-option>
									<a-select-option value="Mrs">Mrs</a-select-option>
									<a-select-option value="Ms">Ms</a-select-option>
								</a-select>
								</a-form-item>
							</a-col>
							<a-col :span="24" :md="9">
								<a-form-item label="First Name">
									<a-input
										v-decorator="['firstName']"
										placeholder="Enter first name"
									/>
								</a-form-item>
							</a-col>
							<a-col :span="24" :md="9">
								<a-form-item label="Last Name">
									<a-input
										v-decorator="['lastName']"
										placeholder="Enter last name"
									/>
								</a-form-item>
							</a-col>
						</a-row>

						<!-- Row 2: Account Credentials -->
						<a-row :gutter="16">
							<a-col :span="24" :md="12">
								<a-form-item label="Username">
									<a-input
										v-decorator="[
											'username',
											{
												rules: [{ required: true, message: 'Please input username!' }]
											}
										]"
										placeholder="Enter username"
									/>
								</a-form-item>
							</a-col>
							<a-col :span="24" :md="12">
								<a-form-item label="Email">
									<a-input
										v-decorator="[
											'email',
											{
												rules: [
													{ required: true, message: 'Please input email!' },
													{ type: 'email', message: 'Please enter a valid email!' }
												]
											}
										]"
										placeholder="Enter email address"
									/>
								</a-form-item>
							</a-col>
						</a-row>

						<!-- Row 3: Role & Security -->
						<a-row :gutter="16">
							<a-col :span="24" :md="12">
								<a-form-item label="Role">
								<a-select
									v-decorator="[
										'role',
										{
											rules: [{ required: true, message: 'Please select a role!' }]
										}
									]"
									placeholder="Select user role"
									:getPopupContainer="triggerNode => triggerNode.parentNode"
								>
									<a-select-option value="admin">Admin</a-select-option>
									<a-select-option value="judger">Judger</a-select-option>
									<a-select-option value="reporter">Reporter</a-select-option>
								</a-select>
								</a-form-item>
							</a-col>
							<a-col :span="24" :md="12">
								<a-form-item label="Password">
									<a-input-password
										v-decorator="[
											'password',
											{
												rules: [
													{ required: true, message: 'Please input password!' },
													{ min: 6, message: 'Password must be at least 6 characters!' }
												]
											}
										]"
										placeholder="Enter password (leave empty to keep current password)"
									/>
								</a-form-item>
							</a-col>
						</a-row>

						<a-form-item label="Upload Image">
							<a-tooltip title="Upload a profile photo (Max 2MB)">
								<a-upload
									v-decorator="[
										'profileImage',
										{
											rules: [{ required: false }]
										}
									]"
									name="file"
									:show-upload-list="false"
									:before-upload="beforeUpload"
									@change="handleImageChange"
									accept="image/jpeg,image/jpg,image/png"
									class="profile-image-uploader"
								>
									<div class="image-upload-box" :class="{ 'has-image': imageUrl, 'loading': uploadLoading }">
										<!-- Preview Image -->
										<div v-if="imageUrl" class="image-preview-wrapper">
											<img 
												:src="imageUrl" 
												alt="Profile preview" 
												class="preview-image"
												@error="handleImageError"
											/>
											<button 
												type="button" 
												class="remove-image-btn" 
												@click.stop="removeImage"
												title="Remove / Change Image"
											>
												<a-icon type="close-circle" />
												<span class="btn-text">Remove</span>
											</button>
										</div>
										<!-- Upload Placeholder -->
										<div v-else class="upload-placeholder">
											<a-icon :type="uploadLoading ? 'loading' : 'upload'" class="upload-icon" />
											<div class="upload-text">
												<div class="upload-title">Click to Upload</div>
												<div class="upload-hint">JPG, PNG or JPEG (Max 2MB)</div>
											</div>
										</div>
									</div>
								</a-upload>
							</a-tooltip>
							<div v-if="imageUrl" class="upload-hint-text">
								Image selected successfully.
							</div>
						</a-form-item>

						<a-form-item style="margin-bottom: 25px;">
							<a-button type="primary" html-type="submit" :loading="loading" size="large">
								{{ isEditMode ? 'Update Account' : 'Create Account' }}
							</a-button>
							<a-button style="margin-left: 8px;" @click="resetForm" size="large">
								Reset
							</a-button>
						</a-form-item>
					</a-form>
				</a-card>
			</a-col>

			<a-col :span="24" :lg="8">
				<a-card title="Account Statistics" class="mb-24">
					<a-row :gutter="16">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ accountStats.total }}</div>
								<div class="stat-label">Total Users</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ accountStats.admins }}</div>
								<div class="stat-label">Admins</div>
							</div>
						</a-col>
					</a-row>
					<a-row :gutter="16" style="margin-top: 16px;">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ accountStats.judgers }}</div>
								<div class="stat-label">Judgers</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ accountStats.reporters }}</div>
								<div class="stat-label">Reporters</div>
							</div>
						</a-col>
					</a-row>
				</a-card>
			</a-col>
		</a-row>

		<!-- User Management Table -->
		<a-card title="Manage Users" class="mb-24">
			<a-table
				:columns="tableColumns"
				:data-source="allAccounts"
				:loading="tableLoading"
				:pagination="pagination"
				@change="handleTableChange"
				:scroll="{ x: 1100 }"
				size="small"
				rowKey="id"
			>
				<template slot="image" slot-scope="text">
					<div class="user-image-tile" v-if="text">
						<img 
							:src="normalizeImageUrl(text)" 
							:alt="'User profile image'" 
							class="user-image"
							@error="handleTableImageError($event, text)"
							@load="handleTableImageLoad"
						/>
					</div>
					<span v-else class="no-image-text">No image</span>
				</template>
				<template slot="role" slot-scope="text">
					<a-tag :color="getRoleColor(text)">{{ text }}</a-tag>
				</template>
				<template slot="action" slot-scope="text, record">
					<a href="javascript:void(0);" @click="editUser(record)" class="action-link">
						<a-icon type="edit" /> Edit
					</a>
					<a-divider type="vertical" />
					<a href="javascript:void(0);" @click="showDeleteConfirm(record)" class="action-link danger">
						<a-icon type="delete" /> Delete
					</a>
				</template>
			</a-table>
		</a-card>
	</div>
</template>

<script>
	export default ({
		data() {
			return {
				form: this.$form.createForm(this),
				loading: false,
				tableLoading: false,
				allAccounts: [],
				isEditMode: false,
				editingUserId: null,
				currentPage: 1,
				pageSize: 10,
				totalUsers: 0,
				imageUrl: '',
				imageFile: null,
				uploadLoading: false,
				pagination: {
					current: 1,
					pageSize: 10,
					total: 0,
					showTotal: (total) => `Total ${total} users`,
					showSizeChanger: true,
					showQuickJumper: true,
					pageSizeOptions: ['10', '20', '50', '100']
				},
				accountStats: {
					total: 0,
					admins: 0,
					judgers: 0,
					reporters: 0
				},
				tableColumns: [
					
					{
						title: 'Username',
						dataIndex: 'username',
						key: 'username',
						width: 120
					},
					{
						title: 'Email',
						dataIndex: 'email',
						key: 'email',
						width: 180
					},
					{
						title: 'Full Name',
						dataIndex: 'fullName',
						key: 'fullName',
						width: 150,
						render: (text, record) => `${record.first_name || ''} ${record.last_name || ''}`.trim()
					},
					{
						title: 'Role',
						dataIndex: 'role',
						key: 'role',
						width: 100,
						scopedSlots: { customRender: 'role' }
					},
					{
						title: 'Image',
						dataIndex: 'profile_image',
						key: 'image',
						width: 100,
						scopedSlots: { customRender: 'image' }
					},


					{
						title: 'Action',
						key: 'action',
						width: 120,
						scopedSlots: { customRender: 'action' }
					}
				]
			}
		},
		beforeDestroy() {
			// Clean up object URL when component is destroyed
			if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
				URL.revokeObjectURL(this.imageUrl);
			}
		},
		methods: {
			beforeUpload(file) {
				// Validate file type
				const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
				const isValidType = allowedTypes.includes(file.type.toLowerCase());
				
				if (!isValidType) {
					this.$message.error('Only JPG, PNG, or JPEG formats are allowed.', 4);
					return false;
				}
				
				// Validate file size (2MB = 2 * 1024 * 1024 bytes)
				const maxSize = 2 * 1024 * 1024; // 2MB in bytes
				if (file.size > maxSize) {
					this.$message.warning('File size exceeds the maximum allowed limit (2MB).', 4);
					return false;
				}
				
				// Store file immediately for later use
				this.imageFile = file;
				
				// Generate preview immediately using Object URL
				this.uploadLoading = true;
				
				// Clean up previous object URL if exists
				if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
					URL.revokeObjectURL(this.imageUrl);
				}
				
				// Create object URL for immediate preview
				this.imageUrl = URL.createObjectURL(file);
				this.uploadLoading = false;
				
				// Show success notification
				this.$nextTick(() => {
					this.$message.success('Image selected successfully.', 3);
				});
				
				// Return false to prevent auto upload, we'll handle it in form submission
				return false;
			},
			handleImageChange(info) {
				// Handle file removal
				if (info.file.status === 'removed' || (info.fileList && info.fileList.length === 0)) {
					this.imageFile = null;
					this.imageUrl = '';
					this.uploadLoading = false;
					return;
				}
			},
			removeImage() {
				// Clean up object URL if it exists
				if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
					URL.revokeObjectURL(this.imageUrl);
				}
				this.imageFile = null;
				this.imageUrl = '';
				this.form.setFieldsValue({ profileImage: null });
				this.$message.info('Image removed. You can upload a new image.', 3);
			},
			handleImageError(event) {
				console.error('Image failed to load:', this.imageUrl);
				this.$message.warning('Image could not be loaded.', 4);
			},
			handleTableImageError(event, imageUrl) {
				// Handle image loading errors in table
				console.error('Table image failed to load:', imageUrl);
				
				// Try to fix the URL if it's malformed
				if (imageUrl && !imageUrl.includes('/api/uploads')) {
					// Try converting to API endpoint
					const normalizedUrl = this.normalizeImageUrl(imageUrl);
					if (normalizedUrl && normalizedUrl !== imageUrl) {
						// Update the src to try the normalized URL
						event.target.src = normalizedUrl;
						return;
					}
				}
				
				// If still fails, show default user icon
				event.target.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23e5e7eb' width='100' height='100'/%3E%3Ccircle cx='50' cy='35' r='20' fill='%239ca3af'/%3E%3Cpath d='M20 85c0-22 13-30 30-30s30 8 30 30' fill='%239ca3af'/%3E%3C/svg%3E";
				event.target.onerror = null; // Prevent infinite loop
			},
			handleTableImageLoad(event) {
				// Image loaded successfully in table
				event.target.style.display = 'block';
			},
			async handleSubmit(e) {
				e.preventDefault();
				this.form.validateFields(async (err, values) => {
					if (!err) {
						this.loading = true;
						try {
							// Transform form values to match API expectations
							const userData = {
								username: values.username,
								email: values.email,
								title: values.title || '',
								first_name: values.firstName || '',
								last_name: values.lastName || '',
								role: values.role
							};

							// Only include password if it's provided
							if (values.password) {
								userData.password = values.password;
							}

							if (this.isEditMode && this.editingUserId) {
								// Update existing user
								await this.$api.updateUser(this.editingUserId, userData, this.imageFile);
								this.$message.success('Account updated successfully!');
							} else {
								// Create new user
								if (!values.password) {
									this.$message.error('Password is required for new accounts');
									this.loading = false;
									return;
								}
								userData.password = values.password;
								await this.$api.createUser(userData, this.imageFile);
								this.$message.success('Account created successfully!');
							}
							
							// Reset form and mode
							this.resetForm();
							
							// Refresh users list and stats
							this.loadAllAccounts();
							this.loadAccountStats();
						} catch (error) {
							this.$message.error(error.message || 'Failed to save account');
						} finally {
							this.loading = false;
						}
					}
				});
			},

			resetForm() {
				// Clean up object URL if it exists
				if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
					URL.revokeObjectURL(this.imageUrl);
				}
				this.form.resetFields();
				this.isEditMode = false;
				this.editingUserId = null;
				this.imageUrl = '';
				this.imageFile = null;
			},

			getRoleColor(role) {
				const colors = {
					admin: 'red',
					judger: 'blue',
					reporter: 'green'
				};
				return colors[role] || 'default';
			},

		normalizeImageUrl(imageUrl) {
			if (!imageUrl) return null;
			
			// If already using /api/uploads/ format, return as is
			if (imageUrl.includes('/api/uploads/')) {
				return imageUrl;
			}
			
			// Convert to API endpoint URL if it's a backend/uploads path
			if (imageUrl.includes('/backend/uploads/')) {
				// Extract the relative path (e.g., users/filename.jpg)
				const pathMatch = imageUrl.match(/\/backend\/uploads\/(.+)$/);
				if (pathMatch && pathMatch[1]) {
					// Use the API uploads endpoint with path segments
					const apiBaseUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
					return `${apiBaseUrl}/uploads/${pathMatch[1]}`;
				}
			}
			
			// Fix URL if it's missing the port (backend runs on port 8000)
			if (imageUrl.includes('localhost/') && !imageUrl.includes('localhost:')) {
				imageUrl = imageUrl.replace('http://localhost/', 'http://localhost:8000/');
				imageUrl = imageUrl.replace('https://localhost/', 'https://localhost:8000/');
			}
			
			// Ensure the URL is absolute
			if (!imageUrl.startsWith('http://') && !imageUrl.startsWith('https://') && !imageUrl.startsWith('blob:')) {
				// If it's a relative URL, make it absolute
				if (imageUrl.startsWith('//')) {
					imageUrl = window.location.protocol + imageUrl;
				} else if (imageUrl.startsWith('/')) {
					// Absolute path from root - use localhost:8000 as backend base
					imageUrl = 'http://localhost:8000' + imageUrl;
				} else {
					// Relative path
					imageUrl = 'http://localhost:8000/' + imageUrl;
				}
			}
			
			return imageUrl;
		},
		async loadAllAccounts() {
			this.tableLoading = true;
			try {
				const response = await this.$api.getUsers({ 
					limit: this.pageSize,
					page: this.currentPage
				});
				const users = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
				// Ensure each user has a fullName property for the table display and normalize image URLs
				this.allAccounts = Array.isArray(users) ? users.map(u => ({
					...u,
					fullName: `${u.first_name || ''} ${u.last_name || ''}`.trim()
				})) : [];
				
				// Update pagination total
				if (response.data && response.data.pagination) {
					this.totalUsers = response.data.pagination.total || this.allAccounts.length;
				} else {
					this.totalUsers = this.allAccounts.length;
				}
				
				// Update pagination object
				this.pagination = {
					...this.pagination,
					current: this.currentPage,
					pageSize: this.pageSize,
					total: this.totalUsers
				};
			} catch (error) {
				console.error('Failed to load accounts:', error);
				this.$message.error('Failed to load user accounts');
			} finally {
				this.tableLoading = false;
			}
		},

		handleTableChange(pagination, filters, sorter) {
			this.currentPage = pagination.current;
			this.pageSize = pagination.pageSize;
			this.loadAllAccounts();
		},

		async loadAccountStats() {
			try {
				const response = await this.$api.getUsers({ limit: 10000 });
				const users = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
				
				if (!Array.isArray(users)) {
					console.error('Users data is not an array:', users);
					return;
				}
				
				this.accountStats = {
					total: users.length,
					admins: users.filter(user => user.role === 'admin').length,
					judgers: users.filter(user => user.role === 'judger').length,
					reporters: users.filter(user => user.role === 'reporter').length
				};
			} catch (error) {
				console.error('Failed to load account stats:', error);
			}
		},

		editUser(user) {
			this.isEditMode = true;
			this.editingUserId = user.id;
			
			// Reset imageFile to null when entering edit mode
			// This ensures we don't accidentally send an old file from a previous operation
			this.imageFile = null;
			
			// Populate form with user data
			this.$nextTick(() => {
				this.form.setFieldsValue({
					username: user.username,
					email: user.email,
					title: user.title || '',
					firstName: user.first_name || '',
					lastName: user.last_name || '',
					role: user.role,
					password: '' // Leave password empty, user can set it if needed
				});
				
				// Load existing image if any
				if (user.profile_image) {
					const apiBaseUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
					if (user.profile_image.includes('/backend/uploads/')) {
						const pathMatch = user.profile_image.match(/\/backend\/uploads\/(.+)$/);
						if (pathMatch && pathMatch[1]) {
							this.imageUrl = `${apiBaseUrl}/uploads/${pathMatch[1]}`;
						} else {
							this.imageUrl = user.profile_image;
						}
					} else {
						this.imageUrl = user.profile_image;
					}
				} else {
					this.imageUrl = '';
				}
			});

				// Scroll to form
				window.scrollTo({ top: 0, behavior: 'smooth' });
			},

			showDeleteConfirm(user) {
				const self = this;
				this.$confirm({
					title: 'Confirm Delete',
					content: `Are you sure you want to delete the user "${user.username}"? This action cannot be undone.`,
					okText: 'Yes, Delete',
					okType: 'danger',
					cancelText: 'Cancel',
					onOk() {
						return self.deleteUser(user.id);
					}
				});
			},

			async deleteUser(userId) {
				try {
					await this.$api.deleteUser(userId);
					this.$message.success('User deleted successfully!');
					// Refresh the user list and stats
					this.loadAllAccounts();
					this.loadAccountStats();
				} catch (error) {
					this.$message.error(error.message || 'Failed to delete user');
				}
			}
		},

		created() {
			// Load initial data
			this.loadAllAccounts();
			this.loadAccountStats();
		}
	})

</script>

<style lang="scss">
.page-header {
	margin-bottom: 24px;
	
	h1 {
		margin: 0;
		font-size: 24px;
		font-weight: 600;
		color: #1f2937;
	}
	
	p {
		margin: 4px 0 0 0;
		color: #6b7280;
		font-size: 14px;
	}
}

.stat-item {
	text-align: center;
	padding: 16px 0;
	
	.stat-value {
		font-size: 24px;
		font-weight: 600;
		color: #1f2937;
		margin-bottom: 4px;
	}
	
	.stat-label {
		font-size: 12px;
		color: #6b7280;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}
}

.action-link {
	color: #1890ff;
	transition: color 0.3s;
	
	&:hover {
		color: #40a9ff;
	}
	
	&.danger {
		color: #ff4d4f;
		
		&:hover {
			color: #ff7875;
		}
	}
}

// Profile Image Uploader Styles
.profile-image-uploader {
	::v-deep .ant-upload {
		width: 100%;
		display: block;
	}
	
	::v-deep .ant-upload-select {
		width: 100%;
		display: block;
		border: none;
		background: transparent;
	}
}

.image-upload-box {
	width: 100%;
	max-width: 200px;
	height: 200px;
	border: 2px dashed #D9D9D9;
	border-radius: 8px;
	background-color: #FAFAFA;
	position: relative;
	overflow: hidden;
	transition: all 0.3s ease;
	cursor: pointer;
	
	&:hover {
		border-color: #1890FF;
		background-color: #F0F7FF;
		box-shadow: 0 2px 8px rgba(24, 144, 255, 0.1);
	}
	
	&.has-image {
		border-color: #52C41A;
		background-color: #FFFFFF;
		cursor: default;
		
		&:hover {
			border-color: #52C41A;
			box-shadow: 0 2px 8px rgba(82, 196, 26, 0.15);
		}
	}
	
	&.loading {
		pointer-events: none;
		opacity: 0.7;
	}
}

.image-preview-wrapper {
	width: 100%;
	height: 100%;
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: #FFFFFF;
	padding: 8px;
	box-sizing: border-box;
	
	.preview-image {
		max-width: 100%;
		max-height: 100%;
		width: auto;
		height: auto;
		object-fit: contain;
		display: block;
		border-radius: 4px;
	}
	
	.remove-image-btn {
		position: absolute;
		top: 8px;
		right: 8px;
		background-color: rgba(255, 255, 255, 0.95);
		border: 1px solid #D9D9D9;
		border-radius: 4px;
		padding: 6px 12px;
		display: flex;
		align-items: center;
		gap: 6px;
		cursor: pointer;
		transition: all 0.2s ease;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		z-index: 10;
		font-size: 12px;
		color: #595959;
		font-weight: 600;
		
		&:hover {
			background-color: #FFFFFF;
			border-color: #F5222D;
			color: #F5222D;
			box-shadow: 0 2px 8px rgba(245, 34, 45, 0.2);
			transform: translateY(-1px);
		}
		
		&:active {
			transform: translateY(0);
		}
		
		.anticon {
			font-size: 14px;
		}
		
		.btn-text {
			font-size: 12px;
			line-height: 1;
		}
	}
}

.upload-placeholder {
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 20px;
	box-sizing: border-box;
	
	.upload-icon {
		font-size: 48px;
		color: #8C8C8C;
		margin-bottom: 16px;
		transition: all 0.3s ease;
	}
	
	.upload-text {
		text-align: center;
		
		.upload-title {
			font-size: 14px;
			font-weight: 600;
			color: #595959;
			margin-bottom: 4px;
		}
		
		.upload-hint {
			font-size: 12px;
			color: #8C8C8C;
		}
	}
}

.image-upload-box:hover .upload-placeholder .upload-icon {
	color: #1890FF;
	transform: translateY(-2px);
}

.upload-hint-text {
	margin-top: 8px;
	font-size: 12px;
	color: #52C41A;
	font-weight: 500;
}

// User Image Tile Styles for Table
.user-image-tile {
	width: 50px;
	height: 50px;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 4px;
	background-color: #ffffff;
	box-sizing: border-box;
}

.user-image {
	max-width: 100%;
	max-height: 100%;
	width: auto;
	height: auto;
	object-fit: cover;
	display: block;
	border-radius: 50%;
}

.no-image-text {
	color: #8c8c8c;
	font-size: 12px;
}
</style>
