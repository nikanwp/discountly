<?php
namespace NikanWP\NWPDiscountly\RestApi;

use WP_REST_Request;

class UsersApi {
    public function __construct() {
        add_action('rest_api_init', [$this, 'rest_routes']);
    }

    public function rest_routes() {
        register_rest_route('nwpdiscountly/v1', '/search-users', [
            'methods' => 'GET',
            'callback' => [$this, 'search_users'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/search-user-roles', [
            'methods' => 'GET',
            'callback' => [$this, 'search_user_roles'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/get-users-by-discount-id', [
            'methods' => 'GET',
            'callback' => [$this, 'get_users_by_discount_id'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/get-roles-by-discount-id', [
            'methods' => 'GET',
            'callback' => [$this, 'get_roles_by_discount_id'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);
    }

    /**
     * Search users
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function search_users(WP_REST_Request $request) {
        $search = sanitize_text_field($request->get_param('search'));
        $per_page = max(1, intval($request->get_param('per_page')));
        $per_page = min($per_page, 20);

        if (empty($search) || strlen($search) < 2) {
            return rest_ensure_response([]);
        }

        global $wpdb;
        $search = '%' . $wpdb->esc_like($search) . '%';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, display_name, user_email 
        FROM {$wpdb->users} 
        WHERE display_name LIKE %s OR user_email LIKE %s OR user_login LIKE %s
        LIMIT %d",
            $search, $search, $search, $per_page
        ));

        $users = array_map(function($user) {
            return [
                'value' => $user->ID,
                'label' => "{$user->display_name} ({$user->user_email})"
            ];
        }, $results);

        return rest_ensure_response($users);
    }

    /**
     * Search user roles
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function search_user_roles(WP_REST_Request $request) {
        global $wp_roles;

        $search = $request->get_param('search');
        $roles = $wp_roles->roles;

        $filtered_roles = array_filter($roles, function ($role, $key) use ($search) {
            return stripos($role['name'], $search) !== false || stripos($key, $search) !== false;
        }, ARRAY_FILTER_USE_BOTH);

        $results = [];
        foreach ($filtered_roles as $key => $role) {
            $results[] = [
                'value' => $key,
                'label' => $role['name'],
            ];
        }

        return rest_ensure_response($results);
    }

    /**
     * Get users by discount ID
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_users_by_discount_id(WP_REST_Request $request) {
        $discount_id = intval($request->get_param('id'));

        if (!$discount_id) {
            return new \WP_Error('missing_discount_id', __('Discount ID is required', 'nwpdiscountly'), ['status' => 400]);
        }

        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT {$wpdb->users}.ID, {$wpdb->users}.display_name, {$wpdb->users}.user_email
     FROM {$wpdb->users}
     INNER JOIN {$wpdb->prefix}nwpdiscountly_meta
         ON JSON_CONTAINS({$wpdb->prefix}nwpdiscountly_meta.meta_value, JSON_QUOTE(CAST({$wpdb->users}.ID AS CHAR)), '$')
     WHERE {$wpdb->prefix}nwpdiscountly_meta.discount_id = %d AND {$wpdb->prefix}nwpdiscountly_meta.meta_key = 'selected_users'
    ", $discount_id));

        $users = array_map(function($user) {
            return [
                'value' => $user->ID,
                'label' => "{$user->display_name} ({$user->user_email})"
            ];
        }, $results);

        return rest_ensure_response($users);
    }

    /**
     * Get roles by discount ID
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_roles_by_discount_id(WP_REST_Request $request) {
        global $wp_roles, $wpdb;

        $discount_id = intval($request->get_param('id'));

        if (!$discount_id) {
            return new \WP_Error('missing_discount_id', __('Discount ID is required', 'nwpdiscountly'), ['status' => 400]);
        }


        $discount_meta = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value 
         FROM {$wpdb->prefix}nwpdiscountly_meta 
         WHERE discount_id = %d AND meta_key = %s",
            $discount_id,
            'selected_roles'
        ));

        $selected_roles = json_decode($discount_meta, true);

        $roles = $wp_roles->roles;

        $filtered_roles = array_filter($roles, function ($role, $key) use ($selected_roles) {
            return in_array($key, $selected_roles, true);
        }, ARRAY_FILTER_USE_BOTH);

        $results = [];
        foreach ($filtered_roles as $key => $role) {
            $results[] = [
                'value' => $key,
                'label' => $role['name'],
            ];
        }

        return rest_ensure_response($results);
    }
}
