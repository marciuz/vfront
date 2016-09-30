jQuery(document).ready(function() {
	
	jQuery.ajax({
	   type: "POST",
	   url: "findupdates.php",
	   success: function(msg){
		  if(msg==''){
		  	
		  }
		  else{
		  	
		  	jQuery('#find_updates').html(msg);
		  	jQuery('#find_updates').show();
		  }
	   }
	 });
	
});