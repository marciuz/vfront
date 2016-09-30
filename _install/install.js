// javascript install functions

document.observe("dom:loaded", function() {
	
	$('dbtype').observe("change", function() {

		$$('.method-radio').each( function (e) { e.style.color='#000'; });

		if(this.value=='mysql'){
			$('conn_mysql').show();
			$('conn_postgres').hide();
			$('conn_oracle').hide();
			$('conn_sqlite').hide();
		}
		else if(this.value=='postgres'){
			$('conn_mysql').hide();
			$('conn_postgres').show();
			$('conn_oracle').hide();
			$('conn_sqlite').hide();
		}
		else if(this.value=='oracle'){
			$('conn_mysql').hide();
			$('conn_postgres').hide();
			$('conn_oracle').show();
			$('conn_sqlite').hide();
		}
		else if(this.value=='sqlite'){
			$('conn_mysql').hide();
			$('conn_postgres').hide();
			$('conn_oracle').hide();
			$('conn_sqlite').show();
			$$('.method-radio').each( function (e) { e.style.color='#CCC'; });
		}


	});

	$('regmethod_internal').observe("click", function(e){

		if($('regmethod_internal').checked==true){
			$$('.internal-frontend').each(function(e){ e.show();});
			$('sqlite-path').hide();
		}
		else{
			$$('.internal-frontend').each(function(e){ e.hide();});
			$('sqlite-path').show();
		}
		
	});

	$('regmethod_sqlite').observe("click", function(e){

		if($('regmethod_internal').checked==true){
			$$('.internal-frontend').each(function(e){ e.show();});
			$('sqlite-path').hide();
		}
		else{
			$$('.internal-frontend').each(function(e){ e.hide();});
			$('sqlite-path').show();
		}

	});



});


function lic(){
	
	if($('accept').checked){
		
		$('form1').submit();
	}
	else{
		
		alert('Please accept the license to continue');
	}
}


function test_db_conn(){
	
	var res=false;
	
	var colore_feed='';
	$('testdb_feedback').update('');
	
	new Ajax.Request("./rpc.testdb.php",{
				 	method: 'post',
				 	postBody: Form.serialize($('installform')),
				 	asynchronous: false,
				 	onSuccess: function(transport){
				 		if(transport.responseText==1){
				 			
				 			$('testdb_feedback').update('Connection well done!');
				 			colore_feed='#060';
				 			res=true;
				 		}
				 		else if(transport.responseText==-1){
				 			$('testdb_feedback').update('Please insert all parameters');
				 			colore_feed='#F60';
				 		}
				 		else{
				 			$('testdb_feedback').update('Connection error');
				 			colore_feed='#F00';
				 		}
				 		
				 		$('testdb_feedback').style.color=colore_feed;
				 		
				 	}
				 });
				 
	return res;
}


function test_ext(){
	
	var check=false;
	var colore_feed='';
	$('testext_feedback').update('');
	
	new Ajax.Request("./rpc.testext.php",{
				 	method: 'post',
				 	asynchronous: false,
				 	postBody: Form.serialize($('installform')),
				 	onSuccess: function(transport){
				 		
				 		var resp=transport.responseText.split('@');
				 		
			 			$('testext_feedback').update(resp[1]);
			 			
			 			if(resp[0]=='1'){
			 				check=true;
			 			}
				 		
				 	}
				});
				
	return check;
}


function show_auth_div(div){
	
	reset_inputs();
	
	$$('.auth-box').each(function (s){ s.hide();});
	
	if(undefined!==$('auth_'+div)){
		
		$('auth_'+div).show();
		if(div!='null') $('auth_ext_common').show();
		/*if(div!='db'){
			reset_inputs();
		}*/
		
		if(div=='ldap'){
			
			$('authext_nick').value='cn';
			$('authext_mail').value='mail';
			$('authext_name').value='givenname';
			$('authext_surname').value='sn';
			
			$('authext_nick_p').show();
			$('authext_mail_p').show();
			$('authext_name_p').show();
			$('authext_surname_p').show();
			
			$('authext_passwd_p').hide();
			$('authext_passwd_encode_p').hide();
			
		}
		
		
	}
}

function reset_inputs(){
	
	$('authext_passwd_p').show();
	$('authext_passwd_encode_p').show();
	$('authext_nick_p').show();
	$('authext_mail_p').show();
	$('authext_passwd_p').show();
	$('authext_passwd_encode_p').show();
	$('authext_name_p').show();
	$('authext_surname_p').show();
	
	$('authext_nick_cont').update('<input type="text" name="var[authext_nick]" id="authext_nick" value="" />');
	$('authext_mail_cont').update('<input type="text" name="var[authext_mail]" id="authext_mail" value="" />');
	$('authext_passwd_cont').update('<input type="text" name="var[authext_passwd]" id="authext_passwd" value="" />');
	$('authext_name_cont').update('<input type="text" name="var[authext_name]" id="authext_name" value="" />');
	$('authext_surname_cont').update('<input type="text" name="var[authext_surname]" id="authext_surname" value="" />');
}


function display_smtp(){
	
	if($F('smtp_use')=='true'){
		
		$('use_smtp').show();
	}
	else {
		$('use_smtp').hide();
	}
}


function anon_bind(){
	
	if($('ldap_anonymus_bind').checked){
		
		$('ldap_bind_user').disable();
		$('ldap_bind_passw').disable();
	}
	else {
		$('ldap_bind_user').enable();
		$('ldap_bind_passw').enable();
	}
}


function set_frontend_name(namedb){
	
	$('dbfrontend1').value=namedb.strip()+'_vfront';
}


function show_ajax_help(type){
	
	if(type=='db'){
		
		$('authdb_dbname_wait').show();
		
		new Ajax.Request("./rpc.getdb.php?a=db",{
				 	method: 'post',
				 	postBody: Form.serialize($('installform')),
				 	onComplete: $('authdb_dbname_wait').hide(),
				 	onSuccess: function(transport){
				 		
				 		if(transport.responseText=="0"){
				 			// errore di connessione
				 		}
				 		else if(transport.responseText=="-1"){
				 			// non ci sono tabelle disponibili
				 		}
				 		else{
				 			
				 			var jsonr=eval(transport.responseText);
				 			var select_db='<select id="authdb_dbname" name="var[authdb_dbname]" onchange="show_ajax_help(\'tab\')">';
				 			
				 			select_db+='<option value="">Select a database</option>';
				 			
				 			for(var i=0;i<jsonr.length;i++){
				 				select_db+='<option value="'+jsonr[i].db+'">'+jsonr[i].db+'</option>';
				 			}
				 			select_db+='</select>';
				 			
				 			$('authdb_dbname_cont').update(select_db);
				 		}
				 	}
				});
	}
	
	if(type=='tab'){
		
		$('authdb_usertable_wait').show();
		
		new Ajax.Request("./rpc.getdb.php?a=tab",{
				 	method: 'post',
				 	postBody: Form.serialize($('installform')),
				 	onComplete: $('authdb_usertable_wait').hide(),
				 	onSuccess: function(transport){
				 		
				 		if(transport.responseText=="0"){
				 			// errore di connessione
				 		}
				 		else if(transport.responseText=="-1"){
				 			// non ci sono tabelle disponibili
				 		}
				 		else{
				 			
				 			var jsonr1=eval(transport.responseText);
				 			var select_tab='<select id="authdb_usertable" name="var[authdb_usertable]" onchange="show_ajax_help(\'field\')">';
				 			
				 			select_tab+='<option value="">Select a table</option>';
				 			
				 			for(var i=0;i<jsonr1.length;i++){
				 				select_tab+='<option value="'+jsonr1[i].tab+'">'+jsonr1[i].tab+'</option>';
				 			}
				 			select_tab+='</select>';
				 			
				 			$('authdb_usertable_cont').update(select_tab);
				 		}
				 		
				 		
				 	}
				});
	}
	
	if(type=='field'){
		
		$('authext_nick_wait','authext_name_wait', 'authext_surname_wait', 'authext_passwd_wait').each(function (s){s.show()});
		
		new Ajax.Request("./rpc.getdb.php?a=field",{
				 	method: 'post',
				 	postBody: Form.serialize($('installform')),
				 	onComplete: $('authext_nick_wait','authext_name_wait', 'authext_surname_wait', 'authext_passwd_wait').each(function (s){s.hide()}),
				 	onSuccess: function(transport){
				 		
				 		if(transport.responseText=="0"){
				 			// errore di connessione
				 		}
				 		else if(transport.responseText=="-1"){
				 			// non ci sono campi disponibili
				 		}
				 		else if(transport.responseText=="-2"){
				 			// la connessione non e' mysql
				 		}
				 		else{
				 			
				 			var jsonr2=eval(transport.responseText);
				 			var select_f1='<select id="authext_nick" name="var[authext_nick]">';
				 			var select_f2='<select id="authext_passwd" name="var[authext_passwd]">';
				 			var select_f3='<select id="authext_name" name="var[authext_name]">';
				 			var select_f4='<select id="authext_surname" name="var[authext_surname]">';
				 			var select_f5='<select id="authext_mail" name="var[authext_mail]">';
				 			
				 			select_f1+='<option value="">Select a field</option>';
				 			select_f2+='<option value="">Select a field</option>';
				 			select_f3+='<option value="">Select a field</option>';
				 			select_f4+='<option value="">Select a field</option>';
				 			select_f5+='<option value="">Select a field</option>';
				 			
				 			for(var i=0;i<jsonr2.length;i++){
				 				select_f1+='<option value="'+jsonr2[i].f+'">'+jsonr2[i].f+'</option>';
				 				select_f2+='<option value="'+jsonr2[i].f+'">'+jsonr2[i].f+'</option>';
				 				select_f3+='<option value="'+jsonr2[i].f+'">'+jsonr2[i].f+'</option>';
				 				select_f4+='<option value="'+jsonr2[i].f+'">'+jsonr2[i].f+'</option>';
				 				select_f5+='<option value="'+jsonr2[i].f+'">'+jsonr2[i].f+'</option>';
				 			}
				 			select_f1+='</select>';
				 			select_f2+='</select>';
				 			select_f3+='</select>';
				 			select_f4+='</select>';
				 			select_f5+='</select>';
				 			
				 			$('authext_nick_cont').update(select_f1);
				 			$('authext_passwd_cont').update(select_f2);
				 			$('authext_name_cont').update(select_f3);
				 			$('authext_surname_cont').update(select_f4);
				 			$('authext_mail_cont').update(select_f5);
				 		}
				 	}
				});
	}
}


function check_installer(){
	
	
	var check=true;
	var check_msg='';
	
	var inputText=$$('input[type="text"]');
	
	inputText.each(function (i){ i.value=i.value.strip()});
	
	if(!test_db_conn()){
		
		check=false;
		check_msg+='Connection error in section <a href="#DBconnection">DB connection</a>, ';
		
	}
	
	if($F('authtype')!='null'){
		
		if(!test_ext(1)){
			
			check=false;
			check_msg+='Check the auth connection in section <a href="#Authentication">Authentication</a>, ';
		}
	}
	
	
	if(check){
		$('check_feed').update('');
		$('submit_button').disable();
		$('installform').submit();
	}
	else {
		$('submit_button').enable();
		$('check_feed').update(check_msg.substr(0,check_msg.length-2));
		
	}
	
}