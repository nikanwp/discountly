<?php
namespace NikanWP\NWPDiscountly\Admin;

class AdminMenu{
    public function __construct(){
        add_action('admin_menu',[$this,'add_admin_menu']);
    }

    /**
     * Add admin menu
     * @return void
     */
    public function add_admin_menu(){
        add_menu_page(
            __('Discountly','nwpdiscountly'),
            __('Discountly','nwpdiscountly'),
            'manage_options',
            'nwpdiscountly',
            [$this,'render_admin_menu'],
            'dashicons-edit-large'
        );
    }

    /**
     * Render admin menu
     * @return void
     */
    public function render_admin_menu(){
        echo '<div id="nwpdiscountly-app"></div>';
    }
}