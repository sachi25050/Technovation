<!-- 
	This is the default layout, used in sign-in and sign-up pages.
 -->

<template>
	<div>

		<!-- Default Layout -->
		<a-layout class="layout-default" id="layout-default" :class="[layoutClass, { 'sign-in-layout': isSignInPage }]">

			<!-- Layout Header ( Navbar ) -->
			<DefaultHeader v-if="!isSignInPage"></DefaultHeader>
			<!-- / Layout Header ( Navbar ) -->


			<!-- Page Content -->
			<a-layout-content>
				<router-view />
			</a-layout-content>
			<!-- / Page Content -->

			<!-- Layout Footer -->
			<DefaultFooter v-if="!isSignInPage"></DefaultFooter>
			<!-- / Layout Footer -->

		</a-layout>
		<!-- / Default Layout -->

	</div>
</template>

<script>

	import DefaultHeader from '../components/Headers/DefaultHeader' ;
	import DefaultFooter from '../components/Footers/DefaultFooter' ;

	export default ({
		components: {
			DefaultHeader,
			DefaultFooter,
		},
		data() {
			return {
			}
		},
		computed: {
			// Sets layout's element's class based on route's meta data.
			layoutClass() {
				return this.$route.meta.layoutClass ;
			},
			// Check if current page is sign-in
			isSignInPage() {
				return this.$route.path === '/sign-in' || this.$route.name === 'Sign-In';
			}
		},
	})

</script>

<style lang="scss">
	.layout-default.sign-in-layout {
		min-height: 100vh;
		background: transparent;

		.ant-layout-content {
			padding: 0;
			background: transparent;
		}
	}
</style>