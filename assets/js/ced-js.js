/**
 * 
 */
jQuery(document).ready(function() {	
	
	/*var current_url = window.location.href;
	if( current_url.indexOf('post='+ obj.current_post_id +'&action=edit') > -1 ) {
		if ( jQuery('#side-sortables').html() === '' ) {
			jQuery( '#side-sortables' ).remove();
		}
	}*/
	
	jQuery(".enab_wtl_btn").on("click",function(){
		
		var prod_id = jQuery(this).attr('id');
		var data = {
				'action': 'chng_sts_wtl_btn',
				'content': prod_id,
			};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			
			console.log( response );
			location.reload();
							
		});
		
	});
	jQuery("#demo_preview_mail").on("click",function(){
		
		jQuery(".license_loading_image").show();
		var econtent= tinymce.activeEditor.getContent();
		var data = {
				'action': 'awl_email_action',
				'content': econtent
			};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			
			if(response == "success"){
				jQuery(".license_loading_image").hide();
				jQuery("#success_mail").show().fadeOut(10000);
				
			}
			else{
				jQuery(".license_loading_image").hide();
				jQuery("#failed_mail").show().fadeOut(10000);
					
				}
		});
	});
	
	if(jQuery("input[type=checkbox][name=ced_awl_email_content]:checked").val() == "1")
	{
		jQuery(".content_email_div").show();
	}
	jQuery("#email_content").on("click",function(){
	if(jQuery("input[type=checkbox][name=ced_awl_email_content]:checked").val() == "1")
	{
		jQuery(".content_email_div").show();
	}
	else
	{
		jQuery(".content_email_div").hide();
	}
	
	})
	
	jQuery("#awl_link_text").on("keyup",function(){
		
		jQuery("#awl_link_demo").text(jQuery("#awl_link_text").val());
	});
	
		jQuery("#select_email_variable").on("click",function(){

			
			var changed_value =  jQuery("#select_email_variable").val();
			if(changed_value == "{link_text}"){
				jQuery("#hidden_thick_button").click();
			}
			if(changed_value != "{link_text}"){
				jQuery("#awl_text_link_div").css("display","none");
			}
		  	
			tinyMCE.triggerSave();
			
			if(tinymce.activeEditor){
				tinymce.activeEditor.insertContent(""+changed_value+"");
			}
			
			jQuery("#ced_awl_email_editor").insertAtCaret(changed_value);
			
		})
			
		});
		jQuery.fn.extend({
		insertAtCaret: function(myValue){
			
		  return this.each(function(i) {
		    if (document.selection) {
		      //For browsers like Internet Explorer
		      this.focus();
		      sel = document.selection.createRange();
		      sel.text = myValue;
		      this.focus();
		    }
		    else if (this.selectionStart || this.selectionStart == "0") {
		      //For browsers like Firefox and Webkit based
		      var startPos = this.selectionStart;
		      var endPos = this.selectionEnd;
		      var scrollTop = this.scrollTop;
		      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
		      this.focus();
		      this.selectionStart = startPos + myValue.length;
		      this.selectionEnd = startPos + myValue.length;
		      this.scrollTop = scrollTop;
		    } else {
		      this.value += myValue;
		      this.focus();
		    }
		  })
		}
		});
		jQuery(document).ready(function() {
							
			jQuery("#metakeyselect").on("change",function(){
				
				var val = jQuery(this).val();
					
				if(val < 1 || isNaN(val)){
					
					jQuery("#error_div").css("display","block").fadeOut(5000);
					return false;
				}
		});
			if(jQuery("input[type=checkbox][name=ced_awl_ced_specific_users]:checked").val() == "1")
			{
				jQuery(".users_div").show();
			}
			jQuery("#woocommerce_demo_store_users").on("click",function(){
			if(jQuery("input[type=checkbox][name=ced_awl_ced_specific_users]:checked").val() == "1")
			{
				jQuery(".users_div").show();
			}
			else
			{
				jQuery(".users_div").hide();
			}
				
			})
		jQuery("#select_email_variable").on("change",function(){
			var changed_value =  jQuery("#select_email_variable").val();
		  	//alert(changed_value);
			console.log(jQuery(this));
			tinyMCE.triggerSave();
			jQuery("#ced_awl_email_editor").insertAtCaret(changed_value);
			
		})
			
		});
		jQuery.fn.extend({
		insertAtCaret: function(myValue){
		  return this.each(function(i) {
		    if (document.selection) {
		      //For browsers like Internet Explorer
		      this.focus();
		      sel = document.selection.createRange();
		      sel.text = myValue;
		      this.focus();
		    }
		    else if (this.selectionStart || this.selectionStart == "0") {
		      //For browsers like Firefox and Webkit based
		      var startPos = this.selectionStart;
		      var endPos = this.selectionEnd;
		      var scrollTop = this.scrollTop;
		      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
		      this.focus();
		      this.selectionStart = startPos + myValue.length;
		      this.selectionEnd = startPos + myValue.length;
		      this.scrollTop = scrollTop;
		    } else {
		      this.value += myValue;
		      this.focus();
		    }
		  })
		}
		});
	
		jQuery(document).ready(function() {
			if(jQuery("input[type=checkbox][name=ced_awl_email_notification]:checked").val() == "1")
			{
				jQuery(".users_email_div").show();
			}
			jQuery("#email_notification").on("click",function(){
			if(jQuery("input[type=checkbox][name=ced_awl_email_notification]:checked").val() == "1")
			{
				jQuery(".users_email_div").show();
			}
			else
			{
				jQuery(".users_email_div").hide();
			}

			})
		
			if(jQuery("input[type=checkbox][name=ced_awl_email_content]:checked").val() == "1")
			{
				jQuery(".content_email_div").show();
			}
			jQuery("#email_content").on("click",function(){
			if(jQuery("input[type=checkbox][name=ced_awl_email_content]:checked").val() == "1")
			{
				jQuery(".content_email_div").show();
			}
			else
			{
				jQuery(".content_email_div").hide();
			}

			})
		
		});