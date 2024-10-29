<?php

/**
 * Exit if accessed directly
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

/**
 * Calls the class on the post edit screen.
 * 
 * @name ced_awl_call_cedmetaClass
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_call_cedmetaClass() {
	new cedmetaClass ();
}

if (is_admin ()) {
	add_action ( 'load-post.php', CED_AWL_PREFIX . 'call_cedmetaClass' );
	add_action ( 'load-post-new.php', CED_AWL_PREFIX . 'call_cedmetaClass' );
}

add_action ( 'admin_print_scripts', 'disable_autosave' );

/**
 * Disabling the autosave script
 * 
 * @name ced_awl_call_cedmetaClass
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function disable_autosave() {
	global $post;
	if ($post && in_array ( get_post_type ( $post->ID ), wc_get_order_types ( 'order-meta-boxes' ) )) {
		wp_dequeue_script ( 'autosave' );
	}
}
/**
 * Creating Class for metabox.
 * 
 * @name cedmetaClass
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 *      
 */
class cedmetaClass {
	/**
	 * Hook into the appropriate actions when the class is constructed.
	 * 
	 * @name __construct
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	public function __construct() {
		add_action ( 'add_meta_boxes', array (
				$this,
				CED_AWL_PREFIX . 'add_meta_box' 
		) );
		add_action ( 'save_post', array (
				$this,
				CED_AWL_PREFIX . 'save' 
		) );
		add_action ( 'post_updated_messages', array (
				$this,
				'post_updated_messages' 
		), 11, 1 );
	}
	
	/**
	 * Adds the meta box container.
	 * 
	 * @name ced_awl_add_meta_box
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 */
	public function ced_awl_add_meta_box($post_type) {
		$post_types = array (
				'wait_list' 
		); // limit meta box to certain post types
		if (in_array ( $post_type, $post_types )) {
			add_meta_box ( 'ced_meta_box_name', __ ( 'Change Stock Status', 'ced-Waitlist' ), array (
					$this,
					CED_AWL_PREFIX . 'render_meta_box_content' 
			), $post_type, 'advanced', 'high' );
		}
	}
	
	/**
	 * Save the meta when the post is saved.
	 * 
	 * @name ced_awl_save
	 * @author CedCommerce<plugins@cedcommerce.com>
	 * @link http://cedcommerce.com/
	 * @param int $post_id
	 *        	The ID of the post being saved.
	 */
	public function ced_awl_save($post_id) {
		/**
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		
		// Check if our nonce is set.
		if (! isset ( $_POST [CED_AWL_PREFIX . 'ced_inner_custom_box_nonce'] ))
			return $post_id;
		
		$nonce = $_POST [CED_AWL_PREFIX . 'ced_inner_custom_box_nonce'];
		
		// Verify that the nonce is valid.
		if (! wp_verify_nonce ( $nonce, CED_AWL_PREFIX . 'ced_inner_custom_box' ))
			return $post_id;
			
			// If this is an autosave, our form has not been submitted,
			// so we don't want to do anything.
		if (defined ( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
			return $post_id;
			
			// Check the user's permissions.
		if ('page' == $_POST ['post_type']) {
			if (! current_user_can ( 'edit_page', $post_id ))
				return $post_id;
		} else {
			if (! current_user_can ( 'edit_post', $post_id ))
				return $post_id;
		}
		
		/* OK, its safe for us to save the data now. */
		
		// Sanitize the user input.
		$mydata = sanitize_text_field ( $_POST [CED_AWL_PREFIX . 'ced_new_field'] );
		
		$id = get_option ( CED_AWL_PREFIX . '' . $post_id );
		
		update_post_meta ( $post_id, CED_AWL_PREFIX . 'ced_waitlist_meta', $mydata );
		
		$user_email = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list' );
		
		$user_email_not = sanitize_text_field ( $_POST [CED_AWL_PREFIX . 'email_notification'] );
		
		$email_custum_mode = get_option ( CED_AWL_PREFIX . 'email_custum_mode' );
		
		$pageid = get_option ( CED_AWL_PREFIX . 'ced_activation_pageid' );
		
		$email_content = (get_option ( CED_AWL_PREFIX . 'email_custum_content' ));
		
		$link_text = get_option ( 'awl_link_text' );
		
		$link = get_home_url ();
		foreach ( $user_email as $key => $value ) {
			foreach ( $value as $k => $v ) {
				if ($email_custum_mode == "yes") {
					$auth_key = rand ( 10000, 10000000 );
					update_option ( $auth_key, $v );
					$link = add_query_arg ( 'ced_auth_key', $auth_key, $link );
					$name_email = get_user_by ( 'email', $v );
					$name = $name_email->user_nicename;
					if ($name_email->user_nicename == "") {
						$name = "Guest user";
					}
					$headers = array (
							'Content-Type: text/html; charset=UTF-8' 
					);
					$email_content = str_replace ( '{user_name}', $name, $email_content );
					$email_content = str_replace ( '{product_name}', get_the_title ( $post_id ), $email_content );
					$email_content = str_replace ( '{qty}', $mydata, $email_content );
					$email_content = str_replace ( '{link}', $link, $email_content );
					$email_content = str_replace ( '{link_text}', $link_text, $email_content );
					$message = stripslashes ( htmlspecialchars_decode ( $email_content ) );
				} else {
					$email_content = '<div class="content_email_div" >
	   								<p>' . __ ( 'The product which you have added in your waitlist is in stock now!!', 'advance-waitlist' ) . '</p>
									<table>
										<tr>
											
										</tr>
										<tr>
											<td><strong>' . __ ( 'Product Name', 'advance-waitlist' ) . ':</strong> </td><td>{product_name}</td>
										</tr>
										<tr>
											<td><strong>' . __ ( 'Status', 'advance-waitlist' ) . ':</strong> </td><td>In Stock</td>
										</tr>
										<tr>
											<td><strong>' . __ ( 'Quantity', 'advance-waitlist' ) . ':</strong> </td><td>{qty}</td>
										</tr>';
										if(!empty($name_email)){
					$email_content .='						
										<tr>
											<td><strong>' . __ ( 'Link to your waitlist', 'advance-waitlist' ) . ':</strong> </td><td><a href ="{link}">Wait List</a></td>
										</tr>';
									}

										
					$email_content .='	</table>
	   								<p>' . __ ( 'Thanks', 'advance-waitlist' ) . '</p>
	   								
								</div>';
					$name_email = get_user_by ( 'email', $v );
					
					$auth_key = rand ( 10000, 10000000 );
					
					update_option ( $auth_key, $v );
					
					$link = add_query_arg ( 'ced_auth_key', $auth_key, $link );
					
					$headers = array (
							'Content-Type: text/html; charset=UTF-8' 
					);
					
					$name_email = get_user_by ( 'email', $v );
					
					$name = $name_email->user_nicename;
					
					if ($name_email->user_nicename == "") {
						$name = "Guest user";
					}
					$headers = array (
							'Content-Type: text/html; charset=UTF-8' 
					);
					
					$email_content = str_replace ( '{user_name}', $name, $email_content );
					
					$email_content = str_replace ( '{product_name}', get_the_title ( $post_id ), $email_content );
					
					$email_content = str_replace ( '{qty}', $mydata, $email_content );

					if(!empty($name_email)) {
					$email_content = str_replace ( '{link}', $link, $email_content );
				   }
					
					$message = $email_content;
				}
				$message = $message;
				$r = wp_mail ( $v, 'Hurry !! PRODUCT ARRIVED', apply_filters ( "ced_email_notification", $message ), $headers );
			}
		}
		$specific = sanitize_text_field ( $_POST [CED_AWL_PREFIX . 'ced_specific_users'] );
		
		if ($user_email == 1) {
			$user_email_not == "yes";
		} else {
			$user_email_not == "no";
		}
		
		if ($email_custum_mode == 1) {
			$email_custum_mode == "yes";
		} else {
			$email_custum_mode == "no";
		}
		update_post_meta ( $id, CED_AWL_PREFIX . 'email_custum_content', $email_content );
		
		update_post_meta ( $id, CED_AWL_PREFIX . 'email_custum_mode', $email_custum_mode );
		
		update_post_meta ( $id, CED_AWL_PREFIX . 'user_email_notofication', $user_email_not );
		
		update_post_meta ( $id, '_manage_stock', 'yes' );
		
		update_post_meta ( $id, 'user_mode', $specific );
		
		wc_update_product_stock ( $id, $mydata );
		
		update_post_meta ( $id, 'reservation', 'reserved' );
		
		update_post_meta ( $id, '_stock_status', 'instock' );
		
		wc_update_product_stock_status ( $id, 'instock' );
		
		do_action ( 'woocommerce_product_set_stock_status', $id, 'instock' );
		
		update_post_meta ( $id, CED_AWL_PREFIX . 'email_custum_content', $email_content );
		
		update_post_meta ( $id, CED_AWL_PREFIX . 'email_custum_mode', $email_custum_mode );
		
		update_post_meta ( $id, CED_AWL_PREFIX . 'user_email_notofication', $user_email_not );
		
		update_post_meta ( $id, 'user_mode', 0 );
		
		ced_awl_ced_waitlist_unregister_user ( $user->ID, $product_id );
		
		update_post_meta ( $id, '_manage_stock', 'yes' );
		
		wc_update_product_stock ( $id, $mydata );
		
		wc_update_product_stock_status ( $id, 'instock' );
	}
	/**
	 * For getting the update notifications
	 * 
	 * @name post_updated_messages
	 * @param string $messages        	
	 * @return string
	 */
	function post_updated_messages($messages = "") {
		$messages ['post'] [1] = "Product Updated";
		return $messages;
	}
	
	/**
	 * Render Meta Box content.
	 * 
	 * @name ced_awl_render_meta_box_content
	 * @param string $messages        	
	 * @return string
	 * @param WP_Post $post
	 *        	The post object.
	 */
	public function ced_awl_render_meta_box_content($post) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field ( CED_AWL_PREFIX . 'ced_inner_custom_box', CED_AWL_PREFIX . 'ced_inner_custom_box_nonce' );
		
		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta ( $post->ID, CED_AWL_PREFIX . 'ced_waitlist_meta', true );
		
		// Display the form, using the current value.
		$id = get_option ( CED_AWL_PREFIX . '' . $post->ID );
		
		$product = get_post ( $id );
		$product = wc_get_product ( $id );
		
		$qty = get_post_meta ( $id, '_stock', true );
		
		$user_email = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list' );
		
		$count = count ( $user_email [0] );
		
		$emails = get_post_meta ( $id, CED_AWL_PREFIX . 'ced_wait_list' );
		
		$spec_usr = get_post_meta ( $id, 'user_mode', true );
		
		$email_custum_mode = get_post_meta ( $id, CED_AWL_PREFIX . 'email_custum_mode', true );
		
		$email_content = get_post_meta ( $id, CED_AWL_PREFIX . 'email_custum_content', true );
		
		$email_chckd = "";
		if ($email_custum_mode == 1) {
			$email_chckd = "checked";
		}
		
		$chkd = "";
		
		if ($spec_usr == 1) {
			$chkd = "checked";
		}
		
		$chkde = "";
		
		$email_not = get_post_meta ( $id, CED_AWL_PREFIX . 'user_email_notofication', true );
		
		if ($email_not == 1) {
			$chkde = "checked";
		}
		
		echo '<div class="fst_col">
				<div id="error_div">
					<p class="ced_metabox_one" >Please fill a valid input in quantity field</p>
				</div>
				<div class="fst_col1">
						<div class="fst_col11">';
		
		echo '<label for="ced_awl_ced_new_field"><h3>';
		_e ( 'Set quantity for the product', 'ced-Waitlist' );
		echo '</h3></label> 
				</div>
					<div class="fst_col12">';
		echo '<input type="text" value = "' . round ( $qty ) . '" id="metakeyselect"  name="ced_awl_ced_new_field"  placeholder="Ex:100">';
		echo '</div></div>';
		echo '<div class="fst_col2">
				<div class="fst_col21">';
		echo '<label class="ced_metabox_common" for = "ceds_count" >
						<h3>' . __ ( 'Number of quantity requested', 'advance-waitlist' ) . '</h3>
					  </label>
				</div>';
		
		echo '<div class="fst_col22">
				<span class="ced_metabox_common" id="ceds_count" >' . $count . '</span>
			</div>
			</div>
			</div><div class="clear"></div>';
		
		echo '<div class="common_awl_class" ><h3>' . __ ( 'Emails of registered users', 'advance-waitlist' ) . '</h3>';
		
		if (isset ( $emails )) {
			foreach ( $emails as $k => $v ) {
				foreach ( $v as $k1 => $v1 ) {
					echo '<li class="ced_show_users_metabox" style="list-style: inside none disc;"><span class="description">'.$v1.'</span></li>';
				}
			}
		}
		echo '</div>';
		
		echo '<div class="common_awl_class"><h3>User name</h3>';
		if (isset ( $emails )) {
			foreach ( $emails as $k => $v ) {
				foreach ( $v as $k1 => $v1 ) {
					$name = get_user_by ( 'email', $v1 );
					
					if ($name != "") {
						echo '<li class="ced_show_users_metabox" style="list-style: inside none disc;"><span class="description">'.$name->data->display_name.'</span></li>';
					} else {
						echo '<li class="ced_show_users_metabox" style="list-style: inside none disc;"><span class="description">Guest User</span></li>';
					}
				}
			}
		}
		echo '</div>';
	}
}
?>