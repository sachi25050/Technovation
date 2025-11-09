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
										placeholder="Enter password"
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
								Create Account
							</a-button>
							<a-button style="margin-left: 8px;" @click="resetForm" size="large">
								Reset
							</a-button>
						</a-form-item>
					</a-form>
				</a-card>
			</a-col>

			<a-col :span="24" :lg="8">
				<a-card title="Recent Accounts" class="mb-24">
					<a-list :data-source="recentAccounts" size="small">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title">{{ item.username }}</a>
								<template slot="description">
									{{ item.email }} • {{ item.role }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-tag :color="getRoleColor(item.role)">{{ item.role }}</a-tag>
							</template>
						</a-list-item>
					</a-list>
				</a-card>

				<a-card title="Account Statistics">
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
	</div>
</template>

<script>
	export default ({
		data() {
			return {
				form: this.$form.createForm(this),
				loading: false,
				recentAccounts: [],
				accountStats: {
					total: 0,
					admins: 0,
					judgers: 0,
					reporters: 0
				}
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
								password: values.password,
								first_name: values.firstName || '',
								last_name: values.lastName || '',
								role: values.role
							};

							// Call API to create user
							await this.$api.createUser(userData);
							
							// Show success message
							this.$message.success('Account created successfully!');
							
							// Reset form
							this.resetForm();
							
							// Refresh recent accounts and stats
							this.loadRecentAccounts();
							this.loadAccountStats();
						} catch (error) {
							this.$message.error(error.message || 'Failed to create account');
						} finally {
							this.loading = false;
						}
					}
				});
			},
			resetForm() {
				this.form.resetFields();
			},
			getRoleColor(role) {
				const colors = {
					admin: 'red',
					judger: 'blue',
					reporter: 'green'
				};
				return colors[role] || 'default';
			},

			async loadRecentAccounts() {
				try {
					// Get the most recent 5 accounts
					const response = await this.$api.getUsers({ limit: 5 });
					// Response structure: { success: true, message: "...", data: { data: [...], pagination: {...} } }
					this.recentAccounts = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
				} catch (error) {
					console.error('Failed to load recent accounts:', error);
				}
			},

			async loadAccountStats() {
				try {
					// Get all users to calculate stats
					const response = await this.$api.getUsers({ limit: 1000 });
					// Response structure: { success: true, message: "...", data: { data: [...], pagination: {...} } }
					const users = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
					
					// Ensure users is an array
					if (!Array.isArray(users)) {
						console.error('Users data is not an array:', users);
						return;
					}
					
					// Calculate stats
					this.accountStats = {
						total: users.length,
						admins: users.filter(user => user.role === 'admin').length,
						judgers: users.filter(user => user.role === 'judger').length,
						reporters: users.filter(user => user.role === 'reporter').length
					};
				} catch (error) {
					console.error('Failed to load account stats:', error);
				}
			}
		},

		created() {
			// Load initial data
			this.loadRecentAccounts();
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
</style>
