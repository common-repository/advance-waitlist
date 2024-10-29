<?php

/**
 * Exit if accessed directly
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

add_action ( 'wp', 'ced_email_action' );

/**
 * Get detail from email link and redirect to waitlist
 * 
 * @name ced_email_action
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function ced_email_action() {
	if (isset ( $_REQUEST ['ced_auth_key'] )) {
		$v = get_option ( $_REQUEST ['ced_auth_key'] );
		$name_email = get_user_by ( 'email', $v );							
		$pageid = get_option ( CED_AWL_PREFIX . 'ced_activation_pageid' );	
		$link = get_permalink ( $pageid );
		if(isset($name_email->ID)){
		$u_id = $name_email->ID;		
		wp_set_current_user ( $u_id );
		wp_set_auth_cookie ( $u_id );
	    }
		wp_redirect ( $link );		
	}
}

add_action ( 'wp_ajax_awl_email_action', 'awl_email_action_callback' );

/**
 * For preview email
 * 
 * @name awl_email_action_callback
 * @author CedCommerce<plugins@cedcommerce.com>
 * @link http://cedcommerce.com/
 */
function awl_email_action_callback() {
	$email_content = $_POST ['content'];
	$email = wp_get_current_user ();
	$headers = array (
			'Content-Type: text/html; charset=UTF-8' 
	);
	$email_content = str_replace ( '{user_name}', "Demo", $email_content );
	$email_content = str_replace ( '{product_name}', "Demo Product", $email_content );
	$email_content = str_replace ( '{qty}', "100", $email_content );
	$email_content = str_replace ( '{link}', "javascript:;", $email_content );
	$email_content = str_replace ( '{link_text}', "Waitlist", $email_content );
	$message = $email_content;
	$r = false;
	$r = wp_mail ( $email->user_email, 'Hurry !! PRODUCT ARRIVED', $message, $headers );
	
	if ($r == 1) {
		echo "success";
	} else {
		echo "failed";
	}
	wp_die (); // this is required to terminate immediately and return a proper response
}
?>