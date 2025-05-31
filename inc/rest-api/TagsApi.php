<?php
namespace NikanWP\NWPDiscountly\RestApi;

use WP_REST_Request;

class TagsApi {
    public function __construct() {
        add_action('rest_api_init', [$this, 'rest_routes']);
    }

    public function rest_routes() {
        register_rest_route('nwpdiscountly/v1', '/search-tags', [
            'methods' => 'GET',
            'callback' => [$this, 'search_tags'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/get-tags-by-discount-id', [
            'methods' => 'GET',
            'callback' => [$this, 'get_tags_by_discount_id'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);
    }

    /**
     * Search tags
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function search_tags(WP_REST_Request $request) {
        $search = $request->get_param('search');
        $per_page = max(1, intval($request->get_param('per_page')));
        $per_page = min($per_page, 20);

        if (empty($search) || strlen($search) < 2) {
            return rest_ensure_response([]);
        }

        $args = [
            'taxonomy' => 'product_tag',
            'hide_empty' => false,
            'search' => esc_attr($search),
            'number' => $per_page,
        ];

        $tags = get_terms($args);

        if (is_wp_error($tags)) {
            return rest_ensure_response([]);
        }

        $results = array_map(function($tag) {
            return [
                'value' => $tag->term_id,
                'label' => $tag->name,
            ];
        }, $tags);

        return rest_ensure_response($results);
    }

    /**
     * Get tags by discount ID
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_tags_by_discount_id(WP_REST_Request $request) {
        global $wpdb;

        $discount_id = intval($request['id']);

        if (empty($discount_id)) {
            return rest_ensure_response([]);
        }

        $tag_ids_json = $wpdb->get_var($wpdb->prepare("
            SELECT meta_value 
            FROM {$wpdb->prefix}nwpdiscountly_meta
            WHERE discount_id = %d AND meta_key = 'selected_tags'
        ", $discount_id));

        if (empty($tag_ids_json)) {
            return rest_ensure_response([]);
        }

        $tag_ids = json_decode($tag_ids_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($tag_ids) || !is_array($tag_ids)) {
            return rest_ensure_response([]);
        }

        $tags = [];

        foreach ($tag_ids as $tag_id) {
            $tag = get_term($tag_id, 'product_tag');
            if ($tag && !is_wp_error($tag)) {
                $tags[] = [
                    'value' => $tag->term_id,
                    'label' => $tag->name,
                ];
            }
        }

        return rest_ensure_response($tags);
    }

}
