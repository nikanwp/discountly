import DiscountsListTable from './components/DiscountsListTable';
import DiscountsListEmpty from './components/DiscountsListEmpty';
import useDiscounts from '../../hooks/useDiscounts';
import { Spinner } from '@wordpress/components';

const DiscountsList = () => {
	const { discounts, isLoading,updateDiscountStatus,deleteDiscount,rowLoading,handleOnDragEnd} = useDiscounts();

	if ( isLoading ) {
		return <Spinner />;
	}

	if ( discounts.length === 0 ) {
		return <DiscountsListEmpty />;
	}

	return (
		<>
			<div className="bg-white border">
				<DiscountsListTable
					discounts={ discounts }
					updateDiscountStatus={updateDiscountStatus}
					deleteDiscount={deleteDiscount}
					rowLoading={rowLoading}
					handleOnDragEnd={handleOnDragEnd}
				/>
			</div>
		</>
	);
};
export default DiscountsList;

