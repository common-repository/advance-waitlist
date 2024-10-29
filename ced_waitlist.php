<?php
/**
 * Plugin Name: Advance Waitlist
 * Plugin URI: https://cedcommerce.com
 * Description: It provide an awesome feature of adding 'out of stock' products to wait list and customers get notified when product status changed to 'in stock'.
 * Author: CedCommerce
 * Author URI: http://cedcommerce.com
 * Requires at least: 4.0
 * Tested up to: 5.8
 * Version: 2.0.2
 * Text Domain: advance-waitlist
 * Domain Path: /language
 * 
 */

/**
 * Exit if accessed directly
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

define ( 'CED_AWL_PREFIX', 'ced_awl_' );
define ( 'CED_AWL_VERSION', '2.0.1' );

define ( 'ced_AWL_DIR_PATH', plugin_dir_path ( __FILE__ ) );
define ( 'CED_AWL_DIR_URL', plugin_dir_url ( __FILE__ ) );

$activated = true;
if (function_exists ( 'is_multisite' ) && is_multisite ()) {
	include_once (ABSPATH . 'wp-admin/includes/plugin.php');
	if (! is_plugin_active ( 'woocommerce/woocommerce.php' )) {
		$activated = false;
	}
} else {
	if (! in_array ( 'woocommerce/woocommerce.php', apply_filters ( 'active_plugins', get_option ( 'active_plugins' ) ) )) {
		$activated = false;
	}
}
/**
 * Check if WooCommerce is active
 */
if ($activated) {
	/**
	 * Check if class exist
	 */
	
	if (! class_exists ( 'ced_waitlist' )) {
		class ced_waitlist {
			/**
			 * Hook into the appropriate actions when the class is constructed.
			 * 
			 * @name __construct
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			public function __construct() {
				$plugin = plugin_basename ( __FILE__ );
				add_action ( 'plugins_loaded', array (
						$this,
						CED_AWL_PREFIX . 'load_text_domain' 
				) );
				add_action ( "plugin_action_links_$plugin", array (
						$this,
						CED_AWL_PREFIX . "add_settings_link" 
				) );
				register_activation_hook ( __FILE__, array (
						$this,
						CED_AWL_PREFIX . 'add_pages' 
				) );
				register_deactivation_hook ( __FILE__, array (
						$this,
						CED_AWL_PREFIX . 'pluginprefix_deactivation' 
				) );
				add_action ( 'deactivated_plugin', array (
						$this,
						CED_AWL_PREFIX . 'detect_plugin_deactivation' 
				), 10, 2 );
				add_action ( 'init', array (
						$this,
						CED_AWL_PREFIX . 'plugin_init' 
				) );
				add_action ( 'admin_menu', array (
						$this,
						CED_AWL_PREFIX . 'register_custom_submenu_page' 
				) );
				add_action ( 'admin_enqueue_scripts', array (
						$this,
						'add_our_script' 
				) );
				add_action ( 'wp_ajax_chng_sts_wtl_btn', array (
						$this,
						'chng_sts_wtl_btn' 
				) );
			}
			
			/**
			 * This function adding script and styles and setting ajax url
			 * 
			 * @name add_our_script
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function add_our_script() {
				$post_id = (isset ( $_GET ['post'] )) ? $_GET ['post'] : null;
				
				$plugin_dir = plugin_dir_url( __FILE__ );
				
				wp_enqueue_style ( "custom_style", $plugin_dir . 'assets/css/ced-css.css', array (), CED_AWL_VERSION, 'all' );
				wp_enqueue_script ( "custom_script", $plugin_dir . 'assets/js/ced-js.js', array (
						'jquery' 
				), CED_AWL_VERSION, true );
				
				if (isset ( $post_id )) {
					wp_localize_script ( 'custom_script', 'obj', array (
							'current_post_id' => $post_id 
					) );
				}
			}
			
			/**
			 * loading text domain
			 * 
			 * @name ced_awl_load_text_domain
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_load_text_domain() {
				$domain = "advance-waitlist";
				$locale = apply_filters ( 'plugin_locale', get_locale (), $domain );
				load_textdomain ( $domain, ced_AWL_DIR_PATH . 'language/' . $domain . '-' . $locale . '.mo' );
				load_plugin_textdomain ( 'advance-waitlist', false, plugin_basename ( dirname ( __FILE__ ) ) . '/language' );
			}
			
			/**
			 * Add Settings Link On Plugin Activation On Plugins Listing Page
			 * 
			 * @name ced_awl_add_settings_link
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_add_settings_link($links) {
				$settings_link = '<a href="' . get_admin_url () . 'admin.php?page=ced_plugin/waitinglist-admin.php">' . __ ( 'Settings', 'advance-waitlist' ) . '</a>';
				array_unshift ( $links, $settings_link );
				return $links;
			}
			
			/**
			 * Register custum waitlist post type
			 * 
			 * @name ced_awl_pluginprefix_setup_post_type
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_pluginprefix_setup_post_type() {
				global $user_ID;
				$page ['post_type'] = 'page';
				$page ['post_content'] = '[wish_list]';
				$page ['post_parent'] = 0;
				$page ['post_author'] = $user_ID;
				$page ['post_status'] = 'publish';
				$page ['post_title'] = 'Wait list';
				$page = apply_filters ( 'yourplugin_add_new_page', $page, 'teams' );
				$pageid = wp_insert_post ( $page );
				update_option ( CED_AWL_PREFIX . 'ced_activation_pageid', $pageid );
				$user = wp_get_current_user ();
			}
			
			/**
			 * add page on activation
			 * 
			 * @name ced_awl_add_pages
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 *      
			 *      
			 */
			function ced_awl_add_pages() {
				$this->ced_awl_pluginprefix_setup_post_type ();
			}
			
			/**
			 * delete page on deactivation
			 * 
			 * @name ced_awl_delete_page
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 *      
			 */
			function ced_awl_delete_page() {
				$page_id = get_option ( CED_AWL_PREFIX . 'ced_activation_pageid' );
				wp_delete_post ( $page_id, true );
				$mode = get_option ( CED_AWL_PREFIX . 'ced_deactivation_mode' );
				if ($mode != 1) {
					delete_post_meta_by_key ( CED_AWL_PREFIX . 'email_custum_content' );
					delete_post_meta_by_key ( CED_AWL_PREFIX . 'email_custum_mode' );
					delete_post_meta_by_key ( CED_AWL_PREFIX . 'user_email_notofication' );
					delete_post_meta_by_key ( 'user_mode' );
					delete_option ( "ced_awl_ced_notifiaction_message" );
					delete_option ( "ced_awl_ced_add_button" );
					delete_option ( "ced_awl_ced_successfull_registration" );
					delete_post_meta_by_key ( 'reservation' );
					$mycustomposts = get_posts ( array (
							'post_type' => 'wait_list' 
					) );
					foreach ( $mycustomposts as $mypost ) {
						wp_delete_post ( $mypost->ID, true );
					}
					$product_id = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
					foreach ( $product_id as $k => $v ) {
						delete_post_meta ( $v, CED_AWL_PREFIX . 'ced_wait_list' );
					}
				}
			}
			
			/**
			 * deleting options on deletion
			 * 
			 * @name ced_awl_pluginprefix_deactivation
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_pluginprefix_deactivation() {
				delete_option ( 'ced_feed' );
				delete_option ( 'ced_feed_awl' );
				$this->ced_awl_delete_page ();
				flush_rewrite_rules ();
			}
			
			/**
			 * detecting any plugin deactivation
			 * 
			 * @name ced_awl_detect_plugin_deactivation
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 * @param string $plugin        	
			 * @param string $network_activation        	
			 */
			function ced_awl_detect_plugin_deactivation($plugin, $network_activation) {
				if ($plugin == "woocommerce/woocommerce.php") {
					deactivate_plugins ( plugin_basename ( __FILE__ ) );
				}
			}
			
			/**
			 * plugin initiallization
			 * 
			 * @name ced_awl_plugin_init
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_plugin_init() {
				require_once ('includes/ced_plugin_class.php');
				require_once ('includes/ced_waitlist_main.php');
				require_once ('includes/ced_metabox.php');
				require_once ('includes/ced_email.php');
			}
			
			/**
			 * register new submenu page
			 * 
			 * @name ced_awl_register_custom_submenu_page
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_register_custom_submenu_page() {
				add_submenu_page ( 'woocommerce', 'WaitList setting', 'WaitList setting', 'manage_options', 'ced_plugin/waitinglist-admin.php', array (
						$this,
						CED_AWL_PREFIX . 'plugin_content' 
				) );
				add_submenu_page ( 'woocommerce', 'Wait', 'Out Of Stock Waitlist button status', 'manage_options', 'ced_plugin/waitingnou_button-admin.php', array (
						$this,
						CED_AWL_PREFIX . 'table_view_all_out_of_stock' 
				) );
			}
			
			/**
			 * content of custum submenu page
			 * 
			 * @name ced_awl_register_custom_submenu_page
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_plugin_content() {
				include 'templates/admin-general.php';
			}
			
			/**
			 * content of custum Sub-submenu page
			 * 
			 * @name ced_awl_register_custom_submenu_page
			 * @author CedCommerce<plugins@cedcommerce.com>
			 * @link http://cedcommerce.com/
			 */
			function ced_awl_table_view_all_out_of_stock() {
				include 'templates/admin-oos_prod.php';
			}
			
			/**
			 * Change status of product waitlist button
			 */
			function chng_sts_wtl_btn() {
				$cur_sts = get_post_meta ( $_POST ['content'], '_waitlist_button', true );
				$new_sts = ($cur_sts == 'yes') ? 'no' : 'yes';
				update_post_meta ( $_POST ['content'], '_waitlist_button', $new_sts );
				$cur_sts1 = get_post_meta ( $_POST ['content'], '_waitlist_button', true );
				wp_send_json_success ( $cur_sts1 );
				DIE ();
			}
		}
		$GLOBALS ['ced'] = new ced_waitlist ();
	}
} else {
	/**
	 * showing error notices
	 * 
	 * @name ced_awl_plugin_error_notice
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_plugin_error_notice() {
		?>
<div class="error notice is-dismissible">
	<p><?php _e( 'Woocommerce is not activated. Please install woocommerce to use the Advance Waitlist plugin !', 'ced-Waitlist' ); ?></p>
</div>
;
		<?php
	}
	
	add_action ( 'admin_init', CED_AWL_PREFIX . 'plugin_deactivate' );
	/**
	 * deleting post types
	 * 
	 * @name ced_awl_plugin_deactivate
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_plugin_deactivate() {
		deactivate_plugins ( plugin_basename ( __FILE__ ) );
		$page_id = get_option ( CED_AWL_PREFIX . 'ced_activation_pageid' );
		wp_delete_post ( $page_id, true );
		add_action ( 'admin_notices', CED_AWL_PREFIX . 'plugin_error_notice' );
	}
}

add_action ( 'woocommerce_product_options_stock_fields', 'custom_field_for_out_of_stock' );
/**
 * Added field if manage stock is enabled
 * 
 * @name custom_field_for_out_of_stock
 */
function custom_field_for_out_of_stock() {
	woocommerce_wp_checkbox ( array (
			'id' => '_waitlist_button',
			'label' => __ ( 'Waitlist Button', 'woocommerce' ),
			'description' => __ ( 'Enable waitlist button at product level', 'woocommerce' ) 
	) );
}

/**
 * Saving field if manage stock is enabled
 * 
 * @name save_custom_field_for_out_of_stock
 * @param $post ID
 *        	of current post
 */
function save_custom_field_for_out_of_stock($post) {
	if (isset ( $_POST ['_waitlist_button'] )) {
		$manage_waitlist_button = 'yes';
		update_post_meta ( $post, '_waitlist_button', $manage_waitlist_button );
	} else {
		$manage_waitlist_button = 'no';
		update_post_meta ( $post, '_waitlist_button', $manage_waitlist_button );
	}
}
add_action ( 'save_post', 'save_custom_field_for_out_of_stock' );
?>