<?php
namespace NikanWP\NWPDiscountly\discount;
class GlobalDiscount {

    public function __construct(){
        add_filter( 'woocommerce_get_price_html', [$this, 'set_price_html'], 20, 2 );
        add_filter( 'woocommerce_available_variation', [$this, 'set_variation_price_html'], 20, 2 );
        add_filter( 'woocommerce_product_get_sale_price', [ $this, 'set_sale_price' ], 20, 2 );
        add_filter( 'woocommerce_product_variation_get_sale_price', [ $this, 'set_sale_price' ], 20, 2 );
        add_filter( 'woocommerce_product_is_on_sale', [ $this, 'set_is_on_sale' ], 20, 2 );
        add_action( 'woocommerce_before_add_to_cart_form', [$this,'display_product_promo_message'], 20 );
        add_action( 'woocommerce_before_calculate_totals', [$this, 'apply_global_discount'], 999 );
    }

    /**
     * Set price html to simple products
     * @param $price_html
     * @param $product
     * @return mixed|string
     */
    public function set_price_html( $price_html, $product ) {
        if ( is_admin() || ( is_cart() && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) || is_checkout() ) {
            return $price_html;
        }

        $discounts = DiscountHelpers::get_discounts();

        foreach ( $discounts as $discount ) {
            $meta = DiscountHelpers::get_discount_meta( $discount['id'] );

            if ( $discount['type'] !== 'global_discount' ) {
                continue;
            }

            // Simple product
            if ( $product->is_type( 'simple' ) ) {
                if ( ! DiscountHelpers::is_discount_valid( $product, $meta ) ) {
                    continue;
                }

                $original_price   = $product->get_regular_price();
                $discounted_price = DiscountHelpers::calculate_discount( $product, $meta, $original_price );

                if ( $original_price > $discounted_price ) {
                    $price_html = wc_format_sale_price( $original_price, $discounted_price );
                } else {
                    $price_html = wc_price( $original_price );
                }

                break;
            }

            // Variable product
            if ( $product->is_type( 'variable' ) ) {
                $variations            = $product->get_available_variations();
                $original_prices       = [];
                $final_prices          = [];

                foreach ( $variations as $variation ) {
                    $variation_obj   = new \WC_Product_Variation( $variation['variation_id'] );
                    $original_price  = $variation_obj->get_regular_price();
                    $final_price     = $original_price;

                    if ( DiscountHelpers::is_discount_valid( $variation_obj, $meta ) ) {
                        $final_price = DiscountHelpers::calculate_discount( $variation_obj, $meta, $original_price );
                    }

                    if ( $original_price > 0 ) {
                        $original_prices[] = $original_price;
                        $final_prices[]    = $final_price;
                    }
                }

                if ( ! empty( $original_prices ) && ! empty( $final_prices ) ) {
                    $final_min = min( $final_prices );
                    $final_max = max( $final_prices );

                    if ( $final_min === $final_max ) {
                        $price_html = wc_price( $final_min );
                    } else {
                        $price_html = sprintf(
                            '%s - %s',
                            wc_price( $final_min ),
                            wc_price( $final_max )
                        );
                    }
                }

                break;
            }
        }

        return $price_html;
    }

    /**
     * Set price html to variable products
     * @param $variation_data
     * @param $product
     * @return mixed
     */
    public function set_variation_price_html( $variation_data, $product ) {
        $discounts = DiscountHelpers::get_discounts();

        foreach ($discounts as $discount) {
            $meta = DiscountHelpers::get_discount_meta($discount['id']);

            if( $discount['type'] !== 'global_discount' ){
                continue;
            }

            $variation_id = $variation_data['variation_id'];
            $variation = new \WC_Product_Variation( $variation_id );

            if( !DiscountHelpers::is_discount_valid($variation, $meta) ){
                continue;
            }

            $original_price = $variation->get_regular_price();
            $discounted_price = DiscountHelpers::calculate_discount( $variation, $meta );

            if ($original_price > $discounted_price) {
                $variation_data['price_html'] = wc_format_sale_price( $original_price, $discounted_price );
            } else {
                $variation_data['price_html'] = wc_price( $original_price );
            }

            break;
        }

        return $variation_data;
    }

    /**
     * Set sale price
     * @param $sale_price
     * @param $product
     * @return mixed
     */
    public function set_sale_price( $sale_price, $product ) {
        if ( is_admin() || ( is_cart() && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) || is_checkout() ) {
            return $sale_price;
        }

        $discounts = DiscountHelpers::get_discounts();

        if ( $product->is_type('simple') ) {
            foreach ( $discounts as $discount ) {
                $meta = DiscountHelpers::get_discount_meta( $discount['id'] );

                if ( $discount['type'] !== 'global_discount' ) {
                    continue;
                }

                if ( ! DiscountHelpers::is_discount_valid( $product, $meta ) ) {
                    continue;
                }

                return DiscountHelpers::calculate_discount( $product, $meta );
            }
        }

        if ( $product->is_type('variable') ) {
            $variations = $product->get_children();

            foreach ( $variations as $variation_id ) {
                $variation = wc_get_product( $variation_id );

                if ( ! $variation || ! $variation->exists() ) {
                    continue;
                }

                foreach ( $discounts as $discount ) {
                    $meta = DiscountHelpers::get_discount_meta( $discount['id'] );

                    if ( $discount['type'] !== 'global_discount' ) {
                        continue;
                    }

                    if ( DiscountHelpers::is_discount_valid( $variation, $meta ) ) {
                        return $sale_price;
                    }
                }
            }
        }

        if ( $product->is_type('variation') ) {
            foreach ( $discounts as $discount ) {
                $meta = DiscountHelpers::get_discount_meta( $discount['id'] );

                if ( $discount['type'] !== 'global_discount' ) {
                    continue;
                }

                if ( ! DiscountHelpers::is_discount_valid( $product, $meta ) ) {
                    continue;
                }

                return DiscountHelpers::calculate_discount( $product, $meta );
            }
        }

        return $sale_price;
    }


    /**
     * Set is on sale
     * @param $is_on_sale
     * @param $product
     * @return mixed|true
     */
    public function set_is_on_sale( $is_on_sale, $product ) {
        if ( is_admin() || ( is_cart() && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) || is_checkout() ) {
            return $is_on_sale;
        }

        $discounts = DiscountHelpers::get_discounts();

        foreach ( $discounts as $discount ) {
            $meta = DiscountHelpers::get_discount_meta( $discount['id'] );

            if ( $discount['type'] !== 'global_discount' ) {
                continue;
            }

            if ( $product->is_type( 'variable' ) ) {
                $children = $product->get_children();

                foreach ( $children as $child_id ) {
                    $variation = wc_get_product( $child_id );

                    if ( ! $variation || ! $variation->exists() ) {
                        continue;
                    }

                    if ( DiscountHelpers::is_discount_valid( $variation, $meta ) ) {
                        return true;
                    }
                }

            } else {
                if ( DiscountHelpers::is_discount_valid( $product, $meta ) ) {
                    return true;
                }
            }
        }

        return $is_on_sale;
    }


    /**
     * Display a promotional message on product page
     * @return void
     */
    public function display_product_promo_message(){
        global $product;

        $discounts = DiscountHelpers::get_discounts();
        foreach ($discounts as $discount) {
            $meta = DiscountHelpers::get_discount_meta($discount['id']);

            if( $discount['type'] !== 'global_discount' ){
                continue;
            }

            if( !DiscountHelpers::is_discount_valid($product,$meta) ){
                continue;
            }

            if ( !empty( $meta['product_promo_message'] ) ) {
                echo '<div class="nwpdiscountly">' . wp_kses_post( $meta['product_promo_message'] ) . '</div>';
            }
        }

    }

    /**
     * Apply discount to cart
     * @param $cart
     * @return void
     */
    public function apply_global_discount($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        $discounts = DiscountHelpers::get_discounts();
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];

            foreach ($discounts as $discount) {
                $meta = DiscountHelpers::get_discount_meta($discount['id']);

                if( $discount['type'] !== 'global_discount' ){
                    continue;
                }

                if ( DiscountHelpers::is_discount_disabled_with_coupon($meta) ) {
                    continue;
                }

                if( !DiscountHelpers::is_discount_valid($product,$meta) ){
                    continue;
                }

                $discounted_price = DiscountHelpers::calculate_discount($product, $meta);

                $product->set_price($discounted_price);
                break;
            }
        }
    }
}
