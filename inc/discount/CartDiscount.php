<?php

namespace NikanWP\NWPDiscountly\discount;

class CartDiscount{
    public function __construct(){
        add_action( 'woocommerce_cart_calculate_fees', [$this, 'apply_cart_discount'], 999 );
    }

    /**
     * Apply discount to cart
     * @param $cart
     * @return void
     */
    public function apply_cart_discount($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $discounts = DiscountHelpers::get_discounts();

        foreach ($discounts as $discount) {
            $meta = DiscountHelpers::get_discount_meta($discount['id']);
            $min_purchase_amount = isset($meta['min_purchase_amount']) ? $meta['min_purchase_amount'] : 0;

            if ($discount['type'] !== 'cart_discount') {
                continue;
            }

            $eligible_cart_total = 0;

            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];

                if ( DiscountHelpers::is_discount_disabled_with_coupon($meta) ) {
                    continue;
                }

                if (!DiscountHelpers::is_discount_valid($product, $meta)) {
                    continue;
                }

                $eligible_cart_total += $cart_item['line_total'];
            }

            if ($eligible_cart_total < $min_purchase_amount) {
                continue;
            }

            $discounted_price = 0;

            $percentage_discount = isset( $meta['percentage_discount'] ) ? floatval( $meta['percentage_discount'] ) : 0;
            $fixed_discount = isset( $meta['fixed_discount'] ) ? floatval( $meta['fixed_discount'] ) : 0;
            $percentage_discount_cap = isset( $meta['percentage_discount_cap'] ) ? floatval( $meta['percentage_discount_cap'] ) : 0;

            if ( $meta['amount_type'] === 'percentage_discount' && $percentage_discount > 0 ) {
                $discounted_price = $eligible_cart_total * ( $percentage_discount / 100 );
            } elseif ( $meta['amount_type'] === 'fixed_discount' && $fixed_discount > 0 ) {
                $discounted_price = $fixed_discount;
            }elseif ( $meta['amount_type'] === 'percentage_discount_cap' && $percentage_discount > 0 ) {
                $discounted_price = $eligible_cart_total * ( $percentage_discount / 100 );
                if ( $percentage_discount_cap > 0 && $discounted_price > $percentage_discount_cap ) {
                    $discounted_price = $percentage_discount_cap;
                }
            }
            if ($discounted_price > 0) {
                $cart->add_fee($discount['name'], -$discounted_price);
            }
        }
    }
}

