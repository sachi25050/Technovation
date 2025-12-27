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
									:getPopupContainer="triggerNode => triggerNode.parentNode"
								>
									<a-select-option value="award-marks">Award Wise Report</a-select-option>
									<a-select-option value="institution-performance">Bank Wise Report</a-select-option>
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
									:getPopupContainer="triggerNode => triggerNode.parentNode"
								>
									<a-select-option value="excel">Excel</a-select-option>
									<a-select-option value="pdf">PDF</a-select-option>
								</a-select>
								</a-form-item>
							</a-col>
						</a-row>

						<a-form-item>
							<a-button type="primary" html-type="submit" :loading="generating" size="large">
								<a-icon type="file-excel" v-if="!generating" />
								{{ generating ? 'Generating...' : 'Generate Report' }}
							</a-button>
							<a-button style="margin-left: 8px;" @click="previewReport" size="large" :disabled="generating">
								<a-icon type="eye" />
								Preview
							</a-button>
							<a-button style="margin-left: 8px;" @click="resetForm" size="large" :disabled="generating">
								<a-icon type="reload" />
								Reset
							</a-button>
						</a-form-item>
					</a-form>
				</a-card>

				<!-- Generated Report Result -->
				<a-card title="Generated Report" v-if="generatedReport" class="mb-24">
					<a-result
						status="success"
						title="Report Generated Successfully!"
						:sub-title="`Report: ${generatedReport.title}`"
					>
						<template #extra>
							<a-button type="primary" size="large" @click="downloadReport">
								<a-icon type="download" />
								Download Report
							</a-button>
							<a-button size="large" @click="generateAnother" style="margin-left: 8px;">
								Generate Another
							</a-button>
						</template>
						
						<div class="report-details">
							<a-descriptions :column="2" bordered size="small">
								<a-descriptions-item label="Report Type">
									{{ getReportTypeName(generatedReport.report_type) }}
								</a-descriptions-item>
								<a-descriptions-item label="Format">
									{{ (generatedReport.report_format || '').toUpperCase() }}
								</a-descriptions-item>
								<a-descriptions-item label="Generated At">
									{{ formatDate(generatedReport.generated_at) }}
								</a-descriptions-item>
								<a-descriptions-item label="Status">
									<a-tag :color="generatedReport.status === 'completed' ? 'green' : 'orange'">
										{{ (generatedReport.status || '').toUpperCase() }}
									</a-tag>
								</a-descriptions-item>
							</a-descriptions>
						</div>
					</a-result>
				</a-card>

				<!-- Report Preview -->
				<a-card title="Report Preview" v-if="showPreview && !generatedReport" class="mb-24">
					<div class="preview-content">
						<h3>{{ previewData.title }}</h3>
						<a-descriptions :column="1" bordered size="small">
							<a-descriptions-item label="Report Type">
								{{ getReportTypeName(previewData.type) }}
							</a-descriptions-item>
						<a-descriptions-item label="Format">
							{{ (previewData.format || '').toUpperCase() }}
						</a-descriptions-item>
							<a-descriptions-item label="Description">
								{{ getReportDescription(previewData.type) }}
							</a-descriptions-item>
						</a-descriptions>
						
						<div class="preview-note" style="margin-top: 16px;">
							<a-alert
								message="Report Preview"
								:description="getPreviewDescription(previewData.type)"
								type="info"
								show-icon
							/>
						</div>
					</div>
				</a-card>

				<!-- Recent Reports -->
				<a-card title="Recent Reports" class="mb-24">
					<a-table 
						:data-source="recentReports" 
						:columns="reportColumns" 
						:loading="loadingReports"
						:pagination="{ pageSize: 5 }"
						size="small"
					>
						<template slot="status" slot-scope="status">
							<a-tag :color="getStatusColor(status)">
								{{ (status || '').toUpperCase() }}
							</a-tag>
						</template>
						<template slot="format" slot-scope="format">
							<a-icon :type="getFormatIcon(format)" style="margin-right: 4px;" />
							{{ (format || '').toUpperCase() }}
						</template>
						<template slot="action" slot-scope="text, record">
							<a-button 
								type="link" 
								size="small" 
								@click="downloadReportById(record.id)"
								:disabled="record.status !== 'completed'"
							>
								<a-icon type="download" /> Download
							</a-button>
						</template>
					</a-table>
				</a-card>
			</a-col>

			<a-col :span="24" :lg="8">
				<!-- Report Info -->
				<a-card title="Report Information" class="mb-24">
					<div class="info-section">
						<h4><a-icon type="file-excel" /> Award Wise Report</h4>
						<p>Generates a comprehensive Excel report matching the LankaPay Technnovation Awards marking scheme format:</p>
						<ul>
							<li>70% Quantitative (Preliminary) scores</li>
							<li>30% Qualitative (Presentation) scores</li>
							<li>Individual judge scores with "Ab" for absent</li>
							<li>Grouped by Award and Category (A, B, C)</li>
							<li>Automatic ranking within categories</li>
						</ul>
					</div>
					
					<a-divider />
					
					<div class="info-section">
						<h4><a-icon type="bank" /> Bank Wise Report</h4>
						<p>Generates a performance summary for each institution:</p>
						<ul>
							<li>Total awards participated</li>
							<li>Average and highest scores</li>
							<li>Overall ranking</li>
						</ul>
					</div>
				</a-card>

				<!-- Statistics -->
				<a-card title="Report Statistics" class="mb-24">
					<a-row :gutter="16">
						<a-col :span="12">
							<a-statistic 
								title="Total Reports" 
								:value="reportStats.total" 
								:prefix="h => h('a-icon', { props: { type: 'file' } })"
							/>
						</a-col>
						<a-col :span="12">
							<a-statistic 
								title="This Month" 
								:value="reportStats.thisMonth"
								:prefix="h => h('a-icon', { props: { type: 'calendar' } })"
							/>
						</a-col>
					</a-row>
				</a-card>
			</a-col>
		</a-row>
	</div>
</template>

<script>
import api from '@/services/api';

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';

export default {
	data() {
		return {
			form: this.$form.createForm(this),
			generating: false,
			showPreview: false,
			reportType: '',
			previewData: {},
			generatedReport: null,
			recentReports: [],
			loadingReports: false,
			reportStats: {
				total: 0,
				thisMonth: 0
			},
			reportColumns: [
				{ 
					title: 'Title', 
					dataIndex: 'title', 
					key: 'title',
					ellipsis: true,
					width: 200
				},
				{ 
					title: 'Type', 
					dataIndex: 'report_type', 
					key: 'report_type',
					customRender: (text) => this.getReportTypeName(text)
				},
				{ 
					title: 'Format', 
					dataIndex: 'report_format', 
					key: 'report_format',
					scopedSlots: { customRender: 'format' }
				},
				{ 
					title: 'Status', 
					dataIndex: 'status', 
					key: 'status',
					scopedSlots: { customRender: 'status' }
				},
				{ 
					title: 'Action', 
					key: 'action',
					scopedSlots: { customRender: 'action' }
				}
			]
		};
	},
	methods: {
		async handleSubmit(e) {
			if (e && e.preventDefault) e.preventDefault();
			
			this.form.validateFields(async (err, values) => {
				if (!err) {
					this.generating = true;
					this.generatedReport = null;
					
					try {
						const response = await api.post('/reporter/reports', {
							reportType: values.reportType,
							reportFormat: values.reportFormat,
							title: `${this.getReportTypeName(values.reportType)} - ${new Date().toLocaleDateString()}`
						});
						
						if (response.success) {
							this.generatedReport = response.data;
							this.$message.success('Report generated successfully!');
							this.showPreview = false;
							
							// Refresh recent reports
							this.fetchRecentReports();
							
							// Automatically download the report
							if (this.generatedReport && this.generatedReport.id) {
								await this.downloadReportById(this.generatedReport.id);
							}
						} else {
							this.$message.error(response.message || 'Failed to generate report');
						}
					} catch (error) {
						console.error('Error generating report:', error);
						this.$message.error(error.message || 'Failed to generate report');
					} finally {
						this.generating = false;
					}
				}
			});
		},
		
		async downloadReport() {
			if (!this.generatedReport) return;
			this.downloadReportById(this.generatedReport.id);
		},
		
		async downloadReportById(reportId) {
			try {
				const token = localStorage.getItem('auth_token');
				const downloadUrl = `${API_BASE_URL}/reporter/reports/${reportId}/download`;
				
				// Create a hidden anchor element to trigger download
				const response = await fetch(downloadUrl, {
					method: 'GET',
					headers: {
						'Authorization': `Bearer ${token}`
					}
				});
				
				if (!response.ok) {
					throw new Error('Failed to download report');
				}
				
				// Get filename from content-disposition header or use default
				const contentDisposition = response.headers.get('content-disposition');
				let filename = 'report.xlsx';
				if (contentDisposition) {
					const match = contentDisposition.match(/filename="(.+)"/);
					if (match) {
						filename = match[1];
					}
				}
				
				// Convert response to blob and download
				const blob = await response.blob();
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.href = url;
				a.download = filename;
				document.body.appendChild(a);
				a.click();
				window.URL.revokeObjectURL(url);
				document.body.removeChild(a);
				
				this.$message.success('Report downloaded successfully!');
			} catch (error) {
				console.error('Error downloading report:', error);
				this.$message.error(error.message || 'Failed to download report');
			}
		},
		
		generateAnother() {
			this.generatedReport = null;
			this.form.resetFields();
		},
		
		previewReport() {
			const values = this.form.getFieldsValue();
			if (values.reportType && values.reportFormat) {
				this.showPreview = true;
				this.previewData = {
					title: `${this.getReportTypeName(values.reportType)} Preview`,
					type: values.reportType,
					format: values.reportFormat
				};
			} else {
				this.$message.warning('Please fill in the required fields first!');
			}
		},
		
		resetForm() {
			this.form.resetFields();
			this.showPreview = false;
			this.generatedReport = null;
		},
		
		onReportTypeChange(value) {
			this.reportType = value;
		},
		
		async fetchRecentReports() {
			this.loadingReports = true;
			try {
				const response = await api.get('/reporter/reports', { limit: 10 });
				if (response.success) {
					this.recentReports = response.data.data || [];
					this.reportStats.total = response.data.pagination?.total || 0;
					
					// Calculate this month's reports
					const now = new Date();
					const thisMonth = this.recentReports.filter(r => {
						const reportDate = new Date(r.created_at);
						return reportDate.getMonth() === now.getMonth() && 
							   reportDate.getFullYear() === now.getFullYear();
					}).length;
					this.reportStats.thisMonth = thisMonth;
				}
			} catch (error) {
				console.error('Error fetching reports:', error);
			} finally {
				this.loadingReports = false;
			}
		},
		
		getReportTypeName(type) {
			const types = {
				'award-marks': 'Award Wise Report',
				'institution-performance': 'Bank Wise Report'
			};
			return types[type] || type;
		},
		
		getReportDescription(type) {
			const descriptions = {
				'award-marks': 'Comprehensive award-wise marking scheme with judge scores, weighted calculations (70% quantitative, 30% qualitative), and category rankings.',
				'institution-performance': 'Bank/institution performance summary with total scores, averages, and overall rankings.'
			};
			return descriptions[type] || '';
		},
		
		getPreviewDescription(type) {
			const descriptions = {
				'award-marks': 'This report will include all awards with their categories, institution scores from judges, weighted calculations (70% preliminary + 30% presentation), and rankings. Judge absences are marked as "Ab" and excluded from averages.',
				'institution-performance': 'This report will show each bank\'s performance across all awards, including participation count, average scores, highest scores, and overall rankings.'
			};
			return descriptions[type] || 'Click "Generate Report" to create your report.';
		},
		
		getStatusColor(status) {
			const colors = {
				'completed': 'green',
				'generating': 'blue',
				'pending': 'orange',
				'failed': 'red'
			};
			return colors[status] || 'default';
		},
		
		getFormatIcon(format) {
			const icons = {
				'excel': 'file-excel',
				'pdf': 'file-pdf'
			};
			return icons[format] || 'file';
		},
		
		formatDate(dateString) {
			if (!dateString) return '-';
			return new Date(dateString).toLocaleString();
		}
	},
	mounted() {
		this.fetchRecentReports();
	}
};

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
}

.report-details {
	margin-top: 24px;
}

.info-section {
	h4 {
		margin: 0 0 8px 0;
		font-size: 14px;
		font-weight: 600;
		color: #1f2937;
	}
	
	p {
		margin: 0 0 8px 0;
		color: #6b7280;
		font-size: 13px;
	}
	
	ul {
		margin: 0;
		padding-left: 20px;
		
		li {
			color: #6b7280;
			font-size: 12px;
			margin-bottom: 4px;
		}
	}
}

.mb-24 {
	margin-bottom: 24px;
}
</style>
