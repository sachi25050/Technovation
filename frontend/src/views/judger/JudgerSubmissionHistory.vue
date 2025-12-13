<!-- 
	Judger Submission History - LankaPay Technnovation Awards Judging System UI
	Display all submission history with edit functionality
 -->

<template>
	<div class="submission-history">
		<!-- Main Content -->
		<div class="main-content">
			<div class="content-container">
				<!-- Header Section -->
				<div class="header-section">
					<h2 class="page-title">Your Submission History</h2>
				</div>

				<!-- Filter Section -->
				<div class="filter-section">
					<div class="filter-content">
						<div class="filter-row">
							<label>Award Category Filter</label>
							<a-select 
								v-model="categoryFilter" 
								placeholder="Select Award Category"
								class="filter-select award-filter-full"
								@change="filterSummary"
								:loading="awardsLoading"
							>
								<a-select-option value="">All Categories</a-select-option>
								<a-select-option 
									v-for="award in awards" 
									:key="award.id" 
									:value="award.id"
								>
									{{ award.category }}
								</a-select-option>
							</a-select>
						</div>
						<div class="filter-row">
							<label>Institute Filter</label>
							<a-select 
								v-model="instituteFilter" 
								placeholder="Select Institute"
								class="filter-select institute-filter-full"
								@change="filterSummary"
								:loading="institutionsLoading"
							>
								<a-select-option value="">All Institutes</a-select-option>
								<a-select-option 
									v-for="institution in institutions" 
									:key="institution.id" 
									:value="institution.id"
								>
									{{ institution.name }}
								</a-select-option>
							</a-select>
						</div>
					</div>
				</div>

				<!-- Summary Section (Submission History) -->
				<div class="summary-section">
					<div class="summary-header">
						<h3>{{ judgeName }} - Submission History</h3>
					</div>
					<div class="modern-table-wrapper">
						<div class="table-container">
							<table class="modern-evaluation-table">
								<colgroup>
									<col class="col-checkbox" />
									<col class="col-institute" />
									<col class="col-award" />
									<col v-for="n in 10" :key="'col-c'+n" class="col-criteria" />
									<col class="col-presentation" />
									<col class="col-overall" />
									<col class="col-actions" />
								</colgroup>
								<thead class="table-header">
									<tr>
										<th class="th-checkbox"></th>
										<th class="th-institute">Institute Name</th>
										<th class="th-award">Award Category</th>
										<th v-for="n in 10" :key="'th-c'+n" class="th-criteria">C-{{ String(n).padStart(2, '0') }}</th>
										<th class="th-presentation">Presentation</th>
										<th class="th-overall">Overall</th>
										<th class="th-actions">Actions</th>
									</tr>
								</thead>
								<tbody class="table-body">
									<tr 
										v-for="(entry, index) in filteredSummaryData" 
										:key="index"
										:class="{ 'selected-row': selectedRowIndex === index }"
										class="evaluation-row"
										:tabindex="0"
										:aria-selected="selectedRowIndex === index"
										@click="selectRow(index)"
										@keydown.space.prevent="selectRow(index)"
										@keydown.enter.prevent="selectRow(index)">
										<td class="td-checkbox">
											<input 
												type="checkbox"
												:checked="selectedRowIndex === index"
												@click.stop="selectRow(index)"
												@change="handleCheckboxChange(index)"
												class="evaluation-checkbox"
												:aria-label="`Select ${entry.institute} for evaluation`"
												:aria-describedby="`row-${index}-description`"
											/>
										</td>
										<td class="td-institute" :id="`row-${index}-description`">
											<span class="institute-name">{{ entry.institute }}</span>
										</td>
										<td class="td-award">
											<span class="award-text">{{ entry.award }}</span>
										</td>
										<td class="td-criteria">{{ entry.c1 || '-' }}</td>
										<td class="td-criteria">{{ entry.c2 || '-' }}</td>
										<td class="td-criteria">{{ entry.c3 || '-' }}</td>
										<td class="td-criteria">{{ entry.c4 || '-' }}</td>
										<td class="td-criteria">{{ entry.c5 || '-' }}</td>
										<td class="td-criteria">{{ entry.c6 || '-' }}</td>
										<td class="td-criteria">{{ entry.c7 || '-' }}</td>
										<td class="td-criteria">{{ entry.c8 || '-' }}</td>
										<td class="td-criteria">{{ entry.c9 || '-' }}</td>
										<td class="td-criteria">{{ entry.c10 || '-' }}</td>
										<td class="td-presentation">{{ entry.presentation }}</td>
										<td class="td-overall">
											<div class="overall-score-container" :data-score="getScoreRange(entry.overall)">
												<span class="overall-score-value">{{ entry.overall }}</span>
												<div class="tiny-progress-bar">
													<div class="progress-fill" :style="{ width: (entry.overall / 100 * 100) + '%' }"></div>
												</div>
											</div>
										</td>
										<td class="td-actions">
											<a-button 
												type="primary" 
												size="small" 
												@click.stop="viewDetails(entry)"
												class="action-btn view-btn"
											>
												View
											</a-button>
											<a-button 
												type="default" 
												size="small" 
												@click.stop="editSubmission(entry)"
												class="action-btn edit-btn"
											>
												Edit
											</a-button>
											<a-button 
												type="danger" 
												size="small" 
												@click.stop="deleteSubmission(entry)"
												class="action-btn delete-btn"
											>
												Delete
											</a-button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- View Details Modal -->
				<a-modal
					v-model:visible="viewModalVisible"
					title="Submission Details"
					:footer="null"
					width="800px"
					class="details-modal"
				>
					<div v-if="selectedSubmission" class="submission-details">
						<div class="detail-section">
							<h4>General Information</h4>
							<div class="detail-grid">
								<div class="detail-item">
									<label>Institute:</label>
									<span>{{ selectedSubmission.institute }}</span>
								</div>
								<div class="detail-item">
									<label>Award Category:</label>
									<span>{{ selectedSubmission.award }}</span>
								</div>
								<div class="detail-item">
									<label>Submitted Date:</label>
									<span>{{ selectedSubmission.submittedDate || '2024-01-15' }}</span>
								</div>
							</div>
						</div>

						<div class="detail-section">
							<h4>Evaluation Scores</h4>
							<div class="scores-table">
								<table>
									<thead>
										<tr>
											<th>Criteria</th>
											<th>Allocated</th>
											<th>Achieved</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Innovation & Creativity</td>
											<td>20%</td>
											<td>{{ selectedSubmission.c1 }}%</td>
										</tr>
										<tr>
											<td>Technical Excellence</td>
											<td>25%</td>
											<td>{{ selectedSubmission.c2 }}%</td>
										</tr>
										<tr>
											<td>Impact & Relevance</td>
											<td>25%</td>
											<td>{{ selectedSubmission.c3 }}%</td>
										</tr>
										<tr>
											<td>Presentation & Documentation</td>
											<td>20%</td>
											<td>{{ selectedSubmission.c4 }}%</td>
										</tr>
										<tr>
											<td>Overall Performance</td>
											<td>10%</td>
											<td>{{ selectedSubmission.c5 }}%</td>
										</tr>
										<tr class="total-row">
											<td><strong>Total</strong></td>
											<td><strong>100%</strong></td>
											<td><strong>{{ selectedSubmission.overall }}%</strong></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</a-modal>
			</div>
		</div>
	</div>
</template>

<script>
import apiService from '@/services/api'

export default {
	name: 'JudgerSubmissionHistory',
	data() {
		return {
			categoryFilter: '',
			instituteFilter: '',
			viewModalVisible: false,
			selectedSubmission: null,
			selectedRowIndex: null,
			
			// Institutions and Awards data from API
			institutions: [],
			institutionsLoading: false,
			awards: [],
			awardsLoading: false,
			
			// Current user info
			currentUser: {
				name: 'Judge',
				title: null,
				first_name: null,
				last_name: null
			},
			
			// Summary data - will be loaded from API
			summaryData: []
		}
	},
	computed: {
		filteredSummaryData() {
			let filtered = this.summaryData;
			
			if (this.categoryFilter) {
				// Filter by award ID or name
				const awardName = this.getAwardName(this.categoryFilter);
				filtered = filtered.filter(entry => 
					entry.award_id === this.categoryFilter || 
					entry.award.toLowerCase().includes(awardName.toLowerCase())
				);
			}
			
			if (this.instituteFilter) {
				// Filter by institution ID or name
				const instituteName = this.getInstitutionName(this.instituteFilter);
				filtered = filtered.filter(entry => 
					entry.institution_id === this.instituteFilter ||
					entry.institute.toLowerCase().includes(instituteName.toLowerCase())
				);
			}
			
			return filtered;
		},
		judgeName() {
			// Format: "Mr. First Last" (title + first_name + last_name)
			const parts = [];
			
			if (this.currentUser.title && this.currentUser.title.trim()) {
				let title = this.currentUser.title.trim();
				if (!title.endsWith('.')) {
					title = title + '.';
				}
				parts.push(title);
			}
			
			if (this.currentUser.first_name && this.currentUser.first_name.trim()) {
				parts.push(this.currentUser.first_name.trim());
			}
			
			if (this.currentUser.last_name && this.currentUser.last_name.trim()) {
				parts.push(this.currentUser.last_name.trim());
			}
			
			if (parts.length === 0) {
				return this.currentUser.name || 'Judge';
			}
			
			return parts.join(' ');
		}
	},
	methods: {
		async loadInstitutions() {
			this.institutionsLoading = true;
			try {
				const response = await apiService.get('/judger/institutions');
				if (response.success && response.data) {
					this.institutions = Array.isArray(response.data) ? response.data : [];
				} else {
					this.institutions = [];
					console.error('Failed to load institutions:', response);
				}
			} catch (error) {
				console.error('Error loading institutions:', error);
				this.institutions = [];
				this.$message.error('Failed to load institutions');
			} finally {
				this.institutionsLoading = false;
			}
		},
		async loadAwards() {
			this.awardsLoading = true;
			try {
				// Load all awards (no institution filter for the filter dropdown)
				const response = await apiService.get('/judger/awards');
				if (response.success && response.data) {
					this.awards = Array.isArray(response.data) ? response.data : [];
				} else {
					this.awards = [];
					console.error('Failed to load awards:', response);
				}
			} catch (error) {
				console.error('Error loading awards:', error);
				this.awards = [];
				this.$message.error('Failed to load award categories');
			} finally {
				this.awardsLoading = false;
			}
		},
		async loadEvaluations() {
			try {
				const response = await apiService.get('/judger/evaluations', { limit: 100 });
				if (response.success && response.data && response.data.data) {
					// Map API data to summary format - supports up to 10 criteria
					this.summaryData = response.data.data.map(evaluation => ({
						id: evaluation.id,
						institution_id: evaluation.institution_id,
						award_id: evaluation.award_id,
						institute: evaluation.institution_name,
						award: evaluation.award_category,
						c1: evaluation.criteria_1_marks || null,
						c2: evaluation.criteria_2_marks || null,
						c3: evaluation.criteria_3_marks || null,
						c4: evaluation.criteria_4_marks || null,
						c5: evaluation.criteria_5_marks || null,
						c6: evaluation.criteria_6_marks || null,
						c7: evaluation.criteria_7_marks || null,
						c8: evaluation.criteria_8_marks || null,
						c9: evaluation.criteria_9_marks || null,
						c10: evaluation.criteria_10_marks || null,
						presentation: evaluation.total_achieved_marks || evaluation.total_marks || 0,
						overall: evaluation.aggregated_score || evaluation.total_marks || 0,
						submittedDate: evaluation.created_at || evaluation.submitted_at
					}));
				}
			} catch (error) {
				console.error('Error loading evaluations:', error);
				this.$message.error('Failed to load submission history');
			}
		},
		getInstitutionName(id) {
			if (typeof id === 'number' || !isNaN(id)) {
				const institution = this.institutions.find(inst => inst.id == id);
				return institution ? institution.name : id;
			}
			return id;
		},
		getAwardName(id) {
			if (typeof id === 'number' || !isNaN(id)) {
				const award = this.awards.find(a => a.id == id);
				return award ? award.category : id;
			}
			return id;
		},
		selectRow(index) {
			// Toggle selection - if clicking the same row, deselect it
			if (this.selectedRowIndex === index) {
				this.selectedRowIndex = null;
			} else {
				this.selectedRowIndex = index;
			}
		},
		handleCheckboxChange(index) {
			// Sync checkbox state with row selection
			if (this.selectedRowIndex === index) {
				this.selectedRowIndex = null;
			} else {
				this.selectedRowIndex = index;
			}
		},
		getScoreRange(score) {
			// Determine score range for color coding
			if (score >= 90) return '90-100';
			if (score >= 80) return '80-89';
			if (score >= 70) return '70-79';
			if (score >= 60) return '60-69';
			return '0-59';
		},
		viewDetails(submission) {
			this.selectedSubmission = submission;
			this.viewModalVisible = true;
		},
		editSubmission(submission) {
			this.$confirm({
				title: 'Edit Submission',
				content: `Are you sure you want to edit the submission for ${submission.institute} - ${submission.award}?`,
				okText: 'Edit',
				cancelText: 'Cancel',
				onOk: () => {
					// Navigate to edit page with submission data
					this.$router.push({
						path: '/judger/evaluate',
						query: { 
							edit: 'true',
							id: submission.id,
							institution_id: submission.institution_id,
							award_id: submission.award_id
						}
					});
				}
			});
		},
		deleteSubmission(submission) {
			this.$confirm({
				title: 'Delete Submission',
				content: `Are you sure you want to delete the submission for ${submission.institute} - ${submission.award}? This action cannot be undone.`,
				okText: 'Yes, Delete',
				okType: 'danger',
				cancelText: 'Cancel',
				onOk: async () => {
					try {
						if (submission.id) {
							const response = await apiService.delete(`/judger/evaluations/${submission.id}`);
							
							if (!response.success) {
								this.$message.error(response.message || 'Failed to delete submission');
								return;
							}
						}
						
						// Remove from local summaryData
						const index = this.summaryData.findIndex(
							item => (item.id && item.id === submission.id) || 
							(item.institute === submission.institute && item.award === submission.award)
						);
						if (index !== -1) {
							this.summaryData.splice(index, 1);
						}
						
						this.selectedRowIndex = null;
						this.$message.success('Submission deleted successfully');
					} catch (error) {
						console.error('Error deleting submission:', error);
						this.$message.error('Failed to delete submission. Please try again.');
					}
				}
			});
		},
		filterSummary() {
			// Filter is handled by computed property
		}
	},
	mounted() {
		// Get current user info
		const user = apiService.getCurrentUser();
		if (user) {
			this.currentUser = {
				name: user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.username || 'Judge',
				title: user.title || null,
				first_name: user.first_name || null,
				last_name: user.last_name || null
			};
		}
		
		// Load data from API
		this.loadInstitutions();
		this.loadAwards();
		this.loadEvaluations();
	}
}
</script>

<style lang="scss">
// Submission History Styles - Matching Original Design
.submission-history {
	font-family: 'Roboto', 'Open Sans', 'Inter', sans-serif;
	background-color: #f8f9fa;
	min-height: 100vh;
	color: #2C3E50;
	width: 100%;
	max-width: 100%;
	overflow-x: hidden;
	box-sizing: border-box;
	
	* {
		box-sizing: border-box;
	}
}

// Main Content - Compact Responsive
.main-content {
	padding: 8px;
	min-height: calc(100vh - 120px);
	width: 100%;
	max-width: 100%;
	box-sizing: border-box;
	overflow-x: hidden;
	
	@media (min-width: 768px) {
		padding: 12px;
	}
	
	@media (min-width: 1024px) {
		padding: 16px;
	}
	
	@media (min-width: 1200px) {
		padding: 20px;
	}
}

.content-container {
	max-width: 100%;
	width: 100%;
	margin: 0 auto;
	display: flex;
	flex-direction: column;
	gap: 12px;
	box-sizing: border-box;
	
	@media (min-width: 768px) {
		gap: 16px;
	}
	
	@media (min-width: 1024px) {
		gap: 20px;
	}
}

// Header Section - Compact
.header-section {
	background: linear-gradient(135deg, #D6EAF8 0%, #AED6F1 100%);
	border-radius: 6px;
	padding: 12px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	text-align: center;
	
	@media (min-width: 768px) {
		border-radius: 8px;
		padding: 16px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
	}
	
	@media (min-width: 1024px) {
		border-radius: 10px;
		padding: 20px;
	}
}

.page-title {
	font-size: 14px;
	font-weight: 700;
	color: #2C3E50;
	margin: 0;
	
	@media (min-width: 768px) {
		font-size: 16px;
	}
}

// Filter Section - Compact
.filter-section {
	background: white;
	border-radius: 6px;
	padding: 12px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	
	@media (min-width: 768px) {
		border-radius: 8px;
		padding: 16px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
	}
	
	@media (min-width: 1024px) {
		border-radius: 10px;
		padding: 18px;
	}
}

.filter-content {
	display: flex;
	flex-direction: column;
	gap: 8px;
	
	@media (min-width: 768px) {
		gap: 12px;
	}
}

.filter-row {
	display: flex;
	align-items: center;
	gap: 8px;
	
	@media (min-width: 768px) {
		gap: 12px;
	}
	
	@media (max-width: 767px) {
		flex-direction: column;
		align-items: flex-start;
		gap: 6px;
	}
}

.filter-row label {
	font-weight: 600;
	color: #2C3E50;
	font-size: 12px;
	min-width: 120px;
}

.filter-select {
	min-width: 300px;
	flex: 1;
}

// Full width filter dropdowns
.award-filter-full {
	flex: 1;
	width: 100%;
}

.institute-filter-full {
	flex: 1;
	width: 100%;
}

// Summary Section (Submission History) - Compact
.summary-section {
	background: white;
	border-radius: 6px;
	padding: 12px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	
	@media (min-width: 768px) {
		border-radius: 8px;
		padding: 16px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
	}
	
	@media (min-width: 1024px) {
		border-radius: 10px;
		padding: 20px;
	}
}

.summary-header {
	margin-bottom: 12px;
	
	@media (min-width: 768px) {
		margin-bottom: 16px;
	}
	
	@media (min-width: 1024px) {
		margin-bottom: 20px;
	}
	
	h3 {
		font-size: 14px;
		font-weight: 700;
		color: #2C3E50;
		margin: 0;
		
		@media (min-width: 768px) {
			font-size: 16px;
		}
	}
}

// Modern Evaluation Table - Page-Fitted Responsive
.modern-table-wrapper {
	width: 100%;
	max-width: 100%;
	background: white;
	border-radius: 12px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
	overflow: hidden;
	margin: 0;
	border: 1px solid #e2e8f0;
}

.table-container {
	overflow-x: auto;
	width: 100%;
	max-width: 100%;
	
	// Custom scrollbar styling
	&::-webkit-scrollbar {
		height: 8px;
	}
	
	&::-webkit-scrollbar-track {
		background: #f1f5f9;
		border-radius: 4px;
	}
	
	&::-webkit-scrollbar-thumb {
		background: #94a3b8;
		border-radius: 4px;
		
		&:hover {
			background: #64748b;
		}
	}
}

// Modern Evaluation Table - Fixed Layout for Alignment
.modern-evaluation-table {
	width: 100%;
	min-width: 1400px; // Enough for 10 criteria columns + other columns
	border-collapse: collapse;
	border-spacing: 0;
	background: white;
	font-family: 'Inter', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
	table-layout: fixed; // Fixed layout for consistent column widths
}

// Colgroup - Define fixed column widths
.col-checkbox { width: 40px; }
.col-institute { width: 140px; }
.col-award { width: 160px; }
.col-criteria { width: 50px; }
.col-presentation { width: 75px; }
.col-overall { width: 65px; }
.col-actions { width: 150px; }

// Table Header - Modern Gradient with fixed alignment
.table-header {
	background: linear-gradient(135deg, #E8F1FD 0%, #F0F7FF 100%);
	border-bottom: 2px solid #3b82f6;
	
	tr {
		height: 44px;
	}
}

// Header cells - consistent styling
.th-checkbox,
.th-institute,
.th-award,
.th-criteria,
.th-presentation,
.th-overall,
.th-actions {
	padding: 10px 4px;
	text-align: center;
	font-weight: 700;
	font-size: 12px;
	color: #1E293B;
	letter-spacing: 0.02em;
	border: none;
	white-space: nowrap;
	vertical-align: middle;
}

.th-checkbox { text-align: center; }
.th-institute { text-align: left; padding-left: 12px; }
.th-award { text-align: left; padding-left: 8px; }
.th-criteria { text-align: center; font-size: 11px; }
.th-presentation { text-align: center; font-size: 11px; }
.th-overall { text-align: center; }
.th-actions { text-align: center; }

// Table Body
.table-body {
	background: white;
}

// Evaluation Rows - Interactive and Accessible
.evaluation-row {
	background: white;
	transition: background 0.15s ease;
	border-bottom: 1px solid #f1f5f9;
	cursor: pointer;
	height: 48px;
	
	&:hover {
		background: #F5F9FF;
	}
	
	&.selected-row {
		background: rgba(37, 99, 235, 0.1) !important;
		border-left: 4px solid #2563EB;
		
		.institute-name {
			color: #1E40AF;
			font-weight: 700;
		}
		
		.award-text {
			color: #1E40AF;
			font-weight: 600;
		}
		
		.td-criteria, .td-presentation, .td-overall {
			color: #1E40AF;
			font-weight: 700;
		}
	}
	
	&:focus {
		outline: 2px solid #2563EB;
		outline-offset: -2px;
	}
	
	&:last-child {
		border-bottom: none;
	}
}

// Data cells - consistent styling matching headers
.td-checkbox,
.td-institute,
.td-award,
.td-criteria,
.td-presentation,
.td-overall,
.td-actions {
	padding: 8px 4px;
	border: none;
	vertical-align: middle;
	font-size: 13px;
}

.td-checkbox {
	text-align: center;
	padding: 8px 4px;
}

.td-institute {
	text-align: left;
	padding-left: 12px;
	
	.institute-name {
		font-weight: 600;
		font-size: 13px;
		color: #1E293B;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		display: block;
	}
}

.td-award {
	text-align: left;
	padding-left: 8px;
	
	.award-text {
		font-weight: 500;
		font-size: 12px;
		color: #475569;
		line-height: 1.3;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		display: block;
	}
}

.td-criteria {
	text-align: center;
	font-weight: 600;
	font-size: 13px;
	color: #1E293B;
}

.td-presentation {
	text-align: center;
	font-weight: 600;
	font-size: 13px;
	color: #1E293B;
}

.td-overall {
	text-align: center;
	font-weight: 700;
	font-size: 13px;
	color: #2563EB;
}

.td-actions {
	text-align: center;
	padding: 6px 4px;
	white-space: nowrap;
}

.action-btn {
	margin: 0 2px;
	font-size: 10px;
	padding: 2px 6px;
	height: 24px;
	line-height: 1;
	
	&.view-btn {
		background: #3B82F6;
		border-color: #3B82F6;
		color: white;
		
		&:hover {
			background: #2563EB;
			border-color: #2563EB;
		}
	}
	
	&.edit-btn {
		background: #10B981;
		border-color: #10B981;
		color: white;
		
		&:hover {
			background: #059669;
			border-color: #059669;
		}
	}
	
	&.delete-btn {
		background: #EF4444;
		border-color: #EF4444;
		color: white;
		
		&:hover {
			background: #DC2626;
			border-color: #DC2626;
		}
	}
}

// Overall Score Container with Progress Bar
.overall-score-container {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 4px;
}

.overall-score-value {
	font-weight: 700;
	font-size: 11px;
	color: #2563EB;
	line-height: 1;
}

// Tiny Progress Bar
.tiny-progress-bar {
	width: 40px;
	height: 4px;
	background: #E2E8F0;
	border-radius: 2px;
	overflow: hidden;
	position: relative;
	box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.progress-fill {
	height: 100%;
	background: linear-gradient(90deg, #3B82F6 0%, #2563EB 50%, #1D4ED8 100%);
	border-radius: 2px;
	transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
	position: relative;
	
	// Add a subtle shine effect
	&::after {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 50%;
		background: linear-gradient(180deg, rgba(255, 255, 255, 0.3) 0%, transparent 100%);
		border-radius: 2px 2px 0 0;
	}
}

// Progress bar color variations based on score
.overall-score-container {
	&[data-score="90-100"] .progress-fill {
		background: linear-gradient(90deg, #10B981 0%, #059669 50%, #047857 100%);
	}
	
	&[data-score="80-89"] .progress-fill {
		background: linear-gradient(90deg, #3B82F6 0%, #2563EB 50%, #1D4ED8 100%);
	}
	
	&[data-score="70-79"] .progress-fill {
		background: linear-gradient(90deg, #F59E0B 0%, #D97706 50%, #B45309 100%);
	}
	
	&[data-score="60-69"] .progress-fill {
		background: linear-gradient(90deg, #EF4444 0%, #DC2626 50%, #B91C1C 100%);
	}
	
	&[data-score="0-59"] .progress-fill {
		background: linear-gradient(90deg, #6B7280 0%, #4B5563 50%, #374151 100%);
	}
}

// Modern Evaluation Checkbox - Reduced Size
.evaluation-checkbox {
	position: relative;
	width: 16px;
	height: 16px;
	margin: 0;
	cursor: pointer;
	appearance: none;
	border: 2px solid #CBD5E1;
	border-radius: 3px;
	background: white;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	
	&:hover {
		border-color: #2563EB;
		box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
		transform: scale(1.05);
	}
	
	&:focus {
		outline: 2px solid #2563EB;
		outline-offset: 2px;
	}
	
	&:checked {
		background: #2563EB;
		border-color: #2563EB;
		box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
		
		&::after {
			content: '';
			position: absolute;
			top: 1px;
			left: 4px;
			width: 4px;
			height: 8px;
			border: 2px solid white;
			border-top: none;
			border-left: none;
			transform: rotate(45deg);
			animation: checkmark 0.2s cubic-bezier(0.4, 0, 0.2, 1);
		}
	}
	
	&:checked:hover {
		transform: scale(1.05);
		box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
	}
}

// Checkmark Animation
@keyframes checkmark {
	0% {
		opacity: 0;
		transform: rotate(45deg) scale(0.5);
	}
	50% {
		opacity: 0.5;
		transform: rotate(45deg) scale(0.8);
	}
	100% {
		opacity: 1;
		transform: rotate(45deg) scale(1);
	}
}

// Details Modal
.details-modal {
	.submission-details {
		.detail-section {
			margin-bottom: 24px;
			
			h4 {
				font-size: 16px;
				font-weight: 700;
				color: #2C3E50;
				margin: 0 0 16px 0;
				border-bottom: 2px solid #E2E8F0;
				padding-bottom: 8px;
			}
		}
		
		.detail-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 16px;
		}
		
		.detail-item {
			display: flex;
			flex-direction: column;
			gap: 4px;
			
			label {
				font-weight: 600;
				color: #64748B;
				font-size: 12px;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}
			
			span {
				font-size: 14px;
				color: #1E293B;
			}
		}
		
		.scores-table {
			table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 8px;
			}
			
			th, td {
				padding: 12px;
				text-align: left;
				border-bottom: 1px solid #E2E8F0;
			}
			
			th {
				background: #F8FAFC;
				font-weight: 600;
				color: #374151;
			}
			
			.total-row {
				background: #F1F5F9;
				font-weight: 700;
				
				td {
					border-top: 2px solid #CBD5E1;
				}
			}
		}
	}
}

// Responsive Design - Enhanced Breakpoints
@media (max-width: 1400px) {
	.modern-evaluation-table {
		min-width: 850px;
	}
	
	.institute-cell {
		min-width: 170px;
	}
	
	.award-cell {
		min-width: 230px;
	}
	
	.criteria-cell, .presentation-cell, .overall-cell {
		min-width: 68px;
	}
}

@media (max-width: 1200px) {
	.modern-evaluation-table {
		min-width: 800px;
	}
	
	.institute-cell {
		min-width: 160px;
	}
	
	.award-cell {
		min-width: 220px;
	}
	
	.criteria-cell, .presentation-cell, .overall-cell {
		min-width: 65px;
	}
}

@media (max-width: 1024px) {
	.modern-evaluation-table {
		min-width: 750px;
	}
	
	.modern-evaluation-table td,
	.table-header th {
		padding: 8px 6px;
		font-size: 10px;
	}
	
	.institute-cell {
		min-width: 150px;
	}
	
	.award-cell {
		min-width: 200px;
	}
	
	.criteria-cell, .presentation-cell, .overall-cell {
		min-width: 60px;
	}
	
	// Responsive progress bars
	.tiny-progress-bar {
		width: 25px;
		height: 3px;
	}
	
	.overall-score-value {
		font-size: 11px;
	}
}

@media (max-width: 768px) {
	.modern-table-wrapper {
		margin: 0;
		border-radius: 8px;
	}
	
	.modern-evaluation-table {
		min-width: 700px;
	}
	
	.modern-evaluation-table td,
	.table-header th {
		padding: 10px 6px;
		font-size: 11px;
	}
	
	.institute-cell {
		min-width: 140px;
	}
	
	.award-cell {
		min-width: 180px;
	}
	
	.criteria-cell, .presentation-cell, .overall-cell {
		min-width: 55px;
	}
	
	// Mobile progress bars
	.tiny-progress-bar {
		width: 30px;
		height: 3px;
	}
	
	.overall-score-value {
		font-size: 10px;
	}
}

@media (max-width: 600px) {
	.modern-table-wrapper {
		margin: 0 -4px;
		border-radius: 6px;
	}
	
	.modern-evaluation-table {
		min-width: 600px;
	}
	
	.modern-evaluation-table td,
	.table-header th {
		padding: 8px 4px;
		font-size: 10px;
	}
	
	.institute-cell {
		min-width: 100px;
	}
	
	.award-cell {
		min-width: 140px;
	}
	
	.criteria-cell, .presentation-cell, .overall-cell {
		min-width: 45px;
	}
	
	// Small mobile progress bars
	.tiny-progress-bar {
		width: 20px;
		height: 2px;
	}
	
	.overall-score-value {
		font-size: 8px;
	}
}

@media (max-width: 480px) {
	.modern-table-wrapper {
		margin: 0 -8px;
		border-radius: 0;
	}
	
	.modern-evaluation-table {
		min-width: 550px;
	}
	
	.modern-evaluation-table td,
	.table-header th {
		padding: 6px 3px;
		font-size: 9px;
	}
	
	.institute-cell {
		min-width: 90px;
	}
	
	.award-cell {
		min-width: 120px;
	}
	
	.criteria-cell, .presentation-cell, .overall-cell {
		min-width: 40px;
	}
	
	// Extra small mobile progress bars
	.tiny-progress-bar {
		width: 18px;
		height: 1px;
	}
	
	.overall-score-value {
		font-size: 7px;
	}
}

// Enhanced Responsive Behavior
@media (max-width: 1400px) {
	.content-container {
		padding: 0 8px;
	}
}

@media (max-width: 1200px) {
	.content-container {
		padding: 0 12px;
	}
}

@media (max-width: 1024px) {
	.content-container {
		padding: 0 16px;
	}
	
	.main-content {
		padding: 16px;
	}
}

@media (max-width: 768px) {
	.header-section,
	.filter-section,
	.summary-section {
		padding: 12px;
	}
}

@media (max-width: 600px) {
	.header-section,
	.filter-section,
	.summary-section {
		padding: 8px;
	}
}

@media (max-width: 480px) {
	.header-section,
	.filter-section,
	.summary-section {
		padding: 6px;
	}
}

// Select dropdown active highlight
.ant-select-focused .ant-select-selector {
	border-color: #ABEBC6 !important;
	box-shadow: 0 0 0 2px rgba(171, 235, 198, 0.2) !important;
}

.ant-select-item-option-selected {
	background-color: #ABEBC6 !important;
}

// Form field focus states
.ant-input:focus,
.ant-input-number:focus {
	border-color: #ABEBC6 !important;
	box-shadow: 0 0 0 2px rgba(171, 235, 198, 0.2) !important;
}

// Accessibility improvements
.ant-select,
.ant-input,
.ant-input-number {
	&:focus {
		outline: 2px solid #ABEBC6;
		outline-offset: 2px;
	}
}

// High contrast text
.submission-history {
	* {
		color: #2C3E50;
	}
}

// Button focus states
.ant-btn:focus {
	outline: 2px solid #ABEBC6;
	outline-offset: 2px;
}
</style>