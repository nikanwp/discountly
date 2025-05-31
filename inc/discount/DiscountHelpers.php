<?php

namespace NikanWP\NWPDiscountly\discount;

class DiscountHelpers {

    /**
     * Get active discounts from the nwpdiscountly table.
     *
     * @return array
     */
    public static function get_discounts(){
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nwpdiscountly WHERE active = %d ORDER BY priority ASC",
                1
            ),
            ARRAY_A
        );
    }


    /**
     * Get meta-data for a specific discount from the nwpdiscountly_meta table.
     *
     * @param int $discount_id
     * @return array
     */
    public static function get_discount_meta($discount_id) {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT meta_key, meta_value 
            FROM {$wpdb->prefix}nwpdiscountly_meta
            WHERE discount_id = %d
        ", $discount_id), ARRAY_A);

        // Format meta data into key-value pairs
        $meta_data = [];
        foreach ($results as $row) {
            $meta_data[$row['meta_key']] = $row['meta_value'];
        }
        return $meta_data;
    }

    /**
     * Check availability
     * @param $meta
     * @return bool
     */
    public static function is_discount_available($meta) {
        $current_date = current_time('mysql');

        if (!empty($meta['availability']) && $meta['availability'] === 'always_available') {
            return true;
        }

        $start_date = $meta['start_date'] ?? '';
        $end_date = $meta['end_date'] ?? '';

        if ($start_date && strtotime($current_date) < strtotime($start_date)) {
            return false;
        }

        if ($end_date && strtotime($current_date) > strtotime($end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check product eligibility
     * @param $product
     * @param $meta
     * @return bool
     */
    public static function is_product_eligible($product, $meta) {
        $products = $meta['products'] ?? 'all_products';
        $product_eligible = false;
        $product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();

        if ($products === 'all_products') {
            $product_eligible = true;
        } elseif ($products === 'selected_products') {
            $selected_products = json_decode($meta['selected_products'], true);
            foreach ($selected_products as $selected_product) {
                if ($selected_product['type'] == 'variation') {
                    if ($product->is_type('variation') && $selected_product['value'] == $product->get_id() && $selected_product['parentId'] == $product->get_parent_id()) {
                        $product_eligible = true;
                        break;
                    }
                } elseif ($selected_product['type'] == 'product') {
                    if (
                        (!$product->is_type('variation') && $selected_product['value'] == $product_id) ||
                        ($product->is_type('variation') && $selected_product['value'] == $product->get_parent_id())
                    ) {
                        $product_eligible = true;
                        break;
                    }
                }

            }
        } elseif ($products === 'selected_categories') {
            $selected_categories = json_decode($meta['selected_categories'], true);
            if ($product->is_type('variation')) {
                $parent_product = wc_get_product($product_id);
                $product_categories = $parent_product ? $parent_product->get_category_ids() : [];
            } else {
                $product_categories = $product->get_category_ids();
            }
            foreach ($product_categories as $category_id) {
                if (in_array($category_id, $selected_categories)) {
                    $product_eligible = true;
                    break;
                }
            }
        } elseif ($products === 'selected_tags') {
            $selected_tags = json_decode($meta['selected_tags'], true);
            if ($product->is_type('variation')) {
                $parent_product = wc_get_product($product_id);
                $product_tags = $parent_product ? $parent_product->get_tag_ids() : [];
            } else {
                $product_tags = $product->get_tag_ids();
            }
            foreach ($product_tags as $tag_id) {
                if (in_array($tag_id, $selected_tags)) {
                    $product_eligible = true;
                    break;
                }
            }
        }
        return $product_eligible;
    }

    /**
     * Check user eligibility
     * @param $meta
     * @return bool
     */
    public static function is_user_eligible($meta){
        $applies_to = $meta['applies_to'] ?? 'all_users';
        $selected_users = json_decode( $meta['selected_users'], true) ?? [];
        $selected_roles = json_decode( $meta['selected_roles'], true) ?? [];
        $user = wp_get_current_user();

        if ( $applies_to === 'selected_users' ) {
            if ( !$user->exists() || !in_array( $user->ID, $selected_users ) ) {
                return false;
            }
        }

        if ( $applies_to === 'selected_roles' ) {
            $user_role = $user->roles[0] ?? null;
            if ( !$user_role || !in_array( $user_role, $selected_roles ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate the discounted price
     * @param $product
     * @param $meta
     * @return mixed
     */
    public static function calculate_discount( $product, $meta ) {
        $price = $product->get_regular_price();

        if ( !is_numeric( $price ) ) {
            $price = $product->get_price();
        }

        if ( !is_numeric( $price ) || $price <= 0 ) {
            return $product->get_price();
        }

        $discount_value = 0;

        $percentage_discount = isset( $meta['percentage_discount'] ) ? floatval( $meta['percentage_discount'] ) : 0;
        $fixed_discount = isset( $meta['fixed_discount'] ) ? floatval( $meta['fixed_discount'] ) : 0;
        $percentage_discount_cap = isset( $meta['percentage_discount_cap'] ) ? floatval( $meta['percentage_discount_cap'] ) : 0;

        if ( $meta['amount_type'] === 'percentage_discount' && $percentage_discount > 0 ) {
            $discount_value = $price * ( $percentage_discount / 100 );
        } elseif ( $meta['amount_type'] === 'fixed_discount' && $fixed_discount > 0 ) {
            $discount_value = $fixed_discount;
        }elseif ( $meta['amount_type'] === 'percentage_discount_cap' && $percentage_discount > 0 ) {
            $discount_value = $price * ( $percentage_discount / 100 );
            if ( $percentage_discount_cap > 0 && $discount_value > $percentage_discount_cap ) {
                $discount_value = $percentage_discount_cap;
            }
        }

        return max( $price - $discount_value, 0 );
    }

    /**
     * Check valid discount
     * @param $product
     * @param $meta
     * @return bool
     */
    public static function is_discount_valid($product,$meta){
        return DiscountHelpers::is_discount_available($meta) &&
            DiscountHelpers::is_product_eligible($product, $meta) &&
            DiscountHelpers::is_user_eligible($meta);
    }

    /**
     * Check if the discount should be applied based on coupon usage
     * @param $meta
     * @param $product
     * @return bool
     */
    public static function is_discount_disabled_with_coupon($meta){
        $applied_coupons = WC()->cart->get_applied_coupons();
        $has_coupon = !empty($applied_coupons);
        $disable_if_coupon_applied = !empty($meta['disable_discount_with_coupon']);

        return $disable_if_coupon_applied && $has_coupon;
    }
}