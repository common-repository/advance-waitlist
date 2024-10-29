<?php 


/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

if (isset($_POST[CED_AWL_PREFIX.'save_ced_plugin_setting']))
{
	if(isset($_POST[CED_AWL_PREFIX.'ced_deactivation']))
	{
		$mode = sanitize_text_field($_POST[CED_AWL_PREFIX.'ced_deactivation']);
	}
	else 
	{
		$mode = 0;
	}
	if(isset($_POST[CED_AWL_PREFIX.'status_out_of_stock_option']))
	{
		$mode_out_of_stock = sanitize_text_field($_POST[CED_AWL_PREFIX.'status_out_of_stock_option']);
	}
	else
	{
		return ;
		$mode_out_of_stock = '';
	}
	update_option(CED_AWL_PREFIX."status_out_of_stock_option", $mode_out_of_stock);
	update_option(CED_AWL_PREFIX."ced_deactivation_mode", $mode);
	update_option(CED_AWL_PREFIX."ced_add_button", sanitize_text_field($_POST[CED_AWL_PREFIX.'add_button_lebel']));
	update_option(CED_AWL_PREFIX."ced_successfull_registration", sanitize_text_field($_POST[CED_AWL_PREFIX.'successfull_registration']));
?>
<div class="updated" style="display:block">
	<p><?php _e('The Setting saved successfully.','advance-waitlist')?></p>
</div>
<?php 
} 
$custom_mode = get_option(CED_AWL_PREFIX.'email_custum_mode');
$chkd = "";
if($custom_mode == "yes")
{
   $chkd  = "checked";
}
$value_OOSS = ''; 
$value_OOS = ''; 
?> 			
<div class="wrap woocommerce">
	<form id="mainform" enctype="multipart/form-data" action="" method="post">
		<h3><?php _e('General Options','advance-waitlist')?></h3>
		<table class="form-table">
			<tbody>
				<tr class="" valign="top">
					<th class="titledesc" scope="row"><?php _e('Waitlist Button Appearance','advance-waitlist')?></th>
					<td class="forminp forminp-checkbox">
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php _e('Activation','advance-waitlist')?></span>
							</legend>
							<label for="woocommerce_out_of_stock">
								<?php $mode_out_of_stocks = get_option(CED_AWL_PREFIX.'status_out_of_stock_option');?>
								<input class="" type="radio" value="out_of_stock" <?php checked('out_of_stock', $mode_out_of_stocks); ?> name="<?php echo CED_AWL_PREFIX ?>status_out_of_stock_option">
									<span class="description">
										<?php _e('Enable it if you want to enable waitlist button for all out of stock products','advance-waitlist')?>
									</span>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr class="" valign="top">
					<th class="titledesc" scope="row"><?php _e('','advance-waitlist')?></th>
					<td class="forminp forminp-checkbox">
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php _e('Activation','advance-waitlist')?></span>
							</legend>
							<label for="woocommerce_out_of_stock">
								<?php $mode_out_of_stocks = get_option(CED_AWL_PREFIX.'status_out_of_stock_option');?>
								<input  class="" type="radio" value="out_of_stock_specific"  <?php checked('out_of_stock_specific', $mode_out_of_stocks); ?> name="<?php echo CED_AWL_PREFIX ?>status_out_of_stock_option">
									<span class="description">
										<?php _e('Enable it if you want to enable waitlist button for specific products','advance-waitlist')?>
										<?php $path=home_url().'/wp-admin/admin.php?page=ced_plugin/waitingnou_button-admin.php';?>
											<p class="description"><a href="<?php echo $path;?>"> Check all Out Of Stock Waitlist button status</a></p>
									</span>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr valign="">
					<th class="titledesc" scope="row"><?php _e('Deactivation mode','advance-waitlist')?></th>
					<td class="forminp forminp-checkbox">
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php _e('Deactivation','advance-waitlist')?></span>
							</legend>
							<label for="woocommerce_demo_store">
								<?php $mode = get_option(CED_AWL_PREFIX.'ced_deactivation_mode');?>
								<input  <?php if($mode == 1){?>checked<?php }?> class="" type="checkbox" value="1" name="<?php echo CED_AWL_PREFIX ?>ced_deactivation">
									<span class="description">
										<?php _e('Enable it if you want to Keep the related data safe while deactivating the plugin','advance-waitlist')?>
									</span>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th class="titledesc" scope="row">
						<label for="woocommerce_default_country"><?php _e('Add button label','advance-waitlist')?></label>
					</th>
					<td class="forminp">
						<?php $add_button_lebel = get_option(CED_AWL_PREFIX.'ced_add_button'); ?>
						<input type = "text" name ="<?php echo CED_AWL_PREFIX ?>add_button_lebel" value = "<?php echo $add_button_lebel;?>" placeholder="Add to waitlist">
						<span class="description">
						 <?php _e('The label of the button to be added to the waitlist.','advance-waitlist')?>
						</span>
					</td>
				</tr>
				<tr valign="top">
					<th class="titledesc" scope="row">
						<label for="woocommerce_default_country"><?php _e(' Successfully added message','advance-waitlist')?></label>
					</th>
					<td class="forminp">
						<?php $successfull_registration = get_option(CED_AWL_PREFIX.'ced_successfull_registration');?>
						<input type = "text" name ="<?php echo CED_AWL_PREFIX ?>successfull_registration" value = "<?php echo $successfull_registration; ?> " placeholder="Product is successfully added to the waitlist.">
						<span class="description">
						 <?php _e('Text for successful addition of product into waitlist.','advance-waitlist')?>
						</span>
					</td>
				</tr>
		</tbody>
	</table>
		<input class="button-primary" type="submit" value="Save changes" name="<?php echo CED_AWL_PREFIX ?>save_ced_plugin_setting">
	</form>
</div>
