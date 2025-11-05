<!-- 
	This is the main page of the application, the layout component is used here,
	and the router-view is passed to it.
	Layout component is dynamically declared based on the layout for each route,
	specified in routes list router/index.js .
 -->

<template>
	<div id="app">
		<component :is="layout">
			<router-view />
		</component>
	</div>
</template>

<script>
	import ModernDashboard from './layouts/ModernDashboard.vue'
	import Dashboard from './layouts/Dashboard.vue'
	import Default from './layouts/Default.vue'

	export default ({
		components: {
			ModernDashboard,
			Dashboard,
			Default
		},
		computed: {
			// Sets components name based on current route's specified layout, defaults to
			// <layout-default></layout-default> component.
			layout() {
				const routeLayout = this.$route.meta.layout || "default"
				
				// Handle modern-dashboard layout
				if (routeLayout === "modern-dashboard") {
					return "ModernDashboard"
				}
				
				// Handle other layouts
				return "layout-" + routeLayout.toLowerCase()
			}
		},
	})
	
</script>

<style lang="scss">
</style>