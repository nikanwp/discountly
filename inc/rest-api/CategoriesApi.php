<?php
namespace NikanWP\NWPDiscountly\RestApi;

use WP_REST_Request;

class CategoriesApi {
    public function __construct() {
        add_action('rest_api_init', [$this, 'rest_routes']);
    }

    public function rest_routes() {
        register_rest_route('nwpdiscountly/v1', '/search-categories', [
            'methods' => 'GET',
            'callback' => [$this, 'search_categories'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/get-categories-by-discount-id', [
            'methods' => 'GET',
            'callback' => [$this, 'get_categories_by_discount_id'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);
    }

    /**
     * Search categories
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function search_categories(WP_REST_Request $request) {
        $search = $request->get_param('search');
        $per_page = max(1, intval($request->get_param('per_page')));
        $per_page = min($per_page, 20);

        if (empty($search) || strlen($search) < 2) {
            return rest_ensure_response([]);
        }

        $args = [
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'search' => esc_attr($search),
            'number' => $per_page,
        ];

        $categories = get_terms($args);

        if (is_wp_error($categories)) {
            return rest_ensure_response([]);
        }

        $results = array_map(function($category) {
            return [
                'value' => $category->term_id,
                'label' => $category->name,
            ];
        }, $categories);

        return rest_ensure_response($results);
    }

    /**
     * Get categories by discount ID
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_categories_by_discount_id(WP_REST_Request $request) {
        global $wpdb;

        $discount_id = intval($request['id']);

        if (empty($discount_id)) {
            return rest_ensure_response([]);
        }

        $category_ids_json = $wpdb->get_var($wpdb->prepare("
            SELECT meta_value 
            FROM {$wpdb->prefix}nwpdiscountly_meta
            WHERE discount_id = %d AND meta_key = 'selected_categories'
        ", $discount_id));

        if (empty($category_ids_json)) {
            return rest_ensure_response([]);
        }

        $category_ids = json_decode($category_ids_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($category_ids) || !is_array($category_ids)) {
            return rest_ensure_response([]);
        }

        $categories = [];

        foreach ($category_ids as $category_id) {
            $category = get_term($category_id, 'product_cat');
            if ($category && !is_wp_error($category)) {
                $categories[] = [
                    'value' => $category->term_id,
                    'label' => $category->name,
                ];
            }
        }

        return rest_ensure_response($categories);
    }

}
