import { __ } from '@wordpress/i18n';
import {closestCorners, DndContext} from "@dnd-kit/core";
import {SortableContext,verticalListSortingStrategy } from "@dnd-kit/sortable";
import DiscountListTableRow from "./DiscountListTableRow";
import {Tooltip} from "@wordpress/components";
import {help, Icon} from "@wordpress/icons";

const DiscountsListTable = ( { discounts,handleOnDragEnd,updateDiscountStatus,deleteDiscount,rowLoading } ) => {
	return (
		<DndContext collisionDetection={closestCorners} onDragEnd={handleOnDragEnd}>
			<div className="overflow-auto">
				<table className="nwpdiscountly-table min-w-full table-auto">
					<thead className="bg-gray-100 uppercase">
					<tr>
						<th className="w-[20px]">
							<Tooltip
								placement="bottom-start"
								text={__('Rearrange the rows to set the priority for applying discounts.', 'discountly')}
							>
								<Icon icon={help}></Icon>
							</Tooltip>
						</th>
						<th>{ __( 'Priority', 'discountly' ) }</th>
						<th>{ __( 'Name', 'discountly' ) }</th>
						<th>{ __( 'Discount ID', 'discountly' ) }</th>
						<th>{ __( 'Type', 'discountly' ) }</th>
						<th>{ __( 'Active', 'discountly' ) }</th>
						<th>{ __( 'Actions', 'discountly' ) }</th>
					</tr>
					</thead>
					<SortableContext
						items={discounts.map(discount => discount.id)}
						strategy={verticalListSortingStrategy}
					>
						<tbody>
						{discounts.map(discount => (
							<DiscountListTableRow
								key={discount.id}
								discount={discount}
								rowLoading={rowLoading}
								updateDiscountStatus={updateDiscountStatus}
								deleteDiscount={deleteDiscount}
							/>
						))}
						</tbody>
					</SortableContext>
				</table>
			</div>
		</DndContext>
	);

};

export default DiscountsListTable;
