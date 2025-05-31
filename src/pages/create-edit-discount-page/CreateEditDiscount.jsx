import DiscountForm from "../../components/DiscountForm";
import useDiscountForm from "../../hooks/useDiscountForm";
import useUsers from "../../hooks/useUsers";
import useCategories from "../../hooks/useCategories";
import useTags from "../../hooks/useTags";
import useProducts from "../../hooks/useProducts";
import {useSearchParams} from "react-router-dom";

const CreateEditDiscount = ({ isEdit = false }) => {
    const [params] = useSearchParams();
    const discountId = params.get('id');
    const { isLoading, formData, handleOnChange, handleSubmit } = useDiscountForm(isEdit ? discountId : null);
    const {users,fetchUsers,roles,fetchRoles} = useUsers(isEdit ? discountId : null);
    const {categories,fetchCategories} = useCategories(isEdit ? discountId : null);
    const {tags,fetchTags} = useTags(isEdit ? discountId : null);
    const {products,fetchProducts} = useProducts(isEdit ? discountId : null);

    return(
        <>
            <DiscountForm
                isLoading={isLoading}
                formData={formData}
                handleOnChange={handleOnChange}
                handleSubmit={handleSubmit}
                users={users}
                fetchUsers={fetchUsers}
                roles={roles}
                fetchRoles={fetchRoles}
                categories={categories}
                fetchCategories={fetchCategories}
                tags={tags}
                fetchTags={fetchTags}
                products={products}
                fetchProducts={fetchProducts}
            />
        </>

    )
}
export const EditDiscount = () => <CreateEditDiscount isEdit={true} />;
export const CreateDiscount = () => <CreateEditDiscount isEdit={false} />;