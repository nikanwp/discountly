<?php
namespace NikanWP\NWPDiscountly\RestApi;

use WP_Error;

class DiscountsApi{
    public function __construct(){
        add_action('rest_api_init',[$this,'rest_routes']);
    }

    /**
     * Register rest api routes
     * @return void
     */
    public function rest_routes(){
        register_rest_route('nwpdiscountly/v1','/get-discounts',array(
            'methods'  => 'GET',
            'callback' => [$this,'get_discounts'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            }
        ));

        register_rest_route('nwpdiscountly/v1', '/get-discount/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_discount'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/create-discount', [
            'methods' => 'POST',
            'callback' => [$this, 'create_discount'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/update-discount/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_discount'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/update-priority', [
            'methods' => 'POST',
            'callback' => [$this, 'update_discount_priority'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/delete-discount', [
            'methods' => 'POST',
            'callback' => [$this, 'delete_discount'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

        register_rest_route('nwpdiscountly/v1', '/update-status', [
            'methods' => 'POST',
            'callback' => [$this, 'update_discount_status'],
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            },
        ]);

    }

    /**
     * Get discount lists
     */
    public function get_discounts() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nwpdiscountly ORDER BY priority ASC", ARRAY_A);
        foreach ($results as &$discount) {
            $discount_id = $discount['id'];
            $meta_results = $wpdb->get_results(
                $wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->prefix}nwpdiscountly_meta WHERE discount_id = %d", $discount_id),
                ARRAY_A
            );

            $discount['meta'] = [];
            foreach ($meta_results as $meta) {
                $discount['meta'][$meta['meta_key']] = $meta['meta_value'];
            }
        }
        return rest_ensure_response($results);
    }

    /**
     * Get a specific discount by ID
     * @param $request
     * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_discount($request) {
        global $wpdb;
        $discount_id = intval($request['id']);

        $discount = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nwpdiscountly WHERE id = %d", $discount_id), ARRAY_A);

        if (!$discount) {
            return new WP_Error('not_found', __('Discount not found.', 'nwpdiscountly'), ['status' => 404]);
        }

        $meta_data = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->prefix}nwpdiscountly_meta WHERE discount_id = %d", $discount_id), ARRAY_A);

        $discount['meta'] = [];
        foreach ($meta_data as $meta) {
            // Try to decode the JSON string if it's a valid JSON
            $decoded_value = json_decode($meta['meta_value'], true); // Decode to associative array

            // Check if decoding was successful (if it's valid JSON)
            if (json_last_error() == JSON_ERROR_NONE) {
                $discount['meta'][$meta['meta_key']] = $decoded_value;
            } else {
                $discount['meta'][$meta['meta_key']] = $meta['meta_value'];
            }
        }


        return rest_ensure_response($discount);
    }

    /**
     * Create discount
     * @param $request
     * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function create_discount($request) {
        global $wpdb;
        $data = $request->get_json_params();

        // Validations
        $validation_result = $this->validate_discount_data($data);
        if (is_wp_error($validation_result)) {
            return $validation_result;
        }

        // Priority
        $max_priority = $wpdb->get_var("SELECT MAX(priority) FROM {$wpdb->prefix}nwpdiscountly");
        $new_priority = $max_priority ? $max_priority + 1 : 1;

        $wpdb->insert(
            $wpdb->prefix . 'nwpdiscountly',
            [
                'name' => sanitize_text_field($data['discountDetails']['discountName']),
                'type' => sanitize_text_field($data['discountDetails']['discountType']),
                'active' => intval($data['discountDetails']['active']),
                'priority' => $new_priority
            ],
            ['%s', '%s', '%d']
        );

        $discount_id = $wpdb->insert_id;

        foreach ($data['discountMeta'] as $meta_key => $meta_value) {
            if (is_array($meta_value)) {
                $meta_value = wp_json_encode($meta_value);
            } elseif ( $meta_key == 'product_promo_message' ){
                $meta_value = wp_kses_post($meta_value);
            } else {
                $meta_value = sanitize_text_field($meta_value);
            }
            $wpdb->insert(
                $wpdb->prefix . 'nwpdiscountly_meta',
                [
                    'discount_id' => $discount_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value,
                ],
                ['%d', '%s', '%s']
            );
        }

        return rest_ensure_response(['message' => __('Discount created successfully.','nwpdiscountly')]);
    }

    /**
     * Update discount
     * @param $request
     * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function update_discount($request) {
        global $wpdb;

        $discount_id = intval($request['id']);
        $data = $request->get_json_params();

        $table = $wpdb->prefix . 'nwpdiscountly';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nwpdiscountly WHERE id = %d", $discount_id));

        if (!$exists) {
            return new WP_Error('not_found', __('Discount not found.', 'nwpdiscountly'), ['status' => 404]);
        }

        // Validations
        $validation_result = $this->validate_discount_data($data);
        if (is_wp_error($validation_result)) {
            return $validation_result;
        }

        $updated = $wpdb->update(
            $table,
            [
                'name' => sanitize_text_field($data['discountDetails']['discountName']),
                'type' => sanitize_text_field($data['discountDetails']['discountType']),
                'active' => intval($data['discountDetails']['active']),
            ],
            ['id' => $discount_id],
            ['%s', '%s', '%d'],
            ['%d']
        );

        if ($updated === false) {
            return new WP_Error('db_error', __('Failed to update discount.', 'nwpdiscountly'), ['status' => 500]);
        }

        $meta_table = $wpdb->prefix . 'nwpdiscountly_meta';
        foreach ($data['discountMeta'] as $meta_key => $meta_value) {
            if (is_array($meta_value)) {
                $meta_value = wp_json_encode($meta_value);
            } elseif ( $meta_key == 'product_promo_message' ){
                $meta_value = wp_kses_post($meta_value);
            } else {
                $meta_value = sanitize_text_field($meta_value);
            }

            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nwpdiscountly_meta WHERE discount_id = %d AND meta_key = %s", $discount_id, $meta_key));

            if ($exists) {
                $wpdb->update(
                    $meta_table,
                    ['meta_value' => $meta_value],
                    ['discount_id' => $discount_id, 'meta_key' => $meta_key],
                    ['%s'],
                    ['%d', '%s']
                );
            } else {
                $wpdb->insert(
                    $meta_table,
                    [
                        'discount_id' => $discount_id,
                        'meta_key' => $meta_key,
                        'meta_value' => $meta_value,
                    ],
                    ['%d', '%s', '%s']
                );
            }
        }

        return rest_ensure_response(['message' => __('Discount updated successfully.', 'nwpdiscountly')]);
    }

    /**
     * Delete discount
     * @param $request
     * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function delete_discount($request) {
        global $wpdb;

        $data = $request->get_json_params();
        $discount_id = intval($data['id']);

        $table = $wpdb->prefix . 'nwpdiscountly';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nwpdiscountly WHERE id = %d", $discount_id));

        if (!$exists) {
            return new WP_Error('not_found', __('Discount not found.', 'nwpdiscountly'), ['status' => 404]);
        }

        $meta_table = $wpdb->prefix . 'nwpdiscountly_meta';
        $wpdb->delete($meta_table, ['discount_id' => $discount_id], ['%d']);

        $deleted = $wpdb->delete($table, ['id' => $discount_id], ['%d']);

        if ($deleted === false) {
            return new WP_Error('db_error', __('Failed to delete discount.', 'nwpdiscountly'), ['status' => 500]);
        }

        return rest_ensure_response(['message' => __('Discount deleted successfully.', 'nwpdiscountly')]);
    }

    /**
     * Update discount priority
     * @param $request
     * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function update_discount_priority($request) {
        global $wpdb;

        $data = $request->get_json_params();
        $discounts = $data['discounts'];

        if (empty($discounts) || !is_array($discounts)) {
            return new WP_Error('invalid_data', __('Invalid data.', 'nwpdiscountly'), ['status' => 400]);
        }

        $table = $wpdb->prefix . 'nwpdiscountly';

        foreach ($discounts as $discount) {
            $discount_id = intval($discount['id']);
            $priority = intval($discount['priority']);

            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nwpdiscountly WHERE id = %d", $discount_id));

            if (!$exists) {
                return new WP_Error('not_found', __('Discount not found.', 'nwpdiscountly'), ['status' => 404]);
            }

            $updated = $wpdb->update(
                $table,
                ['priority' => $priority],
                ['id' => $discount_id],
                ['%d'],
                ['%d']
            );

            if ($updated === false) {
                return new WP_Error('db_error', __('Failed to update priority.', 'nwpdiscountly'), ['status' => 500]);
            }
        }

        return rest_ensure_response(['message' => __('Discount priorities updated successfully.', 'nwpdiscountly')]);
    }

    /**
     * Update discount status
     * @param $request
     * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function update_discount_status($request) {
        global $wpdb;

        $data = $request->get_json_params();
        $discount_id = intval($data['id']);
        $active = intval($data['active']);

        $table = $wpdb->prefix . 'nwpdiscountly';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nwpdiscountly WHERE id = %d", $discount_id));

        if (!$exists) {
            return new WP_Error('not_found', __('Discount not found.', 'nwpdiscountly'), ['status' => 404]);
        }

        $updated = $wpdb->update(
            $table,
            ['active' => $active],
            ['id' => $discount_id],
            ['%d'],
            ['%d']
        );

        if ($updated === false) {
            return new WP_Error('db_error', __('Failed to update status.', 'nwpdiscountly'), ['status' => 500]);
        }

        return rest_ensure_response(['message' => __('Discount status updated successfully.', 'nwpdiscountly')]);
    }

    /**
     * Validate discount data
     * @param $data
     * @return true|WP_Error
     */
    private function validate_discount_data($data){
        $products_type = $data['discountMeta']['products'];
        $discount_name = $data['discountDetails']['discountName'];
        $discount_type = $data['discountDetails']['discountType'];
        $availability = $data['discountMeta']['availability'];
        $start_date = $data['discountMeta']['start_date'];
        $end_date = $data['discountMeta']['end_date'];
        $amount_type = $data['discountMeta']['amount_type'];
        $percentage_discount = $data['discountMeta']['percentage_discount'];
        $fixed_discount = $data['discountMeta']['fixed_discount'];
        $percentage_discount_cap = $data['discountMeta']['percentage_discount_cap'];
        $min_purchase_amount = $data['discountMeta']['min_purchase_amount'];
        $selected_products = $data['discountMeta']['selected_products'];
        $selected_categories = $data['discountMeta']['selected_categories'];
        $applies_to = $data['discountMeta']['applies_to'];
        $selected_users = $data['discountMeta']['selected_users'];
        $selected_roles = $data['discountMeta']['selected_roles'];

        if ( empty($discount_name) ){
            return new WP_Error('missing_discount_name', __('Please enter a discount name.','nwpdiscountly'), ['status' => 400]);
        }

        if( $availability === 'specific_date' ){
            if ( empty($start_date) || empty($end_date) ) {
                return new WP_Error(
                    'missing_discount_availability',
                    __('Availability date cannot be left empty.', 'nwpdiscountly'),
                    ['status' => 400]
                );
            }

            if ( strtotime($end_date) < strtotime($start_date) ) {
                return new WP_Error(
                    'invalid_date_range',
                    __('The end date must be after the start date.', 'nwpdiscountly'),
                    ['status' => 400]
                );
            }

        }

        if ( $amount_type === 'percentage_discount' || $amount_type === 'percentage_discount_cap' ) {
            if ( empty($percentage_discount) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a percentage discount amount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( !is_numeric($percentage_discount) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a valid percentage discount amount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( $percentage_discount <= 0 ) {
                return new WP_Error('missing_discount_amount', __('Please enter a positive percentage discount amount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if( $percentage_discount > 100 ){
                return new WP_Error('missing_discount_amount', __('Please enter a percentage discount amount less than 100.', 'nwpdiscountly'), ['status' => 400]);
            }
        }
        if ( $amount_type === 'fixed_discount' ) {

            if ( empty($fixed_discount) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a fixed discount amount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( !is_numeric($fixed_discount) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a valid fixed discount amount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( $fixed_discount <= 0 ) {
                return new WP_Error('missing_discount_amount', __('Please enter a positive fixed discount amount.', 'nwpdiscountly'), ['status' => 400]);
            }

        }

        if ( $amount_type === 'percentage_discount_cap' ) {

            if ( empty($percentage_discount_cap) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a discount cap amount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( !is_numeric($percentage_discount_cap) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a valid discount cap amount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( $percentage_discount_cap <= 0 ) {
                return new WP_Error('missing_discount_amount', __('Please enter a positive discount cap amount.', 'nwpdiscountly'), ['status' => 400]);
            }

        }

        if ( $discount_type === 'cart_discount' ) {

            if ( empty($min_purchase_amount) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a minimum purchase amount for discount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( !is_numeric($min_purchase_amount) ) {
                return new WP_Error('missing_discount_amount', __('Please enter a valid minimum purchase amount for discount.', 'nwpdiscountly'), ['status' => 400]);
            }

            if ( $min_purchase_amount <= 0 ) {
                return new WP_Error('missing_discount_amount', __('Please enter a positive minimum purchase amount for discount.', 'nwpdiscountly'), ['status' => 400]);
            }

        }

        if( $products_type === 'selected_products' ){
            if( empty($selected_products) ){
                return new WP_Error('missing_discount_name', __('Please select a product.','nwpdiscountly'), ['status' => 400]);
            }
        }

        if( $products_type === 'selected_categories' ){
            if( empty($selected_categories) ){
                return new WP_Error('missing_discount_name', __('Please select a category.','nwpdiscountly'), ['status' => 400]);
            }
        }

        if( $applies_to === 'selected_users' ){
            if( empty($selected_users) ){
                return new WP_Error('missing_discount_name', __('Please select a user.','nwpdiscountly'), ['status' => 400]);
            }
        }

        if( $applies_to === 'selected_roles' ){
            if( empty($selected_roles) ){
                return new WP_Error('missing_discount_name', __('Please select a role.','nwpdiscountly'), ['status' => 400]);
            }
        }

        return true;
    }
}