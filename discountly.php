<?php
/**
 * Plugin Name: Discountly – Discount Manager for WooCommerce
 * Plugin URI: https://nikanwp.com/product/discountly
 * Description: Smart Discounts for WooCommerce
 * Version: 1.0.0
 * Author: NikanWP
 * Author URI: https://nikanwp.com
 * Text Domain: nwpdiscountly
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
defined( 'ABSPATH' ) || exit;

/**
 * Define constants for plugin directory and URL
 */
define( 'NWPDISCOUNTLY_DIR', plugin_dir_path(__FILE__) );
define( 'NWPDISCOUNTLY_URL', plugin_dir_url(__FILE__) );

/**
 * Import the necessary classes
 */
include_once NWPDISCOUNTLY_DIR . 'inc/database/Tables.php';
include_once NWPDISCOUNTLY_DIR . 'inc/admin/AdminMenu.php';
include_once NWPDISCOUNTLY_DIR . 'inc/admin/AdminAsset.php';
include_once NWPDISCOUNTLY_DIR . 'inc/rest-api/DiscountsApi.php';
include_once NWPDISCOUNTLY_DIR . 'inc/rest-api/UsersApi.php';
include_once NWPDISCOUNTLY_DIR . 'inc/rest-api/CategoriesApi.php';
include_once NWPDISCOUNTLY_DIR . 'inc/rest-api/TagsApi.php';
include_once NWPDISCOUNTLY_DIR . 'inc/rest-api/ProductsApi.php';
include_once NWPDISCOUNTLY_DIR . 'inc/discount/GlobalDiscount.php';
include_once NWPDISCOUNTLY_DIR . 'inc/discount/CartDiscount.php';
include_once NWPDISCOUNTLY_DIR . 'inc/discount/DiscountHelpers.php';

/**
 * Plugin activation
 */
function nwpdiscountly_activation(){
    ob_start();
    \NikanWP\NWPDiscountly\database\Tables::create_tables();
    ob_end_clean();
}
register_activation_hook(__FILE__,'nwpdiscountly_activation');


/**
 * Initialize plugin
 * @return void
 */
function nwpdiscountly_init(){

    // API
    new \NikanWP\NWPDiscountly\RestApi\DiscountsApi();
    new \NikanWP\NWPDiscountly\RestApi\UsersApi();
    new \NikanWP\NWPDiscountly\RestApi\CategoriesApi();
    new \NikanWP\NWPDiscountly\RestApi\TagsApi();
    new \NikanWP\NWPDiscountly\RestApi\ProductsApi();

    // Admin
    new \NikanWP\NWPDiscountly\Admin\AdminMenu();
    new \NikanWP\NWPDiscountly\Admin\AdminAsset();

    // Discount
    new \NikanWP\NWPDiscountly\discount\GlobalDiscount();
    new \NikanWP\NWPDiscountly\discount\CartDiscount();

}
add_action('init','nwpdiscountly_init');
