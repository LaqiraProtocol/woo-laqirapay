const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const webpack = require( 'webpack' );
const Dotenv = require('dotenv-webpack');

module.exports = {
	...defaultConfig,
	entry: {
	wooLaqiraPayMain: [
		path.resolve( process.cwd(), 'src', 'index.jsx' ),
		path.resolve( process.cwd(), 'src', 'css', 'index.scss' ),
	],
},
	plugins: [
		...defaultConfig.plugins,
		new Dotenv({
            path: './.env', 
            safe: false, 
        }),
		
	],
};
