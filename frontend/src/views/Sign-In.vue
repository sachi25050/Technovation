<!-- 
	Professional Sign In page for LankaPay Technnovation Awards e-Judging System
-->

<template>
	<div class="sign-in-container">
		<div class="sign-in-wrapper">
			<!-- Sign In Card -->
			<div class="sign-in-card">
				<!-- Logo Section -->
				<div class="logo-section">
					<img src="/images/image.png" alt="LankaPay Technnovation Awards" class="logo" />
				</div>

				<div class="card-header">
					<h1 class="card-title">Sign In</h1>
					<p class="card-subtitle">Technnovation Judging System</p>
				</div>

				<!-- Error Message -->
				<a-alert
					v-if="errorMessage"
					:message="errorMessage"
					type="error"
					closable
					@close="errorMessage = ''"
					class="error-alert"
				/>

				<!-- Sign In Form -->
				<a-form
					id="sign-in-form"
					:form="form"
					class="sign-in-form"
					@submit="handleSubmit"
					:hideRequiredMark="true"
				>
					<a-form-item class="form-item" label="Username" :colon="false">
						<a-input 
							v-decorator="[
								'username',
								{ rules: [{ required: true, message: 'Please enter your username' }] },
							]" 
							placeholder="Enter your username"
							:disabled="loading"
							size="large"
							class="form-input"
						>
							<a-icon slot="prefix" type="user" />
						</a-input>
					</a-form-item>

					<a-form-item class="form-item" label="Password" :colon="false">
						<a-input
							v-decorator="[
								'password',
								{ rules: [{ required: true, message: 'Please enter your password' }] },
							]" 
							type="password" 
							placeholder="Enter your password"
							:disabled="loading"
							size="large"
							class="form-input"
							@pressEnter="handleSubmit"
						>
							<a-icon slot="prefix" type="lock" />
						</a-input>
					</a-form-item>

					<a-form-item class="form-item remember-me">
						<a-checkbox v-model="rememberMe" :disabled="loading">
							Remember me
						</a-checkbox>
					</a-form-item>

					<a-form-item class="form-item submit-item">
						<a-button 
							type="primary" 
							block 
							html-type="submit" 
							class="submit-button"
							:loading="loading"
							:disabled="loading"
							size="large"
						>
							{{ loading ? 'Signing In...' : 'Sign In' }}
						</a-button>
					</a-form-item>
				</a-form>
				<!-- / Sign In Form -->

				<div class="card-footer">
					<p class="footer-text">
						Need help? <a href="mailto:poornimal.alexander@lankapay.net" class="help-link">Contact Support</a>
					</p>
				</div>
			</div>
			<!-- / Sign In Card -->

			<!-- Footer -->
			<div class="page-footer">
				<p class="copyright">
					&copy; {{ currentYear }} LankaPay Technnovation Awards. All rights reserved.  |HPA| ver 1.0
				</p>
			</div>
		</div>
	</div>
</template>

<script>
	import apiService from '@/services/api';
	import userRoleStore from '@/store/userRole';

	export default {
		name: 'SignIn',
		data() {
			return {
				rememberMe: false,
				loading: false,
				errorMessage: '',
			}
		},
		computed: {
			currentYear() {
				return new Date().getFullYear() + 1;
			}
		},
		beforeCreate() {
			// Creates the form and adds to it component's "form" property.
			this.form = this.$form.createForm(this, { name: 'normal_login' });
		},
		mounted() {
			// Check if user is already logged in
			if (apiService.isAuthenticated()) {
				this.redirectToDashboard();
			}
		},
		methods: {
			// Handles input validation after submission.
			async handleSubmit(e) {
				e.preventDefault();
				this.errorMessage = '';
				
				this.form.validateFields(async (err, values) => {
					if (!err) {
						this.loading = true;
						
						try {
							// Call login API
							const response = await apiService.login(values.username, values.password);
							
							if (response && response.user) {
								// Set user role in store
								userRoleStore.setRole(response.user.role);
								
								// Show success message
								this.$message.success('Login successful!');
								
								// Redirect to appropriate dashboard based on role
								this.redirectToDashboard(response.user.role);
							}
						} catch (error) {
							// Handle error
							this.errorMessage = error.message || 'Invalid username or password. Please try again.';
							console.error('Login error:', error);
						} finally {
							this.loading = false;
						}
					}
				});
			},
			
			// Redirect to dashboard based on user role
			redirectToDashboard(role = null) {
				// Get role from user data if not provided
				if (!role) {
					const user = apiService.getCurrentUser();
					role = user ? user.role : 'admin';
				}
				
				// Set role in store
				userRoleStore.setRole(role);
				
				// Redirect based on role
				switch (role) {
					case 'admin':
						this.$router.push('/admin');
						break;
					case 'judger':
						this.$router.push('/judger');
						break;
					case 'reporter':
						this.$router.push('/reporter');
						break;
					default:
						this.$router.push('/admin');
				}
			},
		},
	}

</script>

<style lang="scss" scoped>
	.sign-in-container {
		min-height: 100vh;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 40px 20px;
		position: relative;
		overflow: auto;

		@media (max-width: 768px) {
			padding: 30px 16px;
		}

		@media (max-width: 480px) {
			padding: 20px 12px;
		}

		&::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
			opacity: 0.5;
			z-index: 0;
		}
	}

	// .background-image {
	// 	position: absolute;
	// 	top: 0;
	// 	left: 0;
	// 	right: 0;
	// 	bottom: 0;
	// 	background-image: url('/images/image.png');
	// 	background-size: cover;
	// 	background-position: center;
	// 	background-repeat: no-repeat;
	// 	opacity: 0.15;
	// 	z-index: 0;
	// 	pointer-events: none;
	// }

	.sign-in-wrapper {
		width: 100%;
		max-width: 520px;
		position: relative;
		z-index: 2;
	}

	.sign-in-card {
		background: #ffffff;
		border-radius: 20px;
		box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
		padding: 48px 40px;
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;

		@media (max-width: 768px) {
			padding: 32px 24px;
			border-radius: 16px;
		}

		@media (max-width: 480px) {
			padding: 24px 20px;
			border-radius: 12px;
		}
	}

	.logo-section {
		text-align: center;
		margin-bottom: 32px;
		width: 100%;
		padding-bottom: 24px;
		border-bottom: 1px solid #e5e7eb;

		.logo {
			max-width: 100%;
			width: auto;
			height: auto;
			max-height: 70px;
			object-fit: contain;
			display: block;
			margin: 0 auto;
			transition: transform 0.3s ease;

			@media (max-width: 768px) {
				max-height: 60px;
			}

			@media (max-width: 480px) {
				max-height: 50px;
			}
		}
	}

	.card-header {
		text-align: center;
		margin-bottom: 32px;
		width: 100%;

		.card-title {
			font-size: 28px;
			font-weight: 700;
			color: #1a202c;
			margin: 0 0 8px 0;
			line-height: 1.3;
			letter-spacing: -0.5px;

			@media (max-width: 768px) {
				font-size: 24px;
			}

			@media (max-width: 480px) {
				font-size: 22px;
			}
		}

		.card-subtitle {
			font-size: 13px;
			color: #718096;
			margin: 0;
			font-weight: 400;
			line-height: 1.5;

			@media (max-width: 480px) {
				font-size: 12px;
			}
		}
	}

	.error-alert {
		margin-bottom: 24px;
	}

	.sign-in-form {
		width: 100%;

		.form-item {
			margin-bottom: 20px;

			&:last-child {
				margin-bottom: 0;
			}

			@media (max-width: 480px) {
				margin-bottom: 16px;
			}
		}

		.form-input {
			border-radius: 10px;
			border: 1.5px solid #e2e8f0;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			font-size: 15px;

			@media (max-width: 480px) {
				font-size: 14px;
				border-radius: 8px;
			}

			&:hover {
				border-color: #667eea;
			}

			&:focus,
			&.ant-input-focused {
				border-color: #667eea;
				box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
			}
		}

		.remember-me {
			margin-bottom: 24px;

			@media (max-width: 480px) {
				margin-bottom: 20px;
			}
		}

		.submit-item {
			margin-top: 28px;
			margin-bottom: 0;

			@media (max-width: 480px) {
				margin-top: 24px;
			}
		}

		.submit-button {
			height: 50px;
			border-radius: 10px;
			font-size: 16px;
			font-weight: 600;
			letter-spacing: 0.5px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4);

			@media (max-width: 480px) {
				height: 46px;
				font-size: 15px;
				border-radius: 8px;
			}

			&:hover:not(:disabled) {
				transform: translateY(-2px);
				box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
			}

			&:active:not(:disabled) {
				transform: translateY(0);
				box-shadow: 0 2px 10px rgba(102, 126, 234, 0.4);
			}

			&:disabled {
				opacity: 0.6;
				cursor: not-allowed;
			}
		}
	}

	.card-footer {
		margin-top: 24px;
		text-align: center;
		width: 100%;

		@media (max-width: 480px) {
			margin-top: 20px;
		}

		.footer-text {
			font-size: 13px;
			color: #718096;
			margin: 0;
			line-height: 1.5;

			@media (max-width: 480px) {
				font-size: 12px;
			}

			.help-link {
				color: #667eea;
				text-decoration: none;
				font-weight: 500;
				transition: color 0.2s ease;

				&:hover {
					color: #764ba2;
					text-decoration: underline;
				}
			}
		}
	}

	.page-footer {
		margin-top: 32px;
		text-align: center;
		width: 100%;

		@media (max-width: 768px) {
			margin-top: 24px;
		}

		@media (max-width: 480px) {
			margin-top: 20px;
		}

		.copyright {
			font-size: 12px;
			color: rgba(255, 255, 255, 0.85);
			margin: 0;
			line-height: 1.5;

			@media (max-width: 480px) {
				font-size: 11px;
			}
		}
	}

	// Override Ant Design form label styles
	::v-deep .ant-form-item-label {
		label {
			font-weight: 500;
			color: #2d3748;
			font-size: 14px;
		}
	}

	// Override Ant Design input prefix icon styles
	::v-deep .ant-input-affix-wrapper {
		.anticon {
			color: #a0aec0;
		}
	}

	// Override Ant Design checkbox styles
	::v-deep .ant-checkbox-wrapper {
		font-size: 14px;
		color: #4a5568;

		.ant-checkbox-checked .ant-checkbox-inner {
			background-color: #667eea;
			border-color: #667eea;
		}
	}
</style>
