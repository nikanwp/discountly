import {useEffect, useState} from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import {toast} from "react-toastify";
import {useNavigate} from "react-router-dom";

const useDiscountForm = ( discountId = null ) => {
    // Loading
    const [isLoading,setIsLoading] = useState(!!discountId);

    // Navigate to routes
    const navigate = useNavigate();

    // Form data
    const initialFormData = {
        discountDetails: {
            discountName: '',
            discountType: 'global_discount',
            active: 0,
        },
        discountMeta: {
            //Availability
            availability: 'always_available',
            start_date: '',
            end_date: '',
            //Disable discount if a coupon is applied
            disable_discount_with_coupon: 0,
            // Minimum purchase amount for discount
            min_purchase_amount: '',
            //Amount
            amount_type: 'percentage_discount',
            percentage_discount: 0,
            percentage_discount_cap: '',
            fixed_discount: '',
            //Applies to
            applies_to: 'all_users',
            selected_users: [],
            selected_roles: [],
            //Products
            products: 'all_products',
            selected_products : [],
            selected_categories: [],
            selected_tags: [],
            // Promotional message on product page
            product_promo_message: '',
        }
    }
    const [formData,setFormData] = useState(initialFormData);

    // Reset form
    const resetForm = () => {
        setFormData(initialFormData);
    }

    // Handle form onChange
    const handleOnChange = (field,value,section) => {
        setFormData((prevData) => ({
            ...prevData,
            [section]: {
                ...prevData[section],
                [field]: value,
            }
        }));
    }

    // Load discount data for editing
    useEffect( () => {
        if( discountId ){
            const fetchDiscount = async () => {
                try {
                    const response = await apiFetch({
                        path: `/nwpdiscountly/v1/get-discount/${discountId}`,
                        method: "GET",
                        headers: {
                            "X-WP-Nonce": nwpdiscountly.nonce,
                        },
                    });
                    if (response) {
                        setFormData({
                            discountDetails: {
                                ...initialFormData.discountDetails,
                                discountName: response.name || '',
                                discountType: response.type || 'global_discount',
                                active: response.active === "1" ? 1 : 0,
                            },
                            discountMeta: {
                                ...initialFormData.discountMeta,
                                ...response.meta,
                                selected_users: response.meta.selected_users || [],
                                selected_roles: response.meta.selected_roles || [],
                                selected_products: response.meta.selected_products || [],
                                selected_categories: response.meta.selected_categories || [],
                                selected_tags: response.meta.selected_tags || [],
                            },
                        });
                    }

                } catch (error) {
                    toast.error("Failed to load discount data.");
                } finally {
                    setIsLoading(false);
                }
            };

            fetchDiscount();
        }
    },[discountId])


    // Handle form submit
    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        try {
            const endPoint = discountId ? `/nwpdiscountly/v1/update-discount/${discountId}` : "/nwpdiscountly/v1/create-discount";
            const method = discountId ? 'PUT' : 'POST';
            const response = await apiFetch({
                path: endPoint,
                method: method,
                headers: {
                    'X-WP-Nonce': nwpdiscountly.nonce
                },
                data: {
                    discountDetails: formData.discountDetails,
                    discountMeta: formData.discountMeta,
                }
            });
            if (response){
                toast.success(response.message);
                if( !discountId ){
                    resetForm();
                    navigate('?page=nwpdiscountly');
                }
            }
        }catch (error){
            toast.error(error.message);
        }finally {
            setIsLoading(false);
        }
    }

    return {
        isLoading,
        formData,
        handleOnChange,
        handleSubmit
    }
}
export default useDiscountForm