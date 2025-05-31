import { Route, Routes } from 'react-router-dom';
import AppRoutesConfig from './AppRoutesConfig';

const AppRoutes = () => {
	const routes = AppRoutesConfig();
	return (
		<Routes>
			{ routes
				.filter( ( { condition } ) => condition )
				.map( ( { path, component: Component }, index ) => (
					<Route
						key={ index }
						path={ path }
						element={ <Component /> }
					/>
				) ) }
		</Routes>
	);
};

export default AppRoutes;
