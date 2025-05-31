import {useEffect, useState} from "@wordpress/element";
import { toast } from "react-toastify";
import apiFetch from "@wordpress/api-fetch";

const useProducts = (discountId = null) => {
    const [products, setProducts] = useState([]);

    const fetchProducts = async (search = "") => {
        if (search.length < 2) return;

        try {
            const response = await apiFetch({
                path: `/nwpdiscountly/v1/search-products?search=${search}&per_page=${nwpdiscountly.per_page}`,
                method: "GET",
                headers: {
                    "X-WP-Nonce": nwpdiscountly.nonce,
                },
            });
            setProducts((prev) => {
                const newProducts = response.filter( (newProduct) => !prev.some((product) => product.value === newProduct.value) );
                return [...prev, ...newProducts];
            });
        } catch (error) {
            toast.error(error.message);
        }
    };

    useEffect(() => {
        if (discountId) {
            const fetchProductsByDiscountId = async () => {
                try {
                    const response = await apiFetch({
                        path: `/nwpdiscountly/v1/get-products-by-discount-id?id=${discountId}`,
                        method: "GET",
                        headers: {
                            "X-WP-Nonce": nwpdiscountly.nonce,
                        },
                    });

                    setProducts((prev) => {
                        const newProducts = response.filter(
                            (newProduct) => !prev.some((product) => product.value === newProduct.value)
                        );
                        return [...prev, ...newProducts];
                    });
                } catch (error) {
                    toast.error(error.message);
                }
            };
            fetchProductsByDiscountId();
        }
    }, [discountId]);


    return {
        products,
        fetchProducts,
    };
};

export default useProducts;
