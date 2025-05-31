<?php
namespace NikanWP\NWPDiscountly\RestApi;

use WP_REST_Request;
use WP_Query;
use WC_Product;
use function wc_get_product;

class ProductsApi {
    public function __construct() {
        add_action('rest_api_init', [$this, 'rest_routes']);
    }

    public function rest_routes() {
        register_rest_route('nwpdiscountly/v1', '/search-products', [
            'methods' => 'GET',
            'callback' => [$this, 'search_products'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/get-products-by-discount-id', [
            'methods' => 'GET',
            'callback' => [$this, 'get_products_by_discount_id'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);
    }

    /**
     * Search products
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function search_products(WP_REST_Request $request) {
        $search = $request->get_param('search');
        $per_page = max(1, intval($request->get_param('per_page')));
        $per_page = min($per_page, 20);

        if (empty($search) || strlen($search) < 2) {
            return rest_ensure_response([]);
        }

        $args = [
            'post_type'      => ['product', 'product_variation'],
            'posts_per_page' => $per_page,
            's'              => $search,
        ];

        $query = new \WP_Query($args);
        $parentProducts = [];
        $variations = [];

        foreach ($query->posts as $post) {
            $product = wc_get_product($post->ID);

            if ($product instanceof WC_Product) {
                $type = $product->is_type('variation') ? 'variation' : 'product';
                $label = $product->get_name();
                $parentId = ($type === 'variation') ? $product->get_parent_id() : 0;

                if ($type === 'product') {
                    $parentProducts[] = [
                        'value' => $product->get_id(),
                        'label' => $label,
                        'type'  => $type,
                        'parentId' => $parentId
                    ];
                } else {
                    $variations[] = [
                        'value' => $product->get_id(),
                        'label' => $label,
                        'type'  => $type,
                        'parentId' => $parentId
                    ];
                }
            }
        }
        $sortedProducts = array_merge($parentProducts, $variations);
        return rest_ensure_response($sortedProducts);
    }


    /**
     * Get products by discount ID
     * @param WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_products_by_discount_id(WP_REST_Request $request) {
        global $wpdb;

        $discount_id = intval($request['id']);

        if (empty($discount_id)) {
            return rest_ensure_response([]);
        }

        $product_ids_json = $wpdb->get_var($wpdb->prepare("
        SELECT meta_value 
        FROM {$wpdb->prefix}nwpdiscountly_meta
        WHERE discount_id = %d AND meta_key = 'selected_products'
    ", $discount_id));

        if (empty($product_ids_json)) {
            return rest_ensure_response([]);
        }

        $selected_products = json_decode($product_ids_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($selected_products) || !is_array($selected_products)) {
            return rest_ensure_response([]);
        }

        $products = [];

        foreach ($selected_products as $item) {
            if (!isset($item['value']) || !isset($item['type'])) {
                continue;
            }

            $product = wc_get_product($item['value']);
            if ($product instanceof WC_Product) {
                $products[] = [
                    'value'    => $product->get_id(),
                    'label'    => $product->get_name(),
                    'type'     => $item['type'],
                    'parentId' => isset($item['parentId']) ? $item['parentId'] : 0,
                ];
            }
        }

        return rest_ensure_response($products);
    }
}
