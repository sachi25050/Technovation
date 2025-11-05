<!-- 
	Generate Reports - Page to create and export reports
 -->

<template>
	<div>
		<!-- Page Header -->
		<div class="page-header">
			<h1>Generate Reports</h1>
			<p>Create and export award marks reports</p>
		</div>

		<a-row :gutter="24">
			<a-col :span="24" :lg="16">
				<!-- Report Configuration -->
				<a-card title="Report Configuration" class="mb-24">
					<a-form
						:form="form"
						@submit="handleSubmit"
						layout="vertical"
					>
						<a-row :gutter="16">
							<a-col :span="12">
								<a-form-item label="Report Type">
									<a-select
										v-decorator="[
											'reportType',
											{
												rules: [{ required: true, message: 'Please select report type!' }]
											}
										]"
										placeholder="Select report type"
										@change="onReportTypeChange"
									>
										<a-select-option value="award-marks">Award Marks Report</a-select-option>
										<a-select-option value="institution-performance">Institution Performance</a-select-option>
										<a-select-option value="judge-evaluation">Judge Evaluation Summary</a-select-option>
										<a-select-option value="detailed-analysis">Detailed Analysis</a-select-option>
									</a-select>
								</a-form-item>
							</a-col>
							<a-col :span="12">
								<a-form-item label="Report Format">
									<a-select
										v-decorator="[
											'reportFormat',
											{
												rules: [{ required: true, message: 'Please select report format!' }]
											}
										]"
										placeholder="Select format"
									>
										<a-select-option value="pdf">PDF</a-select-option>
										<a-select-option value="excel">Excel</a-select-option>
										<a-select-option value="csv">CSV</a-select-option>
									</a-select>
								</a-form-item>
							</a-col>
						</a-row>

						<a-form-item label="Select Awards">
							<a-select
								v-decorator="[
									'selectedAwards',
									{
										rules: [{ required: true, message: 'Please select at least one award!' }]
									}
								]"
								mode="multiple"
								placeholder="Select awards to include in report"
								style="width: 100%"
							>
								<a-select-option value="innovation-excellence">Innovation Excellence Award</a-select-option>
								<a-select-option value="academic-achievement">Academic Achievement Award</a-select-option>
								<a-select-option value="research-excellence">Research Excellence Award</a-select-option>
								<a-select-option value="leadership-excellence">Leadership Excellence Award</a-select-option>
								<a-select-option value="community-service">Community Service Award</a-select-option>
								<a-select-option value="sports-excellence">Sports Excellence Award</a-select-option>
							</a-select>
						</a-form-item>

						<a-row :gutter="16">
							<a-col :span="12">
								<a-form-item label="Filter by Institution">
									<a-select
										v-decorator="['institutionFilter']"
										placeholder="Select institution (optional)"
										allowClear
									>
										<a-select-option value="university-1">University of Colombo</a-select-option>
										<a-select-option value="university-2">University of Peradeniya</a-select-option>
										<a-select-option value="university-3">University of Moratuwa</a-select-option>
										<a-select-option value="university-4">University of Kelaniya</a-select-option>
										<a-select-option value="college-1">Sri Lanka Institute of Technology</a-select-option>
										<a-select-option value="school-1">Colombo International School</a-select-option>
									</a-select>
								</a-form-item>
							</a-col>
							<a-col :span="12">
								<a-form-item label="Date Range">
									<a-range-picker
										v-decorator="['dateRange']"
										style="width: 100%"
									/>
								</a-form-item>
							</a-col>
						</a-row>

						<a-form-item label="Report Options">
							<a-checkbox-group v-decorator="['reportOptions']">
								<a-checkbox value="include-comments">Include Judge Comments</a-checkbox>
								<a-checkbox value="include-statistics">Include Statistics</a-checkbox>
								<a-checkbox value="include-charts">Include Charts</a-checkbox>
								<a-checkbox value="detailed-scores">Detailed Score Breakdown</a-checkbox>
							</a-checkbox-group>
						</a-form-item>

						<a-form-item label="Report Title">
							<a-input
								v-decorator="['reportTitle']"
								placeholder="Enter custom report title (optional)"
							/>
						</a-form-item>

						<a-form-item>
							<a-button type="primary" html-type="submit" :loading="generating" size="large">
								Generate Report
							</a-button>
							<a-button style="margin-left: 8px;" @click="previewReport" size="large">
								Preview
							</a-button>
							<a-button style="margin-left: 8px;" @click="resetForm" size="large">
								Reset
							</a-button>
						</a-form-item>
					</a-form>
				</a-card>

				<!-- Report Preview -->
				<a-card title="Report Preview" v-if="showPreview" class="mb-24">
					<div class="preview-content">
						<h3>{{ previewData.title }}</h3>
						<p><strong>Report Type:</strong> {{ previewData.type }}</p>
						<p><strong>Format:</strong> {{ previewData.format }}</p>
						<p><strong>Awards:</strong> {{ previewData.awards.join(', ') }}</p>
						<p><strong>Date Range:</strong> {{ previewData.dateRange }}</p>
						<p><strong>Options:</strong> {{ previewData.options.join(', ') }}</p>
					</div>
				</a-card>
			</a-col>

			<a-col :span="24" :lg="8">
				<!-- Report Templates -->
				<a-card title="Quick Templates" class="mb-24">
					<a-list :data-source="reportTemplates" size="small">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title" @click="useTemplate(item)">
									{{ item.name }}
								</a>
								<template slot="description">
									{{ item.description }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-button type="primary" size="small" @click="useTemplate(item)">
									Use
								</a-button>
							</template>
						</a-list-item>
					</a-list>
				</a-card>

				<!-- Export Options -->
				<a-card title="Export Options" class="mb-24">
					<a-list :data-source="exportOptions" size="small">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title" @click="exportReport(item.type)">
									{{ item.name }}
								</a>
								<template slot="description">
									{{ item.description }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-button type="primary" size="small" @click="exportReport(item.type)">
									Export
								</a-button>
							</template>
						</a-list-item>
					</a-list>
				</a-card>

				<!-- Report Statistics -->
				<a-card title="Report Statistics">
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
								<div class="stat-value">{{ reportStats.downloads }}</div>
								<div class="stat-label">Downloads</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">{{ reportStats.avgSize }}</div>
								<div class="stat-label">Avg Size (MB)</div>
							</div>
						</a-col>
					</a-row>
				</a-card>
			</a-col>
		</a-row>
	</div>
</template>

<script>
	export default ({
		data() {
			return {
				form: this.$form.createForm(this),
				generating: false,
				showPreview: false,
				previewData: {},
				reportTemplates: [
					{
						name: 'Monthly Summary',
						description: 'Complete monthly award summary',
						reportType: 'award-marks',
						format: 'pdf',
						options: ['include-statistics', 'include-charts']
					},
					{
						name: 'Institution Performance',
						description: 'Performance analysis by institution',
						reportType: 'institution-performance',
						format: 'excel',
						options: ['detailed-scores', 'include-statistics']
					},
					{
						name: 'Judge Evaluation Report',
						description: 'Summary of judge evaluations',
						reportType: 'judge-evaluation',
						format: 'pdf',
						options: ['include-comments', 'include-statistics']
					}
				],
				exportOptions: [
					{
						name: 'Export to PDF',
						description: 'Generate PDF report',
						type: 'pdf'
					},
					{
						name: 'Export to Excel',
						description: 'Generate Excel spreadsheet',
						type: 'excel'
					},
					{
						name: 'Export to CSV',
						description: 'Generate CSV data file',
						type: 'csv'
					}
				],
				reportStats: {
					total: 156,
					thisMonth: 45,
					downloads: 234,
					avgSize: 2.5
				}
			}
		},
		methods: {
			handleSubmit(e) {
				e.preventDefault();
				this.form.validateFields((err, values) => {
					if (!err) {
						this.generating = true;
						console.log('Report configuration: ', values);
						
						// Simulate report generation
						setTimeout(() => {
							this.generating = false;
							this.$message.success('Report generated successfully!');
							this.showPreview = true;
							this.previewData = {
								title: values.reportTitle || `${values.reportType} Report`,
								type: values.reportType,
								format: values.reportFormat,
								awards: values.selectedAwards,
								dateRange: values.dateRange ? `${values.dateRange[0]} - ${values.dateRange[1]}` : 'All dates',
								options: values.reportOptions || []
							};
						}, 3000);
					}
				});
			},
			previewReport() {
				const values = this.form.getFieldsValue();
				if (values.reportType && values.reportFormat && values.selectedAwards) {
					this.showPreview = true;
					this.previewData = {
						title: values.reportTitle || `${values.reportType} Report`,
						type: values.reportType,
						format: values.reportFormat,
						awards: values.selectedAwards,
						dateRange: values.dateRange ? `${values.dateRange[0]} - ${values.dateRange[1]}` : 'All dates',
						options: values.reportOptions || []
					};
				} else {
					this.$message.warning('Please fill in the required fields first!');
				}
			},
			resetForm() {
				this.form.resetFields();
				this.showPreview = false;
			},
			onReportTypeChange(value) {
				// Update form based on report type
				console.log('Report type changed to:', value);
			},
			useTemplate(template) {
				this.form.setFieldsValue({
					reportType: template.reportType,
					reportFormat: template.format,
					reportOptions: template.options
				});
				this.$message.success(`Template "${template.name}" applied!`);
			},
			exportReport(type) {
				this.$message.success(`Exporting report as ${type.toUpperCase()}...`);
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

.preview-content {
	padding: 16px;
	background-color: #f9fafb;
	border-radius: 8px;
	
	h3 {
		margin: 0 0 16px 0;
		font-size: 18px;
		font-weight: 600;
		color: #1f2937;
	}
	
	p {
		margin: 8px 0;
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
