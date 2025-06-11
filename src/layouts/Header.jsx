import { Link } from 'react-router-dom';
import AppRoutesConfig from '../routes/AppRoutesConfig';
import { __ } from '@wordpress/i18n';

const Header = () => {
	const routes = AppRoutesConfig();

	return (
		<div className="nwpdiscountly-layout__header bg-white px-8 flex items-center justify-between h-16">
			<div className="flex items-center gap-x-10">
				<h1 className="text-lg font-bold">
					{ __( 'Discountly', 'discountly' ) }
				</h1>
				<div className="nwpdiscountly-layout__header-navigations flex gap-x-6 items-center">
					{ routes.map( ( { to, title, condition }, index ) => (
						<Link
							key={ index }
							to={ to }
							className={ condition ? 'active' : '' }
						>
							{ title }
						</Link>
					) ) }
				</div>
			</div>
			<a
				className="text-sm underline hover:text-wp-primary-hover"
				href="https://nikanwp.com/docs/discountly/"
				target="_blank"
				rel="noopener noreferrer"
			>
				{ __( 'Documentation', 'discountly' ) }
			</a>
		</div>
	);
};

export default Header;
