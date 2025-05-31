import Header from './Header';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const Layout = ( { children } ) => {
	return (
		<div className="nwpdiscountly-layout p-0 m-0">
			<Header />
			<div className="nwpdiscountly-layout__main py-10 px-8">
				{ children }
			</div>
			<ToastContainer
				className="w-max"
				theme="dark"
				position="bottom-center"
			/>
		</div>
	);
};
export default Layout;
