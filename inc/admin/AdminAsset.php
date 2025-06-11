<?php
namespace NikanWP\NWPDiscountly\Admin;

class AdminAsset{
    public function __construct(){
        add_action('admin_enqueue_scripts',[$this,'add_scripts']);
    }

    /**
     * Add scripts
     * @param $admin_page
     * @return void
     */
    public function add_scripts($admin_page){
        // Check the current page
        if( $admin_page !== 'toplevel_page_nwpdiscountly' ) return;

        // Include assets
        $asset_file = NWPDISCOUNTLY_DIR . 'build/index.asset.php';
        if ( ! file_exists( $asset_file ) ) return;
        $asset = include $asset_file;

        // Add JS file
        $js_handle_name = 'nwpdiscountly-js';
        $js_file = NWPDISCOUNTLY_URL . 'build/index.js';
        wp_register_script(
            $js_handle_name,
            $js_file,
            is_array($asset['dependencies']) ? $asset['dependencies'] : [],
            $asset['version'],
            array(
                'in_footer' => true,
            )
        );
        wp_enqueue_script($js_handle_name);
        wp_set_script_translations($js_handle_name, 'discountly', NWPDISCOUNTLY_DIR . 'languages/');

        // Add CSS file
        $css_file = is_rtl() ? NWPDISCOUNTLY_URL . 'build/index-rtl.css' : NWPDISCOUNTLY_URL . 'build/index.css';
        $css_handle_name = is_rtl() ? 'nwpdiscountly-css-rtl' : 'nwpdiscountly-css';
        wp_enqueue_style(
            $css_handle_name,
            $css_file,
            array_filter(
                $asset['dependencies'],
                function ( $style ) {
                    return wp_style_is( $style, 'registered' );
                }
            ),
            $asset['version'],
        );

        // Enqueue the WP editor
        wp_enqueue_media();
        wp_enqueue_editor();

        // Localize script
        $localize = array(
            'nonce' => wp_create_nonce('wp_rest'),
            'admin_url' => admin_url('admin.php'),
            'per_page' => 40,
            'wp_lang' => get_locale(),
            'wc_currency_symbol' => get_woocommerce_currency_symbol(),
            'wc_decimal_separator' => wc_get_price_decimal_separator(),
            'wc_number_of_decimals' => wc_get_price_decimals(),
        );
        wp_localize_script( $js_handle_name,'nwpdiscountly',$localize);
    }
}

