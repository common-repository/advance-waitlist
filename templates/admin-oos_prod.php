<?php

$args = array( 'post_type' => 'product' );

$products = new WP_Query( $args );
$post_meta_record = array();
foreach ( $products->posts as $prod ){
	$array = wc_get_product ( $prod ->ID );
	if( !$array->is_in_stock () ){
		$post_meta_record[] = $prod;
	}
}
if( isset( $post_meta_record ) ){
	
	foreach ( $post_meta_record as $k=> $val ){
		$status = 'Disabled';
		$test_sts = get_post_meta( $val->ID, '_waitlist_button' );
		if($test_sts[0] == 'yes'){
				$status = 'Enabled';
				$html = '<label class="switch"><input type="checkbox" checked><div id='.$val->ID.' class="slider round enab_wtl_btn"></div></label>';
		}else{
			$html = '<label class="switch"><input type="checkbox"><div id='.$val->ID.' class="slider round enab_wtl_btn"></div></label>';
		}
		$detail[$k] = array( $val->post_title, $status, $html, $val->ID );
	}
	
	function test_prod_waitlisted($get_waitlisted_product1, $det_val){
		$id = get_post_meta($det_val, CED_AWL_PREFIX.'ced_waitlist_id',true);
		foreach ( $get_waitlisted_product1 as $kop => $vop ){
			
			return ($id == $vop['ID']) ?  'yes': 'no';
			}
	}
	
	
	function get_ID_prod_waitlisted($get_waitlisted_product1, $det_val){
	
		foreach ( $get_waitlisted_product1 as $kop => $vop ){
				
			if($vop['post_title'] == $det_val){
				return $vop['ID'];
			}else{
				return false;
			}
		}
	}
	?>
	<div>
	<?php if(isset($detail)):?>
		<span style="text-align: center"><h1>Status of all Out Of Stock Products </h1></span>
		<table name="wtl_btn_sts" class="wp-list-table widefat fixed striped posts">
			<tr>
				<th class='aw-text-center-head'>Product</th>
				<th class='aw-text-center-head'>Status</th>
				<th class='aw-text-center-head'>Edit</th>
			</tr>
		<?php foreach ( $detail as $ke => $det_val ){?>
				<tr>
					<?php 
					$my_post = array(
							'post_title'    => $det_val[0],
							'post_type'    => 'Wait_list',
							'post_content'  => 'This is my Waitlist.',
							'post_status'   => 'publish',
							'post_author'   => 1,
					);
					$get_waitlisted_product = get_posts($my_post);
					$get_waitlisted_product1 = array();
					foreach ( $get_waitlisted_product as $kep => $det_valp ){
						$get_waitlisted_product1[] = (array)$det_valp;
					}
					 	
					?>
					<td class='aw-text-center' ><?php echo $det_val[0];?></td>
					<?php if('yes' == test_prod_waitlisted($get_waitlisted_product1, $det_val[3])){?>
						<?php $ID_Waitlisted_Prod = get_ID_prod_waitlisted($get_waitlisted_product1, $det_val[0])?>
						<td class='aw-text-center'>
						<?php $path_to_edit = home_url().'/wp-admin/post.php?post='.$ID_Waitlisted_Prod.'&action=edit';?>
							<!-- <div class='aw-disabled' title='<?php //echo $det_val[1];?>' style='color: #ff0000'> -->
								<a href="<?php echo $path_to_edit;?>">Waitlisted</a>
							<!-- </div> -->
						</td>
					<?php }else{?>
						<td class='aw-text-center'>
						<?php $path_to_edit = home_url().'/wp-admin/post.php?post='.$det_val[3].'&action=edit';?>
								<!-- <div class='aw-enabled' title='<?php echo $det_val[1];?>' style='color: #00b33c'> -->
								Not Waitlisted
								<!-- </div> -->
						</td>
					<?php }?>
					<td class='aw-text-center' ><?php echo $det_val[2];?></td>
				</tr>
		<?php }?>
		</table>
		<?php else:?>
		<span style="text-align: center"><h1>There are No Out Of Stock Products </h1></span>
		<?php endif;?>
	</div><?php 
}
