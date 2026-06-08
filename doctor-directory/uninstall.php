<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}dd_doctors`" );
delete_option( 'dd_db_version' );
