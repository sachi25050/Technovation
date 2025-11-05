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
				recentAccounts: [
					{
						username: 'john.doe',
						email: 'john.doe@example.com',
						role: 'judger'
					},
					{
						username: 'jane.smith',
						email: 'jane.smith@example.com',
						role: 'reporter'
					},
					{
						username: 'admin.user',
						email: 'admin@example.com',
						role: 'admin'
					},
					{
						username: 'judge.mike',
						email: 'mike@example.com',
						role: 'judger'
					}
				],
				accountStats: {
					total: 1250,
					admins: 5,
					judgers: 45,
					reporters: 8
				}
			}
		},
		methods: {
			handleSubmit(e) {
				e.preventDefault();
				this.form.validateFields((err, values) => {
					if (!err) {
						this.loading = true;
						console.log('Received values of form: ', values);
						
						// Simulate API call
						setTimeout(() => {
							this.loading = false;
							this.$message.success('Account created successfully!');
							this.resetForm();
						}, 2000);
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
			}
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
