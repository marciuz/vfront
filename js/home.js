/* $Id: home.js 1096 2014-06-19 09:16:31Z marciuz $ */

jQuery(document).ready( function() {
    
    jQuery('#hide_alert_config').on('click', function(){
        
        jQuery.ajax({
            url: "/admin/variabili.php?modglob",
            type: 'POST',
            data: 'var[alert_config]=0&rpc_query=1',
            success: function (transport){
                if(transport==1){

                    jQuery('#alert_config').fadeOut();
                }
                else{
                    jQuery('#hide_alert_config').html(_('Unable to change the option'));
                }
            }
        });
        
    });
	
});

/*
Event.observe(window, 'load', function() {
    
	if(null!==$('alert_config')){
	
		Event.observe($('hide_alert_config'), 'click', function(){
			
			new Ajax.Request("./admin/variabili.php?modglob",{
				 	method: 'post',
				 	postBody: 'var[alert_config]=0&rpc_query=1',
				 	onSuccess: function(transport){
				 		if(transport.responseText==1){
				 			
				 			$('alert_config').fade();
				 		}
				 		else{
				 			$('hide_alert_config').innerHTML=_('Unable to change the option');
				 		}
				 		
				 	}
			});
				 	
			
		});
	
	}
});
*/