const path = require('path');

module.exports = {
	runtimeCompiler: true,

	chainWebpack: config => {
		config
			.plugin('html')
			.tap(args => {
				args[0].title = 'Muse Vue Ant Design - by Creative Tim'
				return args
			})
		
		// Ensure @ alias points to src directory
		config.resolve.alias
			.set('@', path.resolve(__dirname, 'src'))
	}
}
