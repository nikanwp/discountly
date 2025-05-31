import { BrowserRouter } from 'react-router-dom';
import AppRoutes from './routes/AppRoutes';
import Layout from './layouts/Layout';
const App = () => {
	return (
		<BrowserRouter future={{ v7_startTransition: true }} basename={ new URL( nwpdiscountly.admin_url ).pathname }>
			<Layout>
				<AppRoutes />
			</Layout>
		</BrowserRouter>
	);
};
export default App;
