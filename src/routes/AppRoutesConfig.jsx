import { useSearchParams } from 'react-router-dom';
import DiscountsList from '../pages/discounts-list-page/DiscountsList';
import { __ } from '@wordpress/i18n';
import { CreateDiscount, EditDiscount } from '../pages/create-edit-discount-page/CreateEditDiscount';

const AppRoutesConfig = () => {
	const [ params ] = useSearchParams();
	const page = params.get('page');
	const path = params.get('path');
	const id = params.get('id');

	const routes = [
		{
			to: '?page=nwpdiscountly',
			title: __( 'Discounts list', 'discountly' ),
			path: '/',
			component: DiscountsList,
			condition: page === 'nwpdiscountly' && !path,
		},
		{
			to: '?page=nwpdiscountly&path=create',
			title: __( 'Create discount', 'discountly' ),
			path: '/',
			component: CreateDiscount,
			condition: page === 'nwpdiscountly' && path === 'create',
		},
	];

	if (id) {
		routes.push({
			to: `?page=nwpdiscountly&path=edit&id=${id}`,
			title: __( 'Edit discount', 'discountly' ),
			path: '/',
			component: EditDiscount,
			condition: page === 'nwpdiscountly' && path === 'edit',
		});
	}

	return routes;
};

export default AppRoutesConfig;
