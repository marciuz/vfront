// JAVASCRIPT


function confirm_delete(theLink, getParams){
	
	msg=_('Do you really want to delete this record?');
	
	if(confirm(msg)){
		theLink.href='?del='+getParams;
		return true;
	}
	else return false;

}


function confirm_delete_f(theLink, getParams){
	
	msg=_('Do you really want to delete this file?');
	
	if(confirm(msg)){
		theLink.href='?del='+getParams;
		return true;
	}
	else return false;

}
