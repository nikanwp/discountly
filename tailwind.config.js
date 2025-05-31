/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [ './src/**/*.{js,jsx}' ],
	important: '#nwpdiscountly-app',
	theme: {
		extend: {
			colors: {
				'wp-primary': '#183ad6',
				'wp-primary-hover': '#2145e6',
			},
		},
	},
	plugins: [],
};
