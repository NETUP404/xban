<?php
/*
Plugin Name: Banner Exchange System
Description: Sistema de intercambio de banners para WordPress.
Version: 2.0
Author: webanner
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Incluir archivos necesarios
include_once plugin_dir_path( __FILE__ ) . 'includes/banner-handler.php';
include_once plugin_dir_path( __FILE__ ) . 'admin/admin-panel.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/user-panel.php';

// Activar el plugin
function bes_activate() {
    bes_create_db();
    bes_add_default_credits_to_users(); // Añadir créditos a usuarios existentes
}
register_activation_hook( __FILE__, 'bes_activate' );

// Desactivar el plugin
function bes_deactivate() {
    // Limpieza si es necesario
}
register_deactivation_hook( __FILE__, 'bes_deactivate' );

// Crear la base de datos
function bes_create_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        banner_url varchar(255) NOT NULL,
        target_url varchar(255) NOT NULL,
        impressions int(11) DEFAULT 0 NOT NULL,
        clicks int(11) DEFAULT 0 NOT NULL,
        credits int(11) DEFAULT 0 NOT NULL,
        approved tinyint(1) DEFAULT 0 NOT NULL,
        title varchar(62) DEFAULT '' NOT NULL, -- Título de campaña para SEO
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Añadir créditos a usuarios existentes
function bes_add_default_credits_to_users() {
    $users = get_users();
    foreach ($users as $user) {
        update_user_meta($user->ID, 'bes_credits', 200); // Otorgar 200 créditos
    }
}

// Limitar impresiones y clics por usuario único
function bes_limit_impressions_and_clicks($banner_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $impression_cookie = "bes_impression_$banner_id";
    $click_cookie = "bes_click_$banner_id";
    
    // Limitar a 4 impresiones por usuario único
    if (!isset($_COOKIE[$impression_cookie])) {
        setcookie($impression_cookie, $ip, time() + 3600 * 24, "/");
        $wpdb->query("UPDATE $table_name SET impressions = impressions + 1 WHERE id = $banner_id");
    }
    
    // Limitar a 1 clic por usuario único
    if (isset($_GET['click']) && !isset($_COOKIE[$click_cookie])) {
        setcookie($click_cookie, $ip, time() + 3600 * 24, "/");
        $wpdb->query("UPDATE $table_name SET clicks = clicks + 1, credits = credits + 20 WHERE id = $banner_id");
    }
}

// Registrar el shortcode para el formulario de envío de banners
function bes_register_shortcodes() {
    add_shortcode('bes_submit_banner', 'bes_display_submit_banner_form');
    add_shortcode('bes_display_banner', 'bes_display_banner_shortcode');
}
add_action('init', 'bes_register_shortcodes');