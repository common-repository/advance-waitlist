<?php
/**
 * Frontend class
 *
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
} // Exit if accessed directly

if (! class_exists ( CED_AWL_PREFIX . 'ced_plugin_Frontend' )) {
	/**
	 * Frontend class.
	 * The class manage all the Frontend behaviors.
	 */
	class ced_awl_ced_plugin_Frontend {
		protected static $instance;
		protected $current_product = false;
		
		/**
		 * The function is called statically and checks whether the static class has the variable $_instance set.
		 * 
		 * @name get_instance
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		public static function get_instance() {
			if (is_null ( self::$instance )) {
				self::$instance = new self ();
			}
			return self::$instance;
		}
		
		/**
		 * Hook into the appropriate actions when the class is constructed.
		 * 
		 * @name __construct
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		public function __construct() {
			$this->init ();
			add_action ( 'woocommerce_before_single_product', array (	$this, CED_AWL_PREFIX . 'add_form' 	) );
			add_action ( 'template_redirect', array ($this, CED_AWL_PREFIX . 'ced_waiting_submit' ), 100 );
			$this->ced_awl_ced_show_notice ();
			
			add_filter( 'woocommerce_available_variation', array($this,'form_to_out_of_stock_product_variations'), 10, 3 );

		}

		function form_to_out_of_stock_product_variations( $data, $product, $variation ) {
			/*print_r($data);
			die();*/
			// echo  $data['is_in_stock'];
    if(empty($data['is_in_stock'] )) {   	
        $data['availability_html'] .= $this->ced_awl_the_form ( $variation , 'gfx' );
    }
    // print_r($data);
    return $data;
}
		/**
		 * showing notice on product detail page
		 * 
		 * @name ced_awl_ced_show_notice
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		function ced_awl_ced_show_notice() {
			if (isset ( $_REQUEST ['added'] )) {
				if ($_REQUEST ['added'] == 'true') {
					$msg = __ ( get_option ( CED_AWL_PREFIX . 'ced_successfull_registration' ), 'advance-waitlist' );
					$msg_type = 'success';
					wc_add_notice ( $msg, $msg_type );
				}
				if ($_REQUEST ['added'] == 'false') {
					$msg = __ ( 'The email is alredy registered for the Waitlist', 'advance-waitlist' );
					$msg_type = 'error';
					wc_add_notice ( $msg, $msg_type );
				}
				if ($_REQUEST ['added'] == 'blank') {
					$msg = __ ( 'Email field can not be left blank.', 'ced-Waitlist' );
					$msg_type = 'error';
					wc_add_notice ( $msg, $msg_type );
				}
			}
		}
		
		/**
		 * new custom post type
		 * 
		 * @name init
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		function init() {
			register_post_type ( 'Wait_list', array (
					'labels' => array (
							'name' => __ ( 'WaitList' ),
							'singular_name' => __ ( 'Waitlist' ) 
					),
					'supports' => false,
					'public' => true,
					'menu_icon' => 'dashicons-products',
					'capabilities' => array (
							'create_posts' => false 
					),
					'map_meta_cap' => true 
			) );
			remove_post_type_support ( 'Wait_list', 'editor' );
		}
		
		/**
		 * custom html
		 * 
		 * @name ced_awl_add_form
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		public function ced_awl_add_form() {
			$user = wp_get_current_user ();
			if ($user->exists ()) {
				global $post;
				global $product;
				if (get_post_type ( $post->ID ) == 'product' && is_product ()) {
					$this->current_product = wc_get_product ( $post->ID );					
					$manage_stock = get_post_meta ( $post->ID, '_manage_stock', true );
					if ($manage_stock == 'yes' || $this->current_product->get_stock_status()  ) {

						if ($this->current_product->get_type() == 'grouped') {
							return;
						}
						if ($this->current_product->get_type() == 'simple') {
						add_action ( 'woocommerce_get_stock_html', array ($this,CED_AWL_PREFIX . 'output_form' ), 20, 3 );
						  }
					}
				}
			} else {
				global $post;
				global $product;
				if (get_post_type ( $post->ID ) == 'product' && is_product ()){
					$this->current_product = wc_get_product ( $post->ID );
				if ($this->current_product->get_type() == 'simple'){
				add_action ( 'woocommerce_get_stock_html', array ($this,CED_AWL_PREFIX . 'login_form' ), 20, 3 );
			    }
			  }
			}
		}
		/**
		 * show login button for guest users
		 * 
		 * @name ced_awl_login_form
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 * @param unknown $login        	
		 * @param unknown $availability        	
		 * @param string $product        	
		 * @return string
		 */
		public function ced_awl_login_form($login, $availability, $product = false) {
			if (! $product) {
				$product = $this->current_product;
			}
			return $login . $this->the_ced_awl_login_form ( $product );
		}		
		/**
		 * custom html
		 * 
		 * @name the_ced_awl_login_form
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		public function the_ced_awl_login_form($product) {
			global $product;

				if ($product->is_in_stock ()) {
				return;
			}			
			$user = wp_get_current_user ();						
			$product_type = $product->get_type();			
			$product_id = ($product_type == 'simple') ? $product->get_id() : $product->get_id();
			$waitlist = ced_awl_get_ced_waitlist ( $product_id );
			$url = ($product_type == 'simple') ? get_permalink ( $product->get_id() ) : get_permalink ( $product->get_parent_id() );
			// set query
			$url = add_query_arg ( CED_AWL_PREFIX . 'ced_meta', $product_id, $url );
			$url = wp_nonce_url ( $url, 'action_waitlist' );
			$url = add_query_arg ( CED_AWL_PREFIX . 'ced_meta' . '-action', 'register', $url );
			
			$label_button_add = __ ( get_option ( CED_AWL_PREFIX . 'ced_add_button' ), 'advance-waitlist' );
			if ($label_button_add == "") {
				$label_button_add = __ ( "Add To WaitList", "ced-Waitlist" );
			}							
			  
				$html = "";
				$html .= '<form method="post" action="' . esc_url ( $url ) . '">';
				$html .= '<label for="ced-wcwtl-email">' . __ ( 'Email Address', 'ced-Waitlist' ) . '<input type="email" name="awl_email" id="ced-wcwtl-email" /></label>';
				$html .= '<input type="submit" name = "" value="' . $label_button_add . '" class="button alt" />';
				$html .= '</form>';

			
			return $html;
		}
		
		/**
		 * Add form to stock html
		 * 
		 * @name ced_awl_output_form
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		public function ced_awl_output_form($html, $availability = false, $product = false) {				
			if (! $product) {
				$product = $this->current_product;
			}
			return $html . $this->ced_awl_the_form ( $product, $html );
		}
		
		/**
		 * Output the form according to product type and user
		 * 
		 * @name ced_awl_the_form
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 */
		public function ced_awl_the_form($product, $html) {	  

			if ($product->is_in_stock ()) {
				return;	
			}	
						
			$html = '';
			$user = wp_get_current_user ();			
			$product_type = $product->get_type();
			$product_id = ($product_type == 'simple') ? $product->get_id() : $product->get_id();
			$waitlist = ced_awl_get_ced_waitlist ( $product_id );
			$url = ($product_type == 'simple') ? get_permalink ( $product->get_id() ) : get_permalink ( $product->get_parent_id());
				
			// set query
			$url = add_query_arg ( CED_AWL_PREFIX . 'ced_meta', $product_id, $url );
			$url = wp_nonce_url ( $url, 'action_waitlist' );
			$url = add_query_arg ( CED_AWL_PREFIX . 'ced_meta' . '-action', 'register', $url );
			
			// add message
			// get buttons label from options
			$label_button_add = __ ( get_option ( CED_AWL_PREFIX . 'ced_add_button' ), 'advance-waitlist' );
			if ($label_button_add == "") {
				$label_button_add = __ ( "Add To WaitList", "ced-Waitlist" );
			}
			$label_button_leave = __ ( 'Remove from wait list', 'advance-waitlist' );
			
			if ($label_button_leave == "") {
				$label_button_add = __ ( "Remove from waitlist", "ced-Waitlist" );
			}
			
			// echo $cur_sts; die;
			$mode_out_of_stocks = get_option ( CED_AWL_PREFIX . 'status_out_of_stock_option' );
			// echo $mode_out_of_stocks; die;out_of_stock
			if ($mode_out_of_stocks == 'out_of_stock_specific') {
				$cur_sts = get_post_meta ( $product->get_id(), '_waitlist_button', true );				
				if ($cur_sts == 'yes') {
					if ($product_type == 'simple' && ! $user->exists ()) {	 	

						$html .= '<div id="ced-wcwtl-output"><p class="ced-wcwtl-msg">' . __ ( get_option ( CED_AWL_PREFIX . 'ced_notifiaction_message' ), 'advance-waitlist' ) . '</p>';
						
						$html .= '<form method="post" action="' . esc_url ( $url ) . '">';
						$html .= '<label for="ced-wcwtl-email">' . __ ( 'Email Address', 'ced-Waitlist' ) . '<input type="email" name="ced-wcwtl-email" id="ced-wcwtl-email" /></label>';
						$html .= '<input type="submit" value="' . $label_button_add . '" class="button alt" />';
						$html .= '</form>';
					} elseif ($product_type == 'variation' && ! $user->exists ()) {	
					
						$html .= '</form><div id="ced-wcwtl-output"><p class="ced-wcwtl-msg">' . __ ( get_option ( CED_AWL_PREFIX . 'ced_notifiaction_message' ), 'advance-waitlist' ) . '</p>';
						$html .= '<form method="post" action="' . esc_url ( $url ) . '">';
						$html .= '<label for="ced-wcwtl-email">' . __ ( 'Email Addresssddd', 'ced-Waitlist' ) . '<input type="email" name="ced-wcwtl-email" id="ced-wcwtl-email" /></label>';
						$html .= '<input type="submit" value="' . $label_button_add . '" class="button alt" />';
						$html .= '</form>';
					} 

					elseif (is_array ( $waitlist ) && ced_awl_ced_waitlist_user_is_register ( $user->user_email, $waitlist )) {								 

						$html .= '<div id="ced-wcwtl-output">';
						$url = add_query_arg ( CED_AWL_PREFIX . 'ced_meta' . '-action', 'leave', $url );
						$html .= '<a href="' . esc_url ( $url ) . '" class="button button-leave alt">' . $label_button_leave . '</a>';
					} else {
							
						$html .= '<div id="ced-wcwtl-output">';
						$html .= '<a href="' . esc_url ( $url ) . '" class="button alt">' . $label_button_add . '</a>';
					}
					$html .= '</div>';
				}
			} else {			
				
				if ($product_type == 'simple' && ! $user->exists ()) {
					
					$html .= '<div id="ced-wcwtl-output"><p class="ced-wcwtl-msg">' . __ ( get_option ( CED_AWL_PREFIX . 'ced_notifiaction_message' ), 'advance-waitlist' ) . '</p>';
					
					$html .= '<form method="post" action="' . esc_url ( $url ) . '">';
					$html .= '<label for="ced-wcwtl-email">' . __ ( 'Email Address', 'ced-Waitlist' ) . '<input type="email" name="ced-wcwtl-email" id="ced-wcwtl-email" /></label>';
					$html .= '<input type="submit" value="' . $label_button_add . '" class="button alt" />';
					$html .= '</form>';
				} elseif ($product_type == 'variation' && ! $user->exists () ) {							 
					$html .= '</form><div id="ced-wcwtl-output"><p class="ced-wcwtl-msg">' . __ ( get_option ( CED_AWL_PREFIX . 'ced_notifiaction_message' ), 'advance-waitlist' ) . '</p>';
						$html .= '<form method="post" action="' . esc_url ( $url ) . '">';
						$html .= '<label for="ced-wcwtl-email">' . __ ( 'Email Address', 'ced-Waitlist' ) . '<input type="email" name="awl_email" id="ced-wcwtl-email" /></label>';
						$html .= '<input type="submit" value="' . $label_button_add . '" class="button alt" />';
						$html .= '</form>';
				} 

				elseif (is_array ( $waitlist ) && ced_awl_ced_waitlist_user_is_register ( $user->user_email, $waitlist )) {	
					$html .= '<div id="ced-wcwtl-output">';
					$url = add_query_arg ( CED_AWL_PREFIX . 'ced_meta' . '-action', 'leave', $url );
					$html .= '<a href="' . esc_url ( $url ) . '" class="button button-leave alt">' . $label_button_leave . '</a>';
				} else {					
					$html .= '<div id="ced-wcwtl-output">';
					$html .= '<a href="' . esc_url ( $url ) . '" class="button alt">' . $label_button_add . '</a>';
				}
				$html .= '</div>';
			}
			return apply_filters ( "cedcomerce_waitlist_button", $html );
		}
	
		
		/**
		 * Add user to waitlist
		 * 
		 * @name ced_awl_ced_waiting_submit
		 * @author CedCommerce<plugins@cedcommerce.com>
		 * @link http://cedcommerce.com/
		 *      
		 */
		public function ced_awl_ced_waiting_submit() {
			$user = wp_get_current_user ();
			if (! (isset ( $_REQUEST [CED_AWL_PREFIX . 'ced_meta'] ) && is_numeric ( $_REQUEST [CED_AWL_PREFIX . 'ced_meta'] ) && isset ( $_REQUEST [CED_AWL_PREFIX . 'ced_meta-action'] ))) {
				return;
			}
			
			$action = $_REQUEST [CED_AWL_PREFIX . 'ced_meta-action'];
			$user_email = $user->user_email;
			
			$product_id = $_REQUEST [CED_AWL_PREFIX . 'ced_meta'];
			
			// set standard msg and type
			$msg = get_option ( CED_AWL_PREFIX . 'ced_successfull_registration' );
			$msg_type = 'success';
			
			if ($action == 'register') {
				if (! empty ( $_POST ['awl_email'] )) {
					$user_email = sanitize_text_field ( $_POST ['awl_email'] );
					$waitlist = ced_awl_get_ced_waitlist ( $product_id );
					if (is_array ( $waitlist ) && ced_awl_ced_waitlist_user_is_register ( $user_email, $waitlist )) {
						$msg = __ ( 'The email is alredy registered for the Waitlist', 'advance-waitlist' );
						$msg_type = 'error';
						$dest = remove_query_arg ( array (
								CED_AWL_PREFIX . 'ced_meta',
								CED_AWL_PREFIX . 'ced_meta' . '-action',
								'_wpnonce',
								'ced-wcwtl-email' 
						) );
						$dest = add_query_arg ( 'added', 'false', $dest );
						wp_redirect ( esc_url ( $dest ) );
						exit ();
					} else {
						ced_awl_ced_waitlist_register_user ( $user_email, $product_id );
						$msg = __ ( get_option ( CED_AWL_PREFIX . 'ced_successfull_registration' ), 'advance-waitlist' );
						$msg_type = 'success';
						$dest = remove_query_arg ( array (
								CED_AWL_PREFIX . 'ced_meta',
								CED_AWL_PREFIX . 'ced_meta' . '-action',
								'_wpnonce',
								'ced-wcwtl-email' 
						) );
						$dest = add_query_arg ( 'added', 'true', $dest );
						wp_redirect ( esc_url ( $dest ) );
						exit ();
					}
				} elseif (isset ( $_POST ['awl_email'] ) && $_POST ['awl_email'] == "") {
					
					$msg = __ ( 'Email field can not be left blank.', 'ced-Waitlist' );
					$msg_type = 'error';
					$dest = remove_query_arg ( array (
							CED_AWL_PREFIX . 'ced_meta',
							CED_AWL_PREFIX . 'ced_meta' . '-action',
							'_wpnonce',
							'ced-wcwtl-email' 
					) );
					$dest = add_query_arg ( 'added', 'blank', $dest );
					wc_add_notice ( apply_filters ( "ced_waitlist_message", $msg ), $msg_type );
					wp_redirect ( esc_url ( $dest ) );

					exit ();
				}
				
				// register user;
				$res = ced_awl_ced_waitlist_register_user ( $user_email, $product_id );
				if (! empty ( $res )) {
					$msg = __ ( get_option ( CED_AWL_PREFIX . 'ced_successfull_registration' ), 'advance-waitlist' );
					$msg_type = 'success';
				}
			} elseif ($action == 'leave') {
				// unregister user
				ced_awl_ced_waitlist_unregister_user ( $user_email, $product_id );
				$msg = __ ( 'Product is removed from your waitlist', 'ced-Waitlist' );
			} else {
				$msg = __ ( 'An error has occurred. Please try again.', 'ced-Waitlist' );
				$msg_type = 'error';
			}
			
			wc_add_notice ( apply_filters ( "ced_waitlist_message", $msg ), $msg_type );
			
			$dest = remove_query_arg ( array (
					CED_AWL_PREFIX . 'ced_meta',
					CED_AWL_PREFIX . 'ced_meta' . '-action',
					'_wpnonce',
					'ced-wcwtl-email' 
			) );
			
			wp_redirect ( esc_url ( $dest ) );
			exit ();
		}
	}
	$GLOBALS ['ced'] = new ced_awl_ced_plugin_Frontend ();
}
/**
 * Initiate the frontend
 * 
 * @name ced_awl_ced_waiting_submit
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_awl_ced_plugin_Frontend() {
	return ced_awl_ced_plugin_Frontend::get_instance ();
}
?>