import domReady from '@wordpress/dom-ready';
import { createRoot, StrictMode } from '@wordpress/element';
import App from './App';
import './index.scss';

domReady( () => {
	const container = document.getElementById( 'nwpdiscountly-app' );
	const root = createRoot( container );
	root.render(
		<StrictMode>
			<App />
		</StrictMode>
	);
} );
