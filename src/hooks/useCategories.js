import {useEffect, useState} from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import {toast} from "react-toastify";

const useCategories = ( discountId = null ) => {
    const [categories,setCategories] = useState([]);

    const fetchCategories = async ( search = "" ) => {
        if ( search.length < 2 ) return;
        try {
            const response = await apiFetch({
                path: `/nwpdiscountly/v1/search-categories?search=${search}&per_page=${nwpdiscountly.per_page}`,
                method: "GET",
                headers: {
                    'X-WP-Nonce': nwpdiscountly.nonce
                }
            });

            setCategories((prev) => {
                const newCategories = response.filter( (newCategory) => !prev.some((category) => category.value === newCategory.value) );
                return [...prev,...newCategories];
            })

        }catch (error){
            toast.error(error.message);
        }
    }

    useEffect(() => {
        if( discountId ){
            const fetchCategoriesByDiscountId = async () => {
                try {
                    const response = await apiFetch({
                        path: `/nwpdiscountly/v1/get-categories-by-discount-id?id=${discountId}`,
                        method: "GET",
                        headers: {
                            "X-WP-Nonce": nwpdiscountly.nonce,
                        },
                    });

                    setCategories((prev) => {
                        const newCategories = response.filter( (newCategory) => !prev.some((category) => category.value === newCategory.value) );
                        return [...prev,...newCategories];
                    })
                } catch (error) {
                    toast.error(error.message);
                }
            };
            fetchCategoriesByDiscountId();
        }
    },[discountId]);

    return {
        categories,
        fetchCategories,
    }
}
export default useCategories