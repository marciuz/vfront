// javascript

function mostra_hid(val,id){
	
	campo=document.getElementById('hid_'+id);

	tendina=document.getElementById('default-select-'+id);
	sqlbox=document.getElementById('default-selectfrom-'+id);
	hid=document.getElementById('default-hidden-'+id);
	passwd=document.getElementById('default-password-'+id);
	enumbox=document.getElementById('enum-'+id);
	
	
	tendina.style.display='none';
	tendina.getElementsByTagName('textarea')[0].disabled='disabled';
	
	sqlbox.style.display='none';
	sqlbox.getElementsByTagName('textarea')[0].disabled='disabled';
						
						
	hid.style.display='none';
	hid.getElementsByTagName('input')[0].disabled='disabled';
	
	if(null!==enumbox){
		enumbox.style.display='none';
		enumbox.getElementsByTagName('textarea')[0].disabled='disabled';
	}
	
	
	if(passwd){
		
		passwd.style.display='none';
		passwd.getElementsByTagName('input')[0].disabled='disabled';
		passwd.getElementsByTagName('input')[1].disabled='disabled';
		passwd.getElementsByTagName('input')[2].disabled='disabled';
	
	}
	
	
	if(val=='autocompleter_from'){
		
		$('in_suggest_'+id).disabled='disabled';
	}
	else{
		if(null!==$('in_suggest_'+id)){
			$('in_suggest_'+id).disabled='';
		}
	}
	
	
	switch(val){
		case 'select': 	tendina.style.display='inline';
						tendina.getElementsByTagName('textarea')[0].disabled=false;
						
					
		break;
		
		case 'select_from': 
		case 'autocompleter_from': 
		
				sqlbox.style.display='inline';
				sqlbox.getElementsByTagName('textarea')[0].disabled=false;
					   
		break;
		
		
		
		case 'hidden':  hid.style.display='inline';
						hid.getElementsByTagName('input')[0].disabled=false;
		break;	
			
		case 'password':  passwd.style.display='inline';
						passwd.getElementsByTagName('input')[0].disabled=false;
						passwd.getElementsByTagName('input')[1].disabled=false;
						passwd.getElementsByTagName('input')[2].disabled=false;
		break;
			
		case 'select_enum': 	enumbox.style.display='inline';
		break;
		
		default: campo.style.display=''; return false;
	}
	
	
}
