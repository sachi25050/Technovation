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
					<h2 class="page-title">Submission History</h2>
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
							>
								<a-select-option value="">All Categories</a-select-option>
								<a-select-option value="award-14">Award No. 14 - Financial Institution of the Year</a-select-option>
								<a-select-option value="award-6a">Award No. 6A - Most Popular Digital Payment Product - State Banks</a-select-option>
								<a-select-option value="award-6b">Award No. 6B - Most Popular Digital Payment Product - Private Banks</a-select-option>
								<a-select-option value="award-7">Award No. 7 - Best Digital Payment Innovation</a-select-option>
								<a-select-option value="award-8">Award No. 8 - Best Digital Payment Security</a-select-option>
							</a-select>
						</div>
						<div class="filter-row">
							<label>Institute Filter</label>
							<a-select 
								v-model="instituteFilter" 
								placeholder="Select Institute"
								class="filter-select institute-filter-full"
								@change="filterSummary"
							>
								<a-select-option value="">All Institutes</a-select-option>
								<a-select-option value="Bank of Ceylon">Bank of Ceylon</a-select-option>
								<a-select-option value="Commercial Bank of Ceylon PLC">Commercial Bank of Ceylon PLC</a-select-option>
								<a-select-option value="People's Bank">People's Bank</a-select-option>
								<a-select-option value="Sampath Bank PLC">Sampath Bank PLC</a-select-option>
								<a-select-option value="Hatton National Bank PLC">Hatton National Bank PLC</a-select-option>
								<a-select-option value="NDB Bank">NDB Bank</a-select-option>
							</a-select>
						</div>
					</div>
				</div>

				<!-- Summary Section (Submission History) -->
				<div class="summary-section">
					<div class="summary-header">
						<h3>Mr. Asita D B Talwatta - Submission History</h3>
					</div>
					<div class="modern-table-wrapper">
						<div class="table-container">
							<table class="modern-evaluation-table">
								<thead class="table-header">
									<tr>
										<th class="checkbox-column"></th>
										<th class="institute-column">Institute Name</th>
										<th class="award-column">Award Category</th>
										<th class="criteria-column">C-01</th>
										<th class="criteria-column">C-02</th>
										<th class="criteria-column">C-03</th>
										<th class="criteria-column">C-04</th>
										<th class="criteria-column">C-05</th>
										<th class="presentation-column">Presentation</th>
										<th class="overall-column">Overall</th>
										<th class="actions-column">Actions</th>
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
										<td class="checkbox-cell">
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
										<td class="institute-cell" :id="`row-${index}-description`">
											<span class="institute-name">{{ entry.institute }}</span>
										</td>
										<td class="award-cell">
											<span class="award-text">{{ entry.award }}</span>
										</td>
										<td class="criteria-cell">{{ entry.c1 }}</td>
										<td class="criteria-cell">{{ entry.c2 }}</td>
										<td class="criteria-cell">{{ entry.c3 }}</td>
										<td class="criteria-cell">{{ entry.c4 }}</td>
										<td class="criteria-cell">{{ entry.c5 }}</td>
										<td class="presentation-cell">{{ entry.presentation }}</td>
										<td class="overall-cell">
											<div class="overall-score-container" :data-score="getScoreRange(entry.overall)">
												<span class="overall-score-value">{{ entry.overall }}</span>
												<div class="tiny-progress-bar">
													<div class="progress-fill" :style="{ width: (entry.overall / 100 * 100) + '%' }"></div>
												</div>
											</div>
										</td>
										<td class="actions-cell">
											<a-button 
												type="primary" 
												size="small" 
												@click="viewDetails(entry)"
												class="action-btn view-btn"
											>
												View
											</a-button>
											<a-button 
												type="default" 
												size="small" 
												@click="editSubmission(entry)"
												class="action-btn edit-btn"
											>
												Edit
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
export default {
	name: 'JudgerSubmissionHistory',
	data() {
		return {
			categoryFilter: '',
			instituteFilter: '',
			viewModalVisible: false,
			selectedSubmission: null,
			selectedRowIndex: null,
			
			// Summary data matching the original design
			summaryData: [
				{
					institute: 'Bank of Ceylon',
					award: 'Award No. 14 - Financial Institution of the Year for Best Digital Pay...',
					c1: 10,
					c2: 10,
					c3: 10,
					c4: 10,
					c5: 10,
					presentation: 100,
					overall: 100
				},
				{
					institute: 'Commercial Bank of Ceylon PLC',
					award: 'Award No. 14 - Financial Institution of the Year for Best Digital Pa...',
					c1: 10,
					c2: 10,
					c3: 10,
					c4: 10,
					c5: 10,
					presentation: 50,
					overall: 50
				},
				{
					institute: 'Bank of Ceylon',
					award: 'Award No. 6A - Most Popular Digital Payment Product - State Banks',
					c1: 15,
					c2: 20,
					c3: 10,
					c4: 15,
					c5: 5,
					presentation: 85,
					overall: 8.5
				}
			]
		}
	},
	computed: {
		filteredSummaryData() {
			let filtered = this.summaryData;
			
			if (this.categoryFilter) {
				filtered = filtered.filter(entry => 
					entry.award.toLowerCase().includes(this.categoryFilter.toLowerCase())
				);
			}
			
			if (this.instituteFilter) {
				filtered = filtered.filter(entry => 
					entry.institute.toLowerCase().includes(this.instituteFilter.toLowerCase())
				);
			}
			
			return filtered;
		}
	},
	methods: {
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
							institute: submission.institute,
							award: submission.award
						}
					});
				}
			});
		},
		filterSummary() {
			// Filter is handled by computed property
		}
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
}

.table-container {
	overflow-x: auto;
	width: 100%;
	max-width: 100%;
	
	// Custom scrollbar styling
	&::-webkit-scrollbar {
		height: 6px;
	}
	
	&::-webkit-scrollbar-track {
		background: #f8fafc;
		border-radius: 3px;
	}
	
	&::-webkit-scrollbar-thumb {
		background: #cbd5e1;
		border-radius: 3px;
		transition: background 0.2s ease;
		
		&:hover {
			background: #94a3b8;
		}
	}
}

// Modern Evaluation Table
.modern-evaluation-table {
	width: 100%;
	min-width: 900px; // Reduced minimum width for better fit
	border-collapse: separate;
	border-spacing: 0;
	background: white;
	font-family: 'Inter', 'Poppins', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
	table-layout: auto; // Allow natural column sizing
}

// Table Header - Modern Gradient
.table-header {
	background: linear-gradient(135deg, #E8F1FD 0%, #F0F7FF 100%);
	position: sticky;
	top: 0;
	z-index: 10;
	
	th {
		padding: 8px 6px;
		text-align: center;
		font-weight: 700;
		font-size: 10px;
		color: #1E293B;
		letter-spacing: 0.025em;
		border: none;
		white-space: nowrap;
		position: relative;
		
		&:first-child {
			text-align: center;
		}
	}
}

// Table Body
.table-body {
	background: white;
}

// Evaluation Rows - Interactive and Accessible
.evaluation-row {
	background: white;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
	border-bottom: 1px solid #f1f5f9;
	cursor: pointer;
	position: relative;
	
	&:hover {
		background: #F5F9FF;
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
	}
	
	&.selected-row {
		background: rgba(37, 99, 235, 0.15) !important;
		border-left: 5px solid #2563EB;
		box-shadow: 0 6px 20px rgba(37, 99, 235, 0.2);
		transform: translateY(-2px);
		border-top: 1px solid rgba(37, 99, 235, 0.2);
		border-bottom: 1px solid rgba(37, 99, 235, 0.2);
		
		// Enhanced text styling for selected row
		.institute-name {
			color: #1E40AF;
			font-weight: 700;
		}
		
		.award-text {
			color: #1E40AF;
			font-weight: 600;
		}
		
		.criteria-cell, .presentation-cell, .overall-cell {
			color: #1E40AF;
			font-weight: 700;
		}
		
		.overall-cell {
			color: #1D4ED8;
			font-weight: 800;
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

// Cell Styling - Compact Padding and Fonts
.modern-evaluation-table td {
	padding: 8px 6px;
	border: none;
	vertical-align: middle;
	transition: all 0.2s ease;
}

// Checkbox Column
.checkbox-column {
	width: 60px;
	text-align: center;
}

.checkbox-cell {
	width: 50px;
	text-align: center;
	padding: 8px 4px !important;
	vertical-align: middle;
}

// Institute Column - Optimized for Page Fit
.institute-column {
	text-align: left;
	min-width: 180px;
	width: 20%;
}

.institute-cell {
	text-align: left;
	min-width: 180px;
	
	.institute-name {
		font-weight: 600;
		font-size: 10px;
		color: #1E293B;
		white-space: nowrap;
		overflow: visible;
		display: block;
	}
}

// Award Column - Optimized for Page Fit
.award-column {
	text-align: left;
	min-width: 250px;
	width: 30%;
}

.award-cell {
	text-align: left;
	min-width: 250px;
	
	.award-text {
		font-weight: 500;
		font-size: 10px;
		color: #475569;
		line-height: 1.4;
		white-space: nowrap;
		overflow: visible;
		display: block;
	}
}

// Criteria Columns - Compact Center Aligned
.criteria-column, .presentation-column, .overall-column {
	text-align: center;
	min-width: 70px;
	width: 8%;
}

.criteria-cell, .presentation-cell, .overall-cell {
	text-align: center;
	font-weight: 600;
	font-size: 6px;
	color: #1E293B;
	min-width: 70px;
}

.overall-cell {
	font-weight: 700;
	font-size: 11px;
	color: #2563EB;
}

// Actions Column
.actions-column {
	text-align: center;
	min-width: 120px;
	width: 10%;
}

.actions-cell {
	text-align: center;
	padding: 8px 4px !important;
}

.action-btn {
	margin: 0 2px;
	font-size: 10px;
	padding: 4px 8px;
	
	&.view-btn {
		background: #3B82F6;
		border-color: #3B82F6;
		
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