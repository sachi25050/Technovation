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
						<a-row :gutter="16">
							<a-col :span="12">
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
							<a-col :span="12">
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

						<a-row :gutter="16">
							<a-col :span="12">
								<a-form-item label="Role">
									<a-select
										v-decorator="[
											'role',
											{
												rules: [{ required: true, message: 'Please select a role!' }]
											}
										]"
										placeholder="Select user role"
									>
										<a-select-option value="admin">Admin</a-select-option>
										<a-select-option value="judger">Judger</a-select-option>
										<a-select-option value="reporter">Reporter</a-select-option>
									</a-select>
								</a-form-item>
							</a-col>
							<a-col :span="12">
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

						<a-row :gutter="16">
							<a-col :span="12">
								<a-form-item label="First Name">
									<a-input
										v-decorator="['firstName']"
										placeholder="Enter first name"
									/>
								</a-form-item>
							</a-col>
							<a-col :span="12">
								<a-form-item label="Last Name">
									<a-input
										v-decorator="['lastName']"
										placeholder="Enter last name"
									/>
								</a-form-item>
							</a-col>
						</a-row>


						<a-form-item>
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
				:scroll="{ x: 1000 }"
				size="small"
				rowKey="id"
			>
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
						title: 'Action',
						key: 'action',
						width: 120,
						scopedSlots: { customRender: 'action' }
					}
				]
			}
		},
		methods: {
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
								await this.$api.updateUser(this.editingUserId, userData);
								this.$message.success('Account updated successfully!');
							} else {
								// Create new user
								if (!values.password) {
									this.$message.error('Password is required for new accounts');
									this.loading = false;
									return;
								}
								userData.password = values.password;
								await this.$api.createUser(userData);
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
				this.form.resetFields();
				this.isEditMode = false;
				this.editingUserId = null;
			},

			getRoleColor(role) {
				const colors = {
					admin: 'red',
					judger: 'blue',
					reporter: 'green'
				};
				return colors[role] || 'default';
			},

		async loadAllAccounts() {
			this.tableLoading = true;
			try {
				const response = await this.$api.getUsers({ 
					limit: this.pageSize,
					page: this.currentPage
				});
				const users = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
				// Ensure each user has a fullName property for the table display
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
				
				// Populate form with user data
				this.$nextTick(() => {
					this.form.setFieldsValue({
						username: user.username,
						email: user.email,
						firstName: user.first_name || '',
						lastName: user.last_name || '',
						role: user.role,
						password: '' // Leave password empty, user can set it if needed
					});
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
</style>
