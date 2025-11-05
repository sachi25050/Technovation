<template>

	<!-- Authors Table Card -->
	<a-card :bordered="false" class="header-solid h-full eval-modern" :bodyStyle="{padding: 0,}">

		<!-- Dropdown row under Evaluation heading -->
		<div class="dropdown-container mb-18">
			<a-row :gutter="16" type="flex">
				<a-col :span="24" :md="12">
					<a-select v-model="selectedInstitute" placeholder="Judger" allow-clear size="small" style="width: 100%">
						<a-select-option value="institute-a">Institute A</a-select-option>
						<a-select-option value="institute-b">Institute B</a-select-option>
						<a-select-option value="institute-c">Institute C</a-select-option>
					</a-select>
				</a-col>
				<a-col :span="24" :md="12">
					<a-select v-model="selectedBank" placeholder="Institute" allow-clear size="small" style="width: 100%">
						<a-select-option value="bank-1">Bank 1</a-select-option>
						<a-select-option value="bank-2">Bank 2</a-select-option>
						<a-select-option value="bank-3">Bank 3</a-select-option>
					</a-select>
				</a-col>
			</a-row>
			<a-row :gutter="16" type="flex" class="dropdown-row-gap">
				<a-col :span="24" :md="24">
					<a-select v-model="selectedCategory" placeholder="Award Category" allow-clear size="small" style="width: 100%">
						<a-select-option value="gold">Gold</a-select-option>
						<a-select-option value="silver">Silver</a-select-option>
						<a-select-option value="bronze">Bronze</a-select-option>
					</a-select>
				</a-col>
			</a-row>

			<!-- Description panel under dropdowns -->
			<div class="description-section mt-16">
				<fieldset class="description-fieldset">
					<legend class="description-legend">Marking Criteria - Please Select the Institute Name and Award Category</legend>
					<div class="description-body">
						<p class="m-0">After selecting the Institute and Award Category, the detailed evaluation criteria will appear here.</p>
					</div>
				</fieldset>
			</div>

			<!-- Evaluation layout matching attached design -->
			<div class="evaluation-layout mt-16">
				<div class="eval-section">
					<div class="eval-title">Presentation Marks</div>
					<table class="eval-table" cellspacing="0" cellpadding="0">
						<thead>
							<tr class="header-row">
								<th class="col-criteria" rowspan="2">Marking Criteria</th>
								<th class="col-group" colspan="2">Presentation Marks</th>
							</tr>
							<tr class="header-subrow">
								<th class="col-allocated">Allocated</th>
								<th class="col-achieved">Achieved</th>
							</tr>
						</thead>
							<tr class="row-criteria"><td></td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
							<tr class="row-criteria"><td></td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
							<tr class="row-criteria"><td></td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
							<tr class="row-criteria"><td></td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
							<tr class="row-criteria"><td></td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
							<tr class="row-summary"><td>Please Select the Institute Name and Award Category</td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
							<tr class="row-gap"><td colspan="3"></td></tr>
							<tr class="row-summary"><td>Please Select the Institute Name and Award Category</td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
							<tr class="row-summary"><td>Please Select the Institute Name and Award Category</td><td class="t-center"></td><td class="t-center"><input type="number" class="num-input" placeholder="" min="0" /></td></tr>
					</table>
				</div>


				<!-- Filters Row -->
				<div class="eval-filters mt-12">
					<div class="filter-item">
						<label>Award Category Filter</label>
						<a-select v-model="filterAwardCategory" placeholder="Select award category" allow-clear size="small" style="width: 100%">
							<a-select-option v-for="opt in awardCategoryOptions" :key="opt" :value="opt">{{ opt }}</a-select-option>
						</a-select>
					</div>
					<div class="filter-item">
						<label>Institute Filter</label>
						<a-select v-model="filterInstitute" placeholder="Select institute" allow-clear size="small" style="width: 100%">
							<a-select-option v-for="opt in instituteOptions" :key="opt" :value="opt">{{ opt }}</a-select-option>
						</a-select>
					</div>
				</div>

				<!-- History section -->
				<div class="history-section mt-16">
					<div class="history-header">History</div>
					<table class="history-table" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th class="h-col-date">Date/Time</th>
								<th class="h-col-user">User</th>
								<th class="h-col-action">Action</th>
								<th class="h-col-remarks">Remarks</th>
							</tr>
						</thead>
						<tbody>
							<tr class="h-row-empty"><td colspan="4">No history yet</td></tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</a-card>
	<!-- / Authors Table Card -->

</template>

<script>

	export default ({
		props: {
			data: {
				type: Array,
				default: () => [],
			},
			columns: {
				type: Array,
				default: () => [],
			},
		},
		data() {
			return {
			// Removed header filter buttons
			selectedInstitute: undefined,
			selectedBank: undefined,
			selectedCategory: undefined,
			// Filter dropdown models and options
			filterAwardCategory: undefined,
			filterInstitute: undefined,
			awardCategoryOptions: ['Gold','Silver','Bronze'],
			instituteOptions: ['Institute A','Institute B','Institute C'],
			}
		},
	})

</script>

<style scoped>
	.dropdown-container {
		background: #ffffff;
		border-radius: 8px;
		padding: 16px;
		box-shadow: 0 1px 2px rgba(0,0,0,0.05);
		margin-top: 0;
	}
	.dropdown-row-gap { margin-top: 8px; }
	.header-solid .ant-card-head { margin-bottom: 0; padding-top: 4px; padding-bottom: 0; border-bottom: none; }
	.header-solid .ant-card-head-title { margin: 0; padding: 0; line-height: 1.1; }
	.header-solid .ant-card-head-title h5 { margin: 0; }
	.header-solid .ant-card-body { padding-top: 8px; }

	/* Further reduce select size (supports Ant Design Vue v1 and v2) */
	.dropdown-container ::v-deep .ant-select { font-size: 12px; }
	/* v1 single selector */
	.dropdown-container ::v-deep .ant-select-selection--single { height: 26px; border-width: 2px; border-color: #7ec1ff; }
	.dropdown-container ::v-deep .ant-select-selection__rendered { line-height: 26px; }
	.dropdown-container ::v-deep .ant-select-arrow { right: 8px; }
	/* v2 selector */
	.dropdown-container ::v-deep .ant-select-selector { height: 26px !important; padding: 0 8px !important; border-width: 2px !important; border-color: #7ec1ff !important; }
	.dropdown-container ::v-deep .ant-select-selection-item,
	.dropdown-container ::v-deep .ant-select-selection-placeholder { line-height: 26px !important; }

	/* focus state */
	.dropdown-container ::v-deep .ant-select-focused .ant-select-selector { border-width: 2px !important; }

	/* Light blue highlight for borders (hover/focus) */
	/* Ant Design Vue v2 */
	.dropdown-container ::v-deep .ant-select:not(.ant-select-disabled):hover .ant-select-selector { border-color: #7ec1ff; }
	.dropdown-container ::v-deep .ant-select-focused .ant-select-selector,
	.dropdown-container ::v-deep .ant-select-open .ant-select-selector { border-color: #7ec1ff !important; box-shadow: 0 0 0 2px rgba(126, 193, 255, 0.2); }
	/* Ant Design Vue v1 */
	.dropdown-container ::v-deep .ant-select-selection { border-color: #7ec1ff; }
	.dropdown-container ::v-deep .ant-select-selection:hover { border-color: #7ec1ff; }
	.dropdown-container ::v-deep .ant-select-selection:focus,
	.dropdown-container ::v-deep .ant-select-selection:active { border-color: #7ec1ff; box-shadow: 0 0 0 2px rgba(126, 193, 255, 0.2); }

	/* Description section styling to match attached image */
	.description-section { margin-top: 8px; margin-bottom: 12px; }
	.description-fieldset {
		border: 1px solid #dbeafe; /* very light blue */
		border-radius: 6px;
		padding: 8px 10px 10px;
		background: #f5f9ff; /* lightest tint */
	}
	.description-legend {
		display: block;
		width: calc(100% - 12px);
		margin: -10px 6px 8px; /* pull inside fieldset and add spacing below */
		padding: 6px 10px;
		font-weight: 600;
		font-size: 13px;
		color: #0f172a;
		background: #e8f2ff; /* blue title bar */
		border: 1px solid #cfe2ff;
		border-radius: 4px;
	}
	.description-body {
		min-height: 100px;
		background: #ffffff;
		border: 1px solid #e6eefc;
		border-radius: 4px;
		padding: 12px;
	}

	/* Evaluation layout styles */
	.evaluation-layout { margin-top: 12px; }
	.eval-section { border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff; }
	.eval-title { padding: 6px 10px; font-weight: 700; color: #111827; border-bottom: 1px solid #cbd5e1; background: #f3f4f6; }
	.eval-table { width: 100%; border-collapse: collapse; }
	.eval-table th, .eval-table td { border: 1px solid #7f1d1d; padding: 6px 8px; font-size: 13px; }
	.eval-table thead th { background: #7f1d1d; color: #f3f4f6; text-align: center; font-weight: 700; }
	.eval-table .col-criteria { width: 70%; }
	.eval-table .col-allocated, .eval-table .col-achieved { width: 15%; }
	.t-center { text-align: center; }
	/* Row segmentation colors */
	.eval-table .row-criteria td { background: #f8c8c8; }
	.eval-table .row-score td { background: #f8c8c8; font-weight: 700; }
	.eval-table .row-summary td { background: #f3a5a5; font-weight: 700; }
	
	/* Controls bar */
	.eval-controls { display: flex; align-items: center; gap: 10px; background: #d1d5db; border: 1px solid #9ca3af; border-radius: 6px; padding: 10px; }
	.eval-controls .eval-spacer { flex: 1 1 auto; }
	.eval-controls .eval-pager .bar { display: inline-block; width: 12px; height: 14px; background: #e5e7eb; border: 1px solid #9ca3af; margin: 0 2px; }
	
	/* Filters */
	.eval-filters { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
	.filter-item label { display: block; background: #c7e1ef; border: 1px solid #9cc3da; padding: 6px 10px; font-weight: 600; color: #0f172a; border-radius: 4px 4px 0 0; }
	.filter-item :deep(.ant-select) { width: 100%; }

	/* Spacing adjustments for evaluation layout */
	.eval-section { margin-bottom: 8px; }
	.eval-controls { margin-top: 8px; margin-bottom: 8px; }
	.eval-filters { margin-top: 8px; gap: 8px; }

	/* Make 7th row (first summary) match 6th row color and add gap */
	.eval-table tbody tr.row-summary:first-of-type td {
		background: #f8c8c8;
		border-top: 6px solid #ffffff; /* small space above 7th row */
	}

	/* Gap between 6th and 7th rows */
	.eval-table tbody tr.row-summary:first-of-type td {
		background: #f8c8c8; /* match 6th row */
		border-top: 6px solid #ffffff; /* small gap */
	}

	/* Gap after 7th row before subsequent rows */
	.eval-table tbody tr.row-summary:first-of-type + tr.row-summary td {
		border-top: 0;
	}

	/* Visible spacer rows between 6→7 and 7→8 */
	.eval-table .row-gap td { height: 6px; background: transparent; border: none; }

	/* Remove any visible line/gap between 8th and 9th rows */
	.eval-table tbody tr.row-summary + tr.row-summary td { border-top: none; }

	/* Remove borders between consecutive summary rows (no gap between 8th and 9th) */
	.eval-table tbody tr.row-summary td { border-bottom: 0; }
	
	/* Add borders to summary rows while preserving 7th-row top gap */
	.eval-table tbody tr.row-summary td {
		border-left: 1px solid #7f1d1d;
		border-right: 1px solid #7f1d1d;
		border-bottom: 1px solid #7f1d1d;
	}
	/* Ensure border between subsequent summary rows (no gap between 8th and 9th) */
	.eval-table tbody tr.row-summary + tr.row-summary td { border-top: 1px solid #7f1d1d; }

	/* History section styling */
	.history-section { margin-top: 16px; }
	.history-header {
		font-weight: 700;
		color: #111827;
		border-bottom: 1px solid #cbd5e1;
		padding-bottom: 8px;
		margin-bottom: 12px;
	}
	.history-table { width: 100%; border-collapse: collapse; }
	.history-table th, .history-table td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 13px; }
	.history-table th { background: #f3f4f6; text-align: left; }
	.history-table .h-col-date { width: 15%; }
	.history-table .h-col-user { width: 20%; }
	.history-table .h-col-action { width: 20%; }
	.history-table .h-col-remarks { width: 45%; }
	.history-table .h-row-empty td { text-align: center; padding: 10px; color: #6b7280; }

	/* History section */
	.history-section { border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff; }
	.history-header { padding: 8px 12px; font-weight: 700; color: #111827; background: #f3f4f6; border-bottom: 1px solid #cbd5e1; }
	.history-table { width: 100%; border-collapse: collapse; }
	.history-table th, .history-table td { border: 1px solid #e5e7eb; padding: 8px 10px; font-size: 13px; text-align: left; }
	.history-table thead th { background: #f8fafc; font-weight: 700; color: #374151; }
	.h-col-date { width: 180px; }
	.h-col-user { width: 160px; }
	.h-col-action { width: 160px; }
	.h-row-empty td { text-align: center; color: #6b7280; font-style: italic; }

	/* Removed conflicting button styles to use pill-shaped design */

	/* Modern Creative UI theme */
	.eval-modern { background: #f8fafc; }
	.eval-modern :deep(.ant-card-body) { padding: 12px; }
	/* Card shells */
	.eval-modern .dropdown-container,
	.eval-modern .eval-section,
	.eval-modern .history-section { border-radius: 12px; box-shadow: 0 8px 24px rgba(15,23,42,0.06); border: 1px solid #e2e8f0; }
	/* Titles */
	.eval-modern .header-solid .ant-card-head-title h5,
	.eval-modern .eval-title,
	.eval-modern .history-header { color: #0f172a; font-weight: 600; }
	.eval-modern .eval-title,
	.eval-modern .history-header { background: #f1f5f9; }
	/* Filters */
	.eval-modern .filter-item label { background: #eaf3ff; border-color: #cfe0ff; color: #0f172a; }
	.eval-modern .filter-item :deep(.ant-select-selector) { height: 40px !important; border-radius: 10px !important; border-color: #e2e8f0 !important; padding: 0 12px !important; }
	/* Align text vertically inside the taller selector */
	.eval-modern .filter-item :deep(.ant-select-selection-item),
	.eval-modern .filter-item :deep(.ant-select-selection-placeholder) { line-height: 40px !important; }
	.eval-modern .filter-item :deep(.ant-select-focused .ant-select-selector),
	.eval-modern .filter-item :deep(.ant-select-open .ant-select-selector) { border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,.18) !important; }
	/* Buttons */
	.eval-modern :deep(.ant-btn-primary) { background: #2563eb; border-color: #1d4ed8; }
	.eval-modern :deep(.ant-btn-primary:hover) { background: #1d4ed8; border-color: #1e40af; }
	.eval-modern :deep(.ant-btn-default) { background: #ffffff; border-color: #e2e8f0; color: #0f172a; }
	.eval-modern :deep(.ant-btn-default:hover) { background: #eef2f7; }
	/* Table */
	.eval-modern .eval-table thead th { background: #f1f5f9; color: #0f172a; border-color: #e2e8f0; }
	.eval-modern .eval-table th,
	.eval-modern .eval-table td { padding: 12px 14px; border-color: #e2e8f0; }
	.eval-modern .eval-table tbody tr:hover td { background: #f8fafc; }
	/* Segments */
	.eval-modern .eval-table .row-criteria td { background: #ffecec; }
	.eval-modern .eval-table .row-score td { background: #ffe4e6; }
	.eval-modern .eval-table .row-summary td { background: #fecaca; }
	/* Ensure any default white cells also pick the lightest red */
	.eval-modern .eval-table tbody tr:not(.row-summary):not(.row-score):not(.row-gap) td { background: #ffecec; }
	/* History */
	.eval-modern .history-table thead th { background: #f1f5f9; color: #0f172a; }
	.eval-modern .history-table th,
	.eval-modern .history-table td { border-color: #e2e8f0; }
	/* Transitions */
	.eval-modern * { transition: background-color .15s ease, color .15s ease, box-shadow .2s ease, border-color .15s ease; }

	/* Increase height for all dropdowns on Evaluation page */
	.eval-modern .dropdown-container :deep(.ant-select-selector),
	.eval-modern .filter-item :deep(.ant-select-selector) { height: 40px !important; border-radius: 10px !important; padding: 0 12px !important; }
	.eval-modern .dropdown-container :deep(.ant-select-selection-item),
	.eval-modern .dropdown-container :deep(.ant-select-selection-placeholder),
	.eval-modern .filter-item :deep(.ant-select-selection-item),
	.eval-modern .filter-item :deep(.ant-select-selection-placeholder) { line-height: 40px !important; }

	/* Hide page header title in Evaluation page */
	.eval-modern :deep(.ant-page-header-heading-title) { display: none; }

	/* Reduce gap between page heading and first container */
	.eval-modern .dropdown-container { margin-top: 4px; }

	/* Numeric inputs in table */
	.eval-modern .num-input { width: 100%; max-width: 100px; height: 32px; padding: 0 8px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: right; }
	.eval-modern .num-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.18); }

	/* Two-row header styles (lightened and aligned) */
	.eval-modern .eval-table thead th { text-align: center; vertical-align: middle; padding: 12px 10px; }
	.eval-modern .eval-table thead .header-row th.col-group { background: #9b2c2c; color: #ffffff; border-color: #9b2c2c; }
	.eval-modern .eval-table thead .header-row th.col-criteria { background: #9b2c2c; color: #ffffff; border-color: #9b2c2c; }
	.eval-modern .eval-table thead .header-subrow th { background: #b54747; color: #ffffff; border-top: 1px solid #c75c5c; border-left-color: #b54747; border-right-color: #b54747; }
	/* Ensure left header text centered */
	.eval-modern .eval-table thead .header-row th.col-criteria { text-align: center; }

	/* Disable hover highlight for summary rows ('Please Select the Institute Name and Award Category') */
	.eval-modern .eval-table tbody tr.row-summary:hover td { background: #fecaca !important; }
	.eval-modern .eval-table tbody tr.row-summary:first-of-type:hover td { background: #f8c8c8 !important; }

	/* Modern polish: spacing, radii, shadows while keeping existing colors */
	.eval-modern .dropdown-container { border-color: #e2e8f0; border-radius: 12px; box-shadow: 0 6px 18px rgba(15,23,42,.06); }
	.eval-modern .evaluation-layout { gap: 12px; display: grid; }
	.eval-modern .eval-section { border-radius: 12px; box-shadow: 0 6px 18px rgba(15,23,42,.06); overflow: hidden; }
	.eval-modern .marks-table-wrapper,
	.eval-modern .history-section { border-radius: 12px; box-shadow: 0 6px 18px rgba(15,23,42,.06); }
	/* Table container corner rounding */
	.eval-modern .eval-table { border-radius: 0; overflow: hidden; }
	/* Reduce visual noise: thinner dividers but same hue */
	.eval-modern .eval-table th, .eval-table td { border-width: 1px; }
	/* Inputs and selects unified focus */
	.eval-modern :deep(input[type="number"]),
	.eval-modern :deep(.ant-select-selector),
	.eval-modern :deep(.ant-btn) { transition: box-shadow .2s ease, background-color .15s ease, color .15s ease, border-color .15s ease; }
	/* Keep brand maroon header but add subtle elevation */
	.eval-modern .eval-table thead .header-row th.col-criteria,
	.eval-modern .eval-table thead .header-row th.col-group,
	.eval-modern .eval-table thead .header-subrow th { box-shadow: inset 0 -1px 0 rgba(255,255,255,.08); }
	/* Compact section gaps */
	.eval-modern .table-section, .eval-modern .history-section { margin-top: 12px; }
	/* Buttons corners and padding */
	.eval-modern :deep(.ant-btn) { border-radius: 10px; padding: 0 14px; height: 36px; }
	/* Removed conflicting button hover styles to use pill-shaped design */

	/* Normalize table borders and lighten palette */
	.eval-modern .eval-table { border-collapse: collapse; border-spacing: 0; }
	.eval-modern .eval-table th,
	.eval-modern .eval-table td { border: 1px solid #e0b8b8; }
	/* Lighten header colors and keep consistent borders */
	.eval-modern .eval-table thead th { text-align: center; vertical-align: middle; padding: 6px 8px; font-size: 12px; border-color: #e0b8b8; }
	.eval-modern .eval-table thead .header-subrow th { padding: 6px 8px; font-size: 12px; }
	.eval-modern .eval-table thead .header-row th.col-criteria,
	.eval-modern .eval-table thead .header-row th.col-group,
	.eval-modern .eval-table thead .header-subrow th { line-height: 1.1; }
	.eval-modern .eval-table thead .header-row th.col-criteria,
	.eval-modern .eval-table thead .header-row th.col-group { background: #b95656; color: #ffffff; }
	.eval-modern .eval-table thead .header-subrow th { background: #cf6e6e; color: #ffffff; }
	/* Lighter body segment colors */
	.eval-modern .eval-table .row-criteria td { background: #ffe6e6; }
	.eval-modern .eval-table .row-score td { background: #ffd6d8; }
	.eval-modern .eval-table .row-summary td { background: #ffc6c6; }
	/* Maintain first summary slightly lighter tint */
	.eval-modern .eval-table tbody tr.row-summary:first-of-type td { background: #ffd2d2; }

	/* Emphasize Achieved inputs (3rd column) */
	.eval-modern .eval-table tbody td:nth-child(3) .num-input { font-size: 14px; font-weight: 600; height: 36px; }

	/* Fix header corner gaps: round outer cells and clip backgrounds */
	.eval-modern .eval-table { border-radius: 12px; overflow: hidden; }
	.eval-modern .eval-table thead .header-row th { background-clip: padding-box; }
	.eval-modern .eval-table thead .header-row th.col-criteria { border-top-left-radius: 0; }
	.eval-modern .eval-table thead .header-row th.col-group { border-top-right-radius: 0; }

	/* Refine top header edges */
	.eval-modern .eval-table thead .header-row th { border-bottom: 0; }
	.eval-modern .eval-table thead .header-subrow th { border-top: 0; }
	.eval-modern .eval-table thead .header-row th.col-criteria { border-left: 1px solid #e0b8b8; }
	.eval-modern .eval-table thead .header-row th.col-group { border-right: 1px solid #e0b8b8; }
	/* Keep top corners smooth */
	.eval-modern .eval-table thead .header-row th.col-criteria { border-top-left-radius: 12px; overflow: hidden; }
	.eval-modern .eval-table thead .header-row th.col-group { border-top-right-radius: 12px; overflow: hidden; }

	/* Compact body rows (exclude header) */
	.eval-modern .eval-table tbody td { padding: 6px 8px; line-height: 1.2; }
	.eval-modern .eval-table tbody .num-input { height: 30px; }
	/* Keep Achieved emphasis while compact */
	.eval-modern .eval-table tbody td:nth-child(3) .num-input { font-size: 14px; font-weight: 600; height: 30px; }

	/* History table uses same palette as eval table */
	.eval-modern .history-table { border-collapse: collapse; border-spacing: 0; }
	.eval-modern .history-table th,
	.eval-modern .history-table td { border: 1px solid #e0b8b8; }
	.eval-modern .history-table thead th { background: #b95656; color: #ffffff; text-align: left; padding: 8px 10px; }
	.eval-modern .history-table tbody tr td { background: #ffe6e6; padding: 6px 8px; }
	.eval-modern .history-table tbody tr:hover td { background: #ffd6d8; }

	/* Force square corners for the Presentation Marks container */
	.eval-modern .eval-section { border-radius: 0 !important; }

	/* Force square corners for the entire Presentation Marks block */
	.eval-modern .eval-section,
	.eval-modern .eval-section * { border-radius: 0 !important; }
	.eval-modern .eval-table { border-radius: 0 !important; overflow: visible; }
	.eval-modern .eval-title { border-radius: 0 !important; }
	.eval-modern .eval-table thead th { border-top-left-radius: 0 !important; border-top-right-radius: 0 !important; }
	
	/* Add border radius to Presentation Marks header */
	.eval-modern .eval-title { border-radius: 8px 8px 0 0 !important; }

	/* Rounded corners for Presentation Marks container and header (with clipping) */
	.eval-modern .eval-section { border-radius: 12px !important; overflow: hidden !important; }
	.eval-modern .eval-title { border-radius: 12px 12px 0 0 !important; }
	/* Keep the inner table square so it fits cleanly inside the rounded shell */
	.eval-modern .eval-table { border-radius: 0 !important; overflow: visible; }
	/* Ensure header cells do not reintroduce curves that fight the shell */
	.eval-modern .eval-table thead th { border-top-left-radius: 0 !important; border-top-right-radius: 0 !important; }

	/* New Pill-Shaped Button Design */
	.button {
		text-decoration: none;
		line-height: 1;
		border-radius: 2rem;
		overflow: hidden;
		position: relative;
		box-shadow: 0 4px 12px rgba(0,0,0,.15);
		background-color: #fff;
		color: #121212;
		border: none;
		cursor: pointer;
		display: flex;
		align-items: center;
		height: 48px;
		min-width: 140px;
		transition: all 0.3s ease;
	}

	.button:hover {
		transform: translateY(-2px);
		box-shadow: 0 6px 16px rgba(0,0,0,.2);
	}

	.button-content {
		display: flex;
		align-items: center;
		font-weight: 600;
		position: relative;
		overflow: hidden;
		width: 100%;
		height: 100%;
	}

	.button__icon {
		width: 48px;
		height: 48px;
		background-color: var(--clr);
		display: grid;
		place-items: center;
		flex-shrink: 0;
		border-radius: 2rem 0 0 2rem;
	}

	.button__icon svg {
		width: 20px;
		height: 20px;
		color: #fff;
	}

	.button__text {
		display: flex;
		align-items: center;
		justify-content: center;
		flex: 1;
		padding: 0 1.5rem;
		font-size: 14px;
		font-weight: 600;
		color: #374151;
		background-color: #fff;
		height: 48px;
		border-radius: 0 2rem 2rem 0;
		transition: all 0.3s ease;
	}

	.button:hover .button__text {
		background-color: #f8fafc;
		color: #1f2937;
	}

	/* Button color variants */
	.button--edit { --clr: #3b82f6; } /* Light blue for Load */
	.button--save { --clr: #10b981; } /* Light green for Submit */
	.button--delete { --clr: #ef4444; } /* Red for Delete/Exit */
	.button--view { --clr: #6b7280; } /* Light gray for Remove Filter/Reset */

	/* Responsive button adjustments */
	@media (max-width: 768px) {
		.button {
			min-width: 100px;
			height: 36px;
		}
		.button__icon {
			width: 40px;
			height: 36px;
		}
		.button__text {
			padding: 2px 1rem 2px;
			padding-left: .5rem;
			max-width: 120px;
		}
	}

	/* Override existing button styles in controls */
	.eval-controls .button {
		margin: 0 6px;
	}

	/* Ensure proper alignment and spacing in controls row */
	.eval-controls {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		flex-wrap: wrap;
		padding: 16px;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
	}

	/* Hide spacer elements */
	.eval-controls .eval-spacer,
	.eval-controls .eval-pager {
		display: none;
	}

	/* Responsive controls layout */
	@media (max-width: 768px) {
		.eval-controls {
			flex-direction: column;
			align-items: stretch;
			gap: 12px;
		}
		
		.eval-controls .button {
			width: 100%;
			margin: 0;
		}
		
		.eval-controls .eval-spacer {
			display: none;
		}
	}

	/* Additional pill button styling */
	.eval-controls .button {
		height: 48px;
		min-width: 140px;
	}

	/* Ensure proper button alignment */
	.eval-controls {
		align-items: center;
		justify-content: flex-start;
	}

</style>