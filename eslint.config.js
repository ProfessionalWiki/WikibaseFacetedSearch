'use strict';

const { defineConfig, globalIgnores } = require( 'eslint/config' );
const globals = require( 'globals' );
const noJquery = require( 'eslint-plugin-no-jquery' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );

module.exports = defineConfig( [
	globalIgnores( [
		'coverage/',
		'i18n/',
		'vendor/'
	] ),

	// resources/ runs in the browser, and tests/ tests it: both get the client config below
	{
		ignores: [ 'resources/**', 'tests/**' ],
		extends: [ wikimedia( 'server' ) ]
	},

	{
		files: [ 'resources/**', 'tests/**' ],
		extends: [
			noJquery.configs[ 'flat/all' ],
			wikimedia( 'client', 'mediawiki' )
		],
		languageOptions: {
			sourceType: 'module',
			globals: {
				exports: 'writable'
			}
		}
	},
	{
		files: [ 'tests/**' ],
		languageOptions: {
			ecmaVersion: 2018,
			globals: {
				...globals.node,
				...globals.jest
			}
		}
	}
] );
