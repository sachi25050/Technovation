Marking Criteria	Allocated	AchievedMarking Criteria	Allocated	AchievedMarking Criteria	Allocated	AchievedMarking Criteria	Allocated	Achieved<!-- 
	Judger Evaluate - LankaPay Technnovation Awards Judging System UI
	Matching the exact design from the provided image
 -->

<template>
	<div class="judging-system">
		<!-- Main Content -->
		<div class="main-content">
			<div class="content-container">
				<!-- A. General Details Section -->
				
					<div class="filter-section general-details-wrapper">
					<div class="section-header-with-image">
						<div class="section-header">
							<h2>General Details</h2>
						</div>
						<!-- Institution Image -->
						<div class="institution-image-container">
							<div class="institution-image-wrapper" v-if="selectedInstitutionImage">
								<img 
									:key="selectedInstitute + '-' + selectedInstitutionImage"
									:src="selectedInstitutionImage" 
									:alt="getInstituteName(selectedInstitute)"
									class="institution-image"
									@error="handleInstitutionImageError"
								/>
							</div>
							<div class="institution-image-placeholder" v-else>
								<a-icon type="bank" class="placeholder-icon" />
								<span class="placeholder-text">No Image</span>
							</div>
						</div>
					</div>
					<div class="section-content">
						<div class="form-row">
							<div class="form-field judge-field">
								<label>Name of the Judge</label>
								<a-input :value="judgeName" :placeholder="currentUser.name" disabled />
								
							</div>
							<div class="form-field institute-field">
								<label>Institute Name</label>
								<a-select 
									v-model="selectedInstitute" 
									placeholder="Select Institute" 
									@change="onInstituteChange"
									:loading="institutionsLoading"
									:getPopupContainer="triggerNode => triggerNode.parentNode"
								>
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
						<div class="form-row award-row">
							<div class="form-field award-field">
								<label>Award Category</label>
								<a-select 
									v-model="selectedAward" 
									placeholder="Select Award Category" 
									@change="onAwardChange"
									:loading="awardsLoading"
									:disabled="!selectedInstitute"
									:getPopupContainer="triggerNode => triggerNode.parentNode"
								>
									<a-select-option 
										v-for="award in availableAwards" 
										:key="award.id" 
										:value="award.id"
									>
										{{ award.category }}
									</a-select-option>
								</a-select>
							</div>
						</div>
					</div>
				</div>

				<!-- Award Category / Marking Criteria Placeholder Section -->
				<div class="marking-criteria-placeholder" v-if="!selectedInstitute || !selectedAward">
					<div class="placeholder-content">
						<p><strong>Award Category</strong> - Please Select the Institute Name and Award Category</p>
						<p><strong>Marking Criteria</strong> - Please Select the Institute Name and Award Category</p>
					</div>
				</div>

				<!-- Dynamic Marking Criteria Section -->
				<div class="marking-criteria-section" v-if="selectedInstitute && selectedAward">

					<div class="criteria-content">
						<p><strong>Award Category:</strong> {{ getAwardName(selectedAward) }}</p>
						<p><strong>Description:</strong> {{ getAwardDescription(selectedAward) }}</p>
					</div>
				</div>

				<!-- B. Presentation Marks Section -->
				<div class="presentation-marks-section">
					<div class="section-header">
						<h2>Presentation Marks</h2>
					</div>
					<div class="modern-marks-container">
						<div class="modern-table-wrapper">
							<table class="modern-marks-table">
							<thead>
								<tr>
										<th class="criteria-header">Marking Criteria</th>
										<th class="allocated-header">Allocated</th>
										<th class="achieved-header">Achieved</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(criterion, index) in markingCriteria" :key="index" 
										:class="{ 
											'even-row': index % 2 === 0, 
											'error-row': !selectedInstitute || !selectedAward,
											'criteria-row': true
										}">
									<td class="criteria-name">
											<span class="criteria-text">
										{{ selectedInstitute && selectedAward ? criterion.name : 'Marking Field 0' + (index + 1) + ' - Please Select the Institute Name and Award Category' }}
											</span>
									</td>
									<td class="allocated-marks">
											<span class="allocated-value">
										{{ selectedInstitute && selectedAward ? criterion.allocated : '0' }}
											</span>
									</td>
									<td class="achieved-marks">
										<a-input
											v-if="selectedInstitute && selectedAward"
											:value="criterion.marks"
											@input="(e) => handleMarksInput(criterion, e.target.value)"
											@blur="(e) => validateMarksOnBlur(criterion, e.target.value)"
											class="modern-marks-input"
											placeholder="0"
											type="text"
											maxlength="2"
										/>
										<span v-else class="placeholder-dash">-</span>
									</td>
								</tr>
								<!-- Total Row - After Overall Performance -->
								<tr class="total-row" v-if="selectedInstitute && selectedAward">
										<td class="total-label">
											<span class="total-text">Total</span>
										</td>
										<td class="total-allocated">
											<span class="total-value">{{ totalAllocated }}</span>
										</td>
										<td class="total-achieved">
											<span class="total-value">{{ totalMarks }}</span>
										</td>
								</tr>
								<!-- Gap Row -->
								<tr class="gap-row" v-if="selectedInstitute && selectedAward">
									<td colspan="3"></td>
								</tr>
								<!-- Presentation Weightage Row -->
								<tr class="weightage-row" v-if="selectedInstitute && selectedAward">
									<td class="criteria-name">
										<span class="criteria-text">
											Score for the Presentation (Weightage for Total is <span class="weightage-label">{{ formatWeightage(presentationWeightage) }}</span>)
										</span>
									</td>
									<td class="allocated-marks">
										<span class="allocated-value">-</span>
									</td>
									<td class="achieved-marks">
										<span class="weightage-value">{{ calculatePresentationScore() }}</span>
									</td>
								</tr>
							<!-- Preliminary Weightage Row -->
							<tr class="weightage-row" v-if="selectedInstitute && selectedAward">
								<td class="criteria-name">
									<span class="criteria-text">
										Preliminary Volume Wise Score (Weighted <span class="weightage-label">{{ formatWeightage(preliminaryWeightage) }}</span>)
									</span>
								</td>
								<td class="allocated-marks">
									<span class="allocated-value">-</span>
								</td>
								<td class="achieved-marks">
									<span class="weightage-value">{{ institutionMarks.toFixed(2) }}</span>
								</td>
							</tr>
								<!-- Aggregate Score Row -->
								<tr class="aggregate-row" v-if="selectedInstitute && selectedAward">
									<td class="criteria-name">
										<span class="criteria-text">
											Aggregated Score (Presentation - <span class="weightage-label">{{ formatWeightage(presentationWeightage) }}</span> + Volume Wise - <span class="weightage-label">{{ formatWeightage(preliminaryWeightage) }}</span>)
										</span>
									</td>
									<td class="allocated-marks">
										<span class="allocated-value">-</span>
									</td>
									<td class="achieved-marks">
										<span class="aggregate-value">{{ calculateAggregateScore() }}</span>
									</td>
								</tr>
							</tbody>
						</table>
						</div>
						<div v-if="totalMarks > totalAllocated && selectedInstitute && selectedAward" class="modern-warning">
							<div class="warning-icon">⚠️</div>
							<span class="warning-text">Total achieved marks ({{ totalMarks }}) cannot exceed allocated marks ({{ totalAllocated }})</span>
					</div>
						</div>
					<div class="modern-action-buttons">
						<div class="button-group left-group">
							<!-- <button class="modern-btn btn-exit" @click="exitApplication">
								<span class="btn-icon">✖️</span>
								<span class="btn-text">Exit</span>
							</button> -->
							<!-- <button class="modern-btn btn-delete" @click="deleteData">
								<span class="btn-icon">🗑️</span>
								<span class="btn-text">Delete</span>
							</button> -->
						</div>
						<div class="button-group right-group">
							<!-- <button class="modern-btn btn-remove-filter" @click="removeFilter">
								<span class="btn-icon">📥</span>
								<span class="btn-text">Remove Filter</span>
							</button> -->
							<!-- <button class="modern-btn btn-load" @click="loadData">
								<span class="btn-icon">📥</span>
								<span class="btn-text">Load</span>
							</button> -->
							<button class="modern-btn btn-reset" @click="resetForm">
								<span class="btn-icon">🔄</span>
								<span class="btn-text">Reset</span>
							</button>
							<button 
								class="modern-btn btn-submit" 
								@click="submitMarks"
								:disabled="!canSubmit"
							>
								<span class="btn-icon">{{ isEditMode ? '✏️' : '📤' }}</span>
								<span class="btn-text">{{ isEditMode ? 'Update' : 'Submit' }}</span>
							</button>
						</div>
					</div>
				</div>

				<!-- C. Filter Section -->
				<div class="filter-section">
					<div class="filter-header">
						<div class="filter-header-spacer"></div>
						<button class="filter-remove-btn" @click="removeFilter">
							<span class="btn-icon">🗑️</span>
							<span class="btn-text">Remove Filter</span>
						</button>
					</div>
					<div class="filter-content">
						<div class="filter-row">
							<label>Award Category Filter</label>
						<a-select 
							v-model="categoryFilter" 
							placeholder="Select Award Category"
							class="filter-select award-filter-full"
							@change="filterSummary"
							:getPopupContainer="triggerNode => triggerNode.parentNode"
						>
							<a-select-option value="">All Categories</a-select-option>
							<a-select-option 
								v-for="award in availableAwards" 
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
							:getPopupContainer="triggerNode => triggerNode.parentNode"
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

				<!-- D. Summary Section (Marking Sheet) -->
				<div class="summary-section">
					<div class="summary-header">
						<h6>{{ judgeName }} - Marking Sheet</h6>
						<div class="summary-header-actions">
							<span v-if="selectedRows.length > 0" class="selected-count">{{ selectedRows.length }} selected</span>
							<button class="summary-delete-btn" @click="deleteSelectedRows" :disabled="selectedRows.length === 0">
								<span class="btn-icon">🗑️</span>
								<span class="btn-text">Delete{{ selectedRows.length > 1 ? ` (${selectedRows.length})` : '' }}</span>
							</button>
							<button class="summary-edit-btn" @click="editSelectedRow" :disabled="selectedRows.length !== 1">
								<span class="btn-text">Edit</span>
							</button>
						</div>
					</div>
					<div class="modern-table-wrapper">
						<div class="table-container">
							<table class="modern-evaluation-table">
								<colgroup>
									<col class="col-checkbox" />
									<col class="col-institute" />
									<col class="col-award" />
									<col v-for="n in 10" :key="'col-c'+n" class="col-criteria" />
									<col class="col-pres-score" />
									<col class="col-volume-score" />
									<col class="col-aggregate" />
								</colgroup>
								<thead class="table-header">
									<tr>
										<th class="th-checkbox">
											<input 
												type="checkbox"
												:checked="isAllSelected"
												:indeterminate.prop="isIndeterminate"
												@change="toggleSelectAll"
												class="evaluation-checkbox select-all-checkbox"
												aria-label="Select all rows"
											/>
										</th>
										<th class="th-institute">Institute Name</th>
										<th class="th-award">Award Category</th>
										<th v-for="n in 10" :key="'th-c'+n" class="th-criteria">C-{{ String(n).padStart(2, '0') }}</th>
										<th class="th-pres-score">Presentation</th>
										<th class="th-volume-score">Preliminary</th>
										<th class="th-aggregate">Aggregate</th>
									</tr>
								</thead>
								<tbody class="table-body">
									<tr 
										v-for="(entry, index) in filteredSummaryData" 
										:key="index"
										:class="{ 'selected-row': isRowSelected(index) }"
										class="evaluation-row"
										:tabindex="0"
										:aria-selected="isRowSelected(index)"
										@click="toggleRowSelection(index, $event)"
										@keydown.space.prevent="toggleRowSelection(index, $event)"
										@keydown.enter.prevent="toggleRowSelection(index, $event)">
										<td class="td-checkbox">
											<input 
												type="checkbox"
												:checked="isRowSelected(index)"
												@click.stop="toggleRowSelection(index, $event)"
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
										<td class="td-pres-score">{{ entry.presentationScore || '-' }}</td>
										<td class="td-volume-score">{{ entry.volumeWiseScore || '-' }}</td>
										<td class="td-aggregate">
											<div class="aggregate-score-container" :data-score="getScoreRange(entry.aggregatedScore || 0)">
												<span class="aggregate-score-value">{{ entry.aggregatedScore || '-' }}</span>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import apiService from '@/services/api'
	export default ({
		data() {
			return {
			// Form selections
			sidebarCollapsed: false,
      currentUser: {
        name: 'John Doe',
        email: 'john.doe@lankapay.com',
        profile_image: null,
        title: null,
        first_name: null,
        last_name: null
	  },
			judgeName: '',
			// Institutions data
			institutions: [],
			institutionsLoading: false,
			// Awards data
			availableAwards: [],
			awardsLoading: false,
			// Filter selections
			categoryFilter: '',
			instituteFilter: '',
			// Form selections
			selectedInstitute: null,
			selectedAward: null,
			// Row selection state - supports multiple selection
			selectedRows: [],
			
			// Edit mode tracking
			isEditMode: false,
			editingEvaluationId: null,
			existingEvaluation: null,
			
			// Weightage values
			presentationWeightage: 0,
			preliminaryWeightage: 0,
			
			// Preliminary/Volume Wise Score (this should come from API or data source)
			// TODO: This should be fetched from API based on selectedInstitute and selectedAward
			// For demonstration, using a default value - replace with actual API call
			preliminaryScore: 0,
			
			// Institution marks from institution_awards table (displayed directly without calculation)
			institutionMarks: 0,
			
			// Marking criteria and marks
			markingCriteria: [
				{ name: 'Innovation & Creativity', allocated: 20, marks: null },
				{ name: 'Technical Excellence', allocated: 25, marks: null },
				{ name: 'Impact & Relevance', allocated: 25, marks: null },
				{ name: 'Presentation & Documentation', allocated: 20, marks: null },
				{ name: 'Overall Performance', allocated: 10, marks: null }
			],
				
				// Summary data - loaded from API
				summaryData: []
			}
		},
		computed: {
			totalMarks() {
				return this.markingCriteria.reduce((total, criterion) => {
					return total + (criterion.marks || 0);
				}, 0);
			},
			totalAllocated() {
				return this.markingCriteria.reduce((total, criterion) => {
					return total + criterion.allocated;
				}, 0);
			},
			canSubmit() {
				return this.selectedInstitute && 
					   this.selectedAward && 
					   this.totalMarks <= this.totalAllocated &&
					   this.totalMarks > 0 &&
					   this.markingCriteria.every(c => c.marks !== null && c.marks >= 0) &&
					   this.markingCriteria.length > 0;
			},
			selectedInstitutionImage() {
				if (!this.selectedInstitute) return null;
				const institution = this.institutions.find(inst => inst.id === this.selectedInstitute);
				console.log('Selected institution:', institution);
				if (institution && institution.image_url) {
					const url = this.normalizeImageUrl(institution.image_url);
					console.log('Institution image URL:', url);
					return url;
				}
				console.log('No image_url found for institution');
				return null;
			},
			filteredSummaryData() {
				let filtered = this.summaryData;
				
				if (this.categoryFilter) {
					// Convert ID to name if categoryFilter is an ID
					const filterName = this.getAwardName(this.categoryFilter);
					filtered = filtered.filter(entry => 
						entry.award.toLowerCase().includes(filterName.toLowerCase())
					);
				}
				
				if (this.instituteFilter) {
					// Convert ID to name if instituteFilter is an ID
					const filterName = this.getInstituteName(this.instituteFilter);
					filtered = filtered.filter(entry => 
						entry.institute.toLowerCase().includes(filterName.toLowerCase())
					);
				}
				
				return filtered;
			},
			// Check if all visible rows are selected
			isAllSelected() {
				return this.filteredSummaryData.length > 0 && 
				       this.selectedRows.length === this.filteredSummaryData.length;
			},
			// Check if some but not all rows are selected
			isIndeterminate() {
				return this.selectedRows.length > 0 && 
				       this.selectedRows.length < this.filteredSummaryData.length;
			}
		},
		
		methods: {
			async loadInstitutions() {
				this.institutionsLoading = true;
				try {
					// Fetch institutions from judger data endpoint
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
			async loadAwardsForInstitution(institutionId) {
				if (!institutionId) {
					this.availableAwards = [];
					return;
				}
				
				this.awardsLoading = true;
				try {
					// Fetch awards for the selected institution
					const response = await apiService.get('/judger/awards', { institution_id: institutionId });
					if (response.success && response.data) {
						this.availableAwards = Array.isArray(response.data) ? response.data : [];
					} else {
						this.availableAwards = [];
						console.error('Failed to load awards:', response);
					}
				} catch (error) {
					console.error('Error loading awards:', error);
					this.availableAwards = [];
					this.$message.error('Failed to load awards for institution');
				} finally {
					this.awardsLoading = false;
				}
			},
			formatJudgeName(user) {
				// Format: "Ms. Sachi Kaldera" (title + first_name + last_name)
				const parts = [];
				
				// Add title if available
				if (user.title && user.title.trim()) {
					let title = user.title.trim();
					// Ensure title has a period (e.g., "Ms" becomes "Ms.")
					if (!title.endsWith('.')) {
						title = title + '.';
					}
					parts.push(title);
				}
				
				// Add first name if available
				if (user.first_name && user.first_name.trim()) {
					parts.push(user.first_name.trim());
				}
				
				// Add last name if available
				if (user.last_name && user.last_name.trim()) {
					parts.push(user.last_name.trim());
				}
				
				// If no name parts, fallback to username or default
				if (parts.length === 0) {
					return user.username || 'Judge';
				}
				
				// Join with space
				return parts.join(' ');
			},
			onInstituteChange() {
				// Reset dependent fields when institute changes
				this.selectedAward = null;
				this.availableAwards = [];
				this.resetMarks();
				
				// Load awards for the selected institution
				if (this.selectedInstitute) {
					this.loadAwardsForInstitution(this.selectedInstitute);
				}
			},
			async onAwardChange() {
				// Reset edit mode when changing award
				this.isEditMode = false;
				this.editingEvaluationId = null;
				this.existingEvaluation = null;
				
				// Reset marks when award changes
				this.resetMarks();
				// Fetch award data including weightages
				this.fetchAwardWeightages();
				// Fetch marking criteria for the selected award
				this.fetchMarkingCriteria();
				
				// Check for existing evaluation (duplicate check)
				if (this.selectedInstitute && this.selectedAward) {
					await this.checkForDuplicate();
				}
			},
		async fetchAwardWeightages() {
			if (!this.selectedAward) {
				this.presentationWeightage = 0;
				this.preliminaryWeightage = 0;
				this.institutionMarks = 0;
				return;
			}
			
			try {
				// Find award from availableAwards
				const award = this.availableAwards.find(a => a.id == this.selectedAward);
				
				if (award) {
					this.presentationWeightage = parseFloat(award.presentation_weightage) || 0;
					this.preliminaryWeightage = parseFloat(award.preliminary_weightage) || 0;
					// Get institution marks directly from institution_awards table
					this.institutionMarks = parseFloat(award.institution_marks) || 0;
				} else {
					// Default values if not found
					this.presentationWeightage = 10;
					this.preliminaryWeightage = 90;
					this.institutionMarks = 0;
				}
			} catch (error) {
				console.error('Error fetching award weightages:', error);
				// Default values on error
				this.presentationWeightage = 10;
				this.preliminaryWeightage = 90;
				this.institutionMarks = 0;
			}
		},
			async fetchMarkingCriteria() {
				if (!this.selectedAward) {
					// Reset to default criteria if no award selected
					this.markingCriteria = [
						{ name: 'Innovation & Creativity', allocated: 20, marks: null },
						{ name: 'Technical Excellence', allocated: 25, marks: null },
						{ name: 'Impact & Relevance', allocated: 25, marks: null },
						{ name: 'Presentation & Documentation', allocated: 20, marks: null },
						{ name: 'Overall Performance', allocated: 10, marks: null }
					];
					return;
				}
				
				try {
					// Fetch criteria for the selected award - endpoint is /judger/criteria/{award_id}
					const response = await apiService.get(`/judger/criteria/${this.selectedAward}`);
					
					if (response.success && response.data && response.data.criteria) {
						// Map criteria from API to component format
						this.markingCriteria = response.data.criteria.map(criterion => ({
							id: criterion.id,
							name: criterion.name,
							allocated: parseInt(criterion.allocated_marks) || 0,
							marks: null // Reset marks when loading new criteria
						}));
					} else {
						console.error('Failed to load criteria:', response);
						// Keep default criteria on error
					}
				} catch (error) {
					console.error('Error loading marking criteria:', error);
					this.$message.error('Failed to load marking criteria');
					// Keep default criteria on error
				}
			},
			updateWeightages() {
				// This method handles dynamic weightage updates
				// The weightages are fetched from API when award changes
			},
			formatWeightage(value) {
				if (value === null || value === undefined || value === '') {
					return '0%';
				}
				const numValue = parseFloat(value) || 0;
				return numValue.toFixed(2) + '%';
			},
			formatWeightageValue(value) {
				if (value === null || value === undefined || value === '') {
					return '0';
				}
				const numValue = parseFloat(value) || 0;
				return numValue.toFixed(2);
			},
			calculatePresentationScore() {
				// Calculate: totalMarks × (presentationWeightage / 100)
				const totalMarks = this.totalMarks || 0;
				const presentationWeightage = this.presentationWeightage || 0;
				const presentationScore = totalMarks * (presentationWeightage / 100);
				// Round to whole number - presentation score must not be a decimal
				return Math.round(presentationScore);
			},
			calculateVolumeWiseScore() {
				// Calculate: preliminaryScore × (preliminaryWeightage / 100)
				// This is the Volume Wise Score weighted by preliminaryWeightage%
				const preliminaryScore = this.preliminaryScore || 0;
				const preliminaryWeightage = this.preliminaryWeightage || 0;
				const volumeWiseScore = preliminaryScore * (preliminaryWeightage / 100);
				return volumeWiseScore.toFixed(2);
			},
		calculateAggregateScore() {
			// Calculate aggregate score: (Presentation - presentationWeightage%) + (Volume Wise - preliminaryWeightage%)
			// Presentation Score = totalMarks × (presentationWeightage / 100)
			const presentationScore = parseFloat(this.calculatePresentationScore()) || 0;
			
			// Volume Wise Score = institutionMarks (directly from institution_awards table)
			const volumeWiseScore = this.institutionMarks || 0;
			
			// Aggregate = Presentation Score + Volume Wise Score
			const aggregate = presentationScore + volumeWiseScore;
			return aggregate.toFixed(2);
		},
			updateTotal() {
				// This method is called automatically when marks change
				// The total is computed reactively
			},
		handleMarksChange(criterion, value) {
			// Validate that achieved marks don't exceed allocated marks
			if (value !== null && value > criterion.allocated) {
				this.$message.destroy();
				this.$message.warning({
					content: `Marks cannot exceed ${criterion.allocated}. Resetting to 0.`,
					duration: 2
				});
				// Reset to zero when exceeded
				this.$nextTick(() => {
					criterion.marks = 0;
				});
			}
			this.updateTotal();
		},
		handleMarksInput(criterion, value) {
			// Allow only numbers and limit to 2 digits
			let numericValue = value.replace(/[^0-9]/g, '').slice(0, 2);
			let parsedValue = numericValue === '' ? null : parseInt(numericValue, 10);
			
			// Validate against allocated marks
			if (parsedValue !== null && parsedValue > criterion.allocated) {
				parsedValue = 0;
				// Destroy existing messages and show only one
				this.$message.destroy();
				this.$message.warning({
					content: `Marks cannot exceed the allocated value of ${criterion.allocated}. Resetting to 0.`,
					duration: 2
				});
			}
			
			criterion.marks = parsedValue;
			this.updateTotal();
		},
		validateMarksOnBlur(criterion, value) {
			const numericValue = value.replace(/[^0-9]/g, '');
			let parsedValue = numericValue === '' ? null : parseInt(numericValue, 10);
			
			// Ensure value doesn't exceed 100 or allocated marks - reset to 0 if exceeded
			if (parsedValue !== null) {
				const maxAllowed = Math.min(100, criterion.allocated);
				if (parsedValue > maxAllowed) {
					parsedValue = 0;
					this.$message.destroy();
					this.$message.warning({
						content: `Marks cannot exceed the allocated value of ${criterion.allocated}. Resetting to 0.`,
						duration: 2
					});
				}
			}
			
			criterion.marks = parsedValue;
			this.updateTotal();
		},
			async checkForDuplicate() {
				if (!this.selectedInstitute || !this.selectedAward) {
					return;
				}
				
				try {
					const response = await apiService.get('/judger/evaluations', {
						check_duplicate: true,
						institution_id: this.selectedInstitute,
						award_id: this.selectedAward
					});
					
					if (response.success && response.data && response.data.exists) {
						// Show warning message - user can use Edit/Delete buttons in the table below
						this.$warning({
							title: 'Evaluation Already Exists',
							content: 'You have already evaluated this. For further changes use the Edit or Delete buttons in the Marking Sheet table below.',
							okText: 'OK',
							onOk: () => {
								// Reset the form completely
								this.selectedInstitute = null;
								this.selectedAward = null;
								this.availableAwards = [];
								this.resetMarks();
								this.isEditMode = false;
								this.editingEvaluationId = null;
								this.existingEvaluation = null;
							}
						});
					} else {
						this.existingEvaluation = null;
						this.isEditMode = false;
						this.editingEvaluationId = null;
					}
				} catch (error) {
					console.error('Error checking for duplicate:', error);
				}
			},
			async loadExistingEvaluation(evaluationId) {
				try {
					const response = await apiService.get(`/judger/evaluations/${evaluationId}`);
					
					if (response.success && response.data) {
						const evaluation = response.data;
						
						// Set form fields from existing evaluation
						this.presentationWeightage = parseFloat(evaluation.presentation_weightage) || 0;
						this.preliminaryWeightage = parseFloat(evaluation.preliminary_weightage) || 0;
						this.institutionMarks = parseFloat(evaluation.preliminary_score) || 0;
						
						// Load criteria marks if available
						if (evaluation.criteria_marks && evaluation.criteria_marks.length > 0) {
							evaluation.criteria_marks.forEach((mark, index) => {
								if (this.markingCriteria[index]) {
									this.markingCriteria[index].marks = parseFloat(mark.achieved_marks) || 0;
								}
							});
						} else {
							// Fallback to individual criteria columns
							for (let i = 0; i < this.markingCriteria.length && i < 10; i++) {
								const criteriaKey = `criteria_${i + 1}_marks`;
								if (evaluation[criteriaKey] !== null) {
									this.markingCriteria[i].marks = parseFloat(evaluation[criteriaKey]) || 0;
								}
							}
						}
						
						this.$message.info('Loaded existing evaluation for editing');
					}
				} catch (error) {
					console.error('Error loading existing evaluation:', error);
					this.$message.error('Failed to load existing evaluation');
				}
			},
			removeFilter() {
				this.$confirm({
					title: 'Remove Filters',
					content: 'Are you sure you want to remove all applied filters?',
					okText: 'Yes, Remove',
					cancelText: 'Cancel',
					onOk: () => {
				this.categoryFilter = '';
				this.instituteFilter = '';
						this.$message.success('All filters have been removed');
					}
				});
			},
			loadData() {
				this.$confirm({
					title: 'Load Data',
					content: 'This will load evaluation data from the server. Continue?',
					okText: 'Load Data',
					cancelText: 'Cancel',
					onOk: () => {
						this.$message.loading('Loading evaluation data...', 2);
				// In a real app, this would fetch data from API
						setTimeout(() => {
							this.$message.success('Data loaded successfully');
						}, 2000);
					}
				});
			},
			// Check if a specific row is selected
			isRowSelected(index) {
				return this.selectedRows.includes(index);
			},
			// Toggle row selection (supports multi-select)
			toggleRowSelection(index, event) {
				const selectedIndex = this.selectedRows.indexOf(index);
				if (selectedIndex > -1) {
					// Row is selected, deselect it
					this.selectedRows.splice(selectedIndex, 1);
				} else {
					// Row is not selected, add it
					this.selectedRows.push(index);
				}
			},
			// Handle checkbox change
			handleCheckboxChange(index) {
				this.toggleRowSelection(index);
			},
			// Toggle select all rows
			toggleSelectAll() {
				if (this.isAllSelected) {
					// Deselect all
					this.selectedRows = [];
				} else {
					// Select all visible rows
					this.selectedRows = this.filteredSummaryData.map((_, index) => index);
				}
			},
			// Clear all selections
			clearSelection() {
				this.selectedRows = [];
			},
			getScoreRange(score) {
				// Determine score range for color coding
				if (score >= 90) return '90-100';
				if (score >= 80) return '80-89';
				if (score >= 70) return '70-79';
				if (score >= 60) return '60-69';
				return '0-59';
			},
			async editRow(index) {
				const entry = this.filteredSummaryData[index];
				
				if (!entry) {
					this.$message.error('Entry not found');
					return;
				}
				
				let institutionId = entry.institution_id;
				let awardId = entry.award_id;
				
				// If IDs are not directly available, find them by name
				if (!institutionId) {
					const institution = this.institutions.find(inst => inst.name === entry.institute);
					if (!institution) {
						this.$message.error('Institution not found. Please reload the page.');
						return;
					}
					institutionId = institution.id;
				}
				
				// Set the institution first
				this.selectedInstitute = institutionId;
				
				// Load awards for the institution
				await this.loadAwardsForInstitution(institutionId);
				
				// If award ID is not directly available, find it by name
				if (!awardId) {
					const award = this.availableAwards.find(a => a.category === entry.award);
					if (!award) {
						this.$message.error('Award not found for this institution.');
						return;
					}
					awardId = award.id;
				}
				
				// Set the award
				this.selectedAward = awardId;
				
				// Set edit mode
				this.isEditMode = true;
				this.editingEvaluationId = entry.id;
				
				// Fetch award weightages and criteria
				await this.fetchAwardWeightages();
				await this.fetchMarkingCriteria();
				
				// Load the marks from the entry - supports up to 10 criteria
				const criteriaMarks = [entry.c1, entry.c2, entry.c3, entry.c4, entry.c5, 
				                      entry.c6, entry.c7, entry.c8, entry.c9, entry.c10];
				for (let i = 0; i < this.markingCriteria.length && i < 10; i++) {
					if (criteriaMarks[i] !== null && criteriaMarks[i] !== undefined) {
						this.markingCriteria[i].marks = criteriaMarks[i];
					}
				}
				
				// If we have the evaluation ID, load full data from API
				if (entry.id) {
					await this.loadExistingEvaluation(entry.id);
				}
				
				this.$message.info(`Editing evaluation for ${entry.institute}`);
				
				// Scroll to top for better UX
				window.scrollTo({ top: 0, behavior: 'smooth' });
			},
			async editSelectedRow() {
				if (this.selectedRows.length === 1) {
					await this.editRow(this.selectedRows[0]);
					this.clearSelection();
				} else if (this.selectedRows.length > 1) {
					this.$message.warning('Please select only one row to edit');
				}
			},
			async deleteSelectedRows() {
				if (this.selectedRows.length === 0) {
					this.$message.warning('Please select at least one row to delete');
					return;
				}
				
				// Get the entries to delete (sorted in descending order for safe removal)
				const selectedIndices = [...this.selectedRows].sort((a, b) => b - a);
				const entriesToDelete = selectedIndices.map(index => this.filteredSummaryData[index]);
				
				const deleteCount = entriesToDelete.length;
				const confirmMessage = deleteCount === 1 
					? `Are you sure you want to delete the evaluation for ${entriesToDelete[0].institute} - ${entriesToDelete[0].award}?`
					: `Are you sure you want to delete ${deleteCount} evaluations? This action cannot be undone.`;
				
				this.$confirm({
					title: deleteCount === 1 ? 'Delete Entry' : `Delete ${deleteCount} Entries`,
					content: confirmMessage,
					okText: 'Yes, Delete',
					cancelText: 'Cancel',
					onOk: async () => {
						try {
							let successCount = 0;
							let failCount = 0;
							
							// Delete each entry
							for (const entry of entriesToDelete) {
								try {
									// If entry has an ID, delete from API
									if (entry.id) {
										const response = await apiService.delete(`/judger/evaluations/${entry.id}`);
										
										if (!response.success) {
											failCount++;
											continue;
										}
									}
									
									// Remove from local summaryData
									const actualIndex = this.summaryData.findIndex(
										item => (item.id && item.id === entry.id) || 
										(item.institute === entry.institute && item.award === entry.award)
									);
									if (actualIndex !== -1) {
										this.summaryData.splice(actualIndex, 1);
										successCount++;
									}
								} catch (error) {
									console.error('Error deleting entry:', error);
									failCount++;
								}
							}
							
							// Clear selection
							this.clearSelection();
							
							// Show result message
							if (failCount === 0) {
								this.$message.success(
									successCount === 1 
										? 'Evaluation deleted successfully' 
										: `${successCount} evaluations deleted successfully`
								);
							} else if (successCount > 0) {
								this.$message.warning(`${successCount} deleted, ${failCount} failed`);
							} else {
								this.$message.error('Failed to delete evaluations');
							}
						} catch (error) {
							console.error('Error deleting evaluations:', error);
							this.$message.error('Failed to delete evaluations. Please try again.');
						}
					}
				});
			},
			resetForm() {
				this.$confirm({
					title: 'Reset Form',
					content: 'Are you sure you want to reset all form data? This action cannot be undone.',
					okText: 'Yes, Reset',
					cancelText: 'Cancel',
					onOk: () => {
				this.selectedInstitute = null;
				this.selectedAward = null;
				this.resetMarks();
						this.$message.success('Form has been reset successfully');
					}
				});
			},
		resetMarks() {
			this.markingCriteria.forEach(criterion => {
				criterion.marks = null;
			});
			this.presentationWeightage = 0;
			this.preliminaryWeightage = 0;
			this.institutionMarks = 0;
			// Reset edit mode state
			this.isEditMode = false;
			this.editingEvaluationId = null;
			this.existingEvaluation = null;
		},
			async submitMarks() {
				if (!this.canSubmit) {
					this.$message.error(`Please complete all required fields and ensure total marks equal ${this.totalAllocated}`);
					return;
				}
				
				const actionTitle = this.isEditMode ? 'Update Marks' : 'Submit Marks';
				const actionText = this.isEditMode ? 'update' : 'submit';
				
				this.$confirm({
					title: actionTitle,
					content: `Are you sure you want to ${actionText} marks for ${this.getInstituteName(this.selectedInstitute)} - ${this.getAwardName(this.selectedAward)}?`,
					okText: this.isEditMode ? 'Update' : 'Submit',
					cancelText: 'Cancel',
					onOk: async () => {
						try {
							// Prepare criteria marks data
							const criteriaMarksData = this.markingCriteria.map((criterion, index) => ({
								criterion_id: criterion.id || (index + 1),
								name: criterion.name,
								allocated_marks: criterion.allocated,
								achieved_marks: criterion.marks || 0
							}));
							
							// Prepare evaluation data
							const evaluationData = {
								institution_id: this.selectedInstitute,
								award_id: this.selectedAward,
								criteria_marks: criteriaMarksData,
								total_achieved_marks: this.totalMarks,
								total_allocated_marks: this.totalAllocated,
								presentation_score: parseFloat(this.calculatePresentationScore()),
								preliminary_score: this.institutionMarks,
								aggregated_score: parseFloat(this.calculateAggregateScore()),
								presentation_weightage: this.presentationWeightage,
								preliminary_weightage: this.preliminaryWeightage,
								status: 'submitted',
								comments: ''
							};
							
							let response;
							if (this.isEditMode && this.editingEvaluationId) {
								// Update existing evaluation using PUT
								response = await apiService.put(`/judger/evaluations/${this.editingEvaluationId}`, evaluationData);
							} else {
								// Create new evaluation using POST
								response = await apiService.post('/judger/evaluations', evaluationData);
							}
							
							if (response.success) {
								const newEntry = {
									id: response.data?.id || this.editingEvaluationId,
									institution_id: this.selectedInstitute,
									award_id: this.selectedAward,
									institute: this.getInstituteName(this.selectedInstitute),
									award: this.getAwardName(this.selectedAward),
									c1: this.markingCriteria[0]?.marks || null,
									c2: this.markingCriteria[1]?.marks || null,
									c3: this.markingCriteria[2]?.marks || null,
									c4: this.markingCriteria[3]?.marks || null,
									c5: this.markingCriteria[4]?.marks || null,
									c6: this.markingCriteria[5]?.marks || null,
									c7: this.markingCriteria[6]?.marks || null,
									c8: this.markingCriteria[7]?.marks || null,
									c9: this.markingCriteria[8]?.marks || null,
									c10: this.markingCriteria[9]?.marks || null,
									presentationScore: parseFloat(this.calculatePresentationScore()),
									volumeWiseScore: this.institutionMarks,
									aggregatedScore: parseFloat(this.calculateAggregateScore())
								};
								
								if (this.isEditMode) {
									// Update existing entry in summaryData
									const existingIndex = this.summaryData.findIndex(
										item => item.id === this.editingEvaluationId ||
										(item.institute === newEntry.institute && item.award === newEntry.award)
									);
									if (existingIndex !== -1) {
										this.summaryData.splice(existingIndex, 1, newEntry);
									}
									this.$message.success(
										`Marks successfully updated for ${newEntry.institute} – ${newEntry.award}`
									);
								} else {
									// Add new entry to summaryData
									this.summaryData.unshift(newEntry);
									this.$message.success(
										`Marks successfully submitted for ${newEntry.institute} – ${newEntry.award}`
									);
								}
								
								// Reset form
								this.selectedInstitute = null;
								this.selectedAward = null;
								this.resetMarks();
								this.isEditMode = false;
								this.editingEvaluationId = null;
								this.existingEvaluation = null;
							} else {
								this.$message.error(response.message || `Failed to ${actionText} evaluation`);
							}
						} catch (error) {
							console.error(`Error ${actionText}ing evaluation:`, error);
							this.$message.error(error.message || `Failed to ${actionText} evaluation. Please try again.`);
						}
					}
				});
			},
			exitApplication() {
				this.$confirm({
					title: 'Exit Application',
					content: 'Are you sure you want to exit? Any unsaved changes will be lost.',
					okText: 'Exit',
					cancelText: 'Cancel',
					onOk: () => {
						this.$message.success('Application closed');
						// In a real app, this would close the application or redirect
					}
				});
			},
			deleteData() {
				this.$confirm({
					title: 'Delete Data',
					content: 'Are you sure you want to delete all evaluation data? This action cannot be undone.',
					okText: 'Delete',
					cancelText: 'Cancel',
					onOk: () => {
						this.summaryData = [];
						this.$message.success('All evaluation data has been deleted');
					}
				});
			},
			filterSummary() {
				// Filter is handled by computed property
				// Clear selections when filters change to avoid index mismatches
				this.clearSelection();
			},
			getInstituteName(value) {
				// If value is a number (ID), find institution by ID
				if (typeof value === 'number' || !isNaN(value)) {
					const institution = this.institutions.find(inst => inst.id == value);
					return institution ? institution.name : value;
				}
				
				// Fallback for old string values (for backward compatibility)
				const institutes = {
					'bank-ceylon': 'Bank of Ceylon',
					'commercial-bank': 'Commercial Bank of Ceylon PLC',
					'peoples-bank': 'People\'s Bank',
					'sampath-bank': 'Sampath Bank PLC',
					'hatton-national': 'Hatton National Bank PLC',
					'ndb-bank': 'NDB Bank'
				};
				return institutes[value] || value;
			},
		getAwardName(value) {
			// If value is a number (ID), find award by ID
			if (typeof value === 'number' || !isNaN(value)) {
				const award = this.availableAwards.find(a => a.id == value);
				return award ? award.category : value;
			}
			
			// Fallback for old string values (for backward compatibility)
			const awards = {
				'award-14': 'Award No. 14 - Financial Institution of the Year for Best Digital Payment',
				'award-6a': 'Award No. 6A - Most Popular Digital Payment Product - State Banks',
				'award-6b': 'Award No. 6B - Most Popular Digital Payment Product - Private Banks',
				'award-7': 'Award No. 7 - Best Digital Payment Innovation',
				'award-8': 'Award No. 8 - Best Digital Payment Security'
			};
			return awards[value] || value;
		},
		getAwardDescription(value) {
			// If value is a number (ID), find award by ID and return its description
			if (typeof value === 'number' || !isNaN(value)) {
				const award = this.availableAwards.find(a => a.id == value);
				return award ? (award.description || 'No description available') : '';
			}
			return '';
		},
		normalizeImageUrl(url) {
			if (!url) return null;
			
			const apiBaseUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
			
			// If it's already a full URL with /api/uploads/, return as is
			if (url.includes('/api/uploads/')) {
				console.log('Image URL (already normalized):', url);
				return url;
			}
			
			// Extract the file path from various URL formats
			let filePath = url;
			
			// If it's a full URL, extract the path after /uploads/
			if (url.includes('/backend/uploads/')) {
				const uploadsIndex = url.indexOf('/backend/uploads/');
				filePath = url.substring(uploadsIndex + '/backend/uploads/'.length);
			} else if (url.includes('/uploads/')) {
				const uploadsIndex = url.indexOf('/uploads/');
				filePath = url.substring(uploadsIndex + '/uploads/'.length);
			} else if (url.startsWith('http')) {
				// Full URL but without /uploads/, try to extract filename
				const lastSlash = url.lastIndexOf('/');
				filePath = 'institutions/' + url.substring(lastSlash + 1);
			} else if (url.startsWith('/')) {
				filePath = url.substring(1);
			}
			
			// Remove 'uploads/' prefix if present
			if (filePath.startsWith('uploads/')) {
				filePath = filePath.substring(8);
			}
			
			// Use the API uploads endpoint with path segments
			// Format: /api/uploads/{subfolder}/{filename}
			const finalUrl = `${apiBaseUrl}/uploads/${filePath}`;
			console.log('Image URL normalized:', url, '->', finalUrl);
			return finalUrl;
		},
		handleInstitutionImageError(event) {
			// Log the failed URL for debugging
			console.error('Institution image failed to load:', event.target.src);
			// Show default institution icon on error
			event.target.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23e5e7eb' width='100' height='100'/%3E%3Cpath fill='%239ca3af' d='M50 20L20 35v5h60v-5L50 20zM25 45v30h10V55h10v20h10V55h10v20h10V45H25zM15 80h70v5H15v-5z'/%3E%3C/svg%3E";
			event.target.onerror = null; // Prevent infinite loop
		},
		async loadEvaluations() {
			try {
				const response = await apiService.get('/judger/evaluations', { limit: 50 });
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
						presentationScore: evaluation.presentation_score || 0,
						volumeWiseScore: evaluation.preliminary_score || 0,
						aggregatedScore: evaluation.aggregated_score || 0
					}));
				}
			} catch (error) {
				console.error('Error loading evaluations:', error);
				// Keep default sample data if API fails
			}
		}
		},
		mounted() {
			// Set initial role based on current route
			// userRoleStore.setRoleFromPath(this.$route.path)
			
			// Get current user from API service if available
			const user = apiService.getCurrentUser()
			if (user) {
				this.currentUser = {
					name: user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.username || 'John Doe',
					email: user.email || 'john.doe@lankapay.com',
					profile_image: user.profile_image || null,
					title: user.title || null,
					first_name: user.first_name || null,
					last_name: user.last_name || null
				}
				
				// Format and set judge name with title
				this.judgeName = this.formatJudgeName(this.currentUser);
			} else {
				// Fallback if no user data
				this.judgeName = 'Judge';
			}
			
			// Load institutions
			this.loadInstitutions();
			
			// Load existing evaluations for summary table
			this.loadEvaluations();
		}
	})
</script>

<style lang="scss">
// LankaPay Technnovation Awards Judging System UI Styles
.judging-system {
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

// Main Content - Full Width Layout
.main-content {
	padding: 4px;
	min-height: calc(100vh - 80px);
	width: 100%;
	max-width: 100%;
	box-sizing: border-box;
	overflow-x: hidden;
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.content-container {
	max-width: 100%;
	width: 100%;
	margin: 0;
	display: flex;
	flex-direction: column;
	gap: 6px;
	box-sizing: border-box;
}

// A. General Details Section - Full Width
.general-details-section {
	background: white;
	border-radius: 6px;
	padding: 10px 12px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
	position: relative;
}

// General Details Wrapper with Image
.general-details-wrapper {
	position: relative;
	padding-right: 100px; // Make room for the institution image
	
	@media (max-width: 768px) {
		padding-right: 95px;
	}
	
	.section-header-with-image {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 12px;
		margin-bottom: 4px;
		
		.section-header {
			margin-bottom: 0;
			flex: 1;
		}
		
		@media (max-width: 768px) {
			flex-direction: row;
			align-items: flex-start;
		}
	}
}

// Institution Image Container - Right side of header
.institution-image-container {
	flex-shrink: 0;
	width: 100px;
	height: 70px;
	position: absolute;
	top: 8px;
	right: 20px;
	
	@media (max-width: 768px) {
		width: 80px;
		height: 55px;
		top: 6px;
		right: 8px;
	}
}

.institution-image-wrapper {
	width: 100%;
	height: 100%;
	border-radius: 4px;
	overflow: hidden;
	border: 1px solid #e2e8f0;
	background: #f8fafc;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
	
	.institution-image {
		width: 100%;
		height: 100%;
		object-fit: contain;
		background: white;
	}
}

.institution-image-placeholder {
	width: 100%;
	height: 100%;
	border-radius: 4px;
	border: 1px dashed #cbd5e1;
	background: #f8fafc;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 2px;
	
	.placeholder-icon {
		font-size: 18px;
		color: #94a3b8;
	}
	
	.placeholder-text {
		font-size: 8px;
		color: #94a3b8;
		font-weight: 500;
	}
}

.section-title {
	font-size: 11px;
	font-weight: 600;
	color: #1E293B;
	margin: 0 0 4px 0;
	letter-spacing: -0.025em;
}

.section-content {
	display: flex;
	flex-direction: column;
	gap: 2px;
	margin-top: 8px;
	
	@media (min-width: 768px) {
		gap: 4px;
		margin-top: 10px;
	}
}

.form-row {
	display: flex;
	gap: 20px;
	
	@media (min-width: 768px) {
		gap: 30px;
	}
	
	@media (min-width: 1024px) {
		gap: 40px;
	}
	
	@media (max-width: 767px) {
		flex-direction: column;
		gap: 4px;
	}
}

.form-field {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 0px;
	
	label {
		font-weight: 500;
		color: #64748b;
		font-size: 11px;
		margin-bottom: 2px;
	}
	
	// Underline style inputs like reference image
	:deep(.ant-input),
	:deep(.ant-input-number) {
		height: 32px !important;
		font-size: 14px !important;
		padding: 4px 0 !important;
		border-radius: 0 !important;
		border: none !important;
		border-bottom: 1px solid #e2e8f0 !important;
		background: transparent !important;
		box-shadow: none !important;
		
		&:focus, &:hover {
			border-bottom-color: #3b82f6 !important;
			box-shadow: none !important;
		}
	}
	
	:deep(.ant-select) {
		font-size: 14px !important;
	}
	
	:deep(.ant-select-selector) {
		height: 32px !important;
		font-size: 14px !important;
		border-radius: 0 !important;
		border: none !important;
		border-bottom: 1px solid #e2e8f0 !important;
		padding: 0 !important;
		background: transparent !important;
		box-shadow: none !important;
	}
	
	:deep(.ant-select-focused .ant-select-selector) {
		border-bottom-color: #3b82f6 !important;
		box-shadow: none !important;
	}
	
	:deep(.ant-select-selection-item) {
		line-height: 30px !important;
		font-size: 14px !important;
	}
	
	:deep(.ant-select-selection-placeholder) {
		line-height: 30px !important;
		font-size: 14px !important;
	}
}

// Specific field widths - fully increased widths
.judge-field {
	flex: 1;
	
	:deep(.ant-input[disabled]) {
		color: #000000 !important;
		-webkit-text-fill-color: #000000 !important;
		cursor: default !important;
	}
}

.institute-field {
	flex: 1;
}

.award-field {
	flex: 1;
}

.award-row {
	margin-top: 2px;
}

// D. Summary Section (Marking Sheet) - Compact Design
.summary-section {
	background: white;
	border-radius: 6px;
	padding: 10px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
	
	@media (min-width: 768px) {
		padding: 12px;
		border-radius: 8px;
	}
	
	@media (min-width: 1024px) {
		padding: 14px;
		border-radius: 10px;
	}
}

.summary-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 8px;
	
	@media (min-width: 768px) {
		margin-bottom: 10px;
	}
	
	@media (min-width: 1024px) {
		margin-bottom: 12px;
	}
	
	h3, h6 {
		font-size: 14px;
		font-weight: 700;
		color: #1E293B;
		margin: 0;
		line-height: 1.3;
		
		@media (min-width: 768px) {
			font-size: 20px;
		}
		
		@media (min-width: 1024px) {
			font-size: 22px;
		}
	}
}

// Summary Header Actions (Delete & Edit buttons)
.summary-header-actions {
	display: flex;
	gap: 8px;
	align-items: center;
}

// Selected count indicator
.selected-count {
	display: inline-flex;
	align-items: center;
	padding: 4px 10px;
	background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
	border: 1px solid #93c5fd;
	border-radius: 12px;
	color: #1e40af;
	font-size: 12px;
	font-weight: 600;
	margin-right: 4px;
}

// Select all checkbox styling
.select-all-checkbox {
	cursor: pointer;
}

.summary-delete-btn {
	display: inline-flex;
	align-items: center;
	gap: 3px;
	padding: 4px 8px;
	background: #fee2e2;
	border: 1px solid #fecaca;
	border-radius: 3px;
	color: #991b1b;
	font-size: 10px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s ease;
	
	&:hover:not(:disabled) {
		background: #fecaca;
		border-color: #fca5a5;
	}
	
	&:disabled {
		opacity: 0.5;
		cursor: not-allowed;
	}
	
	.btn-icon {
		font-size: 12px;
	}
}

.summary-edit-btn {
	display: inline-flex;
	align-items: center;
	gap: 3px;
	padding: 4px 10px;
	background: #10b981;
	border: 1px solid #059669;
	border-radius: 3px;
	color: white;
	font-size: 10px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s ease;
	
	&:hover:not(:disabled) {
		background: #059669;
		border-color: #047857;
	}
	
	&:disabled {
		opacity: 0.5;
		cursor: not-allowed;
	}
}

// Modern Evaluation Table - Compact Fixed Layout
.modern-evaluation-table {
	width: 100%;
	min-width: 1200px;
	border-collapse: collapse;
	background: white;
	font-size: 10px;
	table-layout: fixed;
}

// Colgroup - Fixed column widths for alignment
.col-checkbox { width: 28px; }
.col-institute { width: 130px; }
.col-award { width: 150px; }
.col-criteria { width: 50px; }
.col-pres-score { width: 75px; }
.col-volume-score { width: 75px; }
.col-aggregate { width: 70px; }

.table-header {
	background: linear-gradient(135deg, #E8F1FD 0%, #F0F7FF 100%);
	border-bottom: 1px solid #3b82f6;
	
	tr {
		height: 28px;
	}
}

// Header cells - compact consistent styling
.th-checkbox,
.th-institute,
.th-award,
.th-criteria,
.th-pres-score,
.th-volume-score,
.th-aggregate {
	padding: 4px 3px;
	text-align: center;
	font-weight: 600;
	font-size: 9px;
	color: #1E293B;
	letter-spacing: 0.02em;
	border: none;
	white-space: nowrap;
	vertical-align: middle;
}

.th-checkbox { text-align: center; }
.th-institute { text-align: left; padding-left: 6px; }
.th-award { text-align: left; padding-left: 4px; }
.th-criteria { text-align: center; font-size: 8px; }
.th-pres-score { text-align: center; font-size: 8px; }
.th-volume-score { text-align: center; font-size: 8px; }
.th-aggregate { text-align: center; font-size: 8px; }

// Data cells - compact styling matching headers
.td-checkbox,
.td-institute,
.td-award,
.td-criteria,
.td-pres-score,
.td-volume-score,
.td-aggregate {
	padding: 4px 3px;
	border: none;
	vertical-align: middle;
	font-size: 10px;
}

.td-checkbox {
	text-align: center;
}

.td-institute {
	text-align: left;
	padding-left: 6px;
	
	.institute-name {
		font-weight: 600;
		font-size: 10px;
		color: #1E293B;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
}

.td-award {
	text-align: left;
	padding-left: 4px;
	
	.award-text {
		font-weight: 700;
		font-size: 12px;
		color: #475569;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
}

.td-criteria {
	text-align: center;
	font-weight: 600;
	font-size: 10px;
	color: #1E293B;
}

.td-pres-score {
	text-align: center;
	font-weight: 600;
	font-size: 10px;
	color: #059669;
}

.td-volume-score {
	text-align: center;
	font-weight: 600;
	font-size: 10px;
	color: #7c3aed;
}

.td-aggregate {
	text-align: center;
	font-weight: 700;
	font-size: 10px;
	color: #2563EB;
}

.aggregate-score-container {
	display: flex;
	justify-content: center;
	align-items: center;
}

.aggregate-score-value {
	font-weight: 700;
	font-size: 12px;
	color: #2563EB;
}

// Legacy column classes for backward compatibility
.checkbox-column {
	width: 35px;
	text-align: center;
}

.institute-column {
	width: 130px;
	text-align: left;
}

.award-column {
	width: 150px;
	text-align: left;
}

.criteria-column {
	width: 50px;
	text-align: center;
}

.presentation-column {
	width: 10%;
	text-align: center;
}

.overall-column {
	width: 15%;
	text-align: center;
}

.table-body {
	tr {
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		border-bottom: 1px solid #f1f5f9;
		cursor: pointer;
		
		&:hover {
			background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
		}
		
		&.selected-row {
			background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
			border-left: 4px solid #3b82f6;
			box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
		}
		
		&:last-child {
			border-bottom: none;
		}
	}
}

.modern-evaluation-table td {
	padding: 12px 8px;
	border: none;
	vertical-align: middle;
	transition: all 0.2s ease;
	font-size: 13px;
	
	@media (min-width: 768px) {
		padding: 14px 10px;
		font-size: 14px;
	}
	
	@media (min-width: 1024px) {
		padding: 16px 12px;
		font-size: 15px;
	}
}

.checkbox-cell {
	text-align: center;
	width: 40px;
}

.evaluation-checkbox {
	width: 16px;
	height: 16px;
	cursor: pointer;
	accent-color: #3b82f6;
	
	@media (min-width: 768px) {
		width: 18px;
		height: 18px;
	}
	
	@media (min-width: 1024px) {
		width: 20px;
		height: 20px;
	}
}

.institute-cell {
	text-align: left;
	
	.institute-name {
		font-weight: 600;
	color: #1E293B;
		font-size: 13px;
		line-height: 1.4;
		
		@media (min-width: 768px) {
			font-size: 14px;
		}
		
		@media (min-width: 1024px) {
			font-size: 15px;
		}
	}
}

.award-cell {
	text-align: left;
	
	.award-text {
		font-weight: 500;
		color: #6b7280;
		font-size: 12px;
		line-height: 1.4;
		
		@media (min-width: 768px) {
			font-size: 13px;
		}
		
		@media (min-width: 1024px) {
			font-size: 14px;
		}
	}
}

.criteria-cell, .presentation-cell, .overall-cell {
	text-align: center;
	font-weight: 600;
	font-size: 13px;
	color: #1E293B;
	min-width: 70px;
}

.overall-cell {
	font-weight: 700;
	font-size: 13px;
	color: #2563EB;
}

.overall-score-container {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 4px;
	position: relative;
	
	&[data-score="excellent"] {
		.overall-score-value {
			color: #059669;
		}
		
		.progress-fill {
			background: linear-gradient(90deg, #10b981 0%, #059669 100%);
		}
	}
	
	&[data-score="good"] {
		.overall-score-value {
			color: #2563eb;
		}
		
		.progress-fill {
			background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
		}
	}
	
	&[data-score="average"] {
		.overall-score-value {
			color: #d97706;
		}
		
		.progress-fill {
			background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
		}
	}
	
	&[data-score="poor"] {
		.overall-score-value {
			color: #dc2626;
		}
		
		.progress-fill {
			background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
		}
	}
}

.overall-score-value {
	font-weight: 700;
	font-size: 14px;
	line-height: 1;
	
	@media (min-width: 768px) {
		font-size: 15px;
	}
	
	@media (min-width: 1024px) {
		font-size: 16px;
	}
}

.tiny-progress-bar {
	width: 100%;
	height: 4px;
	background: #e5e7eb;
	border-radius: 2px;
	overflow: hidden;
	position: relative;
	
	@media (min-width: 768px) {
		height: 5px;
	}
	
	@media (min-width: 1024px) {
		height: 6px;
	}
}

.progress-fill {
	height: 100%;
	border-radius: 2px;
	transition: width 0.3s ease;
	position: relative;
	
	&::after {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: linear-gradient(90deg, rgba(255, 255, 255, 0.2) 0%, transparent 50%, rgba(255, 255, 255, 0.2) 100%);
		animation: shimmer 2s infinite;
	}
}

@keyframes shimmer {
	0% {
		transform: translateX(-100%);
	}
	100% {
		transform: translateX(100%);
	}
}

// Responsive Design for All Sections
@media (max-width: 1200px) {
	.modern-marks-table {
		font-size: 13px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 12px 8px;
	}
	
	.modern-marks-input {
		width: 70px !important;
	}
}

@media (max-width: 1024px) {
	.modern-marks-table {
		font-size: 12px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 10px 6px;
	}
	
	.modern-marks-input {
		width: 60px !important;
		font-size: 12px !important;
	}
	
	.modern-evaluation-table {
		font-size: 12px;
	}
	
	.modern-evaluation-table th,
	.modern-evaluation-table td {
		padding: 10px 6px;
	}
	
	.criteria-cell, .presentation-cell {
		font-size: 11px;
	}
	
	.overall-cell {
		font-size: 12px;
	}
}

@media (max-width: 768px) {
	.main-content {
		padding: 8px;
		gap: 12px;
	}
	
	.content-container {
		gap: 12px;
	}
	
	.general-details-section,
	.marking-criteria-section,
	.presentation-marks-section,
	.filter-section,
	.summary-section {
		padding: 16px;
		border-radius: 12px;
	}
	
	.section-title {
		font-size: 16px;
		margin-bottom: 12px;
	}
	
	.section-header h2 {
		font-size: 18px;
	}
	
	.form-row {
		flex-direction: column;
		gap: 12px;
	}
	
	.form-field {
		.ant-input,
		.ant-select-selector {
			height: 36px !important;
			font-size: 13px !important;
		}
	}
	
	.modern-marks-table {
		font-size: 11px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 8px 4px;
	}
	
	.modern-marks-input {
		width: 50px !important;
		font-size: 11px !important;
		padding: 4px 6px !important;
	}
	
	.modern-evaluation-table {
		font-size: 11px;
	}
	
	.modern-evaluation-table th,
	.modern-evaluation-table td {
		padding: 8px 4px;
	}
	
	.criteria-cell, .presentation-cell {
		font-size: 10px;
	}
	
	.overall-cell {
		font-size: 11px;
	}
	
	.modern-action-buttons {
	flex-direction: column;
	gap: 12px;
		padding: 16px;
	}
	
	.button-group {
		justify-content: center;
		gap: 8px;
	}
	
	.modern-btn {
		padding: 10px 16px;
		font-size: 13px;
	}
}

@media (max-width: 600px) {
	.modern-marks-table {
		font-size: 10px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 6px 3px;
	}
	
	.modern-marks-input {
		width: 45px !important;
		font-size: 10px !important;
		padding: 3px 4px !important;
	}
	
	.modern-evaluation-table {
		font-size: 10px;
	}
	
	.modern-evaluation-table th,
	.modern-evaluation-table td {
		padding: 6px 3px;
	}
	
	.criteria-cell, .presentation-cell {
		font-size: 9px;
	}
	
	.overall-cell {
		font-size: 10px;
	}
	
	.modern-action-buttons {
		padding: 12px;
	}
	
	.modern-btn {
		padding: 8px 12px;
		font-size: 12px;
	}
}

@media (max-width: 480px) {
	.modern-marks-table {
		font-size: 9px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 4px 2px;
	}
	
	.modern-marks-input {
		width: 40px !important;
		font-size: 9px !important;
		padding: 2px 3px !important;
	}
	
	.modern-evaluation-table {
		font-size: 9px;
	}
	
	.modern-evaluation-table th,
	.modern-evaluation-table td {
		padding: 4px 2px;
	}
	
	.criteria-cell, .presentation-cell {
		font-size: 8px;
	}
	
	.overall-cell {
		font-size: 9px;
	}
	
	.total-value {
		font-size: 14px;
		padding: 4px 8px;
	}
	
	.modern-warning {
		padding: 12px;
		gap: 8px;
	}
	
	.warning-icon {
		font-size: 16px;
	}
	
	.warning-text {
		font-size: 12px;
		line-height: 1.3;
	}
	
	.modern-action-buttons {
		padding: 8px;
	}
	
	.modern-btn {
		padding: 6px 10px;
		font-size: 11px;
	}
}

// High contrast text
.judging-system {
	* {
		color: #2C3E50;
	}
}

// Button focus states
.ant-btn:focus {
	outline: 2px solid #ABEBC6;
	outline-offset: 2px;
}

.ant-input,
.ant-input-number {
	&:focus {
		outline: 2px solid #ABEBC6;
		outline-offset: 2px;
	}
}

// Dynamic Marking Criteria Section - Compact
.marking-criteria-section {
	background: white;
	border-radius: 6px;
	padding: 8px 10px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
	
	@media (min-width: 768px) {
		padding: 10px 12px;
		border-radius: 8px;
	}
	
	@media (min-width: 1024px) {
		padding: 10px 14px;
		border-radius: 8px;
	}
}

.criteria-content {
	p {
		margin: 0 0 6px 0;
		color: #374151;
		font-size: 14px;
		line-height: 1.5;
		
		&:last-child {
			margin-bottom: 0;
		}
		
		strong {
			color: #1f2937;
			font-weight: 600;
		}
	
	@media (min-width: 768px) {
			font-size: 15px;
	}
	
	@media (min-width: 1024px) {
			font-size: 16px;
		}
	}
}

// B. Presentation Marks Section - Full Width
.presentation-marks-section {
	background: white;
	border-radius: 8px;
	padding: 12px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
}

.section-header {
	margin-bottom: 8px;
	
	h2 {
		font-size: 14px;
		font-weight: 600;
		color: #1E293B;
		margin: 0;
		line-height: 1.3;
	}
}

// Modern Marks Container - Full Width
.modern-marks-container {
	display: flex;
		flex-direction: column;
		gap: 8px;
	width: 100%;
}

.modern-table-wrapper {
	background: white;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
}

// Modern Marks Table - Full Width
.modern-marks-table {
	width: 100%;
	border-collapse: collapse;
	background: white;
	font-size: 13px;
	font-family: 'Inter', 'Poppins', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
}

// Table Header - Modern Styling
.modern-marks-table thead {
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
	position: relative;
	
	&::after {
		content: '';
		position: absolute;
		bottom: 0;
		left: 0;
		right: 0;
		height: 2px;
		background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
	}
}

.modern-marks-table th {
	padding: 12px 8px;
	text-align: center;
	font-weight: 700;
	font-size: 13px;
	color: #1E293B;
	letter-spacing: 0.025em;
	position: relative;
	border: none;
	
	&.criteria-header {
		text-align: left;
		width: 50%;
	}
	
	&.allocated-header,
	&.achieved-header {
		width: 25%;
	}
}

// Table Body - Modern Rows
.modern-marks-table tbody tr {
	transition: all 0.3s ease;
	border-bottom: 1px solid #e2e8f0;
	
	&:hover {
		background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
		transform: translateY(-1px);
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
	}
	
	&.even-row {
		background: #fafbfc;
	}
	
	&.error-row {
		background: #fef2f2;
		border-left: 4px solid #ef4444;
		box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1);
	}
	
	&:last-child {
		border-bottom: none;
	}
}

// Table Cells - Modern Styling
.modern-marks-table td {
	padding: 10px 8px;
	border: none;
	vertical-align: middle;
	transition: all 0.3s ease;
	font-size: 13px;
	line-height: 1.4;
}

// Criteria Column
.criteria-name {
	text-align: left;
	
	.criteria-text {
		font-weight: 600;
		color: #1E293B;
		line-height: 1.0;
		display: block;
		font-size: 10px;
		
		.weightage-label {
			color: #dc2626;
			font-weight: 700;
		}
	}
}

// Allocated Column
.allocated-marks {
	text-align: center;
	
	.allocated-value {
		font-weight: 700;
		color: #2563EB;
		font-size: 13px;
		display: inline-block;
		padding: 4px 8px;
		background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
		border-radius: 6px;
		min-width: 35px;
		box-shadow: 0 1px 3px rgba(37, 99, 235, 0.1);
	}
}

// Achieved Column
.achieved-marks {
	text-align: center;
	
	.weightage-value {
		font-weight: 700;
		color: #1E293B;
		font-size: 13px;
		display: inline-block;
		padding: 4px 8px;
		background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
		border-radius: 6px;
		min-width: 50px;
		box-shadow: 0 1px 3px rgba(245, 158, 11, 0.2);
	}
	
	.aggregate-value {
		font-weight: 700;
		color: #1E293B;
		font-size: 14px;
		display: inline-block;
		padding: 4px 8px;
		background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
		border-radius: 6px;
		min-width: 50px;
		box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2);
	}
}

// Modern Input Styling - Readable with Bold Centered Text
.modern-marks-input {
	width: 70px !important;
	text-align: center !important;
	border: 1px solid #e2e8f0 !important;
	border-radius: 6px !important;
	font-weight: 700 !important;
	font-size: 14px !important;
	height: 32px !important;
	transition: all 0.2s ease !important;
	background: white !important;
	line-height: 1.4 !important;
	
	// Ensure input text is bold and centered
	input {
		text-align: center !important;
		font-weight: 700 !important;
		font-size: 14px !important;
	}
	
	&:hover {
		border-color: #3b82f6 !important;
		box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1) !important;
	}
	
	&:focus {
		border-color: #2563eb !important;
		box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
		background: #f8fafc !important;
		
		input {
			text-align: center !important;
			font-weight: 700 !important;
		}
	}
}

// Ensure Ant Design input-number components have bold centered text
.ant-input-number {
	.modern-marks-input {
		text-align: center !important;
		font-weight: 700 !important;
		
		.ant-input-number-input {
			text-align: center !important;
			font-weight: 700 !important;
			font-size: 13px !important;
		}
	}
}

// Global input styling for bold centered text
.ant-input-number-input {
	text-align: center !important;
	font-weight: 700 !important;
	font-size: 13px !important;
}

.placeholder-dash {
	color: #94a3b8;
	font-size: 14px;
	font-weight: 500;
}

// Total Row - Perfect Alignment
.total-row {
	background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%) !important;
	color: white !important;
	font-weight: 700;
	position: sticky;
	bottom: 0;
	z-index: 10;
	pointer-events: none;
	box-shadow: 0 -2px 12px rgba(30, 64, 175, 0.2);
	
	&:hover {
		background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%) !important;
		transform: none !important;
		box-shadow: 0 -2px 12px rgba(30, 64, 175, 0.2) !important;
		border-left: none !important;
	}
	
	// Total row cells should match regular table cell padding and alignment
	td {
		padding: 10px 8px !important;
		vertical-align: middle !important;
		font-size: 13px !important;
		line-height: 1.4 !important;
	}
	
	.total-label {
		text-align: left;
	}
	
	.total-allocated {
		text-align: center;
	}
	
	.total-achieved {
		text-align: center;
	}
	
	.total-text {
		color: white !important;
		font-weight: 700;
		text-align: left;
		pointer-events: none;
		font-size: 13px;
		display: block;
		line-height: 1.4;
	}

	.total-value {
		color: white !important;
		font-weight: 700;
		text-align: center;
		font-size: 13px;
		display: inline-block;
		padding: 4px 8px;
		background: rgba(255, 255, 255, 0.15);
		border-radius: 6px;
		pointer-events: none;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		min-width: 35px;
		line-height: 1.4;
	}
}

// Modern Warning Message - Compact
.modern-warning {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 8px;
	background: #fef3c7;
	border: 1px solid #f59e0b;
	border-radius: 6px;
	margin-top: 8px;
}

.warning-icon {
	font-size: 14px;
	flex-shrink: 0;
}

.warning-text {
	color: #92400e;
	font-weight: 500;
	font-size: 10px;
}

// Minimal Action Buttons
.modern-action-buttons {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
	padding: 8px;
	background: white;
	border-radius: 6px;
	margin-top: 8px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
}

.button-group {
	display: flex;
	gap: 6px;
	flex-wrap: wrap;
	align-items: center;
	
	&.left-group {
		justify-content: flex-start;
	}
	
	&.right-group {
		justify-content: flex-end;
	}
}

// Minimal Button Styles
.modern-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 4px;
	padding: 6px 12px;
	border: 1px solid #e5e7eb;
	border-radius: 4px;
	font-weight: 500;
	font-size: 11px;
	font-family: 'Inter', 'Poppins', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
	transition: all 0.2s ease;
	cursor: pointer;
	background: white;
	
	.btn-icon {
		font-size: 12px;
	}
	
	.btn-text {
		font-weight: 500;
	}
	
	&:hover {
		background: #f8fafc;
		border-color: #d1d5db;
	}
	
	&:focus {
		outline: 1px solid #3b82f6;
		outline-offset: 1px;
	}
}

// Exit Button
.btn-exit {
	background: #ef4444;
	color: white;
	border-color: #ef4444;
	
	&:hover {
		background: #dc2626;
		border-color: #dc2626;
	}
}

// Delete Button
.btn-delete {
	background: #f97316;
	color: white;
	border-color: #f97316;
	
	&:hover {
		background: #ea580c;
		border-color: #ea580c;
	}
}

// Placeholder Buttons
.btn-placeholder {
	background: #e2e8f0;
	color: #94a3b8;
	width: 30px;
	height: 30px;
	padding: 0;
	border-radius: 4px;
	opacity: 0.6;
	cursor: default;
	
	&:hover {
		background: #e2e8f0;
		border-color: #e2e8f0;
	}
}

// Remove Filter Button
.btn-remove-filter {
	background: #94a3b8;
	color: white;
	border-color: #94a3b8;
	
	&:hover {
		background: #64748b;
		border-color: #64748b;
	}
}

// Load Button
.btn-load {
	background: #3b82f6;
	color: white;
	border-color: #3b82f6;
	
	&:hover {
		background: #2563eb;
		border-color: #2563eb;
	}
}

// Reset Button
.btn-reset {
	background: #6b7280;
	color: white;
	border-color: #6b7280;
	
	&:hover {
		background: #4b5563;
		border-color: #4b5563;
	}
}

// Submit Button
.btn-submit {
	background: #10b981;
	color: white;
	border-color: #10b981;
	
	&:hover:not(:disabled) {
		background: #059669;
		border-color: #059669;
	}
	
	&:disabled {
		background: #d1d5db;
		color: #6b7280;
		border-color: #d1d5db;
		cursor: not-allowed;
		
		&:hover {
			background: #d1d5db;
			border-color: #d1d5db;
		}
	}
}


// C. Filter Section - Compact Card Design
.filter-section {
	background: white;
	border-radius: 6px;
	padding: 8px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
	
	@media (min-width: 768px) {
		padding: 24px;
		border-radius: 20px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
	}
	
	@media (min-width: 1024px) {
		padding: 28px;
		border-radius: 24px;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
	}
}

// Filter Header with Remove Filter Button - Compact
.filter-header {
	display: flex;
	justify-content: flex-end;
	align-items: center;
	margin-bottom: 6px;
	
	.filter-header-spacer {
		flex: 1;
	}
}

.filter-remove-btn {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 4px 10px;
	background: #fca5a5;
	border: 1px solid #f87171;
	border-radius: 4px;
	color: #991b1b;
	font-size: 11px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s ease;
	
	&:hover {
		background: #f87171;
		border-color: #ef4444;
	}
	
	.btn-icon {
		font-size: 14px;
	}
	
	.btn-text {
		font-weight: 500;
	}
}

.filter-content {
	display: flex;
	flex-direction: column;
	gap: 6px;
	width: 100%;
	
	@media (min-width: 768px) {
		flex-direction: row;
		gap: 10px;
	}
	
	@media (min-width: 1024px) {
		gap: 12px;
	}
}

.filter-row {
	display: flex;
	flex-direction: column;
	gap: 3px;
	flex: 1;
	width: 100%;
	
	label {
		font-weight: 600;
		color: #374151;
		font-size: 14px;
		margin: 0;
		line-height: 1.4;
		
		@media (min-width: 768px) {
			font-size: 15px;
		}
		
		@media (min-width: 1024px) {
			font-size: 16px;
		}
	}
}

.filter-select {
	width: 100% !important;
	
	.ant-select-selector {
		height: 28px !important;
		border-radius: 4px !important;
		border: 1px solid #d1d5db !important;
		font-size: 12px !important;
		transition: all 0.2s ease !important;
		
		&:hover {
			border-color: #9ca3af !important;
		}
		
		&:focus,
		&.ant-select-focused .ant-select-selector {
			border-color: #3b82f6 !important;
			box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
		}
		
		@media (min-width: 768px) {
			height: 44px !important;
			font-size: 15px !important;
		}
		
		@media (min-width: 1024px) {
			height: 48px !important;
			font-size: 16px !important;
		}
	}
}

.award-filter-full,
.institute-filter-full {
	flex: 0 0 100%;
	
	@media (min-width: 768px) {
		flex: 0 0 50%;
	}
}

.form-field label {
	font-weight: 600;
	color: #2C3E50;
	font-size: 12px;
}

// Award Category / Marking Criteria Placeholder Section - Compact
.marking-criteria-placeholder {
	background: #AED6F1;
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

.placeholder-content {
	p {
		margin: 6px 0;
		color: #2C3E50;
		font-size: 12px;
	}
}

// Dynamic Marking Criteria Section - Compact Modern
.marking-criteria-section {
	background: white;
	border-radius: 10px;
	padding: 16px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
	border: 1px solid #e2e8f0;
	position: relative;
	overflow: hidden;
	
	&::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, #10B981 0%, #059669 100%);
	}
	
	@media (min-width: 768px) {
		padding: 18px;
		border-radius: 12px;
		box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
	}
	
	@media (min-width: 1024px) {
		padding: 20px;
		border-radius: 14px;
	}
}

.criteria-content {
	p {
		margin: 6px 0;
		color: #2C3E50;
		font-size: 14px;
	}
}

// B. Presentation Marks Section - Compact Design
.presentation-marks-section {
	background: white;
	border-radius: 6px;
	padding: 10px;
	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
	border: 1px solid #e2e8f0;
	position: relative;
	overflow: hidden;
	
	&::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, #8B5CF6 0%, #7C3AED 100%);
	}
	
	@media (min-width: 768px) {
		padding: 12px;
		border-radius: 8px;
	}
	
	@media (min-width: 1024px) {
		padding: 14px;
		border-radius: 10px;
	}
}

.section-header {
	margin-bottom: 6px;
	
	@media (min-width: 768px) {
		margin-bottom: 8px;
	}
	
	@media (min-width: 1024px) {
		margin-bottom: 10px;
	}
	
	h2 {
		font-size: 14px;
		font-weight: 700;
		color: #2C3E50;
		margin: 0;
		border-bottom: 2px solid #B03A2E;
		padding-bottom: 3px;
		display: inline-block;
		
		@media (min-width: 768px) {
			font-size: 15px;
			padding-bottom: 4px;
		}
	}
}

// Modern Marks Container
.modern-marks-container {
	margin-bottom: 10px;
	overflow: hidden;
}

.modern-table-wrapper {
	background: white;
	border-radius: 6px;
	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
	overflow: hidden;
	border: 1px solid #e2e8f0;
	position: relative;
	
	&::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 2px;
		background: linear-gradient(90deg, #2563EB 0%, #3B82F6 100%);
	}
	
	@media (min-width: 768px) {
		border-radius: 8px;
	}
}

// Modern Marks Table
.modern-marks-table {
	width: 100%;
	border-collapse: separate;
	border-spacing: 0;
	background: white;
	font-family: 'Inter', 'Poppins', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
	table-layout: fixed;
}

// Table Header - Modern Design
.modern-marks-table thead {
	background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
	position: relative;
	
	&::after {
		content: '';
		position: absolute;
		bottom: 0;
		left: 0;
		right: 0;
		height: 2px;
		background: linear-gradient(90deg, #2563EB 0%, #3B82F6 100%);
	}
}

.modern-marks-table th {
	padding: 10px 8px;
	text-align: center;
	font-weight: 700;
	font-size: 13px;
	color: #1E293B;
	letter-spacing: 0.025em;
	position: relative;
	border: none;
	
	&.criteria-header {
		text-align: left;
		width: 50%;
	}
	
	&.allocated-header,
	&.achieved-header {
		width: 25%;
	}
	
	@media (min-width: 768px) {
		padding: 12px 10px;
		font-size: 14px;
	}
	
	@media (min-width: 1024px) {
		padding: 12px 10px;
		font-size: 14px;
	}
}

// Table Body - Compact Rows
.modern-marks-table tbody tr {
	transition: all 0.2s ease;
	border-bottom: 1px solid #f1f5f9;
	position: relative;
	
	&:nth-child(even) {
		background: #f8fafc;
	}
	
	&:nth-child(odd) {
		background: white;
	}
	
	&:hover {
		background: #e0f2fe !important;
		border-left: 3px solid #3b82f6;
	}
	
	&.error-row {
		background: #fef2f2 !important;
		border-left: 3px solid #ef4444;
		
		.criteria-text {
			color: #dc2626;
		}
	}
	
	&.weightage-row {
		background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
		font-weight: 600;
		border-left: 4px solid #f59e0b;
		
		&:hover {
			background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%) !important;
		}
	}
	
	&.gap-row {
		height: 16px;
		background: transparent !important;
		border: none !important;
		
		td {
			padding: 0 !important;
			border: none !important;
		}
	}
	
	&.aggregate-row {
		background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
		font-weight: 700;
		border-left: 4px solid #2563eb;
		
		&:hover {
			background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%) !important;
		}
	}
	
	&:last-child {
		border-bottom: none;
	}
}

// Table Cells - Readable Styling
.modern-marks-table td {
	padding: 8px 8px;
	border: none;
	vertical-align: middle;
	transition: all 0.2s ease;
	font-size: 13px;
	
	@media (min-width: 768px) {
		padding: 12px 10px;
		font-size: 14px;
	}
	
	@media (min-width: 1024px) {
		padding: 12px 10px;
		font-size: 14px;
	}
}

// Criteria Column
.criteria-name {
	text-align: left;
	
	.criteria-text {
		font-weight: 600;
		color: #1E293B;
		line-height: 1.4;
		display: block;
		font-size: 13px;
		
		.weightage-label {
			color: #dc2626;
			font-weight: 700;
		}
		
		@media (min-width: 768px) {
			font-size: 14px;
		}
	}
}

// Allocated Column
.allocated-marks {
	text-align: center;
	
	.allocated-value {
		font-weight: 700;
		color: #2563EB;
		font-size: 13px;
		display: inline-block;
		padding: 4px 10px;
		background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
		border-radius: 6px;
		min-width: 35px;
		box-shadow: 0 1px 3px rgba(37, 99, 235, 0.15);
		
		@media (min-width: 768px) {
			font-size: 14px;
		}
	}
}

// Achieved Column
.achieved-marks {
	text-align: center;
	
	.weightage-value {
		font-weight: 700;
		color: #1E293B;
		font-size: 13px;
		display: inline-block;
		padding: 4px 10px;
		background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
		border-radius: 6px;
		min-width: 50px;
		box-shadow: 0 1px 3px rgba(245, 158, 11, 0.2);
		
		@media (min-width: 768px) {
			font-size: 14px;
		}
	}
	
	.aggregate-value {
		font-weight: 700;
		color: #1E293B;
		font-size: 14px;
		display: inline-block;
		padding: 4px 10px;
		background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
		border-radius: 6px;
		min-width: 50px;
		box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2);
	}
}

// Modern Input Styling - Readable
.modern-marks-input {
	width: 70px !important;
	text-align: center;
	border-radius: 6px !important;
	border: 1px solid #e2e8f0 !important;
	transition: all 0.2s ease !important;
	height: 32px !important;
	font-size: 14px !important;
	
	&:focus {
		border-color: #3b82f6 !important;
		box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
	}
	
	&:hover {
		border-color: #94a3b8 !important;
	}
}

.placeholder-dash {
	color: #94a3b8;
	font-size: 13px;
	font-weight: 500;
}

// Total Row - Modern Highlight
.total-row {
	background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%) !important;
	color: white !important;
	font-weight: 700;
	position: sticky;
	bottom: 0;
	z-index: 10;
	
	.total-text,
	.total-value {
		color: white !important;
		font-weight: 700;
	}
	
	.total-value {
		font-size: 13px;
		display: inline-block;
		padding: 4px 8px;
		background: rgba(255, 255, 255, 0.15);
		border-radius: 6px;
		backdrop-filter: blur(10px);
		min-width: 35px;
		line-height: 1.4;
		text-align: center;
	}
}

// Modern Warning Message
.modern-warning {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	margin-top: 16px;
	padding: 12px 16px;
	background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
	border: 1px solid #fecaca;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1);
	
	.warning-icon {
	font-size: 18px;
		animation: pulse 2s infinite;
}

	.warning-text {
		color: #dc2626;
	font-weight: 600;
	font-size: 14px;
	}
}

@keyframes pulse {
	0%, 100% {
		opacity: 1;
	}
	50% {
		opacity: 0.5;
	}
}

// Minimal Action Buttons
.modern-action-buttons {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
	padding: 8px;
	background: white;
	border-radius: 6px;
	margin-top: 8px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	border: 1px solid #e2e8f0;
	width: 100%;
	box-sizing: border-box;
}

.button-group {
	display: flex;
	gap: 6px;
	flex-wrap: wrap;
	align-items: center;
	
	&.left-group {
		justify-content: flex-start;
	}
	
	&.right-group {
		justify-content: flex-end;
	}
}

// Minimal Button Styles
.modern-btn {
	display: inline-flex;
	align-items: center;
		justify-content: center;
	gap: 4px;
	padding: 6px 12px;
	border: 1px solid #e5e7eb;
	border-radius: 4px;
	font-weight: 500;
	font-size: 11px;
	font-family: 'Inter', 'Poppins', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
	transition: all 0.2s ease;
	cursor: pointer;
	background: white;
	
	.btn-icon {
		font-size: 12px;
	}
	
	.btn-text {
		font-weight: 500;
	}
	
	&:hover {
		background: #f8fafc;
		border-color: #d1d5db;
	}
	
	&:focus {
		outline: 1px solid #3b82f6;
		outline-offset: 1px;
	}
}

// Exit Button
.btn-exit {
	background: #ef4444;
	color: white;
	border-color: #ef4444;
	
	&:hover {
		background: #dc2626;
		border-color: #dc2626;
	}
}

// Delete Button
.btn-delete {
	background: #f97316;
	color: white;
	border-color: #f97316;
	
	&:hover {
		background: #ea580c;
		border-color: #ea580c;
	}
}

// Placeholder Buttons
.btn-placeholder {
	background: #e2e8f0;
	color: #94a3b8;
	width: 30px;
	height: 30px;
	padding: 0;
	border-radius: 4px;
	opacity: 0.6;
	cursor: default;
	
	&:hover {
		background: #e2e8f0;
		border-color: #e2e8f0;
	}
}

// Remove Filter Button
.btn-remove-filter {
	background: #94a3b8;
	color: white;
	border-color: #94a3b8;
	
	&:hover {
		background: #64748b;
		border-color: #64748b;
	}
}

// Load Button
.btn-load {
	background: #3b82f6;
	color: white;
	border-color: #3b82f6;
	
	&:hover {
		background: #2563eb;
		border-color: #2563eb;
	}
}

// Reset Button
.btn-reset {
	background: #6b7280;
	color: white;
	border-color: #6b7280;
	
	&:hover {
		background: #4b5563;
		border-color: #4b5563;
	}
}

// Submit Button
.btn-submit {
	background: #10b981;
	color: white;
	border-color: #10b981;
	
	&:hover:not(:disabled) {
		background: #059669;
		border-color: #059669;
	}
	
	&:disabled {
		background: #d1d5db;
		color: #6b7280;
		border-color: #d1d5db;
		cursor: not-allowed;
		
		&:hover {
			background: #d1d5db;
			border-color: #d1d5db;
		}
	}
}

// C. Filter Section - Compact
.filter-section {
	background: white;
	border-radius: 10px;
	padding: 16px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
	border: 1px solid #e2e8f0;
	position: relative;
	overflow: hidden;
	
	&::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, #F59E0B 0%, #D97706 100%);
	}
	
	@media (min-width: 768px) {
		padding: 18px;
		border-radius: 12px;
		box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
	}
	
	@media (min-width: 1024px) {
		padding: 20px;
		border-radius: 14px;
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
	font-weight: 500;
	color: #2C3E50;
	font-size: 12px;
	min-width: auto;
}

.filter-select {
	min-width: 300px;
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

// D. Summary Section (Marking Sheet) - Compact
.summary-section {
	background: white;
	border-radius: 10px;
	padding: 16px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
	border: 1px solid #e2e8f0;
	position: relative;
	overflow: hidden;
	
	&::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, #8B5CF6 0%, #7C3AED 100%);
	}
	
	@media (min-width: 768px) {
		padding: 18px;
		border-radius: 12px;
		box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
	}
	
	@media (min-width: 1024px) {
		padding: 20px;
		border-radius: 14px;
	}
}

.summary-header {
	margin-bottom: 20px;
	
	@media (min-width: 768px) {
	margin-bottom: 24px;
	}
	
	@media (min-width: 1024px) {
		margin-bottom: 28px;
	}
	
	h3 {
		font-size: 16px;
		font-weight: 700;
		color: #1E293B;
		margin: 0;
		letter-spacing: -0.025em;
		position: relative;
		
		&::after {
			content: '';
			position: absolute;
			bottom: -6px;
			left: 0;
			width: 30px;
			height: 2px;
			background: linear-gradient(90deg, #8B5CF6 0%, #7C3AED 100%);
			border-radius: 1px;
		}
		
		@media (min-width: 768px) {
			font-size: 18px;
		}
		
		@media (min-width: 1024px) {
			font-size: 20px;
		}
	}
}

// Modern Evaluation Table - Compact Responsive
.modern-table-wrapper {
	width: 100%;
	max-width: 100%;
	background: white;
	border-radius: 10px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
	overflow: hidden;
	margin: 0;
	border: 1px solid #e2e8f0;
	position: relative;
	
	&::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, #2563EB 0%, #3B82F6 100%);
	}
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

// Table Header - Compact Design
.table-header {
	background: #E8F1FD;
	position: sticky;
	top: 0;
	z-index: 10;
	
	th {
		padding: 12px 10px;
		text-align: center;
		font-weight: 700;
		font-size: 13px;
		color: #1E293B;
		letter-spacing: 0.025em;
		border: none;
		white-space: nowrap;
		position: relative;
		
		&:first-child {
			text-align: center;
		}
		
		@media (min-width: 768px) {
			padding: 14px 12px;
			font-size: 14px;
		}
		
		@media (min-width: 1024px) {
			padding: 16px 14px;
			font-size: 15px;
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
		background: rgba(37, 99, 235, 0.08) !important;
		border-left: 4px solid #2563EB;
		box-shadow: 0 8px 25px rgba(37, 99, 235, 0.15);
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
		
		td:first-child {
			border-bottom-left-radius: 16px;
		}
		
		td:last-child {
			border-bottom-right-radius: 16px;
		}
	}
}

// Cell Styling - Compact Padding and Fonts
.modern-evaluation-table td {
	padding: 10px 8px;
	border: none;
	vertical-align: middle;
	transition: all 0.2s ease;
	font-size: 13px;
	
	@media (min-width: 768px) {
		padding: 12px 10px;
		font-size: 14px;
	}
	
	@media (min-width: 1024px) {
		padding: 14px 12px;
		font-size: 15px;
	}
}

// Checkbox Column
.checkbox-column {
	width: 60px;
	text-align: center;
}

.checkbox-cell {
	width: 50px;
	text-align: center;
	padding: 10px 6px !important;
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
		font-size: 13px;
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
		font-size: 13px;
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
	font-size: 13px;
	color: #1E293B;
	min-width: 70px;
}

.overall-cell {
	font-weight: 700;
	font-size: 13px;
	color: #2563EB;
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
	font-size: 13px;
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

// Modern Evaluation Checkbox - Enhanced Design
.evaluation-checkbox {
	position: relative;
	width: 20px;
	height: 20px;
	margin: 0;
	cursor: pointer;
	appearance: none;
	border: 2px solid #CBD5E1;
	border-radius: 4px;
	background: white;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	
	&:hover {
		border-color: #2563EB;
		box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
		transform: scale(1.05);
	}
	
	&:focus {
		outline: 2px solid #2563EB;
		outline-offset: 2px;
	}
	
	&:checked {
		background: linear-gradient(135deg, #2563EB 0%, #3B82F6 100%);
		border-color: #2563EB;
		box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
		
		&::after {
			content: '';
			position: absolute;
			top: 2px;
			left: 6px;
			width: 5px;
			height: 10px;
			border: 2px solid white;
			border-top: none;
			border-left: none;
			transform: rotate(45deg);
			animation: checkmark 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		}
	}
	
	&:checked:hover {
		transform: scale(1.05);
		box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
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

// Enhanced Responsive Design for Modern Marks Table
@media (max-width: 1200px) {
	.modern-marks-table {
		font-size: 13px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 12px 8px;
	}
	
	.modern-marks-input {
		width: 70px !important;
	}
}

@media (max-width: 1024px) {
	.modern-marks-table {
		font-size: 12px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 10px 6px;
	}
	
	.modern-marks-input {
		width: 60px !important;
		font-size: 12px !important;
	}
	
	.allocated-value {
		font-size: 14px;
		padding: 3px 6px;
	}
}

@media (max-width: 768px) {
	.modern-marks-container {
		margin-bottom: 16px;
	}
	
	.modern-table-wrapper {
		border-radius: 12px;
		box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
	}
	
	.modern-marks-table {
		font-size: 11px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 8px 4px;
	}
	
	.modern-marks-input {
		width: 50px !important;
		font-size: 11px !important;
		padding: 4px 6px !important;
	}
	
	.allocated-value {
		font-size: 12px;
		padding: 2px 4px;
		min-width: 30px;
	}
	
	.criteria-text {
		font-size: 11px;
		line-height: 1.4;
	}
	
	.modern-warning {
		padding: 10px 12px;
		margin-top: 12px;
		
		.warning-text {
			font-size: 12px;
		}
	}
}

@media (max-width: 600px) {
	.modern-marks-table {
		font-size: 10px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 6px 3px;
	}
	
	.modern-marks-input {
		width: 45px !important;
		font-size: 10px !important;
		padding: 3px 4px !important;
	}
	
	.allocated-value {
		font-size: 10px;
		padding: 2px 3px;
		min-width: 25px;
	}
	
	.criteria-text {
		font-size: 10px;
		line-height: 1.3;
	}
	
	.total-value {
		font-size: 10px;
		padding: 4px 8px;
	}
	
	.modern-action-buttons {
		padding: 16px;
		gap: 12px;
	}
	
	.modern-btn {
		padding: 10px 16px;
		font-size: 12px;
		border-radius: 8px;
		
		.btn-icon {
			font-size: 14px;
		}
	}
}

@media (max-width: 480px) {
	.modern-marks-table {
		font-size: 9px;
	}
	
	.modern-marks-table th,
	.modern-marks-table td {
		padding: 4px 2px;
	}
	
	.modern-marks-input {
		width: 40px !important;
		font-size: 9px !important;
		padding: 2px 3px !important;
	}
	
	.allocated-value {
		font-size: 9px;
		padding: 1px 2px;
		min-width: 20px;
	}
	
	.criteria-text {
		font-size: 9px;
		line-height: 1.2;
	}
	
	.total-value {
		font-size: 9px;
		padding: 3px 6px;
	}
	
	.modern-action-buttons {
		padding: 12px;
		gap: 8px;
	}
	
	.modern-btn {
		padding: 8px 12px;
		font-size: 11px;
		border-radius: 6px;
		
		.btn-icon {
			font-size: 12px;
		}
	}
	
	.button-group {
		gap: 6px;
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
	
	.modern-marks-table {
		font-size: 11px;
	}
	
	.modern-marks-table th {
		padding: 8px 4px;
		font-size: 11px;
	}
	
	.modern-marks-table td {
		padding: 6px 4px;
		font-size: 11px;
	}
	
	.modern-marks-input {
		width: 60px !important;
		height: 32px !important;
		font-size: 11px !important;
	}
	
	// Total row responsive alignment
	.total-row {
		td {
			padding: 6px 4px !important;
			font-size: 11px !important;
		}
		
		.total-text,
		.total-value {
			font-size: 11px !important;
		}
	}
}

@media (max-width: 768px) {
	.general-details-section,
	.presentation-marks-section,
	.filter-section,
	.summary-section,
	.marking-criteria-placeholder,
	.marking-criteria-section {
		padding: 16px;
	}
	
	.marks-table th,
	.marks-table td {
		padding: 12px 8px;
		font-size: 13px;
	}
	
	.summary-table th,
	.summary-table td {
		padding: 10px 6px;
		font-size: 12px;
	}
	
	.modern-marks-table {
		font-size: 10px;
	}
	
	.modern-marks-table th {
		padding: 6px 3px;
		font-size: 10px;
	}
	
	.modern-marks-table td {
		padding: 4px 3px;
		font-size: 10px;
	}
	
	.modern-marks-input {
		width: 50px !important;
		height: 28px !important;
		font-size: 10px !important;
	}
	
	.modern-btn {
		padding: 8px 12px;
		font-size: 11px;
		min-height: 36px;
	}
	
	// Total row responsive alignment
	.total-row {
		td {
			padding: 4px 3px !important;
			font-size: 10px !important;
		}
		
		.total-text,
		.total-value {
			font-size: 10px !important;
		}
	}
}

@media (max-width: 600px) {
	.general-details-section,
	.presentation-marks-section,
	.filter-section,
	.summary-section,
	.marking-criteria-placeholder,
	.marking-criteria-section {
		padding: 12px;
	}
	
	.marks-table th,
	.marks-table td {
		padding: 10px 6px;
		font-size: 12px;
	}
	
	.modern-marks-table {
		font-size: 9px;
	}
	
	.modern-marks-table th {
		padding: 4px 2px;
		font-size: 9px;
	}
	
	.modern-marks-table td {
		padding: 3px 2px;
		font-size: 9px;
	}
	
	.modern-marks-input {
		width: 45px !important;
		height: 24px !important;
		font-size: 9px !important;
	}
	
	.modern-btn {
		padding: 6px 10px;
		font-size: 10px;
		min-height: 32px;
	}
	
	// Total row responsive alignment
	.total-row {
		td {
			padding: 3px 2px !important;
			font-size: 9px !important;
		}
		
		.total-text,
		.total-value {
			font-size: 9px !important;
		}
	}
}

@media (max-width: 480px) {
	.general-details-section,
	.presentation-marks-section,
	.filter-section,
	.summary-section,
	.marking-criteria-placeholder,
	.marking-criteria-section {
		padding: 8px;
	}
	
	.marks-table th,
	.marks-table td {
		padding: 8px 4px;
		font-size: 11px;
	}
	
	.modern-marks-table {
		font-size: 8px;
	}
	
	.modern-marks-table th {
		padding: 3px 1px;
		font-size: 8px;
	}
	
	.modern-marks-table td {
		padding: 2px 1px;
		font-size: 8px;
	}
	
	.modern-marks-input {
		width: 40px !important;
		height: 20px !important;
		font-size: 8px !important;
	}
	
	.modern-btn {
		padding: 4px 8px;
		font-size: 9px;
		min-height: 28px;
	}
	
	// Total row responsive alignment
	.total-row {
		td {
			padding: 2px 1px !important;
			font-size: 8px !important;
		}
		
		.total-text,
		.total-value {
			font-size: 8px !important;
		}
	}
}

// Simple Button Overrides - Remove Fancy Design
.modern-btn {
	padding: 8px 16px !important;
	font-size: 14px !important;
	font-weight: 500 !important;
	border: 1px solid #d1d5db !important;
	border-radius: 4px !important;
	background: white !important;
	transition: none !important;
	box-shadow: none !important;
	transform: none !important;
	
	&:hover {
		background: #f9fafb !important;
		border-color: #9ca3af !important;
		transform: none !important;
		box-shadow: none !important;
	}
	
	&:active {
		transform: none !important;
		box-shadow: none !important;
	}
}

// Light Button Colors
.btn-exit {
	background: #fca5a5 !important;
	border-color: #fca5a5 !important;
	color: #991b1b !important;
	
	&:hover {
		background: #f87171 !important;
		border-color: #f87171 !important;
		transform: none !important;
		box-shadow: none !important;
	}
}

.btn-delete {
	background: #fed7aa !important;
	border-color: #fed7aa !important;
	color: #c2410c !important;
	
	&:hover {
		background: #fdba74 !important;
		border-color: #fdba74 !important;
		transform: none !important;
		box-shadow: none !important;
	}
}

.btn-remove-filter {
	background: #d1d5db !important;
	border-color: #d1d5db !important;
	color: #4b5563 !important;
	
	&:hover {
		background: #9ca3af !important;
		border-color: #9ca3af !important;
		transform: none !important;
		box-shadow: none !important;
	}
}

.btn-load {
	background: #bfdbfe !important;
	border-color: #bfdbfe !important;
	color: #1e40af !important;
	
	&:hover {
		background: #93c5fd !important;
		border-color: #93c5fd !important;
		transform: none !important;
		box-shadow: none !important;
	}
}

.btn-reset {
	background: #d1d5db !important;
	border-color: #d1d5db !important;
	color: #374151 !important;
	
	&:hover {
		background: #9ca3af !important;
		border-color: #9ca3af !important;
		transform: none !important;
		box-shadow: none !important;
	}
}

.btn-submit {
	background: #86efac !important;
	border-color: #86efac !important;
	color: #166534 !important;
	
	&:hover:not(:disabled) {
		background: #4ade80 !important;
		border-color: #4ade80 !important;
		transform: none !important;
		box-shadow: none !important;
	}
}

// Edit Button Styles - Light Green
.edit-btn {
	background: #bbf7d0 !important;
	border: 1px solid #86efac !important;
	color: #166534 !important;
	padding: 4px 8px !important;
	font-size: 12px !important;
	font-weight: 500 !important;
	border-radius: 4px !important;
	cursor: pointer !important;
	transition: none !important;
	
	&:hover {
		background: #86efac !important;
		border-color: #4ade80 !important;
		transform: none !important;
		box-shadow: none !important;
	}
	
	&:focus {
		outline: 2px solid #22c55e !important;
		outline-offset: 1px !important;
	}
}

// Tiny Progress Bar Styles with Severity Colors
.presentation-score-container {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 1px;
	width: 100%;
}

.presentation-score-value {
	font-weight: 600;
	font-size: 11px;
	color: #1E293B;
	text-align: center;
}

.presentation-progress-bar {
	width: 100%;
	height: 2px;
	background: #f1f5f9;
	border-radius: 1px;
	overflow: hidden;
	position: relative;
}

.presentation-progress-bar .progress-fill {
	height: 100%;
	border-radius: 1px;
	transition: width 0.2s ease;
}

// Severity colors based on marks
.presentation-score-container[data-score="90-100"] .progress-fill {
	background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

.presentation-score-container[data-score="80-89"] .progress-fill {
	background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
}

.presentation-score-container[data-score="70-79"] .progress-fill {
	background: linear-gradient(90deg, #84cc16 0%, #65a30d 100%);
}

.presentation-score-container[data-score="60-69"] .progress-fill {
	background: linear-gradient(90deg, #eab308 0%, #ca8a04 100%);
}

.presentation-score-container[data-score="0-59"] .progress-fill {
	background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
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

// Enhanced Alert/Message Styles for Better Visibility
.ant-message {
	top: 80px !important;
	
	.ant-message-notice {
		padding: 12px !important;
	}
	
	.ant-message-notice-content {
		padding: 16px 24px !important;
		font-size: 16px !important;
		font-weight: 600 !important;
		border-radius: 12px !important;
		box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
		min-width: 300px !important;
		
		.ant-message-custom-content {
			display: flex !important;
			align-items: center !important;
			gap: 12px !important;
			
			.anticon {
				font-size: 24px !important;
			}
			
			span {
				font-size: 16px !important;
				line-height: 1.5 !important;
			}
		}
	}
	
	// Success message styling
	.ant-message-success .ant-message-notice-content {
		background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%) !important;
		border: 2px solid #10b981 !important;
		color: #065f46 !important;
	}
	
	// Info message styling
	.ant-message-info .ant-message-notice-content {
		background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
		border: 2px solid #3b82f6 !important;
		color: #1e40af !important;
	}
	
	// Warning message styling
	.ant-message-warning .ant-message-notice-content {
		background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
		border: 2px solid #f59e0b !important;
		color: #92400e !important;
	}
	
	// Error message styling
	.ant-message-error .ant-message-notice-content {
		background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
		border: 2px solid #ef4444 !important;
		color: #991b1b !important;
	}
}

// Enhanced Modal/Confirm Dialog Styles
.ant-modal {
	.ant-modal-content {
		border-radius: 16px !important;
		overflow: hidden;
		box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
	}
	
	.ant-modal-header {
		padding: 20px 24px !important;
		border-bottom: 1px solid #e5e7eb !important;
		
		.ant-modal-title {
			font-size: 20px !important;
			font-weight: 700 !important;
			color: #1f2937 !important;
		}
	}
	
	.ant-modal-body {
		padding: 24px !important;
		font-size: 16px !important;
		line-height: 1.6 !important;
		color: #4b5563 !important;
	}
	
	.ant-modal-footer {
		padding: 16px 24px !important;
		border-top: 1px solid #e5e7eb !important;
		
		.ant-btn {
			height: 40px !important;
			padding: 0 24px !important;
			font-size: 15px !important;
			font-weight: 500 !important;
			border-radius: 8px !important;
		}
		
		.ant-btn-primary {
			background: #3b82f6 !important;
			border-color: #3b82f6 !important;
			
			&:hover {
				background: #2563eb !important;
				border-color: #2563eb !important;
			}
		}
	}
	
	// Confirm dialog icon
	.ant-modal-confirm-body {
		.anticon {
			font-size: 28px !important;
			margin-right: 16px !important;
		}
		
		.ant-modal-confirm-title {
			font-size: 20px !important;
			font-weight: 700 !important;
			color: #1f2937 !important;
		}
		
		.ant-modal-confirm-content {
			font-size: 16px !important;
			margin-top: 12px !important;
			margin-left: 44px !important;
			color: #4b5563 !important;
			line-height: 1.6 !important;
		}
	}
	
	.ant-modal-confirm-btns {
		margin-top: 24px !important;
		
		.ant-btn {
			height: 42px !important;
			padding: 0 28px !important;
			font-size: 15px !important;
			font-weight: 500 !important;
			border-radius: 8px !important;
		}
	}
}

// Warning modal specific styling
.ant-modal-confirm-warning,
.ant-modal-confirm-confirm {
	.ant-modal-confirm-body > .anticon {
		color: #f59e0b !important;
	}
}

// Success modal specific styling  
.ant-modal-confirm-success {
	.ant-modal-confirm-body > .anticon {
		color: #10b981 !important;
	}
}

// Error modal specific styling
.ant-modal-confirm-error {
	.ant-modal-confirm-body > .anticon {
		color: #ef4444 !important;
	}
}

// Info modal specific styling
.ant-modal-confirm-info {
	.ant-modal-confirm-body > .anticon {
		color: #3b82f6 !important;
	}
}

// Bigger validation message styling
.ant-message {
	top: 100px !important;
	
	.ant-message-notice {
		.ant-message-notice-content {
			padding: 20px 32px !important;
			font-size: 18px !important;
			font-weight: 700 !important;
			border-radius: 12px !important;
			box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25) !important;
			background: #fffbeb !important;
			border: 2px solid #f59e0b !important;
		}
		
		.ant-message-warning {
			display: flex !important;
			align-items: center !important;
			
			.anticon {
				font-size: 28px !important;
				margin-right: 16px !important;
				color: #d97706 !important;
			}
			
			span:last-child {
				color: #92400e !important;
				font-size: 18px !important;
				font-weight: 700 !important;
			}
		}
	}
}
</style>