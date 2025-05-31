<?php
namespace NikanWP\NWPDiscountly\database;
class Tables
{
    public static function create_tables(){
        ob_start();

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Tables name
        $table_nwpdiscountly = $wpdb->prefix . 'nwpdiscountly';
        $table_nwpdiscountly_meta = $wpdb->prefix . 'nwpdiscountly_meta';

        // SQL to create 'nwpdiscountly' table
        $sql_nwpdiscountly = "
            CREATE TABLE $table_nwpdiscountly (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(50) NOT NULL,
                active TINYINT(1) NOT NULL DEFAULT 0,
                priority INT(11) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX (active),
                INDEX (type)
            ) $charset_collate;
        ";

        // SQL to create 'nwpdiscountly_meta' table
        $sql_nwpdiscountly_meta = "
            CREATE TABLE $table_nwpdiscountly_meta (
            id bigint NOT NULL AUTO_INCREMENT,
            discount_id bigint NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext NOT NULL,
            PRIMARY KEY (id),
            KEY discount_id (discount_id),
            KEY meta_key (meta_key)
            ) $charset_collate;
        ";

        // Run SQL
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_nwpdiscountly);
        dbDelta($sql_nwpdiscountly_meta);

        ob_end_clean();
    }
}