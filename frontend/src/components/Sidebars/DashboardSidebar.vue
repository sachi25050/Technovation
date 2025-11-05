<template>
	
	<!-- Main Sidebar -->
	<a-layout-sider
		collapsible
		class="sider-primary"
		breakpoint="lg"
		collapsed-width="0"
		width="200px"
		:collapsed="sidebarCollapsed"
		@collapse="$emit('toggleSidebar', ! sidebarCollapsed)"
		:trigger="null"
		:class="['ant-layout-sider-' + sidebarColor, 'ant-layout-sider-' + sidebarTheme]"
		theme="light"
		:style="{ backgroundColor: 'transparent',}">
			<div class="brand"><span class="brand-primary">Technovation</span> <span class="brand-secondary">e-Judging System</span></div>
			<hr>

			<!-- Role Switcher (for testing) -->
			<div class="role-switcher">
				<a-radio-group v-model="currentRole" @change="changeRole">
					<a-radio-button value="admin">Admin</a-radio-button>
					<a-radio-button value="judger">Judger</a-radio-button>
					<a-radio-button value="reporter">Reporter</a-radio-button>
				</a-radio-group>
			</div>

			<!-- Sidebar Navigation Menu -->
			<a-menu theme="light" mode="inline">
				<!-- Admin Navigation -->
				<template v-if="isAdmin">
					<a-menu-item>
						<router-link to="/admin">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="#111827"/>
									<path d="M3 10C3 9.44771 3.44772 9 4 9H10C10.5523 9 11 9.44771 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="#111827"/>
									<path d="M14 9C13.4477 9 13 9.44771 13 10V16C13 16.5523 13.4477 17 14 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44771 16.5523 9 16 9H14Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Dashboard</span>
						</router-link>
					</a-menu-item>
					
					<a-menu-item>
						<router-link to="/admin/create-accounts">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M9 6C9 7.65685 7.65685 9 6 9C4.34315 9 3 7.65685 3 6C3 4.34315 4.34315 3 6 3C7.65685 3 9 4.34315 9 6Z" fill="#111827"/>
									<path d="M17 6C17 7.65685 15.6569 9 14 9C12.3431 9 11 7.65685 11 6C11 4.34315 12.3431 3 14 3C15.6569 3 17 4.34315 17 6Z" fill="#111827"/>
									<path d="M12.9291 17C12.9758 16.6734 13 16.3395 13 16C13 14.3648 12.4393 12.8606 11.4998 11.6691C12.2352 11.2435 13.0892 11 14 11C16.7614 11 19 13.2386 19 16V17H12.9291Z" fill="#111827"/>
									<path d="M6 11C8.76142 11 11 13.2386 11 16V17H1V16C1 13.2386 3.23858 11 6 11Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Create Accounts</span>
						</router-link>
					</a-menu-item>
					
					<a-menu-item>
						<router-link to="/admin/add-institution">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C4.73367 3.60948 7.26633 3.60948 8.82843 5.17157L10 6.34315L11.1716 5.17157C12.7337 3.60948 15.2663 3.60948 16.8284 5.17157C18.3905 6.73367 18.3905 9.26633 16.8284 10.8284L10 17.6569L3.17157 10.8284C1.60948 9.26633 1.60948 6.73367 3.17157 5.17157Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Add Institution</span>
						</router-link>
					</a-menu-item>
					
					<a-menu-item>
						<router-link to="/admin/add-awards">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M8.43338 7.41784C8.58818 7.31464 8.77939 7.2224 9 7.15101L9.00001 8.84899C8.77939 8.7776 8.58818 8.68536 8.43338 8.58216C8.06927 8.33942 8 8.1139 8 8C8 7.8861 8.06927 7.66058 8.43338 7.41784Z" fill="#111827"/>
									<path d="M11 12.849L11 11.151C11.2206 11.2224 11.4118 11.3146 11.5666 11.4178C11.9308 11.6606 12 11.8861 12 12C12 12.1139 11.9308 12.3394 11.5666 12.5822C11.4118 12.6854 11.2206 12.7776 11 12.849Z" fill="#111827"/>
									<path fill-rule="evenodd" clip-rule="evenodd" d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18ZM11 5C11 4.44772 10.5523 4 10 4C9.44772 4 9 4.44772 9 5V5.09199C8.3784 5.20873 7.80348 5.43407 7.32398 5.75374C6.6023 6.23485 6 7.00933 6 8C6 8.99067 6.6023 9.76515 7.32398 10.2463C7.80348 10.5659 8.37841 10.7913 9.00001 10.908L9.00002 12.8492C8.60902 12.7223 8.31917 12.5319 8.15667 12.3446C7.79471 11.9275 7.16313 11.8827 6.74599 12.2447C6.32885 12.6067 6.28411 13.2382 6.64607 13.6554C7.20855 14.3036 8.05956 14.7308 9 14.9076L9 15C8.99999 15.5523 9.44769 16 9.99998 16C10.5523 16 11 15.5523 11 15L11 14.908C11.6216 14.7913 12.1965 14.5659 12.676 14.2463C13.3977 13.7651 14 12.9907 14 12C14 11.0093 13.3977 10.2348 12.676 9.75373C12.1965 9.43407 11.6216 9.20873 11 9.09199L11 7.15075C11.391 7.27771 11.6808 7.4681 11.8434 7.65538C12.2053 8.07252 12.8369 8.11726 13.254 7.7553C13.6712 7.39335 13.7159 6.76176 13.354 6.34462C12.7915 5.69637 11.9405 5.26915 11 5.09236V5Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Add Awards</span>
						</router-link>
					</a-menu-item>
				</template>

				<!-- Judger Navigation -->
				<template v-if="isJudger">
					<a-menu-item>
						<router-link to="/judger">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="#111827"/>
									<path d="M3 10C3 9.44771 3.44772 9 4 9H10C10.5523 9 11 9.44771 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="#111827"/>
									<path d="M14 9C13.4477 9 13 9.44771 13 10V16C13 16.5523 13.4477 17 14 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44771 16.5523 9 16 9H14Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Dashboard</span>
						</router-link>
					</a-menu-item>
					
					<a-menu-item>
						<router-link to="/judger/evaluate">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M9 2C8.44772 2 8 2.44772 8 3C8 3.55228 8.44772 4 9 4H11C11.5523 4 12 3.55228 12 3C12 2.44772 11.5523 2 11 2H9Z" fill="#111827"/>
									<path fill-rule="evenodd" clip-rule="evenodd" d="M4 5C4 3.89543 4.89543 3 6 3C6 4.65685 7.34315 6 9 6H11C12.6569 6 14 4.65685 14 3C15.1046 3 16 3.89543 16 5V16C16 17.1046 15.1046 18 14 18H6C4.89543 18 4 17.1046 4 16V5ZM7 9C6.44772 9 6 9.44772 6 10C6 10.5523 6.44772 11 7 11H7.01C7.56228 11 8.01 10.5523 8.01 10C8.01 9.44772 7.56228 9 7.01 9H7ZM10 9C9.44772 9 9 9.44772 9 10C9 10.5523 9.44772 11 10 11H13C13.5523 11 14 10.5523 14 10C14 9.44772 13.5523 9 13 9H10ZM7 13C6.44772 13 6 13.4477 6 14C6 14.5523 6.44772 15 7 15H7.01C7.56228 15 8.01 14.5523 8.01 14C8.01 13.4477 7.56228 13 7.01 13H7ZM10 13C9.44772 13 9 13.4477 9 14C9 14.5523 9.44772 15 10 15H13C13.5523 15 14 14.5523 14 14C14 13.4477 13.5523 13 13 13H10Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Evaluate</span>
						</router-link>
					</a-menu-item>
				</template>

				<!-- Reporter Navigation -->
				<template v-if="isReporter">
					<a-menu-item>
						<router-link to="/reporter">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="#111827"/>
									<path d="M3 10C3 9.44771 3.44772 9 4 9H10C10.5523 9 11 9.44771 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="#111827"/>
									<path d="M14 9C13.4477 9 13 9.44771 13 10V16C13 16.5523 13.4477 17 14 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44771 16.5523 9 16 9H14Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Dashboard</span>
						</router-link>
					</a-menu-item>
					
					<a-menu-item>
						<router-link to="/reporter/generate-reports">
							<span class="icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C2 5.58172 5.58172 2 10 2V10H18C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10Z" fill="#111827"/>
									<path d="M12 2.25195C14.8113 2.97552 17.0245 5.18877 17.748 8.00004H12V2.25195Z" fill="#111827"/>
								</svg>
							</span>
							<span class="label">Generate Reports</span>
						</router-link>
					</a-menu-item>
				</template>
			</a-menu>
			<!-- / Sidebar Navigation Menu -->

			<!-- Sidebar Footer -->
			<div class="aside-footer">
				<div class="footer-box">
					<span class="icon">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V6C17 6.55228 16.5523 7 16 7H4C3.44772 7 3 6.55228 3 6V4Z" fill="#111827"/>
							<path d="M3 10C3 9.44771 3.44772 9 4 9H10C10.5523 9 11 9.44771 11 10V16C11 16.5523 10.5523 17 10 17H4C3.44772 17 3 16.5523 3 16V10Z" fill="#111827"/>
							<path d="M14 9C13.4477 9 13 9.44771 13 10V16C13 16.5523 13.4477 17 14 17H16C16.5523 17 17 16.5523 17 16V10C17 9.44771 16.5523 9 16 9H14Z" fill="#111827"/>
						</svg>
					</span>
					<h6>Need Help?</h6>
					<p>Please check our docs</p>
					<a-button type="primary" href="https://lankapay.com/docs" block target="_blank">
						DOCUMENTATION
					</a-button>
				</div>
			</div>
			<!-- / Sidebar Footer -->

			<a-button type="primary" href="https://lankapay.com/enterprise" block target="_blank">
				ENTERPRISE
			</a-button>

	</a-layout-sider>
	<!-- / Main Sidebar -->

</template>

<script>
	import userRoleStore from '@/store/userRole';

	export default ({
		props: {
			// Sidebar collapsed status.
			sidebarCollapsed: {
				type: Boolean,
				default: false,
			},
			
			// Main sidebar color.
			sidebarColor: {
				type: String,
				default: "primary",
			},
			
			// Main sidebar theme : light, white, dark.
			sidebarTheme: {
				type: String,
				default: "light",
			},
		},
		data() {
			return {
				// sidebarCollapsedModel: this.sidebarCollapsed,
				currentRole: userRoleStore.role
			}
		},
		computed: {
			isAdmin() {
				return userRoleStore.isAdmin();
			},
			isJudger() {
				return userRoleStore.isJudger();
			},
			isReporter() {
				return userRoleStore.isReporter();
			}
		},
		methods: {
			changeRole(e) {
				userRoleStore.setRole(e.target.value);
			}
		}
	})

</script>

<style scoped>
.role-switcher {
	padding: 10px 24px;
	margin-bottom: 15px;
}
</style>

<style scoped>
	.brand { display: inline-block; }
	.brand .brand-primary { font-size: 20px; display: block; }
	.brand .brand-secondary { display: block; text-align: right; }

	/* Align icons and labels inside the sidebar menu */
	.sider-primary :deep(.ant-menu-item) {
		display: flex;
		align-items: center;
		gap: 8px;
		padding-left: 8px !important;
		padding-right: 8px !important;
		text-align: right;
		justify-content: flex-end;
	}
	.sider-primary :deep(.ant-menu-item .icon) {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 20px;
		min-width: 20px;
	}
	.sider-primary :deep(.ant-menu-item .icon svg) { display: block; }
	.sider-primary :deep(.ant-menu-item .label) { flex: 1 1 auto; min-width: 0; }

	/* Fix hover state for Evaluation and all menu items */
	.sider-primary :deep(.ant-menu-item:hover) {
		background-color: #f0f0f0;
		color: #1890ff;
		display: flex !important;
		align-items: center !important;
	}
	.sider-primary :deep(.ant-menu-item.ant-menu-item-selected) {
		background-color: #e6f7ff;
		color: #1890ff;
		display: flex !important;
		align-items: center !important;
	}
	.sider-primary :deep(.ant-menu-item a) {
		color: inherit;
		text-decoration: none;
		display: flex !important;
		align-items: center !important;
		width: 100%;
		gap: 8px;
		justify-content: flex-end;
		text-align: right;
	}
	.sider-primary :deep(.ant-menu-item:hover a) {
		color: #1890ff;
	}
	.sider-primary :deep(.ant-menu-item a .icon) {
		flex-shrink: 0;
		width: 20px;
		min-width: 20px;
	}
	.sider-primary :deep(.ant-menu-item a .label) {
		flex: 1;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
</style>