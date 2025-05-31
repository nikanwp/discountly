import {useEffect, useState} from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import {toast} from "react-toastify";

const useTags = ( discountId = null ) => {
    const [tags,setTags] = useState([]);

    const fetchTags = async ( search = "" ) => {
        if ( search.length < 2 ) return;
        try {
            const response = await apiFetch({
                path: `/nwpdiscountly/v1/search-tags?search=${search}&per_page=${nwpdiscountly.per_page}`,
                method: "GET",
                headers: {
                    'X-WP-Nonce': nwpdiscountly.nonce
                }
            });

            setTags((prev) => {
                const newTags = response.filter( (newTag) => !prev.some((tag) => tag.value === newTag.value) );
                return [...prev,...newTags];
            })

        }catch (error){
            toast.error(error.message);
        }
    }

    useEffect(() => {
        if( discountId ){
            const fetchTagsByDiscountId = async () => {
                try {
                    const response = await apiFetch({
                        path: `/nwpdiscountly/v1/get-tags-by-discount-id?id=${discountId}`,
                        method: "GET",
                        headers: {
                            "X-WP-Nonce": nwpdiscountly.nonce,
                        },
                    });

                    setTags((prev) => {
                        const newTags = response.filter( (newTag) => !prev.some((tag) => tag.value === newTag.value) );
                        return [...prev,...newTags];
                    })
                } catch (error) {
                    toast.error(error.message);
                }
            };
            fetchTagsByDiscountId();
        }
    },[discountId]);

    return {
        tags,
        fetchTags,
    }
}
export default useTags