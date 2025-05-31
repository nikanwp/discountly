import { __ } from '@wordpress/i18n';
import { Link } from 'react-router-dom';
import Button from '../../../components/Button';
import { Icon, percent } from '@wordpress/icons';

const DiscountsListEmpty = () => {
	return (
		<div className="flex flex-col items-center justify-center w-full max-w-xl mx-auto space-y-16">
			<Icon
				fill="currentColor"
				className="text-slate-400 mt-20"
				icon={ percent }
				size={ 260 }
			/>
			<p className="text-slate-500 font-medium text-xl text-center">
				{ __(
					'Discounts are a great way to increase sales. After creating their list, it will be placed here.',
					'nwpdiscountly'
				) }
			</p>
			<div className="flex space-x-2">
				<Link
					to="?page=nwpdiscountly&path=create"
					className="components-button is-large is-primary"
				>
					{ __( 'Create your first discount', 'nwpdiscountly' ) }
				</Link>
				<Button
					target="_blank"
					href="https://nikanwp.com/docs/discountly/"
					variant="secondary"
					size="large"
				>
					{ __( 'Documentation', 'nwpdiscountly' ) }
				</Button>
			</div>
		</div>
	);
};
export default DiscountsListEmpty;
