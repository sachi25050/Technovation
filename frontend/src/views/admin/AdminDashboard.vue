<!-- 
	Admin Dashboard - Overview of system statistics and management
 -->

<template>
	<div class="admin-dashboard">
		<!-- Page Header -->
		<div class="page-header">
			<h1>Admin Dashboard</h1>
			<p>System overview and analytics</p>
		</div>

		<!-- Loading State -->
		<div v-if="loading" class="loading-container">
			<a-spin size="large" tip="Loading dashboard data..." />
		</div>

		<template v-else>
		<!-- Counter Widgets -->
		<a-row :gutter="24">
				<a-col :span="24" :lg="12" :xl="6" class="mb-24">
					<div class="stat-card stat-card-blue">
						<div class="stat-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M19 21V5C19 3.9 18.1 3 17 3H7C5.9 3 5 3.9 5 5V21L12 18L19 21Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="stat-content">
							<div class="stat-value">{{ dashboardStats.totalInstitutions }}</div>
							<div class="stat-label">Total Institutions</div>
						</div>
					</div>
				</a-col>
				<a-col :span="24" :lg="12" :xl="6" class="mb-24">
					<div class="stat-card stat-card-green">
						<div class="stat-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="stat-content">
							<div class="stat-value">{{ dashboardStats.totalAwards }}</div>
							<div class="stat-label">Total Awards</div>
						</div>
					</div>
				</a-col>
				<a-col :span="24" :lg="12" :xl="6" class="mb-24">
					<div class="stat-card stat-card-purple">
						<div class="stat-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M16 11C17.66 11 18.99 9.66 18.99 8C18.99 6.34 17.66 5 16 5C14.34 5 13 6.34 13 8C13 9.66 14.34 11 16 11ZM8 11C9.66 11 10.99 9.66 10.99 8C10.99 6.34 9.66 5 8 5C6.34 5 5 6.34 5 8C5 9.66 6.34 11 8 11ZM8 13C5.67 13 1 14.17 1 16.5V19H15V16.5C15 14.17 10.33 13 8 13ZM16 13C15.71 13 15.38 13.02 15.03 13.05C16.19 13.89 17 15.02 17 16.5V19H23V16.5C23 14.17 18.33 13 16 13Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="stat-content">
							<div class="stat-value">{{ dashboardStats.totalJudges }}</div>
							<div class="stat-label">Total Judges</div>
						</div>
					</div>
				</a-col>
				<a-col :span="24" :lg="12" :xl="6" class="mb-24">
					<div class="stat-card stat-card-orange">
						<div class="stat-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM9 17H7V10H9V17ZM13 17H11V7H13V17ZM17 17H15V13H17V17Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="stat-content">
							<div class="stat-value">{{ dashboardStats.totalCriteria }}</div>
							<div class="stat-label">Total Criteria</div>
						</div>
					</div>
			</a-col>
		</a-row>
		<!-- / Counter Widgets -->

			<!-- Charts Row -->
		<a-row :gutter="24" type="flex" align="stretch">
				<!-- Institution Performance Chart -->
				<a-col :span="24" class="mb-24">
					<a-card title="Institution Performance - Marks Comparison" class="chart-card h-100">
						<template slot="extra">
							<a-tag color="blue">Preliminary Marks vs Presentation Marks</a-tag>
						</template>
						<div class="chart-container" v-if="institutionChartData.length > 0">
							<canvas ref="institutionPerformanceChart"></canvas>
						</div>
						<div v-else class="empty-chart-container">
							<a-empty description="No institution performance data available">
								<template slot="image">
									<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M3 3V21H21" stroke="#d1d5db" stroke-width="2" stroke-linecap="round"/>
										<path d="M7 16L11 12L15 14L21 8" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</template>
								<a-button type="primary" size="small" @click="navigateTo('/admin/add-institution')">
									Add Institution
								</a-button>
							</a-empty>
						</div>
					</a-card>
			</a-col>
		</a-row>
			<!-- / Charts Row -->

		<!-- All Awards & Institution Rankings -->
			<a-row :gutter="24" type="flex" align="stretch">
				<a-col :span="24" :lg="12" class="mb-24">
					<a-card title="All Awards" class="h-100">
						<a-table
							:columns="allAwardsColumns"
							:data-source="allAwards"
							:pagination="false"
							:loading="tableLoading"
							size="middle"
							rowKey="id"
							:scroll="{ y: 400 }"
						>
							<template slot="category" slot-scope="text">
								<div class="category-name">
									<strong>{{ text }}</strong>
								</div>
								</template>
							<template slot="criteriaCount" slot-scope="text">
								<a-tag color="blue">{{ text }}</a-tag>
							</template>
						</a-table>
				</a-card>
			</a-col>

		<!-- Institution Rankings by Aggregated Marks -->
			<a-col :span="24" :lg="12" class="mb-24">
				<a-card title="Institution Rankings" class="h-100 institution-rankings-card">
					<template slot="extra">
						<a-tag color="green">By Aggregated Marks</a-tag>
					</template>
					<div class="rankings-list" v-if="institutionRankings.length > 0">
						<div 
							v-for="(institution, index) in institutionRankings" 
							:key="institution.id" 
							class="ranking-item"
						>
							<div class="rank-badge" :class="getRankClass(index)">
								{{ index + 1 }}
							</div>
							<div class="institution-image">
								<img 
									:src="getInstitutionImageUrl(institution.image)" 
									:alt="institution.name"
									class="inst-img"
									@error="handleImageError"
								/>
							</div>
							<div class="institution-info">
								<div class="institution-name">{{ institution.name }}</div>
								<div class="institution-marks">
									<span class="marks-label">Aggregated:</span>
									<span class="marks-value">{{ institution.aggregatedMarks.toFixed(2) }}</span>
								</div>
							</div>
							<div class="marks-bar">
								<a-progress 
									:percent="getMarksPercent(institution.aggregatedMarks)" 
									:showInfo="false"
									:strokeColor="getProgressColor(index)"
									size="small"
								/>
							</div>
						</div>
					</div>
					<a-empty v-else description="No institution data available" />
				</a-card>
			</a-col>
		</a-row>
			<!-- / Institution Awards Summary -->

		</template>
	</div>
</template>

<script>
import {
	Chart,
	BarController,
	CategoryScale,
	LinearScale,
	BarElement,
	Title,
	Tooltip,
	Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(
	BarController,
	CategoryScale,
	LinearScale,
	BarElement,
	Title,
	Tooltip,
	Legend
);
import apiService from '@/services/api';

export default {
	name: 'AdminDashboard',
	data() {
		return {
			loading: true,
			tableLoading: false,
			dashboardStats: {
				totalInstitutions: 0,
				totalAwards: 0,
				totalJudges: 0,
				totalCriteria: 0
			},
			institutionChartData: [],
			allAwards: [],
			institutionPerformanceChartInstance: null,
			allAwardsColumns: [
				{
					title: 'Award Category',
					dataIndex: 'category',
					key: 'category',
					scopedSlots: { customRender: 'category' }
				},
				{
					title: 'No. of Criteria',
					dataIndex: 'criteriaCount',
					key: 'criteriaCount',
					scopedSlots: { customRender: 'criteriaCount' },
					align: 'center'
				}
			],
			institutionRankings: [],
			maxAggregatedMarks: 100
		}
	},
	async mounted() {
		await this.loadDashboardData();
	},
	beforeDestroy() {
		// Destroy chart instances to prevent memory leaks
		if (this.institutionPerformanceChartInstance) {
			this.institutionPerformanceChartInstance.destroy();
		}
	},
	watch: {
		// Watch for changes in chart data and render when ready
		institutionChartData: {
			handler(newVal) {
				if (newVal && newVal.length > 0) {
					this.$nextTick(() => {
						setTimeout(() => {
							this.renderInstitutionPerformanceChart();
						}, 200);
					});
				}
			},
			deep: true
		}
	},
	methods: {
		async loadDashboardData() {
			this.loading = true;
			try {
				// Fetch dashboard data, institutions and awards in parallel
				const [dashboardRes, institutionsRes, awardsRes] = await Promise.all([
					apiService.getDashboard().catch(() => ({ data: {} })),
					apiService.getInstitutions({ limit: 1000 }).catch(() => ({ data: [] })),
					apiService.getAwards({ limit: 1000 }).catch(() => ({ data: [] }))
				]);

				// Extract dashboard data
				const dashboardData = dashboardRes.data || dashboardRes;
				const stats = dashboardData.stats || {};
				const institutionPerformance = dashboardData.institution_performance || [];
				
				// Extract institutions and awards
				const institutions = this.extractData(institutionsRes);
				const awards = this.extractData(awardsRes);

				// Calculate total criteria
				let totalCriteria = 0;
				awards.forEach(award => {
					if (award.criteria && Array.isArray(award.criteria)) {
						totalCriteria += award.criteria.length;
					}
				});

				// Set dashboard stats
				this.dashboardStats = {
					totalInstitutions: stats.institutions || institutions.length,
					totalAwards: stats.awards_created || awards.length,
					totalJudges: stats.active_judges || 0,
					totalCriteria: totalCriteria
				};

				// Prepare chart data - use institution performance if available, otherwise fall back to institutions
				if (institutionPerformance.length > 0) {
					this.prepareInstitutionChartFromPerformance(institutionPerformance);
					this.prepareInstitutionRankings(institutionPerformance);
				} else if (institutions.length > 0) {
					this.prepareInstitutionChartFromInstitutions(institutions);
					this.prepareInstitutionRankingsFromInstitutions(institutions);
				}
				
				if (awards.length > 0) {
					this.prepareAllAwards(awards);
				}

				this.loading = false;

				// Render charts after DOM updates
				this.$nextTick(() => {
					setTimeout(() => {
						this.renderInstitutionPerformanceChart();
					}, 100);
				});

			} catch (error) {
				console.error('Failed to load dashboard data:', error);
				this.$message.error('Failed to load dashboard data. Please try again.');
				this.loading = false;
			}
		},

		extractData(response) {
			if (response.data && Array.isArray(response.data)) {
				return response.data;
			}
			if (response.data && response.data.data && Array.isArray(response.data.data)) {
				return response.data.data;
			}
			if (Array.isArray(response)) {
				return response;
			}
			return [];
		},

		prepareInstitutionChartFromPerformance(institutionPerformance) {
			// Use real data from evaluations table via dashboard API
			this.institutionChartData = institutionPerformance.slice(0, 10).map(inst => {
				return {
					name: inst.name ? (inst.name.length > 15 ? inst.name.substring(0, 15) + '...' : inst.name) : 'Unknown',
					fullName: inst.name || 'Unknown',
					preliminaryMarks: parseFloat(inst.preliminary_marks) || 0,
					presentationMarks: parseFloat(inst.presentation_marks) || 0
				};
			});
		},

		prepareInstitutionChartFromInstitutions(institutions) {
			// Fallback: extract marks from institution awards array
			this.institutionChartData = institutions.slice(0, 10).map(inst => {
				let preliminaryMarks = 0;
				let presentationMarks = 0;

				if (inst.awards && Array.isArray(inst.awards)) {
					inst.awards.forEach(award => {
						// Get preliminary marks
						preliminaryMarks += parseInt(award.marks) || parseInt(award.preliminary_marks) || 0;
						// Get presentation marks
						presentationMarks += parseInt(award.presentation_marks) || parseInt(award.presentationMarks) || 0;
					});
				}

				return {
					name: inst.name ? (inst.name.length > 15 ? inst.name.substring(0, 15) + '...' : inst.name) : 'Unknown',
					fullName: inst.name || 'Unknown',
					preliminaryMarks,
					presentationMarks
				};
			});
		},

		prepareAllAwards(awards) {
			// Map all awards to display format
			this.allAwards = awards.map(award => ({
				id: award.id || award._id,
				category: award.category || 'Uncategorized',
				criteriaCount: (award.criteria && Array.isArray(award.criteria)) ? award.criteria.length : 0
			}));
		},

		renderInstitutionPerformanceChart() {
			if (!this.$refs.institutionPerformanceChart || this.institutionChartData.length === 0) {
				return;
			}

			const ctx = this.$refs.institutionPerformanceChart.getContext('2d');
			
			if (this.institutionPerformanceChartInstance) {
				this.institutionPerformanceChartInstance.destroy();
			}

			this.institutionPerformanceChartInstance = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: this.institutionChartData.map(d => d.name),
					datasets: [
						{
							label: 'Preliminary Marks',
							data: this.institutionChartData.map(d => d.preliminaryMarks),
							backgroundColor: 'rgba(59, 130, 246, 0.8)',
							borderColor: 'rgba(59, 130, 246, 1)',
							borderWidth: 1,
							borderRadius: 4
						},
						{
							label: 'Presentation Marks',
							data: this.institutionChartData.map(d => d.presentationMarks),
							backgroundColor: 'rgba(16, 185, 129, 0.8)',
							borderColor: 'rgba(16, 185, 129, 1)',
							borderWidth: 1,
							borderRadius: 4
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'top',
						},
						tooltip: {
							callbacks: {
								title: (context) => {
									const index = context[0].dataIndex;
									return this.institutionChartData[index].fullName;
								}
							}
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							title: {
								display: true,
								text: 'Marks'
							}
						},
						x: {
							title: {
								display: true,
								text: 'Institutions'
							}
						}
					}
				}
			});
		},

		prepareInstitutionRankings(institutionPerformance) {
			// Prepare institution rankings sorted by aggregated marks (preliminary + presentation)
			this.institutionRankings = institutionPerformance.map(inst => {
				const preliminaryMarks = parseFloat(inst.preliminary_marks) || 0;
				const presentationMarks = parseFloat(inst.presentation_marks) || 0;
				const aggregatedMarks = preliminaryMarks + presentationMarks;
				
				return {
					id: inst.id,
					name: inst.name || 'Unknown',
					image: inst.image || inst.logo || null,
					preliminaryMarks,
					presentationMarks,
					aggregatedMarks
				};
			}).sort((a, b) => b.aggregatedMarks - a.aggregatedMarks);

			// Calculate max for progress bar
			if (this.institutionRankings.length > 0) {
				this.maxAggregatedMarks = Math.max(...this.institutionRankings.map(i => i.aggregatedMarks), 100);
			}
		},

		prepareInstitutionRankingsFromInstitutions(institutions) {
			// Fallback: prepare rankings from institutions with awards
			this.institutionRankings = institutions.map(inst => {
				let preliminaryMarks = 0;
				let presentationMarks = 0;

				if (inst.awards && Array.isArray(inst.awards)) {
					inst.awards.forEach(award => {
						preliminaryMarks += parseInt(award.marks) || parseInt(award.preliminary_marks) || 0;
						presentationMarks += parseInt(award.presentation_marks) || 0;
					});
				}

			return {
					id: inst.id,
					name: inst.name || 'Unknown',
					image: inst.image || inst.logo || null,
					preliminaryMarks,
					presentationMarks,
					aggregatedMarks: preliminaryMarks + presentationMarks
				};
			}).sort((a, b) => b.aggregatedMarks - a.aggregatedMarks);

			// Calculate max for progress bar
			if (this.institutionRankings.length > 0) {
				this.maxAggregatedMarks = Math.max(...this.institutionRankings.map(i => i.aggregatedMarks), 100);
			}
		},

		getRankClass(index) {
			if (index === 0) return 'rank-gold';
			if (index === 1) return 'rank-silver';
			if (index === 2) return 'rank-bronze';
			return 'rank-default';
		},

		getMarksPercent(marks) {
			return Math.min(100, (marks / this.maxAggregatedMarks) * 100);
		},

		getProgressColor(index) {
			const colors = ['#ffd700', '#c0c0c0', '#cd7f32', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];
			return colors[index] || '#6b7280';
		},

		getInstitutionImageUrl(image) {
			// Default institution icon SVG as data URI
			const defaultIcon = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23e5e7eb' width='100' height='100'/%3E%3Cpath fill='%239ca3af' d='M50 20L20 35v5h60v-5L50 20zM25 45v30h10V55h10v20h10V55h10v20h10V45H25zM15 80h70v5H15v-5z'/%3E%3C/svg%3E";
			
			if (!image) return defaultIcon;
			
			// If already a full URL, fix old URL format if needed
			if (image.startsWith('http://') || image.startsWith('https://')) {
				// Convert old /backend/uploads/ format to new /api/uploads/ format
				return image.replace('/backend/uploads/', '/api/uploads/');
			}
			
			if (image.startsWith('data:') || image.startsWith('/')) {
				return image;
			}
			
			// Otherwise, construct the API URL
			return `${process.env.VUE_APP_API_URL || 'http://localhost:8000'}/api/uploads/institutions/${image}`;
		},

		handleImageError(event) {
			// Set default icon on image load error
			event.target.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23e5e7eb' width='100' height='100'/%3E%3Cpath fill='%239ca3af' d='M50 20L20 35v5h60v-5L50 20zM25 45v30h10V55h10v20h10V55h10v20h10V45H25zM15 80h70v5H15v-5z'/%3E%3C/svg%3E";
			event.target.onerror = null; // Prevent infinite loop
		},

		navigateTo(route) {
			this.$router.push(route);
		}
		}
}
</script>

<style lang="scss" scoped>
.admin-dashboard {
.page-header {
	margin-bottom: 24px;
	
	h1 {
		margin: 0;
			font-size: 28px;
			font-weight: 700;
		color: #1f2937;
	}
	
	p {
		margin: 4px 0 0 0;
		color: #6b7280;
		font-size: 14px;
		}
	}

	.loading-container {
		display: flex;
		justify-content: center;
		align-items: center;
		min-height: 400px;
	}

	.stat-card {
		background: #ffffff;
		border-radius: 12px;
		padding: 24px;
		display: flex;
		align-items: center;
		gap: 16px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		transition: transform 0.2s ease, box-shadow 0.2s ease;

		&:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		}

		.stat-icon {
			width: 56px;
			height: 56px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
		}

		.stat-content {
			flex: 1;

			.stat-value {
				font-size: 28px;
				font-weight: 700;
				color: #1f2937;
				line-height: 1.2;
			}

			.stat-label {
				font-size: 14px;
				color: #6b7280;
				margin-top: 4px;
			}
		}

		&.stat-card-blue .stat-icon {
			background: linear-gradient(135deg, #3b82f6, #2563eb);
		}

		&.stat-card-green .stat-icon {
			background: linear-gradient(135deg, #10b981, #059669);
		}

		&.stat-card-purple .stat-icon {
			background: linear-gradient(135deg, #8b5cf6, #7c3aed);
		}

		&.stat-card-orange .stat-icon {
			background: linear-gradient(135deg, #f59e0b, #d97706);
		}
	}

	.chart-card {
		.chart-container {
			height: 300px;
			position: relative;
		}

		.empty-chart-container {
			height: 300px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
			border-radius: 8px;
			border: 2px dashed #e5e7eb;
		}
	}

	.h-100 {
		height: 100%;
	}

	.mb-24 {
		margin-bottom: 24px;
	}

	.institution-name {
		max-width: 200px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.action-link {
		color: #1f2937;
		font-weight: 600;
		cursor: pointer;

		&:hover {
			color: #3b82f6;
		}
	}

	.action-icon {
		width: 40px;
		height: 40px;
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	// Institution Rankings Styles
	.institution-rankings-card {
		.rankings-list {
			max-height: 400px;
			overflow-y: auto;
		}

		.ranking-item {
			display: flex;
			align-items: center;
			padding: 12px 0;
			border-bottom: 1px solid #f3f4f6;
			gap: 12px;

			&:last-child {
				border-bottom: none;
			}

			&:hover {
				background-color: #f9fafb;
				margin: 0 -12px;
				padding: 12px;
				border-radius: 8px;
			}
		}

		.rank-badge {
			width: 28px;
			height: 28px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 700;
			font-size: 12px;
			flex-shrink: 0;

			&.rank-gold {
				background: linear-gradient(135deg, #ffd700, #ffb700);
				color: #7c5800;
				box-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
			}

			&.rank-silver {
				background: linear-gradient(135deg, #e8e8e8, #c0c0c0);
				color: #555;
				box-shadow: 0 2px 8px rgba(192, 192, 192, 0.4);
			}

			&.rank-bronze {
				background: linear-gradient(135deg, #cd7f32, #b8722d);
				color: #fff;
				box-shadow: 0 2px 8px rgba(205, 127, 50, 0.4);
			}

			&.rank-default {
				background: #f3f4f6;
				color: #6b7280;
			}
		}

		.institution-image {
			flex-shrink: 0;

			.inst-img {
				width: 48px;
				height: 48px;
				border-radius: 8px;
				object-fit: contain;
				border: 1px solid #e5e7eb;
				background: #ffffff;
				padding: 4px;
			}
		}

		.institution-info {
			flex: 1;
			min-width: 0;

			.institution-name {
				font-weight: 600;
				color: #1f2937;
				font-size: 14px;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
				max-width: 100%;
			}

			.institution-marks {
				display: flex;
				align-items: center;
				gap: 6px;
				margin-top: 4px;

				.marks-label {
					font-size: 12px;
					color: #9ca3af;
				}

				.marks-value {
					font-size: 14px;
					font-weight: 700;
					color: #10b981;
				}
			}
		}

		.marks-bar {
			width: 60px;
			flex-shrink: 0;
		}
	}
}
</style>
