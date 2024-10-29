<?php
/**
 * Uninstall plugin
 */


/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// If uninstall not called from WordPress exit
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
