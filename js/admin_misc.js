/* 
 * Table Administration js
 */

var divs = new Array('tabella-gen','tabella-campi','campi-sort','tabella-sottomaschere','tabella-pulsanti', 'widget','default-filters');

function add_orderby(){
	
	var o=jQuery('#orderby_tn div').last().clone();
    jQuery(o).val(0);
	jQuery('#orderby_tn').append(o);
}

function eti(ido){
			
    for (i=0;i<divs.length;i++){
        $(divs[i]).style.display='none';
        $('li-'+divs[i]).className='disattiva';
    }

    // attiva il selezionato
    $(ido).style.display='';
    $('li-'+ido).className='attiva';
}

jQuery(document).ready( function() {
	
	jQuery('#widget_type').on("change", function() {
        
        jQuery('div.widget_options').each( function (i, e){ e.hide(); });
        jQuery( jQuery(this).val()+'-options').show();
    });

	jQuery('.sub_gen_tv').on("click", function(){
        
		if( jQuery(this)[0].id == 'tipo_vista_3' ){

			jQuery('#sub_insert').attr('disabled','disabled');
			jQuery('#sub_update').attr('disabled','disabled');
			jQuery('#sub_delete').attr('disabled','disabled');
		}
		else{
			jQuery('#sub_insert').attr('disabled', false);
			jQuery('#sub_update').attr('disabled', false);
			jQuery('#sub_delete').attr('disabled', false);
		}
	});

    jQuery('tr').on('change', '.op-selector', function(){
        
        var that = jQuery(this);
        var input = that.parent().next().find('input');
        
        if(that.val() === 'is_null' || that.val() === 'is_not_null'){
            
            input.addClass('disabled');
        }
        else{
            input.removeClass('disabled');
        }
    });
    
    jQuery('#df-reset').on('click', function(){
        jQuery('.df-field input').val('').removeClass('disabled');
        jQuery('.df-op select').val('equal');
    });
	
		//var attiva = '<?php echo $attiva;?>';
		
		

});
