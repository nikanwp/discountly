import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {toast} from "react-toastify";
import {arrayMove} from "@dnd-kit/sortable";

const useDiscounts = () => {
	const [ discounts, setDiscounts ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ rowLoading,setRowLoading ] = useState({});

	// Get discounts
	const fetchDiscounts = async () => {
        try {
            const data = await apiFetch({
                path: '/nwpdiscountly/v1/get-discounts/',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': nwpdiscountly.nonce
                }
            });

			setDiscounts( data );

        } catch (error){
			toast.error(
				__(
					'Failed to fetch discounts, please try again later.',
					'discountly'
				) );
        } finally {
			setIsLoading( false );
		}
    }

	// Update discount status
	const updateDiscountStatus = async (discountId,active) => {
		setRowLoading((prev) => ({ ...prev, [discountId]: true }));
		try {
			const response = await apiFetch({
				path: '/nwpdiscountly/v1/update-status',
				method: 'POST',
				headers: {
					'X-WP-Nonce': nwpdiscountly.nonce
				},
				data: {
					id: discountId,
					active: +!active,
				},
			});

			if (response.message){
				setDiscounts((prevDiscounts) =>
					prevDiscounts.map((discount) =>
						discount.id === discountId
							? { ...discount, active: +!active }
							: discount
					)
				);
				toast.success(response.message);
			}
		}catch (error){
			toast.error(
				__(
					'Failed to update discount status. Please try again later.',
					'discountly'
				)
			);
		} finally {
			setRowLoading((prev) => ({ ...prev, [discountId]: false }));
		}
	}

	// Delete discount
	const deleteDiscount = async (discountId) => {
		setRowLoading((prev) => ({ ...prev, [discountId]: true }));
		try {
			const response = await apiFetch({
				path: '/nwpdiscountly/v1/delete-discount',
				method: 'POST',
				headers: {
					'X-WP-Nonce': nwpdiscountly.nonce
				},
				data: {
					id: discountId,
				},
			});

			if (response.message) {
				setDiscounts((prevDiscounts) =>
					prevDiscounts.filter((discount) => discount.id !== discountId)
				);
				toast.success(response.message);
			}
		}catch (error){
			toast.error(
				__(
					'Failed to delete discount, please try again later.',
					'discountly'
				)
			);
		} finally {
			setRowLoading((prev) => ({ ...prev, [discountId]: false }));
		}
	}


	// Handle Drag discount row
	const handleOnDragEnd = async (event) => {
		const { active, over } = event;

		if (!over || active.id === over.id) return;

		const oldIndex = discounts.findIndex((discount) => discount.id === active.id);
		const newIndex = discounts.findIndex((discount) => discount.id === over.id);

		if (oldIndex !== -1 && newIndex !== -1) {
			const updatedDiscounts = arrayMove(discounts, oldIndex, newIndex);

			const formattedDiscounts = updatedDiscounts.map((discount, index) => ({
				...discount,
				priority: index + 1,
			}));

			setDiscounts(formattedDiscounts);

			try {
				await apiFetch({
					path: '/nwpdiscountly/v1/update-priority',
					method: 'POST',
					headers: {
						'X-WP-Nonce': nwpdiscountly.nonce
					},
					data: { discounts: formattedDiscounts },
				});

				toast.success(__('Priority updated successfully', 'discountly'));
			} catch (error) {
				toast.error(__('Failed to update priority', 'discountly'));
			}
		}
	};


	useEffect( () => {
		fetchDiscounts();
	}, [] );

	return {
		discounts,
		isLoading,
		updateDiscountStatus,
		deleteDiscount,
		rowLoading,
		handleOnDragEnd
	};
};
export default useDiscounts;
