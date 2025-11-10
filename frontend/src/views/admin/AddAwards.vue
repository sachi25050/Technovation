<!-- 
	Add Awards - Page to create awards with criteria
 -->

<template>
	<div>
		<!-- Page Header -->
		<div class="page-header">
			<h1>Create New Award</h1>
			<p>Set up new awards with marking criteria</p>
		</div>

		<a-row :gutter="24">
			<a-col :span="24" :lg="16">
				<a-card title="Award Information" class="mb-24">
					<a-form
						:form="form"
						@submit="handleSubmit"
						layout="vertical"
					>
						<a-form-item label="Award Category">
							<a-input
								v-decorator="[
									'awardCategory',
									{
										rules: [{ required: true, message: 'Please input award category!' }]
									}
								]"
								placeholder="Enter award category"
							/>
						</a-form-item>

						<a-form-item label="Award Description">
							<a-textarea
								v-decorator="[
									'awardDescription',
									{
										rules: [{ required: true, message: 'Please input award description!' }]
									}
								]"
								placeholder="Enter detailed description of the award"
								:rows="4"
							/>
						</a-form-item>

					<a-row :gutter="16">
						<a-col :span="12">
							<a-form-item label="Presentation Weightage">
								<a-input-number
									v-decorator="[
										'presentationWeightage',
										{
											rules: [{ required: true, message: 'Please input presentation weightage!' }]
										}
									]"
									placeholder="Enter presentation weightage"
									style="width: 100%"
									:min="0"
									:max="100"
									@change="updateWeightages"
								/>
							</a-form-item>
						</a-col>
						<a-col :span="12">
							<a-form-item label="Preliminary Weightage">
								<a-input-number
									v-decorator="[
										'preliminaryWeightage',
										{
											rules: [{ required: true, message: 'Please input preliminary weightage!' }]
										}
									]"
									placeholder="Enter preliminary weightage"
									style="width: 100%"
									:min="0"
									:max="100"
									@change="updateWeightages"
								/>
							</a-form-item>
						</a-col>
					</a-row>
					</a-form>
				</a-card>

				<!-- Marking Criteria Section -->
				<a-card title="Marking Criteria" class="mb-24">
					<div class="criteria-section">
						<div class="criteria-header">
							<h3>Evaluation Criteria</h3>
							<p>Define the criteria and allocated marks for this award</p>
						</div>

						<div v-for="(criterion, index) in criteria" :key="index" class="criterion-item">
							<a-row :gutter="16" align="middle">
								<a-col :span="16">
									<a-input
										v-model="criterion.name"
										placeholder="Enter criteria name"
										size="large"
									/>
								</a-col>
								<a-col :span="6">
									<a-input-number
										v-model="criterion.marks"
										placeholder="Marks"
										style="width: 100%"
										:min="1"
										:max="1000"
									/>
								</a-col>
								<a-col :span="2">
									<a-button
										type="danger"
										icon="delete"
										@click="removeCriterion(index)"
										:disabled="criteria.length === 1"
									/>
								</a-col>
							</a-row>
						</div>

						<div class="criteria-actions">
							<a-button type="dashed" @click="addCriterion" icon="plus" size="large">
								Add More Criteria
							</a-button>
						</div>

					<div class="criteria-summary" v-if="criteria.length > 0">
						<a-alert
							:message="`Total Allocated Marks: ${totalAllocatedMarks}`"
							type="info"
							show-icon
						/>
					</div>
					</div>
				</a-card>

				<!-- Presentation Marks Preview Table -->
				<a-card title="Presentation Marks Preview" class="mb-24">
					<div class="presentation-marks-preview">
						<div class="modern-table-wrapper">
							<table class="modern-marks-table">
								<thead>
									<tr>
										<th class="criteria-header">Marking Criteria</th>
										<th class="allocated-header">Presentation Marks Allocated</th>
										<th class="achieved-header">Achieved</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(criterion, index) in criteria" :key="index" 
										:class="{ 
											'even-row': index % 2 === 0, 
											'criteria-row': true
										}">
										<td class="criteria-name">
											<span class="criteria-text">
												{{ criterion.name || 'Marking Field 0' + (index + 1) }}
											</span>
										</td>
										<td class="allocated-marks">
											<span class="allocated-value">
												{{ criterion.marks || 0 }}
											</span>
										</td>
										<td class="achieved-marks">
											<span class="placeholder-dash">-</span>
										</td>
									</tr>
									<!-- Presentation Weightage Row -->
									<tr class="weightage-row">
										<td class="criteria-name">
											<span class="criteria-text">Presentation Weightage</span>
										</td>
										<td class="allocated-marks">
											<span class="allocated-value">100%</span>
										</td>
										<td class="achieved-marks">
											<span class="weightage-value">{{ formatWeightage(presentationWeightage) }}</span>
										</td>
									</tr>
									<!-- Preliminary Weightage Row -->
									<tr class="weightage-row">
										<td class="criteria-name">
											<span class="criteria-text">Preliminary Weightage</span>
										</td>
										<td class="allocated-marks">
											<span class="allocated-value">-</span>
										</td>
										<td class="achieved-marks">
											<span class="weightage-value">{{ formatWeightage(preliminaryWeightage) }}</span>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</a-card>

				<div style="margin-top: 24px;">
					<a-button type="primary" @click="handleCreateAward" :loading="loading" size="large">
						Create Award
					</a-button>
					<a-button style="margin-left: 8px;" @click="resetForm" size="large">
						Reset
					</a-button>
				</div>
			</a-col>

			<a-col :span="24" :lg="8">
				<a-card title="Recent Awards" class="mb-24">
					<a-list :data-source="recentAwards" size="small">
							<a-list-item slot="renderItem" slot-scope="item">
								<a-list-item-meta>
									<a slot="title">{{ item.name }}</a>
									<template slot="description">
										{{ item.category }}
									</template>
								</a-list-item-meta>
								<template slot="actions">
									<a-tag :color="getCategoryColor(item.category)">{{ item.category }}</a-tag>
								</template>
							</a-list-item>
					</a-list>
				</a-card>

				<a-card title="Award Statistics">
					<a-row :gutter="16">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ awardStats.total }}</div>
								<div class="stat-label">Total Awards</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ awardStats.active }}</div>
								<div class="stat-label">Active</div>
							</div>
						</a-col>
					</a-row>
					<a-row :gutter="16" style="margin-top: 16px;">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ awardStats.completed }}</div>
								<div class="stat-label">Completed</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ awardStats.pending }}</div>
								<div class="stat-label">Pending</div>
							</div>
						</a-col>
					</a-row>
				</a-card>
			</a-col>
		</a-row>
	</div>
</template>

<script>
	import apiService from '@/services/api';

	export default ({
		data() {
			return {
				form: this.$form.createForm(this),
				loading: false,
				criteria: [
					{
						name: '',
						marks: null
					}
				],
				recentAwards: [
					{
						name: 'Excellence in Innovation',
						category: 'innovation',
						totalMarks: 100
					},
					{
						name: 'Academic Achievement Award',
						category: 'academic',
						totalMarks: 150
					},
					{
						name: 'Leadership Excellence',
						category: 'leadership',
						totalMarks: 120
					},
					{
						name: 'Research Excellence',
						category: 'research',
						totalMarks: 200
					}
				],
				awardStats: {
					total: 156,
					active: 45,
					completed: 89,
					pending: 22
				},
				presentationWeightage: 0,
				preliminaryWeightage: 0
			}
		},
		computed: {
			totalAllocatedMarks() {
				return this.criteria.reduce((total, criterion) => {
					return total + (criterion.marks || 0);
				}, 0);
			}
		},
		methods: {
			async handleCreateAward() {
				// Manually validate form fields
				this.form.validateFields(async (err, values) => {
					if (err) {
						// Show validation errors
						const firstError = Object.keys(err)[0];
						if (firstError && err[firstError] && err[firstError].errors) {
							this.$message.error(err[firstError].errors[0].message || 'Please fill in all required fields');
						} else {
							this.$message.error('Please fill in all required fields correctly');
						}
						return;
					}

					// Validate criteria
					const validCriteria = this.criteria.filter(c => c.name && c.marks);
					if (validCriteria.length === 0) {
						this.$message.error('Please add at least one criterion!');
						return;
					}

					// Validate weightage sum
					const presentationWeightage = values.presentationWeightage || 0;
					const preliminaryWeightage = values.preliminaryWeightage || 0;
					const totalWeightage = presentationWeightage + preliminaryWeightage;
					
				

					this.loading = true;
					
					try {
						// Prepare criteria data for API
						const criteriaData = validCriteria.map(criterion => ({
							name: criterion.name.trim(),
							marks: parseFloat(criterion.marks),
							description: criterion.description ? criterion.description.trim() : null
						}));
						
						// Prepare award data matching backend API expectations
						const awardData = {
							awardCategory: values.awardCategory.trim(),
							awardDescription: values.awardDescription.trim(),
							presentationWeightage: parseFloat(presentationWeightage),
							preliminaryWeightage: parseFloat(preliminaryWeightage),
							criteria: criteriaData
						};
						
						console.log('Submitting award data:', awardData);
						
						// Make API call using the API service
						const response = await apiService.createAward(awardData);
						
						this.loading = false;
						
						if (response && response.success) {
							this.$message.success(response.message || 'Award created successfully!');
							this.resetForm();
							
							// Refresh recent awards list
							await this.loadRecentAwards();
						} else {
							this.$message.error(response?.message || 'Failed to create award');
						}
					} catch (error) {
						this.loading = false;
						console.error('Error creating award:', error);
						
						// Handle validation errors from backend
						if (error.data && error.data.errors) {
							// Handle both object and array error formats
							const errors = error.data.errors;
							if (typeof errors === 'object' && !Array.isArray(errors)) {
								// Object format: { field: "message" }
								const errorMessages = Object.values(errors);
								errorMessages.forEach(msg => {
									if (typeof msg === 'string') {
										this.$message.error(msg);
									} else if (Array.isArray(msg)) {
										msg.forEach(m => this.$message.error(m));
									}
								});
							} else if (Array.isArray(errors)) {
								// Array format: ["message1", "message2"]
								errors.forEach(msg => this.$message.error(msg));
							}
						} else if (error.data && error.data.message) {
							// Handle error message directly
							this.$message.error(error.data.message);
						} else {
							// Generic error message
							this.$message.error(error.message || 'Failed to create award. Please try again.');
						}
					}
				});
			},
			async handleSubmit(e) {
				e.preventDefault();
				await this.handleCreateAward();
			},
			resetForm() {
				this.form.resetFields();
				this.criteria = [
					{
						name: '',
						marks: null
					}
				];
				this.presentationWeightage = 0;
				this.preliminaryWeightage = 0;
			},
			addCriterion() {
				this.criteria.push({
					name: '',
					marks: null
				});
			},
			removeCriterion(index) {
				if (this.criteria.length > 1) {
					this.criteria.splice(index, 1);
				}
			},
			getCategoryColor(category) {
				const colors = {
					academic: 'blue',
					innovation: 'green',
					leadership: 'purple',
					research: 'orange',
					community: 'cyan',
					sports: 'red',
					arts: 'magenta'
				};
				return colors[category] || 'default';
			},
			updateWeightages() {
				// This method is called when weightage input fields change
				// Update the weightage values from form fields
				const values = this.form.getFieldsValue();
				this.presentationWeightage = values.presentationWeightage || 0;
				this.preliminaryWeightage = values.preliminaryWeightage || 0;
			},
			formatWeightage(value) {
				if (value === null || value === undefined || value === '') {
					return '0%';
				}
				const numValue = parseFloat(value) || 0;
				return numValue.toFixed(2) + '%';
			},
			async loadRecentAwards() {
				try {
					const response = await apiService.getAwards({ page: 1, limit: 5 });
					if (response.success && response.data && response.data.data) {
						this.recentAwards = response.data.data.map(award => ({
							name: award.description || award.category,
							category: award.category,
							totalMarks: (award.presentation_weightage || 0) + (award.preliminary_weightage || 0)
						}));
					}
				} catch (error) {
					console.error('Error loading recent awards:', error);
				}
			}
		},
		mounted() {
			// Load recent awards on component mount
			this.loadRecentAwards();
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

.criteria-section {
	.criteria-header {
		margin-bottom: 24px;
		
		h3 {
			margin: 0 0 8px 0;
			font-size: 18px;
			font-weight: 600;
			color: #1f2937;
		}
		
		p {
			margin: 0;
			color: #6b7280;
			font-size: 14px;
		}
	}
	
	.criterion-item {
		margin-bottom: 16px;
		padding: 16px;
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		background-color: #f9fafb;
	}
	
	.criteria-actions {
		margin-top: 16px;
		text-align: center;
	}
	
	.criteria-summary {
		margin-top: 16px;
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

// Presentation Marks Preview Table Styles
.presentation-marks-preview {
	width: 100%;
}

.modern-table-wrapper {
	background: white;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
	border: 1px solid #e2e8f0;
	width: 100%;
}

.modern-marks-table {
	width: 100%;
	border-collapse: collapse;
	background: white;
	font-size: 13px;
	font-family: 'Inter', 'Poppins', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
}

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

.modern-marks-table tbody tr {
	transition: all 0.3s ease;
	border-bottom: 1px solid #e2e8f0;
	
	&:hover {
		background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
	}
	
	&.even-row {
		background: #fafbfc;
	}
	
	&.weightage-row {
		background: #fef3c7;
		font-weight: 600;
	}
	
	&:last-child {
		border-bottom: none;
	}
}

.modern-marks-table td {
	padding: 10px 8px;
	border: none;
	vertical-align: middle;
	font-size: 13px;
	line-height: 1.4;
}

.criteria-name {
	text-align: left;
	
	.criteria-text {
		font-weight: 600;
		color: #1E293B;
		line-height: 1.4;
		display: block;
		font-size: 13px;
	}
}

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

.achieved-marks {
	text-align: center;
	
	.placeholder-dash {
		color: #94a3b8;
		font-size: 14px;
		font-weight: 500;
	}
	
	.weightage-value {
		font-weight: 700;
		color: #1E293B;
		font-size: 13px;
		display: inline-block;
		padding: 4px 8px;
		background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
		border-radius: 6px;
		min-width: 50px;
		box-shadow: 0 1px 3px rgba(37, 99, 235, 0.1);
	}
}
</style>
