<?php
/**
 * General Functions
 *
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
} // Exit if accessed directly

if (! function_exists ( CED_AWL_PREFIX . 'get_ced_waitlist' )) {
	/**
	 * Get waitlist for product id
	 * 
	 * @name ced_awl_get_ced_waitlist
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_get_ced_waitlist($id) {
		$id = intval ( $id );
		return get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list', true );
	}
}

if (! function_exists ( CED_AWL_PREFIX . 'ced_waitlist_save' )) {
	/**
	 * Save waitlist for product id
	 * 
	 * @name ced_awl_ced_waitlist_save
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_ced_waitlist_save($id, $waitlist) {
		$id = intval ( $id );
		update_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list', $waitlist );
	}
}

if (! function_exists ( CED_AWL_PREFIX . 'ced_waitlist_user_is_register' )) {
	/**
	 * Check if user is already register for a waitlist
	 * 
	 * @name ced_awl_ced_waitlist_user_is_register
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_ced_waitlist_user_is_register($user, $waitlist) {
		return in_array ( $user, $waitlist );
	}
}

if (! function_exists ( CED_AWL_PREFIX . 'ced_waitlist_register_user' )) {
	/**
	 * Register user to waitlist
	 * 
	 * @name ced_awl_ced_waitlist_register_user
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_ced_waitlist_register_user($user, $id) {
				
		$waitlist = ced_awl_get_ced_waitlist( $id );

		if ( ! is_email( $user ) || ( is_array( $waitlist ) && ced_awl_ced_waitlist_user_is_register( $user, $waitlist ) ) )
			return false;

		if( empty($waitlist) )
			$waitlist = array();

		$waitlist[] = $user;
		
		update_post_meta( $id, CED_AWL_PREFIX.'ced_wait_list', $waitlist );
		$post = get_post($id);
		
		$post_waitlist = get_posts( array('post_type' => 'wait_list') );
		$wait_list_id = get_post_meta($id, CED_AWL_PREFIX.'ced_waitlist_id',true);
		
		
		if(!empty($wait_list_id))
		{
			$wait_list_title = get_post($wait_list_id);

			 if( isset($wait_list_title->post_title) && $wait_list_title->post_title == $post->post_title)
			 {
				return false;
			}  
		}  
		$post = array(
				'post_title'    => $post->post_title,
				'post_type'    => 'Wait_list',
				'post_content'  => 'This is my Waitlist.',
				'post_status'   => 'publish',
				'post_author'   => 1
				);
		
		// Insert the post into the database
		$pid=wp_insert_post( $post );
		// print_r($pid);die;
		
		update_option(CED_AWL_PREFIX.''.$pid, $id);
		update_post_meta($id, CED_AWL_PREFIX.'ced_waitlist_id', $pid);
		
		return true; 
	}
}

if (! function_exists ( CED_AWL_PREFIX . 'ced_waitlist_unregister_user' )) {
	/**
	 * Unregister user from waitlist
	 * 
	 * @name ced_awl_ced_waitlist_unregister_user
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_ced_waitlist_unregister_user($user, $id) {
		$waitlist = ced_awl_get_ced_waitlist ( $id );
		if (is_array ( $waitlist ) && ced_awl_ced_waitlist_user_is_register ( $user, $waitlist )) {
			$waitlist = array_diff ( $waitlist, array (
					$user 
			) );
			// save it
			if (count ( $waitlist ) == 0) {
				$waitlist_id = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_waitlist_id', true );
				wp_delete_post ( $waitlist_id, true );
			}
			ced_awl_ced_waitlist_save ( $id, $waitlist );
			return true;
		}
		return false;
	}
}

if (! function_exists ( CED_AWL_PREFIX . 'ced_waitlist_get_registered_users' )) {
	/**
	 * Get registered users for product waitlist
	 * 
	 * @name ced_awl_ced_waitlist_get_registered_users
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	function ced_awl_ced_waitlist_get_registered_users($id) {
		$waitlist = ced_awl_get_ced_waitlist ( $id );
		$users = array ();
		
		if (is_array ( $waitlist )) {
			foreach ( $waitlist as $key => $email ) {
				$users [] = $email;
			}
		}
		
		return $users;
	}
}

if (! function_exists ( CED_AWL_PREFIX . 'ced_waitlist_empty' )) {
	/**
	 * Empty waitlist by product id
	 * 
	 * @name ced_awl_ced_waitlist_empty
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 *      
	 */
	function ced_awl_ced_waitlist_empty($id) {
		update_post_meta ( $id, '', array () );
	}
}
/**
 * get values of post meta through key
 * 
 * @name get_meta_values
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 * @param string $key        	
 * @param string $type        	
 * @param string $status        	
 *
 */
function get_meta_values($key = '', $type = 'product', $status = 'publish') {
	global $wpdb;
	if (empty ( $key ))
		return;
	
	$r = $wpdb->get_col ( $wpdb->prepare ( "
			SELECT pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s'
			AND p.post_status = '%s'
			AND p.post_type = '%s'
			", $key, $status, $type ) );
	
	return $r;
}

/**
 * get values of post meta by key
 * 
 * @name get_meta_post_id
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 * @param string $key        	
 * @param string $type        	
 * @param string $status        	
 *
 */
function get_meta_post_id($key = '', $type = 'product', $status = 'publish') {
	global $wpdb;
	
	if (empty ( $key ))
		return;
	
	$r = $wpdb->get_col ( $wpdb->prepare ( "
			SELECT pm.post_id FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s'
			AND p.post_status = '%s'
			AND p.post_type = '%s'
			", $key, $status, $type ) );
	
	return $r;
}

add_shortcode ( 'wish_list', CED_AWL_PREFIX . 'wishlist' );

/**
 * creating shorcode for My wishlist
 * 
 * @name ced_awl_wishlist
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 *      
 *      
 */
function ced_awl_wishlist() {
	$user = wp_get_current_user ();
	
	global $product_id;
	
	$wtlist = get_meta_values ( CED_AWL_PREFIX . 'ced_wait_list' );
	
	$product_id = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
	
	$count = 1;	
	$html_tr = "";
	
	foreach ( $product_id as $k => $v ) {
		$email = get_post_meta ( $v, CED_AWL_PREFIX . 'ced_wait_list' );
		foreach ( $email as $k1 => $v1 ) {
			foreach ( $v1 as $k2 => $v2 ) {
				$pid = $v;
				$product = wc_get_product ( $v );
				if ($product->is_in_stock ()) {
					global $post;
					$url = get_permalink ( $post->ID );
					$link = add_query_arg ( 'p_id', $v, $url );
					$link = add_query_arg ( CED_AWL_PREFIX . 'ced_add_to_cart', 'ced_remove', $link );
					$html_td = '<td class="order-number ced_awl_ced_add_to_cart" data-title="Order">
							<a href="' . $link . '">Add to cart</a>
							</td>';
				} else {
					$html_td = '<td class="order-number" data-title="Order">
								<a href="' . get_permalink ( $v ) . '">View</a>
							</td>';
				}
				
				if ($v2 == $user->user_email) {
					$html_tr .= '<tr class="order">
							<td class="order-number" data-title="Order">' . get_the_title ( $v ) . '</td>
							<td class="order-number" data-title="Order">' . $v2 . '</td>' . $html_td . '</tr>';
					$count ++;
				}

				else if (!$user->exists()){			

					$html_tr .= '<tr class="order">
							<td class="order-number" data-title="Order">' . get_the_title ( $v ) . '</td>
							<td class="order-number" data-title="Order">' . $v2 . '</td>' . $html_td . '</tr>';
					$count ++;
				}

			}
		}
	}
	if ($count >=1) {
		$html = '<main id="main" class="site-main" role="main">
					<article id="post-7" class="post-7 page type-page status-publish hentry">

						<div class="woocommerce">
							<table class="shop_table shop_table_responsive my_account_orders">
								<thead>
									<tr>
										<th>' . __ ( 'Product Name', 'advance-waitlist' ) . '
										</th>
										<th>' . __ ( 'User Email', 'advance-waitlist' ) . '
										</th>
										<th>' . __ ( 'Action', 'advance-waitlist' ) . '
										</th>
									</tr>
								</thead>
								<tbody>' . $html_tr . '</tbody>
							</table>
						</div>
					</article>
				</main>';
		return $html;
	} else {
		$html = '<h2>No Products In WaitList</h2>';
		return $html;
	}
}

/**
 * getting stock status through product id
 * 
 * @name get_stock_status
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 * @param
 *        	$productid
 * @return mixed
 */
function get_stock_status($productid) {
	return get_post_meta ( $productid, '_stock_status', true );
}

add_action ( 'woocommerce_after_shop_loop_item', 'my_woocommerce_template_loop_add_to_cart_remove', 9 );

/**
 * removing add to cart button
 * 
 * @name my_woocommerce_template_loop_add_to_cart_remove
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function my_woocommerce_template_loop_add_to_cart_remove() {
	add_action ( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	$id = get_the_ID ();
	if (get_post_meta ( $id, 'user_mode', true ) == 1) {
		$user = wp_get_current_user ();
		$postids = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
		
		if (in_array ( $id, $postids )) {
			$emails = ced_awl_get_ced_waitlist ( $id );
			if (in_array ( $user->user_email, $emails )) {
				$reserved = get_post_meta ( $id, 'reservation', true );
				if ($reserved == "reserved") {
					return;
				}
			} else {
				remove_action ( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			}
		} else {
			return;
		}
	} else {
		return;
	}
}

add_action ( 'woocommerce_after_shop_loop_item', CED_AWL_PREFIX . 'woocommerce_template_loop_add_to_cart', 10 );

/**
 * using our own buttons
 * 
 * @name ced_awl_woocommerce_template_loop_add_to_cart
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 * @param int $id        	
 */
function ced_awl_woocommerce_template_loop_add_to_cart($id) {
	$id = get_the_ID ();
	
	if (get_post_meta ( $id, 'user_mode', true ) == 1) {
		$user = wp_get_current_user ();
		$postids = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
		
		if (in_array ( $id, $postids )) {
			$emails = ced_awl_get_ced_waitlist ( $id );
			if (in_array ( $user->user_email, $emails )) {
				$reserved = get_post_meta ( $id, 'reservation', true );
				if ($reserved == "reserved") {
					return;
				}
			} else {
				echo '<a class="button product_type_simple ajax_add_to_cart" data-product_sku="" data-product_id="' . $id . '" data-quantity="1" href="' . get_permalink () . '" rel="nofollow">Read More</a>';
			}
		} else {
			return;
		}
	} else {
		return;
	}
}
/**
 * removing loop buttons
 * 
 * @name ced_awl_remove_loop_button
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_remove_loop_button() {
	$id = get_the_ID ();
	if (get_post_meta ( $id, 'user_mode', true ) == 1) {
		$user = wp_get_current_user ();
		$postids = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
		
		if (in_array ( $id, $postids )) {
			$emails = ced_awl_get_ced_waitlist ( $id );
			if (in_array ( $user->user_email, $emails )) {
				$reserved = get_post_meta ( $id, 'reservation', true );
				if ($reserved == "reserved") {
					return;
				}
			} else {
				$count = count ( $emails );
				$qty = get_post_meta ( $id, '_stock', true );
				if ($qty > $count) {
					return;
				}
				remove_action ( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			}
		} else {
			return;
		}
	}
}
add_action ( 'woocommerce_single_product_summary', CED_AWL_PREFIX . 'remove_loop_button', 29 );

/**
 * changing loop button
 * 
 * @name ced_awl_change_loop_button
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_change_loop_button() {
	$id = get_the_ID ();
	$status = get_stock_status ( $id );
	if ($status == "instock") {
		if (get_post_meta ( $id, 'user_mode', true ) == 1) {
			$user = wp_get_current_user ();
			$postids = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
			if (in_array ( $id, $postids )) {
				$emails = ced_awl_get_ced_waitlist ( $id );
				if (in_array ( $user->user_email, $emails )) {
					$reserved = get_post_meta ( $id, 'reservation', true );
					if ($reserved == "reserved") {
						return;
					}
				} else {
					$count = count ( $emails );
					$qty = get_post_meta ( $id, '_stock', true );
					$diff = $qty - $count;
					if ($qty > $count) {
						echo '</br><p style="margin-top:10px;">Only ' . $diff . ' Item(s)left in stock </p>';
						return;
					}
					echo '</br><p style="margin-top:10px;">' . _e ( 'This product is reserved for specific users', 'advance-waitlist' ) . '</p>';
				}
			} else {
				return;
			}
		}
	}
}
add_action ( 'woocommerce_single_product_summary', CED_AWL_PREFIX . 'change_loop_button', 30 );

/**
 * custum add to cart management
 * 
 * @name ced_awl_ced_add_to_cart_management
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 * @throws Exception
 */
function ced_awl_ced_add_to_cart_management($passed, $id) {
	global $woocommerce;
	
	// Get 'product_id' and 'quantity' for the current woocommerce_add_to_cart operation
	if (isset ( $_REQUEST ['add-to-cart'] )) {
		$prodId = $_REQUEST ['add-to-cart'];
	} else {
		$prodId = ( int ) $_POST ["add-to-cart"];
	}
	if (isset ( $_POST ["quantity"] )) {
		$prodQty = ( int ) $_POST ["quantity"];
	} else {
		$prodQty = 1;
	}
	foreach ( WC ()->cart->get_cart () as $k => $v ) {
		if ($v ['product_id'] == $prodId) {
			$quant_in_cart = $v ['quantity'];
		}
	}
	
	if (get_post_meta ( $prodId, 'user_mode', true ) == 1) {
		$user = wp_get_current_user ();
		
		$postids = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
		$emails = ced_awl_get_ced_waitlist ( $prodId );
		if (in_array ( $prodId, $postids )) {
			if (in_array ( $user->user_email, $emails )) {
				$reserved = get_post_meta ( $prodId, 'reservation', true );
				
				if ($reserved == "reserved") {
					return $passed;
					;
				}
			} else {
				$count = count ( $emails );
				$qty = get_post_meta ( $prodId, '_stock', true );
				$diff = $qty - $count;
				$total = $quant_in_cart + $prodQty;
				if ($prodQty > $diff) {
					$passed = false;
					wc_add_notice ( sprintf ( __ ( 'You cannot add that amount to the cart because there is not enough stock(%s remaining) .', 'ced-Waitlist' ), $diff ), 'error' );
					return $passed;
				}
				if ($total > $diff) {
					$passed = false;
					wc_add_notice ( sprintf ( __ ( 'You cannot add that amount to the cart because you have already(%s) in cart .', 'ced-Waitlist' ), $quant_in_cart ), 'error' );
					return $passed;
				} 

				else {
					return $passed;
				}
			}
		} else {
			$count = count ( $emails );
			$qty = get_post_meta ( $prodId, '_stock', true );
			$diff = $qty - $count;
			
			if ($prodQty > $diff) {
				$passed = false;
				wc_add_notice ( sprintf ( __ ( 'You cannot add that amount to the cart because there is not enough stock(%s remaining) .', 'ced-Waitlist' ), $diff ), 'error' );
				return $passed;
			} 

			else {
				return $passed;
			}
		}
	} else {
		return $passed;
	}
}
add_action ( 'woocommerce_add_to_cart_validation', CED_AWL_PREFIX . 'ced_add_to_cart_management', 10, 2 );

add_action ( 'woocommerce_add_to_cart', CED_AWL_PREFIX . 'ced_meta_delete', 0 );

/**
 * deleting meta data
 * 
 * @name ced_awl_ced_meta_delete
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_ced_meta_delete() {
	if (isset ( $_REQUEST ['add-to-cart'] )) {
		$id = $_REQUEST ['add-to-cart'];
		$prodQty = 1;
	} else {
		$id = ( int ) $_POST ["add-to-cart"];
	}
	$postids = get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' );
	$user = wp_get_current_user ();
	if (in_array ( $id, $postids )) {
		$emails = ced_awl_get_ced_waitlist ( $id );
		if (in_array ( $user->user_email, $emails )) {
			ced_awl_ced_waitlist_unregister_user ( $user->user_email, $id );
			$products = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list' );
			$count = count ( $products [0] );
			if ($count == '0') {
				$waitlist_id = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_waitlist_id', true );
				wp_delete_post ( $waitlist_id, true );
			}
			ced_awl_ced_waitlist_unregister_user ( $user->user_email, $id );
		}
	}
}
/**
 * custom add to cart for waitlist product
 * 
 * @name ced_awl_ced_add_to_cart
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_ced_add_to_cart() {
	$user = wp_get_current_user ();
	if (! (isset ( $_REQUEST [CED_AWL_PREFIX . 'ced_add_to_cart'] ))) {
		return;
	}
	$action = $_REQUEST [CED_AWL_PREFIX . 'ced_add_to_cart'];
	if ($action == 'ced_remove') {
		ced_awl_ced_waitlist_unregister_user ( $user->user_email, $_REQUEST ['p_id'] );
		$products = get_post_meta ( $_REQUEST ['p_id'], CED_AWL_PREFIX . 'ced_wait_list' );
		$count = count ( $products [0] );
		
		if ($count == 0) {
			$waitlist_id = get_post_meta ( $_REQUEST ['p_id'], CED_AWL_PREFIX . 'ced_waitlist_id', true );
			wp_delete_post ( $waitlist_id, true );
		}
		ced_awl_ced_waitlist_unregister_user ( $user->user_email, $_REQUEST ['p_id'] );
		
		$url = get_permalink ( woocommerce_get_page_id ( 'cart' ) );
		$dest_url = add_query_arg ( 'add-to-cart', $_REQUEST ['p_id'], $url );
		
		wp_redirect ( apply_filters ( "ced_after_add_to_cart_redirect", $dest_url ) );
	}
}
add_action ( 'wp', CED_AWL_PREFIX . 'ced_add_to_cart' );

/**
 * disabling create new post for waitlists
 * 
 * @name ced_awl_disable_new_posts
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_disable_new_posts() {
	// Hide sidebar link
	global $submenu;
	unset ( $submenu ['edit.php?post_type=wait_list'] [10] );
	
	// Hide link on listing page
	if (isset ( $_GET ['post_type'] ) && $_GET ['post_type'] == 'wait_list') {
		wp_enqueue_style ( "conditional1", CED_AWL_DIR_URL . 'assets/css/conditional1.css', array(), CED_AWL_VERSION, 'all' );
	}
}
add_action ( 'admin_menu', CED_AWL_PREFIX . 'disable_new_posts' );

/**
 * hiding unnecessary content from publish metabox
 * 
 * @name ced_awl_hide_publishing_actions
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_hide_publishing_actions() {
	$my_post_type = 'wait_list';
	$plugin_dir = CED_AWL_DIR_URL;
	global $post;
	if ($post->post_type == $my_post_type) {
		wp_enqueue_style ( "conditional", $plugin_dir . 'assets/css/conditional.css' );
	}
}
add_action ( 'admin_head-post.php', CED_AWL_PREFIX . 'hide_publishing_actions' );
add_action ( 'admin_head-post-new.php', CED_AWL_PREFIX . 'hide_publishing_actions' );

/**
 * changing position of the save meta box
 * 
 * @name ced_awl_position_upadte_button
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_position_upadte_button() {
	$my_post_type = 'wait_list';
	$plugin_dir = CED_AWL_DIR_URL;
	global $post;
	if ($post->post_type == $my_post_type) {
		wp_enqueue_style ( "conditional", $plugin_dir . 'assets/css/conditional.css' );
	}
}
add_action ( 'admin_head-post.php', CED_AWL_PREFIX . 'position_upadte_button' );

/**
 * changing the text of the publish metabox heading
 */
add_filter ( 'gettext', CED_AWL_PREFIX . 'change_publish_button', 10, 2 );

/**
 * For svaing the change and getting the response
 * 
 * @name ced_awl_change_publish_button
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_change_publish_button($translation, $text) {
	if ('wait_list' == get_post_type ())
		if ($text == 'Update')
			return 'Save';
	
	return $translation;
}

add_filter ( 'manage_wait_list_posts_columns', CED_AWL_PREFIX . 'set_custom_edit_waitlist_columns' );

/**
 * Waitlist users table
 * 
 * @name ced_awl_change_publish_button
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_set_custom_edit_waitlist_columns($defaults) {
	$defaults ['wait_list_request'] = '<a href="javascript:;" ><span>' . __ ( 'Total WaitList Request', 'advance-waitlist' ) . '</span></a>';
	$defaults ['total_available'] = '<a href="javascript:;"><span>' . __ ( 'Available Stock Quantity', 'advance-waitlist' ) . '</span></a>';
	$defaults ['Registered_users'] = '<a href="javascript:;" ><span>' . __ ( 'Registered users', 'advance-waitlist' ) . '</span></a>';
	$defaults ['date'] = __ ( "Waitlisted", "ced-Waitlist" );
	$defaults ['title'] = __ ( "Product", "ced-Waitlist" );

	return $defaults;
}

add_action ( 'manage_posts_custom_column', CED_AWL_PREFIX . 'waitlist_columns_content', 10, 2 );

/**
 * Putting the content Waitlist users table
 * 
 * @name ced_awl_waitlist_columns_content
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_waitlist_columns_content($column_name, $post_ID) {
	$my_post_type = 'wait_list';
	global $post;
	
	if ($post->post_type == $my_post_type) {
		if ($column_name == 'wait_list_request') {
			$id = get_option ( CED_AWL_PREFIX . '' . $post_ID );
			$user_email = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list' );
			$count = count ( $user_email [0] );
			echo round ( $count );
		}
	}
	
	if ($post->post_type == $my_post_type) {
		if ($column_name == 'total_available') {
			$id = get_option ( CED_AWL_PREFIX . '' . $post_ID );
			$product = get_post ( $id );
			$qty = get_post_meta ( $id, '_stock', true );
			echo round ( $qty );
		}
	}
	
	if ($post->post_type == $my_post_type) {
		if ($column_name == 'Registered_users') {
			$id = get_option ( CED_AWL_PREFIX . '' . $post_ID );
			$emails = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list' );
			foreach ( $emails as $k => $v ) {
				foreach ( $v as $k1 => $v1 ) {
					$name = get_user_by ( 'email', $v1 );
					if ($name != "") {
						echo '<br><span class="description">' . $name->data->display_name . '</span>';
					} else {
						echo '<br><span class="description">Guest User</span>';
					}
				}
			}
		}
	}
}

/**
 * Hiding unnecessary contents
 * 
 * @name ced_awl_hide_add_new
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_hide_add_new() {
	$my_post_type = 'wait_list';
	$plugin_dir = CED_AWL_DIR_URL;
	global $post;
	if ($post->post_type == $my_post_type) {
		wp_enqueue_style ( "conditional", $plugin_dir . 'assets/css/conditional.css' );
	}
}
add_action ( 'admin_head-post.php', CED_AWL_PREFIX . 'hide_add_new' );

add_action ( 'admin_menu', CED_AWL_PREFIX . 'add_user_menu_bubble' );

/**
 * waitlist notification
 * 
 * @name ced_awl_add_user_menu_bubble
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_add_user_menu_bubble() {
	global $menu;
	$user_count = count_users (); // get whatever count you need
	
	$custom_post_count = wp_count_posts ( 'wait_list' );
	if ($user_count) {
		foreach ( $menu as $key => $value ) {
			if ($menu [$key] [2] == 'edit.php?post_type=wait_list') {
				$menu [$key] [0] .= '<span class="update-plugins count-1">
								<span class="plugin-count">' . $custom_post_count->publish . '</span>
								</span>';
				return;
			}
		}
	}
}

add_filter ( 'wp_get_nav_menu_items', CED_AWL_PREFIX . 'ced_exclude_menu_items', null, 3 );

/**
 * Excluding menu items
 * 
 * @name ced_awl_add_user_menu_bubble
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_ced_exclude_menu_items($items, $menu, $args) {
	$user = wp_get_current_user ();
	if (! $user->exists ()) {
		foreach ( $items as $k => $v ) {
			if ($v->title == "Wait list") {
				unset ( $items [$k] );
			}
		}
	}
	return $items;
}

/**
 * Filtering the columns and unsetting the date
 * 
 * @name ced_awl_columns_filter
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_columns_filter($columns) {
	unset ( $columns ['date'] );
	return $columns;
}

// Filter pages
add_filter ( 'manage_edit-page_columns', CED_AWL_PREFIX . 'columns_filter', 10, 1 );

add_action ( 'wp_dashboard_setup', CED_AWL_PREFIX . 'add_custom_dashboard_activity' );
/**
 * register your custom activity widget
 * 
 * @name ced_awl_add_custom_dashboard_activity
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_add_custom_dashboard_activity() {
	wp_add_dashboard_widget ( 'custom_dashboard_activity', 'WaitList', CED_AWL_PREFIX . 'custom_wp_dashboard_site_activity' );
}

/**
 * the new function based on wp_dashboard_recent_posts (in wp-admin/includes/dashboard.php)
 * 
 * @name ced_awl_wp_dashboard_recent_post_types
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_wp_dashboard_recent_post_types($args) {
	/* Chenged from here */
	if (! $args ['post_type']) {
		$args ['post_type'] = 'any';
	}
	$query_args = array (
			'post_type' => $args ['post_type'],
			'post_status' => $args ['status'],
			'orderby' => 'date',
			'order' => $args ['order'],
			'posts_per_page' => intval ( $args ['max'] ),
			'no_found_rows' => true,
			'cache_results' => false 
	);
	
	$posts = new WP_Query ( $query_args );
	if ($posts->have_posts ()) {
		echo '<div id="' . $args ['id'] . '" class="activity-block">';
		if ($posts->post_count > $args ['display']) {
			echo '<small class="show-more hide-if-no-js"><a href="#">' . sprintf ( __ ( 'See %s more…', 'advance-waitlist' ), $posts->post_count - intval ( $args ['display'] ) ) . '</a></small>';
		}
		echo '<h4>' . $args ['title'] . '</h4>';
		echo '<ul>';
		$i = 0;
		$today = date ( 'Y-m-d', current_time ( 'timestamp' ) );
		$tomorrow = date ( 'Y-m-d', strtotime ( '+1 day', current_time ( 'timestamp' ) ) );
		while ( $posts->have_posts () ) {
			$posts->the_post ();
			$id = get_the_ID ();
			$id = get_option ( "ced_awl_" . $id );
			$user_email = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list' );
			$count = count ( $user_email [0] );
			$time = get_the_time ( 'U' );
			if (date ( 'Y-m-d', $time ) == $today) {
				$relative = __ ( 'Today' );
			} elseif (date ( 'Y-m-d', $time ) == $tomorrow) {
				$relative = __ ( 'Tomorrow' );
			} else {
				/* translators: date and time format for recent posts on the dashboard, see http://php.net/date */
				$relative = date_i18n ( __ ( 'M jS' ), $time );
			}
			$text = sprintf ( __ ( '<span>%1$s, %2$s</span> <a href="%3$s">%4$s</a><span style="margin-left :76px">%5$s waitlist request</span>' ), $relative, get_the_time (), get_edit_post_link (), _draft_or_post_title (), $count );
			
			$hidden = $i >= $args ['display'] ? ' class="hidden"' : '';
			echo "<li{$hidden}>$text</li>";
			$i ++;
		}
		echo '</ul>';
		echo '</div>';
	} else {
		return false;
	}
	wp_reset_postdata ();
	return true;
}

/**
 * The replacement widget.
 * 
 * @name ced_awl_custom_wp_dashboard_site_activity
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_custom_wp_dashboard_site_activity() {
	echo '<div id="activity-widget">';
	$recent_posts = ced_awl_wp_dashboard_recent_post_types ( array (
			'post_type' => 'wait_list',
			'display' => 3,
			'max' => 7,
			'status' => 'publish',
			'order' => 'DESC',
			'title' => __ ( 'Recently waitlisted Products' ),
			'id' => 'published-posts' 
	) );
	
	if (! $recent_posts) {
		echo '<div class="no-activity">';
		echo '<p class="smiley"></p>';
		echo '<p>' . __ ( 'No Waitlist yet!' ) . '</p>';
		echo '</div>';
	}
	echo '</div>';
}

add_filter ( 'woocommerce_login_redirect', CED_AWL_PREFIX . 'wcs_login_redirect' );

/**
 * For redirecting to the login.
 * 
 * @name ced_awl_wcs_login_redirect
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_wcs_login_redirect($redirect) {
	if (isset ( $_REQUEST ['ced_waitlist_login'] ) && $_REQUEST ['ced_waitlist_login'] != "") {
		$redirect_back = get_permalink ( $_REQUEST ['ced_waitlist_login'] );
		return $redirect_back;
	} else {
		return $redirect;
	}
}

/**
 * For hide/show the button for adding to waitlist or rtemove from waitlist
 * 
 * @name ced_awl_wpc_remove_row_actions
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_wpc_remove_row_actions($actions) {
	if (get_post_type () === 'wait_list') // choose the post type where you want to hide the button
		unset ( $actions ['view'] ); // this hides the VIEW button on your edit post screen
		unset ( $actions ['inline hide-if-no-js'] );
	return $actions;
}
add_filter ( 'post_row_actions', CED_AWL_PREFIX . 'wpc_remove_row_actions', 10, 1 );

/**
 * Filter the views
 * 
 * @name remove_views
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function remove_views($views) {
	unset ( $views ['all'] );
	unset ( $views ['publish'] );
	unset ( $views ['trash'] );
	return $views;
}

add_action ( 'views_edit-wait_list', 'remove_views' );

// add_filter ( 'post_row_actions', 'remove_row_actions_waitlist', 10, 2 );

/**
 * Unsetting the screen
 * 
 * @name remove_row_actions_waitlist
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function remove_row_actions_waitlist($actions, $post) {
	global $current_screen;
	if ($current_screen->post_type != 'wait_list')
		return $actions;
	unset ( $actions ['edit'] );
	unset ( $actions ['view'] );
	unset ( $actions ['trash'] );
	unset ( $actions ['inline hide-if-no-js'] );
	return $actions;
}

 // add_action( 'wp_loaded', 'wpse_53371_remove_bulk_actions' );

/**
 * Adding filters
 * 
 * @name wpse_53371_remove_bulk_actions
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */

 //  function wpse_53371_remove_bulk_actions()
 //  {
 //  add_filter( 'bulk_actions-edit-wait_list', '__return_empty_array' );
 //  add_filter( 'bulk_actions-upload', '__return_empty_array' );
 // }
 
 add_filter('months_dropdown_results', '__return_empty_array');
 
add_filter ( 'woocommerce_cart_item_quantity', 'awl_cart_update', 10, 3 );

/**
 * Unsetting the screen
 * 
 * @name awl_cart_update
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function awl_cart_update($product_quantity, $cart_item_key, $cart_item) {
	$prodId = $cart_item ['product_id'];
	if (get_post_meta ( $prodId, 'user_mode', true ) == 1) {
		if (in_array ( $prodId, get_meta_post_id ( CED_AWL_PREFIX . 'ced_wait_list' ) )) {
			$emails = ced_awl_get_ced_waitlist ( $prodId );
			$user = wp_get_current_user ();
			if (! in_array ( $user->user_email, $emails )) {
				$count = count ( $emails );
				$qty = get_post_meta ( $prodId, '_stock', true );
				$diff = $qty - $count;
				$product_quantity = woocommerce_quantity_input ( array (
						'input_name' => "cart[{$cart_item_key}][qty]",
						'input_value' => $cart_item ['quantity'],
						'max_value' => $diff,
						'min_value' => '0' 
				), $_product, false );
			}
		}
	}
	return $product_quantity;
}
function wp_hide_minor_publishing_waitlist() {
	$screen = get_current_screen ();
	if ($screen->id == 'wait_list') { // PRINT_R($screen);DIE('POP');
		echo '<style>#minor-publishing { display: none; }</style>';
	} else {
		return;
	}
}

// Hook to admin_head for the CSS to be applied earlier
add_action ( 'admin_head', 'wp_hide_minor_publishing_waitlist' );

?>