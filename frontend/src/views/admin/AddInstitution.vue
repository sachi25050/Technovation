<!-- 
	Add Institution - Form to add new institutions
 -->

<template>
	<div>
		<!-- Page Header -->
		<div class="page-header">
			<h1>Add New Institution</h1>
			<p>Register new institutions in the system</p>
		</div>

		<a-row :gutter="24">
			<a-col :span="24" :lg="16">
				<a-card title="Institution Information" class="mb-24">
					<a-form
						:form="form"
						@submit="handleSubmit"
						layout="vertical"
					>
						<a-form-item label="Institution Name">
							<a-input
								v-decorator="[
									'institutionName',
									{
										rules: [{ required: true, message: 'Please input institution name!' }]
									}
								]"
								placeholder="Enter institution name"
							/>
						</a-form-item>

						<a-form-item label="Contact Email">
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
								placeholder="Enter email (e.g., contact@institution.com)"
							/>
						</a-form-item>

						<a-form-item label="Upload Institute Image">
							<a-upload
								v-decorator="[
									'instituteImage',
									{
										rules: [{ required: false, message: 'Please upload institute image!' }]
									}
								]"
								name="file"
								list-type="picture-card"
								class="image-uploader"
								:show-upload-list="false"
								:before-upload="beforeUpload"
								@change="handleChange"
							>
								<div v-if="imageUrl">
									<img :src="imageUrl" alt="institute" style="width: 100%" />
								</div>
								<div v-else>
									<a-icon :type="loading ? 'loading' : 'plus'" />
									<div class="ant-upload-text">Upload</div>
								</div>
							</a-upload>
						</a-form-item>

						<a-form-item label="* Award Category">
							<a-select
								:value="selectedAwardValues"
								@change="handleAwardCategoryChange"
								mode="multiple"
								placeholder="Select award categories"
								style="width: 100%"
							>
								<a-select-option value="award-14">Award No. 14 - Financial Institution of the Year for Best Digital Payment</a-select-option>
								<a-select-option value="award-6a">Award No. 6A - Most Popular Digital Payment Product - State Banks</a-select-option>
								<a-select-option value="award-6b">Award No. 6B - Most Popular Digital Payment Product - Private Banks</a-select-option>
								<a-select-option value="award-7">Award No. 7 - Best Digital Payment Innovation</a-select-option>
								<a-select-option value="award-8">Award No. 8 - Best Digital Payment Security</a-select-option>
							</a-select>
							
							<!-- Display selected awards with mark input -->
							<div v-if="selectedAwards.length > 0" class="selected-awards-container">
								<div 
									v-for="(award, index) in selectedAwards" 
									:key="award.value"
									class="award-item-row"
								>
									<a-tag 
										closable 
										@close="removeAward(index)"
										class="award-tag"
									>
										{{ award.label }}
									</a-tag>
									<a-input-number
										v-model="award.marks"
										placeholder="Enter marks"
										class="award-marks-input"
										:min="0"
										:max="1000"
										size="default"
									/>
								</div>
							</div>
						</a-form-item>

						<a-form-item>
							<a-button type="primary" html-type="submit" :loading="loading" size="large">
								Add Institution
							</a-button>
							<a-button style="margin-left: 8px;" @click="resetForm" size="large">
								Reset
							</a-button>
						</a-form-item>
					</a-form>
				</a-card>
			</a-col>

			<a-col :span="24" :lg="8">
				<a-card title="Recent Institutions" class="mb-24">
					<a-list :data-source="recentInstitutions" size="small">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title">{{ item.name }}</a>
								<template slot="description">
									{{ item.type }} • {{ item.contactPerson }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-tag :color="getTypeColor(item.type)">{{ item.type }}</a-tag>
							</template>
						</a-list-item>
					</a-list>
				</a-card>

				<a-card title="Institution Statistics">
					<a-row :gutter="16">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ institutionStats.total }}</div>
								<div class="stat-label">Total</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ institutionStats.universities }}</div>
								<div class="stat-label">Universities</div>
							</div>
						</a-col>
					</a-row>
					<a-row :gutter="16" style="margin-top: 16px;">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ institutionStats.colleges }}</div>
								<div class="stat-label">Colleges</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ institutionStats.schools }}</div>
								<div class="stat-label">Schools</div>
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
				imageUrl: '',
				imageFile: null,
				uploadLoading: false,
				selectedAwards: [],
				awardOptions: {
					'award-14': 'Award No. 14 - Financial Institution of the Year for Best Digital Payment',
					'award-6a': 'Award No. 6A - Most Popular Digital Payment Product - State Banks',
					'award-6b': 'Award No. 6B - Most Popular Digital Payment Product - Private Banks',
					'award-7': 'Award No. 7 - Best Digital Payment Innovation',
					'award-8': 'Award No. 8 - Best Digital Payment Security'
				},
				recentInstitutions: [
					{
						name: 'University of Colombo',
						type: 'university',
						contactPerson: 'Dr. John Smith'
					},
					{
						name: 'Colombo International School',
						type: 'school',
						contactPerson: 'Ms. Sarah Johnson'
					},
					{
						name: 'Sri Lanka Institute of Technology',
						type: 'college',
						contactPerson: 'Prof. Michael Brown'
					},
					{
						name: 'National Research Institute',
						type: 'research',
						contactPerson: 'Dr. Emily Davis'
					}
				],
				institutionStats: {
					total: 28,
					universities: 8,
					colleges: 12,
					schools: 6
				}
			}
		},
		computed: {
			selectedAwardValues() {
				return this.selectedAwards.map(award => award.value);
			}
		},
		methods: {
			handleAwardCategoryChange(selectedValues) {
				// Find removed awards
				const removedAwards = this.selectedAwards.filter(
					award => !selectedValues.includes(award.value)
				);
				
				// Remove awards that are no longer selected
				this.selectedAwards = this.selectedAwards.filter(
					award => selectedValues.includes(award.value)
				);
				
				// Add new awards
				selectedValues.forEach(value => {
					const exists = this.selectedAwards.some(award => award.value === value);
					if (!exists) {
						this.selectedAwards.push({
							value: value,
							label: this.awardOptions[value],
							marks: null
						});
					}
				});
			},
			removeAward(index) {
				this.selectedAwards.splice(index, 1);
			},
			async handleSubmit(e) {
				e.preventDefault();
				this.form.validateFields(async (err, values) => {
					if (!err) {
						// Validate award categories (optional, but if provided, validate marks)
						if (this.selectedAwards.length > 0) {
							// Validate marks for each award if awards are selected
							const awardsWithoutMarks = this.selectedAwards.filter(award => !award.marks && award.marks !== 0);
							if (awardsWithoutMarks.length > 0) {
								this.$message.error('Please enter marks for all selected award categories!');
								return;
							}
						}
						
						this.loading = true;
						
						try {
							// Prepare institution data matching the database schema
							const institutionData = {
								name: values.institutionName,
								email: values.email, // Maps to contact_email in backend
								awardCategories: this.selectedAwards.map(award => ({
									value: award.value,
									label: award.label,
									marks: award.marks
								}))
							};
							
							// Call API with image file if available
							const response = await apiService.createInstitution(
								institutionData,
								this.imageFile
							);
							
							if (response.success) {
								this.$message.success(response.message || 'Institution added successfully!');
								this.resetForm();
							} else {
								this.$message.error(response.message || 'Failed to add institution');
							}
						} catch (error) {
							console.error('Error adding institution:', error);
							this.$message.error(error.message || 'Failed to add institution. Please try again.');
						} finally {
							this.loading = false;
						}
					}
				});
			},
			resetForm() {
				this.form.resetFields();
				this.selectedAwards = [];
				this.imageUrl = '';
				this.imageFile = null;
			},
			getTypeColor(type) {
				const colors = {
					university: 'blue',
					college: 'green',
					school: 'orange',
					research: 'purple',
					other: 'default'
				};
				return colors[type] || 'default';
			},
			beforeUpload(file) {
				const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png';
				if (!isJpgOrPng) {
					this.$message.error('You can only upload JPG/PNG file!');
					return false;
				}
				const isLt2M = file.size / 1024 / 1024 < 2;
				if (!isLt2M) {
					this.$message.error('Image must smaller than 2MB!');
					return false;
				}
				// Store file immediately for later use
				this.imageFile = file;
				// Return false to prevent auto upload, we'll handle it in form submission
				return false;
			},
			handleChange(info) {
				// Since we prevent auto-upload, handle file selection directly
				if (info.file.status === 'removed') {
					this.imageFile = null;
					this.imageUrl = '';
					this.uploadLoading = false;
					return;
				}
				
				// When file is selected (beforeUpload was called and returned false)
				// The file is already stored in this.imageFile from beforeUpload
				if (info.file.originFileObj && this.imageFile) {
					// Get preview URL
					this.getBase64(this.imageFile, imageUrl => {
						this.imageUrl = imageUrl;
						this.uploadLoading = false;
					});
				}
			},
			getBase64(img, callback) {
				const reader = new FileReader();
				reader.addEventListener('load', () => callback(reader.result));
				reader.readAsDataURL(img);
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

// Selected Awards Container Styles
.selected-awards-container {
	margin-top: 16px;
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.award-item-row {
	display: flex;
	align-items: center;
	gap: 12px;
	height: 32px;
	min-height: 32px;
	flex-wrap: nowrap;
	
	@media (max-width: 768px) {
		flex-direction: column;
		align-items: flex-start;
		height: auto;
		min-height: auto;
	}
}

.award-tag {
	margin: 0 !important;
	padding: 0 12px;
	font-size: 13px;
	font-weight: 400;
	background-color: #f5f5f5;
	border: 1px solid #d9d9d9;
	border-radius: 4px;
	color: #595959;
	height: 32px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	line-height: 32px;
	max-width: 100%;
	box-sizing: border-box;
	flex-shrink: 0;
	
	::v-deep .anticon-close {
		margin-left: 8px;
		margin-right: 0;
		color: #8c8c8c;
		font-size: 12px;
		vertical-align: middle;
		
		&:hover {
			color: #595959;
		}
	}
	
	&:hover {
		background-color: #e6f7ff;
		border-color: #40a9ff;
	}
	
	::v-deep span {
		line-height: 32px;
		vertical-align: middle;
	}
}

.award-marks-input {
	width: 150px;
	min-width: 120px;
	flex-shrink: 0;
	
	::v-deep .ant-input-number {
		width: 100%;
		height: 32px;
		border-radius: 4px;
		border: 1px solid #d9d9d9;
		transition: all 0.3s;
		display: flex;
		align-items: center;
		
		&:hover {
			border-color: #40a9ff;
		}
		
		&:focus,
		&.ant-input-number-focused {
			border-color: #40a9ff;
			box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
		}
		
		.ant-input-number-input {
			height: 30px;
			line-height: 30px;
			font-size: 14px;
			padding: 0 11px;
		}
	}
	
	@media (max-width: 768px) {
		width: 100%;
		min-width: 100%;
	}
}
</style>
