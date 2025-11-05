<!-- 
	Judger Dashboard - Overview for judges
 -->

<template>
	<div>
		<!-- Page Header -->
		<div class="page-header">
			<h1>Judger Dashboard</h1>
			<p>Welcome to the judging panel</p>
		</div>

		<!-- Counter Widgets -->
		<a-row :gutter="24">
			<a-col :span="24" :lg="12" :xl="6" class="mb-24" v-for="(stat, index) in stats" :key="index">
				<!-- Widget Card -->
				<WidgetCounter
					:title="stat.title"
					:value="stat.value"
					:prefix="stat.prefix"
					:suffix="stat.suffix"
					:icon="stat.icon"
					:status="stat.status"
				></WidgetCounter>
				<!-- / Widget Card -->
			</a-col>
		</a-row>
		<!-- / Counter Widgets -->

		<!-- Charts -->
		<a-row :gutter="24" type="flex" align="stretch">
			<a-col :span="24" :lg="10" class="mb-24">
				<!-- Evaluation Progress Card -->
				<CardBarChart></CardBarChart>
				<!-- Evaluation Progress Card -->
			</a-col>
			<a-col :span="24" :lg="14" class="mb-24">
				<!-- Evaluation Timeline Card -->
				<CardLineChart></CardLineChart>
				<!-- / Evaluation Timeline Card -->
			</a-col>
		</a-row>
		<!-- / Charts -->

		<!-- Pending Evaluations & Recent Activity -->
		<a-row :gutter="24" type="flex" align="stretch">
			<!-- Pending Evaluations -->
			<a-col :span="24" :lg="12" class="mb-24">
				<a-card title="Pending Evaluations" class="h-100">
					<a-list :data-source="pendingEvaluations" size="large">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title" @click="navigateToEvaluation(item.id)">
									{{ item.title }}
								</a>
								<template slot="description">
									{{ item.institution }} • Due: {{ item.dueDate }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-button type="primary" size="small" @click="navigateToEvaluation(item.id)">
									Evaluate
								</a-button>
							</template>
						</a-list-item>
					</a-list>
					<div slot="extra">
						<a-button type="link" @click="navigateToEvaluate">
							View All
						</a-button>
					</div>
				</a-card>
			</a-col>

			<!-- Recent Activity -->
			<a-col :span="24" :lg="12" class="mb-24">
				<CardOrderHistory></CardOrderHistory>
			</a-col>
		</a-row>
		<!-- / Pending Evaluations & Recent Activity -->

		<!-- Quick Actions -->
		<a-row :gutter="24" type="flex" align="stretch">
			<a-col :span="24" :lg="8" class="mb-24">
				<a-card title="Quick Actions" class="h-100">
					<a-list :data-source="quickActions" size="large">
						<a-list-item slot="renderItem" slot-scope="item">
							<a-list-item-meta>
								<a slot="title" @click="navigateTo(item.route)">
									{{ item.title }}
								</a>
								<template slot="description">
									{{ item.description }}
								</template>
							</a-list-item-meta>
							<template slot="actions">
								<a-button type="primary" size="small" @click="navigateTo(item.route)">
									Go
								</a-button>
							</template>
						</a-list-item>
					</a-list>
				</a-card>
			</a-col>
			<a-col :span="24" :lg="16" class="mb-24">
				<!-- Evaluation Guidelines -->
				<a-card title="Evaluation Guidelines" class="h-100">
					<a-timeline>
						<a-timeline-item color="green">
							<h4>Review Criteria</h4>
							<p>Carefully review all marking criteria for each award before evaluation.</p>
						</a-timeline-item>
						<a-timeline-item color="blue">
							<h4>Fair Assessment</h4>
							<p>Ensure fair and unbiased evaluation of all submissions.</p>
						</a-timeline-item>
						<a-timeline-item color="orange">
							<h4>Documentation</h4>
							<p>Provide detailed feedback and justification for scores.</p>
						</a-timeline-item>
						<a-timeline-item color="red">
							<h4>Deadlines</h4>
							<p>Complete evaluations within the specified timeframes.</p>
						</a-timeline-item>
					</a-timeline>
				</a-card>
			</a-col>
		</a-row>
		<!-- / Quick Actions -->

	</div>
</template>

<script>
	// Bar chart for "Evaluation Progress" card.
	import CardBarChart from '../../components/Cards/CardBarChart' ;

	// Line chart for "Evaluation Timeline" card.
	import CardLineChart from '../../components/Cards/CardLineChart' ;

	// Counter Widgets
	import WidgetCounter from '../../components/Widgets/WidgetCounter' ;

	// Order History card component.
	import CardOrderHistory from '../../components/Cards/CardOrderHistory' ;

	// Judger Dashboard stats
	const stats = [
		{
			title: "Pending Evaluations",
			value: 12,
			suffix: "items",
			status: "warning",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M10 2C7.79086 2 6 3.79086 6 6V7H5C4.49046 7 4.06239 7.38314 4.00612 7.88957L3.00612 16.8896C2.97471 17.1723 3.06518 17.455 3.25488 17.6669C3.44458 17.8789 3.71556 18 4 18H16C16.2844 18 16.5554 17.8789 16.7451 17.6669C16.9348 17.455 17.0253 17.1723 16.9939 16.8896L15.9939 7.88957C15.9376 7.38314 15.5096 7 15 7H14V6C14 3.79086 12.2091 2 10 2ZM12 7V6C12 4.89543 11.1046 4 10 4C8.89543 4 8 4.89543 8 6V7H12ZM6 10C6 9.44772 6.44772 9 7 9C7.55228 9 8 9.44772 8 10C8 10.5523 7.55228 11 7 11C6.44772 11 6 10.5523 6 10ZM13 9C12.4477 9 12 9.44772 12 10C12 10.5523 12.4477 11 13 11C13.5523 11 14 10.5523 14 10C14 9.44772 13.5523 9 13 9Z" fill="#111827"/>
				</svg>`,
		},
		{
			title: "Completed Today",
			value: 8,
			suffix: "evaluations",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M16.7071 5.29289C17.0976 5.68342 17.0976 6.31658 16.7071 6.70711L8.70711 14.7071C8.31658 15.0976 7.68342 15.0976 7.29289 14.7071L3.29289 10.7071C2.90237 10.3166 2.90237 9.68342 3.29289 9.29289C3.68342 8.90237 4.31658 8.90237 4.70711 9.29289L8 12.5858L15.2929 5.29289C15.6834 4.90237 16.3166 4.90237 16.7071 5.29289Z" fill="#111827"/>
				</svg>`,
		},
		{
			title: "Average Score",
			value: 85,
			suffix: "%",
			status: "success",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 2C8.44772 2 8 2.44772 8 3C8 3.55228 8.44772 4 9 4H11C11.5523 4 12 3.55228 12 3C12 2.44772 11.5523 2 11 2H9Z" fill="#111827"/>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M4 5C4 3.89543 4.89543 3 6 3C6 4.65685 7.34315 6 9 6H11C12.6569 6 14 4.65685 14 3C15.1046 3 16 3.89543 16 5V16C16 17.1046 15.1046 18 14 18H6C4.89543 18 4 17.1046 4 16V5ZM7 9C6.44772 9 6 9.44772 6 10C6 10.5523 6.44772 11 7 11H7.01C7.56228 11 8.01 10.5523 8.01 10C8.01 9.44772 7.56228 9 7.01 9H7ZM10 9C9.44772 9 9 9.44772 9 10C9 10.5523 9.44772 11 10 11H13C13.5523 11 14 10.5523 14 10C14 9.44772 13.5523 9 13 9H10ZM7 13C6.44772 13 6 13.4477 6 14C6 14.5523 6.44772 15 7 15H7.01C7.56228 15 8.01 14.5523 8.01 14C8.01 13.4477 7.56228 13 7.01 13H7ZM10 13C9.44772 13 9 13.4477 9 14C9 14.5523 9.44772 15 10 15H13C13.5523 15 14 14.5523 14 14C14 13.4477 13.5523 13 13 13H10Z" fill="#111827"/>
				</svg>`,
		},
		{
			title: "Total Evaluated",
			value: 156,
			suffix: "submissions",
			icon: `
				<svg width="22" height="22" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 6C9 7.65685 7.65685 9 6 9C4.34315 9 3 7.65685 3 6C3 4.34315 4.34315 3 6 3C7.65685 3 9 4.34315 9 6Z" fill="#111827"/>
					<path d="M17 6C17 7.65685 15.6569 9 14 9C12.3431 9 11 7.65685 11 6C11 4.34315 12.3431 3 14 3C15.6569 3 17 4.34315 17 6Z" fill="#111827"/>
					<path d="M12.9291 17C12.9758 16.6734 13 16.3395 13 16C13 14.3648 12.4393 12.8606 11.4998 11.6691C12.2352 11.2435 13.0892 11 14 11C16.7614 11 19 13.2386 19 16V17H12.9291Z" fill="#111827"/>
					<path d="M6 11C8.76142 11 11 13.2386 11 16V17H1V16C1 13.2386 3.23858 11 6 11Z" fill="#111827"/>
				</svg>`,
		},
	] ;

	// Pending evaluations
	const pendingEvaluations = [
		{
			id: 1,
			title: 'Innovation Excellence Award',
			institution: 'University of Colombo',
			dueDate: '2024-01-20'
		},
		{
			id: 2,
			title: 'Academic Achievement Award',
			institution: 'University of Peradeniya',
			dueDate: '2024-01-22'
		},
		{
			id: 3,
			title: 'Research Excellence Award',
			institution: 'University of Moratuwa',
			dueDate: '2024-01-25'
		},
		{
			id: 4,
			title: 'Leadership Excellence Award',
			institution: 'University of Kelaniya',
			dueDate: '2024-01-28'
		}
	];

	// Quick actions for judger
	const quickActions = [
		{
			title: "Start Evaluation",
			description: "Begin evaluating submissions",
			route: "/judger/evaluate"
		},
		{
			title: "View Guidelines",
			description: "Review evaluation criteria",
			route: "/judger/evaluate"
		}
	];

	export default ({
		components: {
			CardBarChart,
			CardLineChart,
			WidgetCounter,
			CardOrderHistory,
		},
		data() {
			return {
				// Counter Widgets Stats
				stats,
				pendingEvaluations,
				quickActions,
			}
		},
		methods: {
			navigateTo(route) {
				this.$router.push(route);
			},
			navigateToEvaluate() {
				this.$router.push('/judger/evaluate');
			},
			navigateToEvaluation(id) {
				this.$router.push(`/judger/evaluate?id=${id}`);
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
</style>
