import {useEffect, useState} from "@wordpress/element";
import {toast} from "react-toastify";
import apiFetch from "@wordpress/api-fetch";

const useUsers = (discountId = null) => {
    const [users,setUsers] = useState([]);
    const [roles,setRoles] = useState([]);

    const fetchUsers = async (search = "") => {
        if( search.length < 2 ) return;
        try {
            const response = await apiFetch({
                path: `/nwpdiscountly/v1/search-users?search=${search}&per_page=${nwpdiscountly.per_page}`,
                method: "GET",
                headers: {
                    'X-WP-Nonce': nwpdiscountly.nonce
                }
            });
            setUsers((prev) => {
                const newUsers = response.filter( (newUser) => !prev.some((user) => user.value === newUser.value) );
                return [...prev, ...newUsers];
            });
        } catch (error) {
            toast.error(error.message);
        }
    }

    const fetchRoles = async (search = "") => {
        if( search.length < 2 ) return;
        try {
            const response = await apiFetch({
                path: `/nwpdiscountly/v1/search-user-roles?search=${search}&per_page=${nwpdiscountly.per_page}`,
                method: "GET",
                headers: {
                    'X-WP-Nonce': nwpdiscountly.nonce
                }
            });
            //setRoles(response);
            setRoles((prev) => {
               const newRoles = response.filter( (newRole) => !prev.some( (role) => role.value === newRole.value ) );
               return [...prev,...newRoles];
            });
        } catch (error) {
            toast.error(error.message);
        }
    }

    useEffect(() => {
        if( discountId ){
            const fetchUsersByDiscountId = async () => {
                try {
                    const response = await apiFetch({
                        path: `/nwpdiscountly/v1/get-users-by-discount-id?id=${discountId}`,
                        method: "GET",
                        headers: {
                            "X-WP-Nonce": nwpdiscountly.nonce,
                        },
                    });
                    setUsers((prev) => {
                        const newUsers = response.filter( (newUser) => !prev.some((user) => user.value === newUser.value) );
                        return [...prev, ...newUsers];
                    });
                } catch (error) {
                    toast.error(error.message);
                }
            };

            const fetchRolesByDiscountId = async () => {
                try {
                    const response = await apiFetch({
                        path: `/nwpdiscountly/v1/get-roles-by-discount-id?id=${discountId}`,
                        method: "GET",
                        headers: {
                            "X-WP-Nonce": nwpdiscountly.nonce,
                        },
                    });

                    setRoles((prev) => {
                        const newRoles = response.filter( (newRole) => !prev.some( (role) => role.value === newRole.value ) );
                        return [...prev,...newRoles];
                    });
                } catch (error) {
                    toast.error(error.message);
                }
            };

            fetchUsersByDiscountId();
            fetchRolesByDiscountId();
        }
    },[discountId]);

    return {
        users,
        fetchUsers,
        roles,
        fetchRoles,
    }
}
export default useUsers