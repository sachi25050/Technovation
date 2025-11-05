import Vue from 'vue'
import VueRouter from 'vue-router'
import userRoleStore from '@/store/userRole'
import apiService from '@/services/api'

Vue.use(VueRouter)

// Route protection based on user role
const requireAdmin = (to, from, next) => {
  userRoleStore.setRoleFromPath(to.path)
  if (userRoleStore.isAdmin()) {
    next()
  } else {
    next('/admin')
  }
}

const requireJudger = (to, from, next) => {
  userRoleStore.setRoleFromPath(to.path)
  if (userRoleStore.isJudger()) {
    next()
  } else {
    next('/judger')
  }
}

const requireReporter = (to, from, next) => {
  userRoleStore.setRoleFromPath(to.path)
  if (userRoleStore.isReporter()) {
    next()
  } else {
    next('/reporter')
  }
}

// Route protection for admin or reporter
const requireAdminOrReporter = (to, from, next) => {
  if (userRoleStore.isAdmin() || userRoleStore.isReporter()) {
    next()
  } else {
    next('/admin')
  }
}

let routes = [
	{
		// will match everything
		path: '*',
		component: () => import('../views/404.vue'),
	},
	{
		path: '/',
		name: 'Home',
		// Redirect dynamically: if not authenticated send to sign-in, otherwise send to role dashboard
		redirect: (to) => {
			const isAuthenticated = apiService.isAuthenticated()
			if (!isAuthenticated) return '/sign-in'
			const user = apiService.getCurrentUser()
			if (user && user.role) {
				switch (user.role) {
					case 'admin':
						return '/admin'
					case 'judger':
						return '/judger'
					case 'reporter':
						return '/reporter'
					default:
						return '/admin'
				}
			}
			return '/admin'
		},
	},
	// Admin Routes
	{
		path: '/admin',
		name: 'AdminDashboard',
		layout: "modern-dashboard",
		component: () => import('../views/admin/AdminDashboard.vue'),
		beforeEnter: requireAdmin
	},
	{
		path: '/admin/create-accounts',
		name: 'CreateAccounts',
		layout: "modern-dashboard",
		component: () => import('../views/admin/CreateAccounts.vue'),
		beforeEnter: requireAdmin
	},
	{
		path: '/admin/add-institution',
		name: 'AddInstitution',
		layout: "modern-dashboard",
		component: () => import('../views/admin/AddInstitution.vue'),
		beforeEnter: requireAdmin
	},
	{
		path: '/admin/add-awards',
		name: 'AddAwards',
		layout: "modern-dashboard",
		component: () => import('../views/admin/AddAwards.vue'),
		beforeEnter: requireAdmin
	},
	// Judger Routes
	{
		path: '/judger',
		name: 'JudgerDashboard',
		layout: "modern-dashboard",
		component: () => import('../views/judger/JudgerDashboard.vue'),
		beforeEnter: requireJudger
	},
	{
		path: '/judger/evaluate',
		name: 'JudgerEvaluate',
		layout: "modern-dashboard",
		component: () => import('../views/judger/JudgerEvaluate.vue'),
		beforeEnter: requireJudger
	},
	{
		path: '/judger/submit-evaluations',
		name: 'SubmitEvaluations',
		layout: "modern-dashboard",
		component: () => import('../views/judger/JudgerSubmissionHistory.vue'),
		beforeEnter: requireJudger
	},
	// Reporter Routes
	{
		path: '/reporter',
		name: 'ReporterDashboard',
		layout: "modern-dashboard",
		component: () => import('../views/reporter/ReporterDashboard.vue'),
		beforeEnter: requireReporter
	},
	{
		path: '/reporter/generate-reports',
		name: 'GenerateReports',
		layout: "modern-dashboard",
		component: () => import('../views/reporter/GenerateReports.vue'),
		beforeEnter: requireReporter
	},
	// Legacy routes for backward compatibility
	{
		path: '/dashboard',
		name: 'Dashboard',
		layout: "dashboard",
		component: () => import('../views/Dashboard.vue'),
	},
	{
		path: '/evaluation',
		name: 'Evaluation',
		layout: "dashboard",
		component: () => import('../views/Tables.vue'),
	},
	{
		path: '/reports',
		name: 'Reports',
		layout: "dashboard",
		component: () => import('../views/Tables.vue'),
		beforeEnter: requireAdminOrReporter
	},
	{
		path: '/billing',
		name: 'Billing',
		layout: "dashboard",
		component: () => import('../views/Billing.vue'),
		beforeEnter: requireAdmin
	},
	{
		path: '/rtl',
		name: 'RTL',
		layout: "dashboard-rtl",
		meta: {
			layoutClass: 'dashboard-rtl',
		},
		component: () => import('../views/RTL.vue'),
		beforeEnter: requireAdmin
	},
	{
		path: '/Profile',
		name: 'Profile',
		layout: "dashboard",
		meta: {
			layoutClass: 'layout-profile',
		},
		component: () => import('../views/Profile.vue'),
		beforeEnter: requireAdmin
	},
	{
		path: '/sign-in',
		name: 'Sign-In',
		component: () => import('../views/Sign-In.vue'),
	},
	{
		path: '/sign-up',
		name: 'Sign-Up',
		meta: {
			layoutClass: 'layout-sign-up',
		},
		component: () => import('../views/Sign-Up.vue'),
	},
]

// Adding layout property from each route to the meta
// object so it can be accessed later.
function addLayoutToRoute( route, parentLayout = "default" )
{
	route.meta = route.meta || {} ;
	route.meta.layout = route.layout || parentLayout ;
	
	if( route.children )
	{
		route.children = route.children.map( ( childRoute ) => addLayoutToRoute( childRoute, route.meta.layout ) ) ;
	}
	return route ;
}

routes = routes.map( ( route ) => addLayoutToRoute( route ) ) ;

const router = new VueRouter({
	mode: 'hash',
	base: process.env.BASE_URL,
	routes,
	scrollBehavior (to, from, savedPosition) {
		if ( to.hash ) {
			return {
				selector: to.hash,
				behavior: 'smooth',
			}
		}
		return {
			x: 0,
			y: 0,
			behavior: 'smooth',
		}
	}
})

// Global navigation guard to check authentication
router.beforeEach((to, from, next) => {
	// Public routes that don't require authentication
	const publicRoutes = ['/sign-in', '/sign-up']
	const isPublicRoute = publicRoutes.includes(to.path)
	
	// Check if user is authenticated
	const isAuthenticated = apiService.isAuthenticated()
	
	// If trying to access a protected route without authentication
	if (!isPublicRoute && !isAuthenticated) {
		next('/sign-in')
		return
	}
	
	// If authenticated user tries to access sign-in/sign-up, redirect to dashboard
	if (isPublicRoute && isAuthenticated && to.path === '/sign-in') {
		const user = apiService.getCurrentUser()
		if (user && user.role) {
			userRoleStore.setRole(user.role)
			switch (user.role) {
				case 'admin':
					next('/admin')
					return
				case 'judger':
					next('/judger')
					return
				case 'reporter':
					next('/reporter')
					return
			}
		}
		next('/admin')
		return
	}
	
	// If authenticated, set role from user data
	if (isAuthenticated && !isPublicRoute) {
		const user = apiService.getCurrentUser()
		if (user && user.role) {
			userRoleStore.setRole(user.role)
		}
	}
	
	next()
})

export default router
