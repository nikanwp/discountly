import { __ } from '@wordpress/i18n';
import { Button, ToggleControl, Tooltip } from '@wordpress/components';
import { trash, edit, menu, Icon } from '@wordpress/icons';
import { Link } from "react-router-dom";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import translations from "../../../utils/translations";

const DiscountListTableRow = ({ discount, rowLoading, updateDiscountStatus, deleteDiscount }) => {
    const { attributes, listeners, setNodeRef, isDragging, transform, transition } = useSortable({
        id: discount.id,
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition: transition,
    };

    return (
        <tr key={discount.id} style={style} ref={setNodeRef}>
            <td className={`${isDragging ? 'cursor-grabbing' : 'cursor-grab'} pr-0`} {...listeners} {...attributes}><Icon icon={menu} /></td>
            <td>{discount.priority}</td>
            <td>
                <Link className="font-semibold text-blue-600 hover:text-blue-700" to={`?page=nwpdiscountly&path=edit&id=${discount.id}`}>{ discount.name }</Link>
            </td>
            <td>{discount.id}</td>
            <td>{ translations[discount.type] }</td>
            <td>
                <ToggleControl
                    checked={Number(discount.active)}
                    onChange={() => updateDiscountStatus(discount.id, Number(discount.active))}
                    disabled={rowLoading[discount.id]}
                />
            </td>
            <td>
                <div className="flex gap-x-2">
                    <Tooltip text={__('Edit', 'nwpdiscountly')}>
                        <Link className="font-semibold text-blue-600 hover:text-blue-700" to={`?page=nwpdiscountly&path=edit&id=${discount.id}`}>
                            <Button
                                variant="secondary"
                                icon={edit}
                                size="small"
                                disabled={rowLoading[discount.id]}
                                isBusy={rowLoading[discount.id]}
                            ></Button>
                        </Link>
                    </Tooltip>
                    <Tooltip text={__('Trash', 'nwpdiscountly')}>
                        <Button
                            isDestructive
                            variant="secondary"
                            icon={trash}
                            size="small"
                            onClick={() => deleteDiscount(discount.id)}
                            disabled={rowLoading[discount.id]}
                            isBusy={rowLoading[discount.id]}
                        ></Button>
                    </Tooltip>
                </div>
            </td>
        </tr>
    );
};
export default DiscountListTableRow;