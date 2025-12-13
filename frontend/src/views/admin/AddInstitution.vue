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
										<img 
											:src="imageUrl" 
											alt="Institute preview" 
											class="preview-image"
											@error="handleImageError"
											@load="handleImageLoad"
										/>
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
								:loading="awardsLoading"
							>
								<a-select-option 
									v-for="award in availableAwards" 
									:key="award.id" 
									:value="getAwardValue(award)"
								>
									{{ award.category }}
								</a-select-option>
							</a-select>
						</a-form-item>

						<!-- Selected Awards with Marks Section (similar to criteria in Add Awards) -->
						<div v-if="selectedAwards.length > 0" class="selected-awards-section">
							<div class="awards-header">
								<h4>Selected Award Categories</h4>
								<p>Enter marks for each selected award category</p>
							</div>

							<div v-for="(award, index) in selectedAwards" :key="award.value" class="award-criterion-item">
								<a-row :gutter="16" align="middle">
									<a-col :span="16">
										<div class="award-name-display">
											<span class="award-category-text">{{ award.label }}</span>
										</div>
									</a-col>
									<a-col :span="6">
										<a-input-number
											v-model="award.marks"
											placeholder="Preliminary Marks"
											style="width: 100%"
											:min="0"
											:max="1000"
											size="large"
										/>
									</a-col>
									<a-col :span="2">
										<a-button
											type="danger"
											icon="delete"
											@click="removeAward(index)"
										/>
									</a-col>
								</a-row>
							</div>

							<div class="awards-summary">
								<a-alert
									:message="`Total Selected Awards: ${selectedAwards.length} | Total Marks: ${totalSelectedMarks}`"
									type="info"
									show-icon
								/>
							</div>
						</div>

						<a-form-item>
							<a-button type="primary" html-type="submit" :loading="loading" size="large">
								{{ isEditMode ? 'Update Institution' : 'Add Institution' }}
							</a-button>
							<a-button style="margin-left: 8px;" @click="resetForm" size="large">
								Reset
							</a-button>
						</a-form-item>
					</a-form>
				</a-card>
			</a-col>

			<a-col :span="24" :lg="8">
				<a-card title="Institution Statistics" class="mb-24">
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

		<!-- Institution Management Table -->
		<a-card title="Manage Institutions" class="mb-24">
			<a-table
				:columns="institutionTableColumns"
				:data-source="allInstitutions"
				:loading="tableLoading"
				:pagination="institutionPagination"
				@change="handleInstitutionTableChange"
				:scroll="{ x: 1200 }"
				size="small"
				rowKey="id"
			>
				<template slot="image" slot-scope="text">
					<div class="institution-image-tile" v-if="text">
						<img 
							:src="text" 
							:alt="'Institution image'" 
							class="institution-image"
							@error="handleTableImageError($event, text)"
							@load="handleTableImageLoad"
						/>
					</div>
					<span v-else class="no-image-text">No image</span>
				</template>
				<template slot="awards" slot-scope="text, record">
					<div v-if="!record.awards || !Array.isArray(record.awards) || record.awards.length === 0" class="no-awards">
						No awards
					</div>
					<div v-else class="awards-display">
						<div 
							v-for="(award, index) in record.awards" 
							:key="award.id || index"
							class="award-item-display"
						>
							<span class="award-name">{{ award.category ? award.category.split(' - ')[0] : (award.award_number || 'Award') }}</span>
							<a-tag color="blue" class="award-marks-tag">
								{{ award.marks || 0 }} marks
							</a-tag>
						</div>
					</div>
				</template>
				<template slot="action" slot-scope="text, record">
					<a href="javascript:void(0);" @click="editInstitution(record)" class="action-link">
						<a-icon type="edit" /> Edit
					</a>
					<a-divider type="vertical" />
					<a href="javascript:void(0);" @click="showDeleteInstitutionConfirm(record)" class="action-link danger">
						<a-icon type="delete" /> Delete
					</a>
				</template>
			</a-table>
		</a-card>
	</div>
</template>

<script>
	import apiService from '@/services/api';

	export default ({
		data() {
			return {
				form: this.$form.createForm(this),
				loading: false,
				tableLoading: false,
				allInstitutions: [],
				isEditMode: false,
				editingInstitutionId: null,
				currentPage: 1,
				pageSize: 10,
				totalInstitutions: 0,
				institutionPagination: {
					current: 1,
					pageSize: 10,
					total: 0,
					showTotal: (total) => `Total ${total} institutions`,
					showSizeChanger: true,
					showQuickJumper: true,
					pageSizeOptions: ['10', '20', '50', '100']
				},
				institutionTableColumns: [
					{
						title: 'Institution Name',
						dataIndex: 'name',
						key: 'name',
						width: 150
					},
					{
						title: 'Email',
						dataIndex: 'contact_email',
						key: 'email',
						width: 180
					},
					{
						title: 'Image',
						dataIndex: 'image_url',
						key: 'image',
						width: 100,
						scopedSlots: { customRender: 'image' }
					},
					{
						title: 'Awards',
						key: 'awards',
						width: 200,
						scopedSlots: { customRender: 'awards' }
					},
					{
						title: 'Action',
						key: 'action',
						width: 120,
						scopedSlots: { customRender: 'action' }
					}
				],
				imageUrl: '',
				imageFile: null,
				uploadLoading: false,
				selectedAwards: [],
				availableAwards: [],
				awardsLoading: false,
				institutionStats: {
					total: 0,
					universities: 0,
					colleges: 0,
					schools: 0
				}
			}
		},
		computed: {
			selectedAwardValues() {
				return this.selectedAwards.map(award => award.value);
			},
			awardOptions() {
				// Build awardOptions object from availableAwards
				const options = {};
				this.availableAwards.forEach(award => {
					const value = this.getAwardValue(award);
					options[value] = award.category;
				});
				return options;
			},
			totalSelectedMarks() {
				return this.selectedAwards.reduce((total, award) => {
					return total + (award.marks || 0);
				}, 0);
			}
		},
		beforeDestroy() {
			// Clean up object URL when component is destroyed
			if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
				URL.revokeObjectURL(this.imageUrl);
			}
		},
		methods: {
			/**
			 * Get award value from award object (uses award ID for consistency)
			 * @param {Object} award - Award object with id
			 * @returns {String} - Value in format like "award-14", "award-6", etc.
			 */
			getAwardValue(award) {
				if (!award || !award.id) {
					return null;
				}
				// Always use the award's database ID for reliable mapping
				return `award-${award.id}`;
			},
			/**
			 * Find award by value (reverse lookup)
			 * @param {String} value - Value like "award-14", "award-6a", etc.
			 * @returns {Object|null} - Award object or null
			 */
			findAwardByValue(value) {
				return this.availableAwards.find(award => this.getAwardValue(award) === value);
			},
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
						const award = this.findAwardByValue(value);
						this.selectedAwards.push({
							value: value,
							label: award ? award.category : this.awardOptions[value] || value,
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
								email: values.email,
								awardCategories: this.selectedAwards.map(award => ({
									value: award.value,
									label: award.label,
									marks: award.marks
								}))
							};
							
							let response;
							if (this.isEditMode && this.editingInstitutionId) {
								// Update existing institution
								// Pass imageFile if a new image was uploaded, otherwise it will keep the existing one
								response = await this.$api.updateInstitution(
									this.editingInstitutionId,
									institutionData,
									this.imageFile // Pass image file if user uploaded a new one
								);
								this.$message.success(response.message || 'Institution updated successfully!');
							} else {
								// Create new institution
								response = await apiService.createInstitution(
									institutionData,
									this.imageFile
								);
								this.$message.success(response.message || 'Institution added successfully!');
							}
							
							if (response.success || response.message) {
								this.resetForm();
								this.loadAllInstitutions();
								this.loadInstitutionStats();
							} else {
								this.$message.error(response.message || 'Failed to save institution');
							}
						} catch (error) {
							console.error('Error saving institution:', error);
							this.$message.error(error.message || 'Failed to save institution. Please try again.');
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
				this.isEditMode = false;
				this.editingInstitutionId = null;
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
			normalizeImageUrl(imageUrl) {
				if (!imageUrl) return null;
				
				// Convert to API endpoint URL if it's a backend/uploads path
				if (imageUrl.includes('/backend/uploads/')) {
					// Extract the relative path (e.g., institutions/filename.jpg)
					const pathMatch = imageUrl.match(/\/backend\/uploads\/(.+)$/);
					if (pathMatch && pathMatch[1]) {
						// Use the API uploads endpoint
						const apiBaseUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
						return `${apiBaseUrl}/uploads?path=${encodeURIComponent(pathMatch[1])}`;
					}
				}
				
				// Fix URL if it's missing the port (backend runs on port 8000)
				if (imageUrl.includes('localhost/') && !imageUrl.includes('localhost:')) {
					imageUrl = imageUrl.replace('http://localhost/', 'http://localhost:8000/');
					imageUrl = imageUrl.replace('https://localhost/', 'https://localhost:8000/');
				}
				
				// Ensure the URL is absolute
				if (!imageUrl.startsWith('http://') && !imageUrl.startsWith('https://') && !imageUrl.startsWith('blob:')) {
					// If it's a relative URL, make it absolute
					if (imageUrl.startsWith('//')) {
						imageUrl = window.location.protocol + imageUrl;
					} else if (imageUrl.startsWith('/')) {
						// Absolute path from root - use localhost:8000 as backend base
						imageUrl = 'http://localhost:8000' + imageUrl;
					} else {
						// Relative path
						imageUrl = 'http://localhost:8000/' + imageUrl;
					}
				}
				
				return imageUrl;
			},
			async loadAllInstitutions() {
			this.tableLoading = true;
			try {
				const response = await this.$api.getInstitutions({ 
					limit: this.pageSize,
					page: this.currentPage
				});
				const institutions = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
				
				// Normalize image URLs for all institutions
				this.allInstitutions = institutions.map(institution => {
					if (institution.image_url) {
						institution.image_url = this.normalizeImageUrl(institution.image_url);
					}
					return institution;
				});
				
				// Debug: Log first institution to check awards data
				if (this.allInstitutions.length > 0) {
					console.log('Sample institution data:', this.allInstitutions[0]);
					console.log('Awards for first institution:', this.allInstitutions[0].awards);
				}
				
				// Update pagination total
				if (response.data && response.data.pagination) {
					this.totalInstitutions = response.data.pagination.total || this.allInstitutions.length;
				} else {
					this.totalInstitutions = this.allInstitutions.length;
				}
				
				// Update pagination object
				this.institutionPagination = {
					...this.institutionPagination,
					current: this.currentPage,
					pageSize: this.pageSize,
					total: this.totalInstitutions
				};
			} catch (error) {
				console.error('Failed to load institutions:', error);
				this.$message.error('Failed to load institutions');
			} finally {
				this.tableLoading = false;
			}
		},

		handleInstitutionTableChange(pagination, filters, sorter) {
			this.currentPage = pagination.current;
			this.pageSize = pagination.pageSize;
			this.loadAllInstitutions();
		},

		async loadInstitutionStats() {
			try {
				const response = await this.$api.getInstitutions({ limit: 10000 });
				const institutions = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
				
				if (!Array.isArray(institutions)) {
					console.error('Institutions data is not an array:', institutions);
					return;
				}
				
				this.institutionStats = {
					total: institutions.length,
					universities: institutions.length,
					colleges: 0,
					schools: 0
				};
			} catch (error) {
				console.error('Failed to load institution stats:', error);
			}
		},
		async loadAwards() {
			this.awardsLoading = true;
			try {
				const response = await apiService.getAwards({ limit: 1000 }); // Get all awards
				const awards = (response.data && response.data.data) ? response.data.data : (Array.isArray(response.data) ? response.data : []);
				
				if (Array.isArray(awards)) {
					this.availableAwards = awards;
					console.log('Loaded awards:', this.availableAwards);
				} else {
					console.error('Awards data is not an array:', awards);
					this.availableAwards = [];
				}
			} catch (error) {
				console.error('Failed to load awards:', error);
				this.$message.error('Failed to load award categories. Please refresh the page.');
				this.availableAwards = [];
			} finally {
				this.awardsLoading = false;
			}
		},

		editInstitution(institution) {
			this.isEditMode = true;
			this.editingInstitutionId = institution.id;
			
			// Clean up any existing blob URL before loading new data
			if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
				URL.revokeObjectURL(this.imageUrl);
			}
			
			// Reset image file when editing
			this.imageFile = null;
			
			// Populate form with institution data
			this.$nextTick(() => {
				this.form.setFieldsValue({
					institutionName: institution.name,
					email: institution.contact_email || institution.email
				});
				
				// Load existing awards if any
				if (institution.awards && Array.isArray(institution.awards) && institution.awards.length > 0) {
					// Map awards from API format to frontend format
					this.selectedAwards = institution.awards.map(award => {
						// Use award ID directly for consistent mapping
						const awardValue = `award-${award.id}`;
						
						return {
							value: awardValue,
							label: award.category || this.awardOptions[awardValue] || 'Award',
							marks: award.marks || 0
						};
					});
				} else {
					this.selectedAwards = [];
				}
				
				// Load existing image if any - ensure it's a valid URL
				if (institution.image_url) {
					this.imageUrl = this.normalizeImageUrl(institution.image_url);
					console.log('Loading image for edit:', this.imageUrl);
				} else {
					// Clear image if no URL
					this.imageUrl = '';
				}
			});

			// Scroll to form
			window.scrollTo({ top: 0, behavior: 'smooth' });
		},

		showDeleteInstitutionConfirm(institution) {
			const self = this;
			this.$confirm({
				title: 'Confirm Delete',
				content: `Are you sure you want to delete "${institution.name}"? This action cannot be undone.`,
				okText: 'Yes, Delete',
				okType: 'danger',
				cancelText: 'Cancel',
				onOk() {
					return self.deleteInstitution(institution.id);
				}
			});
		},

		async deleteInstitution(institutionId) {
			try {
				await this.$api.deleteInstitution(institutionId);
				this.$message.success('Institution deleted successfully!');
				// Refresh the institutions list and stats
				this.loadAllInstitutions();
				this.loadInstitutionStats();
			} catch (error) {
				this.$message.error(error.message || 'Failed to delete institution');
			}
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
			},
			handleImageError(event) {
				// Handle image loading errors
				const failedUrl = this.imageUrl;
				console.error('Image failed to load:', failedUrl);
				
				// Prevent infinite loop by checking if we've already tried to fix this URL
				if (event.target.dataset.retryAttempt) {
					// Already tried once, show error and stop
					this.$message.warning('Image could not be loaded. The file may have been moved or deleted.', 4);
					// Clear the broken image
					if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
						URL.revokeObjectURL(this.imageUrl);
					}
					this.imageUrl = '';
					this.imageFile = null;
					return;
				}
				
				// If it's a remote URL (not a blob), try to fix it once
				if (failedUrl && !failedUrl.startsWith('blob:')) {
					let fixedUrl = failedUrl;
					
					// Convert to API endpoint URL if it's a backend/uploads path
					if (fixedUrl.includes('/backend/uploads/')) {
						const pathMatch = fixedUrl.match(/\/backend\/uploads\/(.+)$/);
						if (pathMatch && pathMatch[1]) {
							const apiBaseUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
							fixedUrl = `${apiBaseUrl}/uploads?path=${encodeURIComponent(pathMatch[1])}`;
						}
					}
					
					// Fix port issue - backend runs on port 8000
					if (fixedUrl.includes('localhost/') && !fixedUrl.includes('localhost:')) {
						fixedUrl = fixedUrl.replace('http://localhost/', 'http://localhost:8000/');
						fixedUrl = fixedUrl.replace('https://localhost/', 'https://localhost:8000/');
					}
					
					// Check if URL needs protocol or is relative
					if (fixedUrl.startsWith('//')) {
						// Protocol-relative URL, try adding http:
						fixedUrl = window.location.protocol + fixedUrl;
					} else if (!fixedUrl.startsWith('http://') && !fixedUrl.startsWith('https://')) {
						// Relative URL, try to make it absolute with correct port
						if (fixedUrl.startsWith('/')) {
							// Absolute path from root - use localhost:8000
							fixedUrl = 'http://localhost:8000' + fixedUrl;
						} else {
							// Relative path
							fixedUrl = 'http://localhost:8000/' + fixedUrl;
						}
					}
					
					// Only retry if URL was actually changed
					if (fixedUrl !== failedUrl) {
						event.target.dataset.retryAttempt = 'true';
						this.imageUrl = fixedUrl;
						event.target.src = fixedUrl;
						return;
					}
				}
				
				// If we get here, the image truly can't be loaded
				event.target.dataset.retryAttempt = 'true';
				this.$message.warning('Image could not be loaded. Please upload a new image.', 4);
			},
			handleImageLoad(event) {
				// Image loaded successfully
				console.log('Image loaded successfully:', this.imageUrl);
			},
			handleTableImageError(event, imageUrl) {
				// Handle image loading errors in table
				console.error('Table image failed to load:', imageUrl);
				
				// Try to fix the URL if it's malformed
				if (imageUrl && !imageUrl.includes('/api/uploads')) {
					// Try converting to API endpoint
					const normalizedUrl = this.normalizeImageUrl(imageUrl);
					if (normalizedUrl && normalizedUrl !== imageUrl) {
						// Update the src to try the normalized URL
						event.target.src = normalizedUrl;
						return;
					}
				}
				
				// If still fails, hide the broken image
				event.target.style.display = 'none';
			},
			handleTableImageLoad(event) {
				// Image loaded successfully in table
				event.target.style.display = 'block';
			}
		},
	
	created() {
		// Load initial data
		this.loadAwards();
		this.loadAllInstitutions();
		this.loadInstitutionStats();
	}
		
	})</script>

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
.action-link {
	color: #1890ff;
	transition: color 0.3s;
	
	&:hover {
		color: #40a9ff;
	}
	
	&.danger {
		color: #ff4d4f;
		
		&:hover {
			color: #ff7875;
		}
	}
}

// Institution Image Tile Styles
.institution-image-tile {
	width: 80px;
	height: 80px;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 8px;
	background-color: #ffffff;
	box-sizing: border-box;
}

.institution-image {
	max-width: 100%;
	max-height: 100%;
	width: auto;
	height: auto;
	object-fit: contain;
	display: block;
}

.no-image-text {
	color: #8c8c8c;
	font-size: 12px;
}
// Selected Awards Section Styles (matching criteria section in Add Awards)
.selected-awards-section {
	margin-top: 8px;
	margin-bottom: 24px;
	
	.awards-header {
		margin-bottom: 16px;
		
		h4 {
			margin: 0 0 4px 0;
			font-size: 16px;
			font-weight: 600;
			color: #1f2937;
		}
		
		p {
			margin: 0;
			color: #6b7280;
			font-size: 13px;
		}
	}
	
	.awards-summary {
		margin-top: 16px;
	}
}

.award-criterion-item {
	margin-bottom: 12px;
	padding: 16px;
	border: 1px solid #e5e7eb;
	border-radius: 8px;
	background-color: #f9fafb;
	border-left: 3px solid #3b82f6;
	transition: all 0.2s ease;
	
	&:hover {
		border-color: #3b82f6;
		box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
	}
}

.award-name-display {
	padding: 8px 12px;
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
	border-radius: 6px;
	border: 1px solid #e2e8f0;
	
	.award-category-text {
		font-size: 14px;
		font-weight: 500;
		color: #1e293b;
		line-height: 1.5;
		display: block;
	}
}

// Awards Display in Table (matching criteria display in Add Awards)
.awards-display {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.award-item-display {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 8px;
	padding: 6px 8px;
	background: #f8fafc;
	border-radius: 4px;
	border-left: 3px solid #3b82f6;
	
	.award-name {
		flex: 1;
		font-size: 13px;
		font-weight: 500;
		color: #1e293b;
		line-height: 1.4;
	}
	
	.award-marks-tag {
		flex-shrink: 0;
		font-size: 12px;
		font-weight: 600;
		margin: 0;
	}
}

.no-awards {
	color: #94a3b8;
	font-size: 13px;
	font-style: italic;
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
