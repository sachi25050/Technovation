<!-- 
	Reporter Dashboard - Overview for reporters
 -->

<template>
	<div>
		<!-- Page Header -->
		<div class="page-header">
			<h1>Reporter Dashboard</h1>
			<p>Generate and manage award reports</p>
		</div>

		<!-- Counter Widgets -->
		<a-row :gutter="24">
			<a-col :span="24" :lg="12" :xl="6" class="mb-24" v-for="(stat, index) in stats" :key="index">
				<!-- Widget Card -->
				<WidgetCounter
					:title="stat.title"
					:value="stat.value"
					:prefix="stat.prefix"
					:suffix="stat.suffix"
					:icon="stat.icon"
					:status="stat.status"
				></WidgetCounter>
				<!-- / Widget Card -->
			</a-col>
		</a-row>
		<!-- / Counter Widgets -->

		<!-- Charts -->
		<a-row :gutter="24" type="flex" align="stretch">
			<a-col :span="24" :lg="10" class="mb-24">
				<!-- Report Generation Card -->
				<CardBarChart></CardBarChart>
				<!-- Report Generation Card -->
			</a-col>
			<a-col :span="24" :lg="14" class="mb-24">
				<!-- Report Timeline Card -->
				<CardLineChart></CardLineChart>
				<!-- / Report Timeline Card -->
			</a-col>
		</a-row>
		<!-- / Charts -->

		<!-- Recent Reports & Quick Actions -->
		<a-row :gutter="24" type="flex" align="stretch">
			<!-- Recent Reports -->
			<a-col :span="24" :lg="12" class="mb-24">
				<a-card title="Recent Reports" class="h-100">
					<a-list :data-source="recentReports" size="large">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title" @click="downloadReport(item.id)">
									{{ item.name }}
								</a>
								<template slot="description">
									{{ item.type }} • Generated: {{ item.generatedDate }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-button type="primary" size="small" @click="downloadReport(item.id)">
									Download
								</a-button>
							</template>
						</a-list-item>
					</a-list>
					<div slot="extra">
						<a-button type="link" @click="navigateToReports">
							View All
						</a-button>
					</div>
				</a-card>
			</a-col>

			<!-- Quick Actions -->
			<a-col :span="24" :lg="12" class="mb-24">
				<a-card title="Quick Actions" class="h-100">
					<a-list :data-source="quickActions" size="large">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title" @click="navigateTo(item.route)">
									{{ item.title }}
								</a>
								<template slot="description">
									{{ item.description }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-button type="primary" size="small" @click="navigateTo(item.route)">
									Go
								</a-button>
							</template>
						</a-list-item>
					</a-list>
				</a-card>
			</a-col>
		</a-row>
		<!-- / Recent Reports & Quick Actions -->

		<!-- Report Templates & Statistics -->
		<a-row :gutter="24" type="flex" align="stretch">
			<!-- Report Templates -->
			<a-col :span="24" :lg="16" class="mb-24">
				<a-card title="Report Templates" class="h-100">
					<a-row :gutter="16">
						<a-col :span="8" v-for="template in reportTemplates" :key="template.id">
							<a-card 
								class="template-card" 
								:bordered="false"
								@click="useTemplate(template.id)"
							>
								<div class="template-icon">
									<component :is="template.icon" />
								</div>
								<h4>{{ template.name }}</h4>
								<p>{{ template.description }}</p>
								<a-button type="primary" size="small" block>
									Use Template
								</a-button>
							</a-card>
						</a-col>
					</a-row>
				</a-card>
			</a-col>

			<!-- Report Statistics -->
			<a-col :span="24" :lg="8" class="mb-24">
				<a-card title="Report Statistics" class="h-100">
					<a-row :gutter="16">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ reportStats.total }}</div>
								<div class="stat-label">Total Reports</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ reportStats.thisMonth }}</div>
								<div class="stat-label">This Month</div>
							</div>
						</a-col>
					</a-row>
					<a-row :gutter="16" style="margin-top: 16px;">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ reportStats.pdf }}</div>
								<div class="stat-label">PDF Reports</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ reportStats.excel }}</div>
								<div class="stat-label">Excel Reports</div>
							</div>
						</a-col>
					</a-row>
				</a-card>
			</a-col>
		</a-row>
		<!-- / Report Templates & Statistics -->

	</div>
</template>

<script>
	// Bar chart for "Report Generation" card.
	import CardBarChart from '../../components/Cards/CardBarChart' ;

	// Line chart for "Report Timeline" card.
	import CardLineChart from '../../components/Cards/CardLineChart' ;

	// Counter Widgets
	import WidgetCounter from '../../components/Widgets/WidgetCounter' ;

	// Reporter Dashboard stats
	const stats = [
		{
			title: "Reports Generated",
			value: 45,
			suffix: "this month",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M2 10C2 5.58172 5.58172 2 10 2V10H18C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10Z" fill="#111827"/>
					<path d="M12 2.25195C14.8113 2.97552 17.0245 5.18877 17.748 8.00004H12V2.25195Z" fill="#111827"/>
				</svg>`,
		},
		{
			title: "Awards Covered",
			value: 28,
			suffix: "awards",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C4.73367 3.60948 7.26633 3.60948 8.82843 5.17157L10 6.34315L11.1716 5.17157C12.7337 3.60948 15.2663 3.60948 16.8284 5.17157C18.3905 6.73367 18.3905 9.26633 16.8284 10.8284L10 17.6569L3.17157 10.8284C1.60948 9.26633 1.60948 6.73367 3.17157 5.17157Z" fill="#111827"/>
				</svg>`,
		},
		{
			title: "Institutions",
			value: 15,
			suffix: "institutions",
			status: "success",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 6C9 7.65685 7.65685 9 6 9C4.34315 9 3 7.65685 3 6C3 4.34315 4.34315 3 6 3C7.65685 3 9 4.34315 9 6Z" fill="#111827"/>
					<path d="M17 6C17 7.65685 15.6569 9 14 9C12.3431 9 11 7.65685 11 6C11 4.34315 12.3431 3 14 3C15.6569 3 17 4.34315 17 6Z" fill="#111827"/>
					<path d="M12.9291 17C12.9758 16.6734 13 16.3395 13 16C13 14.3648 12.4393 12.8606 11.4998 11.6691C12.2352 11.2435 13.0892 11 14 11C16.7614 11 19 13.2386 19 16V17H12.9291Z" fill="#111827"/>
					<path d="M6 11C8.76142 11 11 13.2386 11 16V17H1V16C1 13.2386 3.23858 11 6 11Z" fill="#111827"/>
				</svg>`,
		},
		{
			title: "Total Submissions",
			value: 1250,
			suffix: "submissions",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M8.43338 7.41784C8.58818 7.31464 8.77939 7.2224 9 7.15101L9.00001 8.84899C8.77939 8.7776 8.58818 8.68536 8.43338 8.58216C8.06927 8.33942 8 8.1139 8 8C8 7.8861 8.06927 7.66058 8.43338 7.41784Z" fill="#111827"/>
					<path d="M11 12.849L11 11.151C11.2206 11.2224 11.4118 11.3146 11.5666 11.4178C11.9308 11.6606 12 11.8861 12 12C12 12.1139 11.9308 12.3394 11.5666 12.5822C11.4118 12.6854 11.2206 12.7776 11 12.849Z" fill="#111827"/>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18ZM11 5C11 4.44772 10.5523 4 10 4C9.44772 4 9 4.44772 9 5V5.09199C8.3784 5.20873 7.80348 5.43407 7.32398 5.75374C6.6023 6.23485 6 7.00933 6 8C6 8.99067 6.6023 9.76515 7.32398 10.2463C7.80348 10.5659 8.37841 10.7913 9.00001 10.908L9.00002 12.8492C8.60902 12.7223 8.31917 12.5319 8.15667 12.3446C7.79471 11.9275 7.16313 11.8827 6.74599 12.2447C6.32885 12.6067 6.28411 13.2382 6.64607 13.6554C7.20855 14.3036 8.05956 14.7308 9 14.9076L9 15C8.99999 15.5523 9.44769 16 9.99998 16C10.5523 16 11 15.5523 11 15L11 14.908C11.6216 14.7913 12.1965 14.5659 12.676 14.2463C13.3977 13.7651 14 12.9907 14 12C14 11.0093 13.3977 10.2348 12.676 9.75373C12.1965 9.43407 11.6216 9.20873 11 9.09199L11 7.15075C11.391 7.27771 11.6808 7.4681 11.8434 7.65538C12.2053 8.07252 12.8369 8.11726 13.254 7.7553C13.6712 7.39335 13.7159 6.76176 13.354 6.34462C12.7915 5.69637 11.9405 5.26915 11 5.09236V5Z" fill="#111827"/>
				</svg>`,
		},
	] ;

	// Recent reports
	const recentReports = [
		{
			id: 1,
			name: 'Award Marks Report - January 2024',
			type: 'PDF',
			generatedDate: '2024-01-20'
		},
		{
			id: 2,
			name: 'Institution Performance Summary',
			type: 'Excel',
			generatedDate: '2024-01-19'
		},
		{
			id: 3,
			name: 'Innovation Excellence Results',
			type: 'PDF',
			generatedDate: '2024-01-18'
		},
		{
			id: 4,
			name: 'Academic Achievement Report',
			type: 'Excel',
			generatedDate: '2024-01-17'
		}
	];

	// Quick actions for reporter
	const quickActions = [
		{
			title: "Generate New Report",
			description: "Create custom reports",
			route: "/reporter/generate-reports"
		},
		{
			title: "View All Reports",
			description: "Browse existing reports",
			route: "/reporter/generate-reports"
		}
	];

	// Report templates
	const reportTemplates = [
		{
			id: 1,
			name: 'Award Summary',
			description: 'Overview of all awards and winners',
			icon: 'award'
		},
		{
			id: 2,
			name: 'Institution Report',
			description: 'Performance by institution',
			icon: 'institution'
		},
		{
			id: 3,
			name: 'Detailed Analysis',
			description: 'Comprehensive evaluation analysis',
			icon: 'analysis'
		}
	];

	export default ({
		components: {
			CardBarChart,
			CardLineChart,
			WidgetCounter,
		},
		data() {
			return {
				// Counter Widgets Stats
				stats,
				recentReports,
				quickActions,
				reportTemplates,
				reportStats: {
					total: 156,
					thisMonth: 45,
					pdf: 89,
					excel: 67
				}
			}
		},
		methods: {
			navigateTo(route) {
				this.$router.push(route);
			},
			navigateToReports() {
				this.$router.push('/reporter/generate-reports');
			},
			downloadReport(id) {
				this.$message.success(`Downloading report ${id}...`);
			},
			useTemplate(templateId) {
				this.$router.push(`/reporter/generate-reports?template=${templateId}`);
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

.template-card {
	text-align: center;
	cursor: pointer;
	transition: all 0.3s ease;
	
	&:hover {
		transform: translateY(-4px);
		box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
	}
	
	.template-icon {
		font-size: 32px;
		margin-bottom: 16px;
		color: #1890ff;
	}
	
	h4 {
		margin: 0 0 8px 0;
		font-size: 16px;
		font-weight: 600;
		color: #1f2937;
	}
	
	p {
		margin: 0 0 16px 0;
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
