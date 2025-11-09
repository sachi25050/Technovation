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
								:show-upload-list="false"
								:before-upload="beforeUpload"
								@change="handleChange"
								accept="image/jpeg,image/jpg,image/png"
								class="custom-image-uploader"
							>
								<div class="image-upload-box" :class="{ 'has-image': imageUrl, 'loading': uploadLoading }">
									<!-- Preview Image -->
									<div v-if="imageUrl" class="image-preview-wrapper">
										<img :src="imageUrl" alt="Institute preview" class="preview-image" />
										<button 
											type="button" 
											class="remove-image-btn" 
											@click.stop="removeImage"
											title="Remove / Change Image"
										>
											<a-icon type="close-circle" />
											<span class="btn-text">Remove / Change</span>
										</button>
									</div>
									<!-- Upload Placeholder -->
									<div v-else class="upload-placeholder">
										<a-icon :type="uploadLoading ? 'loading' : 'upload'" class="upload-icon" />
										<div class="upload-text">
											<div class="upload-title">Click to Upload</div>
											<div class="upload-hint">JPG, PNG or JPEG (Max 2MB)</div>
										</div>
									</div>
								</div>
							</a-upload>
							<div v-if="imageUrl" class="upload-hint-text">
								Image uploaded successfully. Click "Remove" to change the image.
							</div>
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
		beforeDestroy() {
			// Clean up object URL when component is destroyed
			if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
				URL.revokeObjectURL(this.imageUrl);
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
				// Clean up object URL if it exists
				if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
					URL.revokeObjectURL(this.imageUrl);
				}
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
				// Validate file type
				const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
				const isValidType = allowedTypes.includes(file.type.toLowerCase());
				
				if (!isValidType) {
					this.$message.error('Only JPG, PNG, or JPEG formats are allowed.', 4);
					return false;
				}
				
				// Validate file size (2MB = 2 * 1024 * 1024 bytes)
				const maxSize = 2 * 1024 * 1024; // 2MB in bytes
				if (file.size > maxSize) {
					this.$message.warning('File size exceeds the maximum allowed limit (2MB).', 4);
					return false;
				}
				
				// Store file immediately for later use
				this.imageFile = file;
				
				// Generate preview immediately using Object URL (faster and more reliable)
				this.uploadLoading = true;
				
				// Clean up previous object URL if exists
				if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
					URL.revokeObjectURL(this.imageUrl);
				}
				
				// Create object URL for immediate preview
				this.imageUrl = URL.createObjectURL(file);
				this.uploadLoading = false;
				
				// Show success notification
				this.$nextTick(() => {
					this.$message.success('Image uploaded successfully.', 3);
				});
				
				// Return false to prevent auto upload, we'll handle it in form submission
				return false;
			},
			handleChange(info) {
				// Handle file removal
				if (info.file.status === 'removed' || (info.fileList && info.fileList.length === 0)) {
					this.imageFile = null;
					this.imageUrl = '';
					this.uploadLoading = false;
					return;
				}
			},
			getBase64(img, callback) {
				const reader = new FileReader();
				reader.addEventListener('load', () => callback(reader.result));
				reader.addEventListener('error', () => {
					this.uploadLoading = false;
					this.$message.error('Failed to load image preview');
				});
				reader.readAsDataURL(img);
			},
			removeImage() {
				// Clean up object URL if it exists
				if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
					URL.revokeObjectURL(this.imageUrl);
				}
				this.imageFile = null;
				this.imageUrl = '';
				this.form.setFieldsValue({ instituteImage: null });
				this.$message.info('Image removed. You can upload a new image.', 3);
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

// Custom Image Uploader Styles
.custom-image-uploader {
	::v-deep .ant-upload {
		width: 100%;
		display: block;
	}
	
	::v-deep .ant-upload-select {
		width: 100%;
		display: block;
		border: none;
		background: transparent;
	}
}

.image-upload-box {
	width: 100%;
	max-width: 300px;
	height: 250px;
	border: 2px dashed #D9D9D9;
	border-radius: 8px;
	background-color: #FAFAFA;
	position: relative;
	overflow: hidden;
	transition: all 0.3s ease;
	cursor: pointer;
	
	&:hover {
		border-color: #1890FF;
		background-color: #F0F7FF;
		box-shadow: 0 2px 8px rgba(24, 144, 255, 0.1);
	}
	
	&.has-image {
		border-color: #52C41A;
		background-color: #FFFFFF;
		cursor: default;
		
		&:hover {
			border-color: #52C41A;
			box-shadow: 0 2px 8px rgba(82, 196, 26, 0.15);
		}
	}
	
	&.loading {
		pointer-events: none;
		opacity: 0.7;
	}
}

.image-preview-wrapper {
	width: 100%;
	height: 100%;
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: #FFFFFF;
	padding: 8px;
	box-sizing: border-box;
	
	.preview-image {
		max-width: 100%;
		max-height: 100%;
		width: auto;
		height: auto;
		object-fit: contain;
		display: block;
		border-radius: 4px;
	}
	
	.remove-image-btn {
		position: absolute;
		top: 8px;
		right: 8px;
		background-color: rgba(255, 255, 255, 0.95);
		border: 1px solid #D9D9D9;
		border-radius: 4px;
		padding: 6px 12px;
		display: flex;
		align-items: center;
		gap: 6px;
		cursor: pointer;
		transition: all 0.2s ease;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		z-index: 10;
		font-size: 12px;
		color: #595959;
		font-weight: 600;
		
		&:hover {
			background-color: #FFFFFF;
			border-color: #F5222D;
			color: #F5222D;
			box-shadow: 0 2px 8px rgba(245, 34, 45, 0.2);
			transform: translateY(-1px);
		}
		
		&:active {
			transform: translateY(0);
		}
		
		.anticon {
			font-size: 14px;
		}
		
		.btn-text {
			font-size: 12px;
			line-height: 1;
		}
	}
}

.upload-placeholder {
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 20px;
	box-sizing: border-box;
	
	.upload-icon {
		font-size: 48px;
		color: #8C8C8C;
		margin-bottom: 16px;
		transition: all 0.3s ease;
	}
	
	.upload-text {
		text-align: center;
		
		.upload-title {
			font-size: 14px;
			font-weight: 600;
			color: #595959;
			margin-bottom: 4px;
		}
		
		.upload-hint {
			font-size: 12px;
			color: #8C8C8C;
		}
	}
}

.image-upload-box:hover .upload-placeholder .upload-icon {
	color: #1890FF;
	transform: translateY(-2px);
}

.upload-hint-text {
	margin-top: 8px;
	font-size: 12px;
	color: #52C41A;
	font-weight: 500;
}

// Responsive Design
@media (max-width: 768px) {
	.image-upload-box {
		max-width: 100%;
		height: 200px;
	}
	
	.upload-placeholder {
		.upload-icon {
			font-size: 40px;
			margin-bottom: 12px;
		}
		
		.upload-text {
			.upload-title {
				font-size: 13px;
			}
			
			.upload-hint {
				font-size: 11px;
			}
		}
	}
	
	.remove-image-btn {
		padding: 5px 10px;
		font-size: 11px;
		
		.anticon {
			font-size: 12px;
		}
		
		.btn-text {
			display: none; // Hide text on mobile, show only icon
		}
	}
}

@media (max-width: 480px) {
	.image-upload-box {
		height: 180px;
	}
	
	.upload-placeholder {
		padding: 16px;
		
		.upload-icon {
			font-size: 36px;
			margin-bottom: 10px;
		}
	}
}
</style>
