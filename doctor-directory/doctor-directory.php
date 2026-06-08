<?php
/**
 * Plugin Name: Doctor Directory
 * Description: National doctor directory with CRUD management integrated in WordPress.
 * Version: 1.0.0
 * Author: Cesar Bolivar
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DD_VERSION',    '1.0.0' );
define( 'DD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DD_TABLE',      'dd_doctors' );

spl_autoload_register( function( $class ) {
    $classes = [
        'DD_Database'  => DD_PLUGIN_DIR . 'includes/class-dd-database.php',
        'DD_Doctor'    => DD_PLUGIN_DIR . 'includes/class-dd-doctor.php',
        'DD_Validator' => DD_PLUGIN_DIR . 'includes/class-dd-validator.php',
        'DD_Admin'     => DD_PLUGIN_DIR . 'admin/class-dd-admin.php',
    ];
    if ( isset( $classes[ $class ] ) ) {
        require_once $classes[ $class ];
    }
} );

register_activation_hook( __FILE__, [ 'DD_Database', 'create_table' ] );
register_deactivation_hook( __FILE__, '__return_null' );

add_action( 'plugins_loaded', function() {
    if ( is_admin() ) {
        new DD_Admin();
    }
} );

// Shortcode [doctor_directory] — muestra el directorio público de médicos activos
add_shortcode( 'doctor_directory', function( $atts ) {
    wp_enqueue_style( 'dd-public', DD_PLUGIN_URL . 'public/dd-public.css', [], DD_VERSION );

    ob_start();
    require DD_PLUGIN_DIR . 'public/view-shortcode.php';
    return ob_get_clean();
} );
