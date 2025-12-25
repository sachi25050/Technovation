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
          <a-form :form="form" @submit="handleSubmit" layout="vertical">
            <a-form-item label="Award Category">
              <a-input
                v-decorator="[
                  'awardCategory',
                  {
                    rules: [
                      {
                        required: true,
                        message: 'Please input award category!',
                      },
                    ],
                  },
                ]"
                placeholder="Enter award category"
              />
            </a-form-item>

            <a-form-item label="Award Description">
              <a-textarea
                v-decorator="[
                  'awardDescription',
                  {
                    rules: [
                      {
                        required: true,
                        message: 'Please input award description!',
                      },
                    ],
                  },
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
                        rules: [
                          {
                            required: true,
                            message: 'Please input presentation weightage!',
                          },
                          { validator: validateTotalWeightage },
                        ],
                      },
                    ]"
                    placeholder="Enter presentation weightage"
                    style="width: 100%"
                    :min="0"
                    :max="100"
                    :precision="2"
                    :step="1"
                    @change="
                      (value) => {
                        updateWeightages(value, 'presentation');
                      }
                    "
                  />
                </a-form-item>
              </a-col>
              <a-col :span="12">
                <a-form-item label="Preliminary Weightage">
                  <a-input-number
                    v-decorator="[
                      'preliminaryWeightage',
                      {
                        rules: [
                          {
                            required: true,
                            message: 'Please input preliminary weightage!',
                          },
                          { validator: validateTotalWeightage },
                        ],
                      },
                    ]"
                    placeholder="Enter preliminary weightage"
                    style="width: 100%"
                    :min="0"
                    :max="100"
                    :precision="2"
                    :step="1"
                    @change="
                      (value) => {
                        updateWeightages(value, 'preliminary');
                      }
                    "
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

            <div
              v-for="(criterion, index) in criteria"
              :key="index"
              class="criterion-item"
            >
              <a-row :gutter="16" align="middle">
                <a-col :span="16">
                  <a-input
                    v-model="criterion.name"
                    placeholder="Enter criteria name"
                    size="large"
                  />
                </a-col>
                <a-col :span="6">
                  <a-input
                    :value="criterion.marks"
                    @keypress="onlyNumbers"
                    @input="
                      (e) =>
                        handleCriterionMarksInput(criterion, e.target.value)
                    "
                    placeholder="Marks"
                    style="width: 100%"
                    maxlength="2"
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
              <a-button
                type="dashed"
                @click="addCriterion"
                icon="plus"
                size="large"
              >
                Add More Criteria
              </a-button>
            </div>

            <div class="criteria-summary" v-if="criteria.length > 0">
              <a-alert
                :message="`Total Allocated Marks: ${totalAllocatedMarks}${totalAllocatedMarks > 100 ? ' (Cannot exceed 100)' : ''}`"
                :type="totalAllocatedMarks > 100 ? 'error' : 'info'"
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
                    <th class="allocated-header">
                      Presentation Marks Allocated
                    </th>
                    <th class="achieved-header">Achieved</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(criterion, index) in criteria"
                    :key="index"
                    :class="{
                      'even-row': index % 2 === 0,
                      'criteria-row': true,
                    }"
                  >
                    <td class="criteria-name">
                      <span class="criteria-text">
                        {{ criterion.name || "Marking Field 0" + (index + 1) }}
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
                      <span class="weightage-value">{{
                        formatWeightage(presentationWeightage)
                      }}</span>
                    </td>
                    <td class="achieved-marks">
                      <span class="placeholder-dash">-</span>
                    </td>
                  </tr>
                  <!-- Preliminary Weightage Row -->
                  <tr class="weightage-row">
                    <td class="criteria-name">
                      <span class="criteria-text">Preliminary Weightage</span>
                    </td>
                    <td class="allocated-marks">
                      <span class="weightage-value">{{
                        formatWeightage(preliminaryWeightage)
                      }}</span>
                    </td>
                    <td class="achieved-marks">
                      <span class="placeholder-dash">-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </a-card>

        <div style="margin-top: 24px; margin-bottom: 24px">
          <a-button
            type="primary"
            @click="handleCreateAward"
            :loading="loading"
            size="large"
          >
            {{ isEditMode ? "Update Award" : "Create Award" }}
          </a-button>
          <a-button style="margin-left: 8px" @click="resetForm" size="large">
            Reset
          </a-button>
        </div>
      </a-col>

      <a-col :span="24" :lg="8">
        <a-card title="Award Statistics" class="mb-24">
          <a-row :gutter="16">
            <a-col :span="12">
              <div class="stat-item">
                <div class="stat-value">{{ awardStats.total }}</div>
                <div class="stat-label">Total Awards</div>
              </div>
            </a-col>
            <a-col :span="12">
              <div class="stat-item">
                <div class="stat-value">{{ awardStats.totalCriteria }}</div>
                <div class="stat-label">Total Criteria</div>
              </div>
            </a-col>
          </a-row>
          <!-- <a-row :gutter="16" style="margin-top: 16px;">
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">N/A</div>
								<div class="stat-label">N/A</div>
							</div>
						</a-col>
						<a-col :span="12">
							<div class="stat-item">
								<div class="stat-value">N/A</div>
								<div class="stat-label">N/A</div>
							</div>
						</a-col>
					</a-row> -->
        </a-card>
      </a-col>
    </a-row>

    <!-- Awards Management Table -->
    <a-card title="Manage Awards" class="mb-24">
      <a-table
        :columns="awardTableColumns"
        :data-source="allAwards"
        :loading="tableLoading"
        :pagination="awardPagination"
        @change="handleAwardTableChange"
        :scroll="{ x: 1200 }"
        size="small"
        rowKey="id"
      >
        <template slot="criteria" slot-scope="text, record">
          <div
            v-if="record.criteria && record.criteria.length > 0"
            class="criteria-display"
          >
            <div
              v-for="(criterion, index) in record.criteria"
              :key="index"
              class="criterion-item-display"
            >
              <span class="criterion-name">{{ criterion.name }}</span>
              <a-tag color="blue" class="criterion-marks">
                {{
                  formatMarks(criterion.allocated_marks || criterion.marks)
                }}
                marks
              </a-tag>
            </div>
          </div>
          <span v-else class="no-criteria">No criteria defined</span>
        </template>
        <template slot="action" slot-scope="text, record">
          <a
            href="javascript:void(0);"
            @click="editAward(record)"
            class="action-link"
          >
            <a-icon type="edit" /> Edit
          </a>
          <a-divider type="vertical" />
          <a
            href="javascript:void(0);"
            @click="showDeleteAwardConfirm(record)"
            class="action-link danger"
          >
            <a-icon type="delete" /> Delete
          </a>
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script>
import apiService from "@/services/api";

export default {
  data() {
    return {
      form: this.$form.createForm(this),
      loading: false,
      tableLoading: false,
      allAwards: [],
      isEditMode: false,
      editingAwardId: null,
      currentPage: 1,
      pageSize: 10,
      totalAwards: 0,
      awardPagination: {
        current: 1,
        pageSize: 10,
        total: 0,
        showTotal: (total) => `Total ${total} awards`,
        showSizeChanger: true,
        showQuickJumper: true,
        pageSizeOptions: ["10", "20", "50", "100"],
      },
      awardTableColumns: [
        {
          title: "Award Category",
          dataIndex: "category",
          key: "category",
          width: 250,
        },
        {
          title: "Description",
          dataIndex: "description",
          key: "description",
          width: 300,
          ellipsis: true,
        },
        {
          title: "Criteria & Marks",
          key: "criteria",
          width: 350,
          scopedSlots: { customRender: "criteria" },
        },
        {
          title: "Presentation Weightage",
          dataIndex: "presentation_weightage",
          key: "presentation_weightage",
          width: 150,
          render: (text) =>
            text !== null && text !== undefined
              ? `${parseFloat(text).toFixed(2)}%`
              : "-",
        },
        {
          title: "Preliminary Weightage",
          dataIndex: "preliminary_weightage",
          key: "preliminary_weightage",
          width: 150,
          render: (text) =>
            text !== null && text !== undefined
              ? `${parseFloat(text).toFixed(2)}%`
              : "-",
        },
        {
          title: "Action",
          key: "action",
          width: 120,
          scopedSlots: { customRender: "action" },
        },
      ],
      criteria: [
        {
          name: "",
          marks: null,
        },
      ],
      recentAwards: [],
      awardStats: {
        total: 0,
        totalCriteria: 0,
      },
      presentationWeightage: 0,
      preliminaryWeightage: 0,
    };
  },
  computed: {
    totalAllocatedMarks() {
      return this.criteria.reduce((total, criterion) => {
        // Ensure marks is parsed as a number to avoid string concatenation
        const marks = parseFloat(criterion.marks) || 0;
        return total + marks;
      }, 0);
    },
  },
  methods: {
    validateTotalWeightage(rule, value, callback) {
      const presentation = this.form.getFieldValue("presentationWeightage");
      const preliminary = this.form.getFieldValue("preliminaryWeightage");

      // Only validate when both fields have values
      if (
        presentation === undefined ||
        presentation === null ||
        presentation === "" ||
        preliminary === undefined ||
        preliminary === null ||
        preliminary === ""
      ) {
        callback();
        return;
      }

      const total = Number(presentation) + Number(preliminary);

      if (total !== 100) {
        callback("Total of Presentation and Preliminary weightage should 100%");
      } else {
        callback();
      }
    },
    async handleCreateAward() {
      // Manually validate form fields
      this.form.validateFields(async (err, values) => {
        if (err) {
          // Show validation errors
          const firstError = Object.keys(err)[0];
          if (firstError && err[firstError] && err[firstError].errors) {
            this.$message.error(
              err[firstError].errors[0].message ||
                "Please fill in all required fields"
            );
          } else {
            this.$message.error("Please fill in all required fields correctly");
          }
          return;
        }

        // Validate criteria
        const validCriteria = this.criteria.filter((c) => c.name && c.marks);
        if (validCriteria.length === 0) {
          this.$message.error("Please add at least one criterion!");
          return;
        }

        // Validate total allocated marks does not exceed 100
        if (this.totalAllocatedMarks > 100) {
          this.$message.error("Total Allocated Marks cannot exceed 100!");
          return;
        }

        // Validate weightage sum
        const presentationWeightage = values.presentationWeightage || 0;
        const preliminaryWeightage = values.preliminaryWeightage || 0;
        const totalWeightage = presentationWeightage + preliminaryWeightage;

        this.loading = true;

        try {
          // Prepare criteria data for API
          const criteriaData = validCriteria.map((criterion, index) => ({
            name: criterion.name.trim(),
            allocated_marks: parseFloat(criterion.marks),
            description: criterion.description
              ? criterion.description.trim()
              : null,
            display_order: index + 1,
          }));

          // Prepare award data matching backend API expectations
          const awardData = {
            awardCategory: values.awardCategory.trim(),
            awardDescription: values.awardDescription.trim(),
            presentationWeightage: parseFloat(presentationWeightage),
            preliminaryWeightage: parseFloat(preliminaryWeightage),
            criteria: criteriaData,
          };

          console.log("Submitting award data:", awardData);

          let response;
          if (this.isEditMode && this.editingAwardId) {
            // Update existing award
            response = await apiService.updateAward(
              this.editingAwardId,
              awardData
            );
            this.$message.success(
              response.message || "Award updated successfully!"
            );
          } else {
            // Create new award
            response = await apiService.createAward(awardData);
            this.$message.success(
              response.message || "Award created successfully!"
            );
          }

          this.loading = false;

          if (response && response.success) {
            this.resetForm();
            // Refresh awards list and stats
            await this.loadAllAwards();
            await this.loadAwardStats();
            await this.loadRecentAwards();
          } else {
            this.$message.error(response?.message || "Failed to save award");
          }
        } catch (error) {
          this.loading = false;
          console.error("Error creating award:", error);

          // Handle validation errors from backend
          if (error.data && error.data.errors) {
            // Handle both object and array error formats
            const errors = error.data.errors;
            if (typeof errors === "object" && !Array.isArray(errors)) {
              // Object format: { field: "message" }
              const errorMessages = Object.values(errors);
              errorMessages.forEach((msg) => {
                if (typeof msg === "string") {
                  this.$message.error(msg);
                } else if (Array.isArray(msg)) {
                  msg.forEach((m) => this.$message.error(m));
                }
              });
            } else if (Array.isArray(errors)) {
              // Array format: ["message1", "message2"]
              errors.forEach((msg) => this.$message.error(msg));
            }
          } else if (error.data && error.data.message) {
            // Handle error message directly
            this.$message.error(error.data.message);
          } else {
            // Generic error message
            this.$message.error(
              error.message || "Failed to create award. Please try again."
            );
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
          name: "",
          marks: null,
        },
      ];
      this.presentationWeightage = 0;
      this.preliminaryWeightage = 0;
      this.isEditMode = false;
      this.editingAwardId = null;
    },
    addCriterion() {
      this.criteria.push({
        name: "",
        marks: null,
      });
    },
    removeCriterion(index) {
      if (this.criteria.length > 1) {
        this.criteria.splice(index, 1);
      }
    },
    onlyNumbers(e) {
      // Allow only digits (0–9)
      if (!/^[0-9]$/.test(e.key)) {
        e.preventDefault();
      }
    },
    handleCriterionMarksInput(criterion, value) {
      // Remove non-numeric characters
      const cleanedValue = value.replace(/\D/g, "");

      // Limit to 2 digits
      const limitedValue = cleanedValue.slice(0, 2);

      criterion.marks = limitedValue !== "" ? Number(limitedValue) : null;
    },
    /**
     * Format marks to remove unnecessary trailing zeros
     * 63.80 -> 63.8, 64.00 -> 64, 63.85 -> 63.85
     */
    formatMarks(marks) {
      if (marks === null || marks === undefined) return 0;
      const num = parseFloat(marks);
      if (isNaN(num)) return 0;
      return parseFloat(num.toFixed(2));
    },
    getCategoryColor(category) {
      const colors = {
        academic: "blue",
        innovation: "green",
        leadership: "purple",
        research: "orange",
        community: "cyan",
        sports: "red",
        arts: "magenta",
      };
      return colors[category] || "default";
    },
    updateWeightages(value, field) {
      // This method is called when weightage input fields change
      // Update the weightage values directly from the event value

      if (field === "presentation") {
        // Use the value directly from the event (it's already a number)
        if (value !== undefined && value !== null && !isNaN(value)) {
          this.presentationWeightage = Number(value);
        }
      } else if (field === "preliminary") {
        // Use the value directly from the event (it's already a number)
        if (value !== undefined && value !== null && !isNaN(value)) {
          this.preliminaryWeightage = Number(value);
        }
      } else {
        // Fallback: get all values from form
        const values = this.form.getFieldsValue();
        this.presentationWeightage =
          values.presentationWeightage !== undefined &&
          values.presentationWeightage !== null
            ? Number(values.presentationWeightage) || 0
            : 0;
        this.preliminaryWeightage =
          values.preliminaryWeightage !== undefined &&
          values.preliminaryWeightage !== null
            ? Number(values.preliminaryWeightage) || 0
            : 0;
      }
    },
    formatWeightage(value) {
      if (value === null || value === undefined || value === "") return "0.00%";

      let numValue = parseFloat(value);

      // If value is NaN, return 0
      if (isNaN(numValue)) return "0.00%";

      // Display the value exactly as entered with % symbol
      return numValue.toFixed(2) + "%";
    },

    async loadRecentAwards() {
      try {
        const response = await apiService.getAwards({ page: 1, limit: 5 });
        if (response.success && response.data && response.data.data) {
          this.recentAwards = response.data.data.map((award) => ({
            name: award.description || award.category,
            category: award.category,
            totalMarks:
              (award.presentation_weightage || 0) +
              (award.preliminary_weightage || 0),
          }));
        }
      } catch (error) {
        console.error("Error loading recent awards:", error);
      }
    },
    async loadAllAwards() {
      this.tableLoading = true;
      try {
        const response = await apiService.getAwards({
          limit: this.pageSize,
          page: this.currentPage,
        });
        const awards =
          response.data && response.data.data
            ? response.data.data
            : Array.isArray(response.data)
            ? response.data
            : [];
        this.allAwards = Array.isArray(awards) ? awards : [];

        // Update pagination total
        if (response.data && response.data.pagination) {
          this.totalAwards =
            response.data.pagination.total || this.allAwards.length;
        } else {
          this.totalAwards = this.allAwards.length;
        }

        // Update pagination object
        this.awardPagination = {
          ...this.awardPagination,
          current: this.currentPage,
          pageSize: this.pageSize,
          total: this.totalAwards,
        };
      } catch (error) {
        console.error("Failed to load awards:", error);
        this.$message.error("Failed to load awards");
      } finally {
        this.tableLoading = false;
      }
    },
    handleAwardTableChange(pagination, filters, sorter) {
      this.currentPage = pagination.current;
      this.pageSize = pagination.pageSize;
      this.loadAllAwards();
    },
    async loadAwardStats() {
      try {
        const response = await apiService.getAwards({ limit: 10000 });
        const awards =
          response.data && response.data.data
            ? response.data.data
            : Array.isArray(response.data)
            ? response.data
            : [];

        if (!Array.isArray(awards)) {
          console.error("Awards data is not an array:", awards);
          return;
        }

        // Count total criteria across all awards
        let totalCriteria = 0;
        awards.forEach((award) => {
          if (award.criteria && Array.isArray(award.criteria)) {
            totalCriteria += award.criteria.length;
          }
        });

        this.awardStats = {
          total: awards.length,
          totalCriteria: totalCriteria,
        };
      } catch (error) {
        console.error("Failed to load award stats:", error);
      }
    },
    async editAward(award) {
      this.isEditMode = true;
      this.editingAwardId = award.id;

      // Load full award details with criteria
      try {
        const response = await apiService.getAward(award.id);
        const fullAward = response.data;

        // Populate form with award data
        this.$nextTick(() => {
          this.form.setFieldsValue({
            awardCategory: fullAward.category || fullAward.award_number || "",
            awardDescription: fullAward.description || "",
            presentationWeightage: fullAward.presentation_weightage || 0,
            preliminaryWeightage: fullAward.preliminary_weightage || 0,
          });

          // Load criteria - ensure marks are parsed as numbers
          if (fullAward.criteria && Array.isArray(fullAward.criteria)) {
            this.criteria = fullAward.criteria.map((c) => {
              // Parse marks as number to avoid string concatenation issues
              const rawMarks = c.allocated_marks || c.marks;
              const parsedMarks = rawMarks !== null && rawMarks !== undefined 
                ? parseFloat(rawMarks) 
                : null;
              
              return {
                name: c.name || "",
                marks: !isNaN(parsedMarks) ? parsedMarks : null,
                description: c.description || null,
              };
            });
          } else {
            this.criteria = [{ name: "", marks: null }];
          }

          // Sync weightages from form to ensure they're displayed correctly
          this.updateWeightages();
        });

        // Scroll to form
        window.scrollTo({ top: 0, behavior: "smooth" });
      } catch (error) {
        console.error("Error loading award details:", error);
        this.$message.error("Failed to load award details");
      }
    },
    showDeleteAwardConfirm(award) {
      const self = this;
      this.$confirm({
        title: "Confirm Delete",
        content: `Are you sure you want to delete "${
          award.category || award.award_number
        }"? This action cannot be undone.`,
        okText: "Yes, Delete",
        okType: "danger",
        cancelText: "Cancel",
        onOk() {
          return self.deleteAward(award.id);
        },
      });
    },
    async deleteAward(awardId) {
      try {
        await apiService.deleteAward(awardId);
        this.$message.success("Award deleted successfully!");
        // Refresh the awards list and stats
        this.loadAllAwards();
        this.loadAwardStats();
        this.loadRecentAwards();
      } catch (error) {
        this.$message.error(error.message || "Failed to delete award");
      }
    },
  },
  mounted() {
    // Load initial data
    this.loadAllAwards();
    this.loadAwardStats();
    this.loadRecentAwards();
  },
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
  font-family: "Inter", "Poppins", "Roboto", -apple-system, BlinkMacSystemFont,
    sans-serif;
}

.modern-marks-table thead {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  position: relative;

  &::after {
    content: "";
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
  color: #1e293b;
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
    color: #1e293b;
    line-height: 1.4;
    display: block;
    font-size: 13px;
  }
}

.allocated-marks {
  text-align: center;

  .allocated-value {
    font-weight: 700;
    color: #2563eb;
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
    color: #1e293b;
    font-size: 13px;
    display: inline-block;
    padding: 4px 8px;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-radius: 6px;
    min-width: 50px;
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.1);
  }
}

// Criteria Display in Table
.criteria-display {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.criterion-item-display {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 8px;
  background: #f8fafc;
  border-radius: 4px;
  border-left: 3px solid #3b82f6;

  .criterion-name {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    line-height: 1.4;
  }

  .criterion-marks {
    flex-shrink: 0;
    font-size: 12px;
    font-weight: 600;
    margin: 0;
  }
}

.no-criteria {
  color: #94a3b8;
  font-size: 13px;
  font-style: italic;
}
</style>
