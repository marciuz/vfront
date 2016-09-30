// javascript


function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLhttp");
    }else{
        ro = new XMLHttpRequest();
    }
    return ro;
}


//	var ID = 0;

	var http = createRequestObject();
	
	var esiti=0;

	

function try_query(SQL,id0) {
	
	

	
	ID=id0;
	
	s = new String(SQL);
	SQL=s.replace(/\n/g," ");
	
    http.open('get', 'test_query.php?sql='+SQL+'&hash='+Math.random());
    http.onreadystatechange = handleResponse;
    http.send(null);
}


function handleResponse(){
	if(http.readyState == 4){
		
	 	var esito_sql = http.responseText;
	 	
	 	if(esito_sql==1){
	 		
	 		document.getElementById('feed_altro_'+ID).innerHTML="<span class=\"verde\">" + _('The query was successful')+ "</span>";
	 	}
	 	
	 	else if(esito_sql==-1){
	 		
	 		document.getElementById('feed_altro_'+ID).innerHTML="<span class=\"rosso\">"+ _('The SQL query contains unsafe words and was not performed')+"</span>";
	 	}
	 	else{
	 		
	 		document.getElementById('feed_altro_'+ID).innerHTML="<span class=\"rosso\">"+ _('Query error')+"</span>";
	 	}
	 }

}



function try_query_all(SQL) {
	
	s = new String(SQL);
	SQL=s.replace(/\n/g," ");
	
    http.open('get', 'test_query.php?sql='+SQL+'&hash='+Math.random());
    http.onreadystatechange = handleResponseAll;
    http.send(null);
}


function handleResponseAll(){
	if(http.readyState == 4){
		
	 	if(http.responseText==1){
	 		alert(esiti);
	 		esiti++;
	 		
	 	}
	 }

}


function test_campi(){
	
	// prendi le textarea
	/*
	var textareas = document.getElementsByTagName('textarea');
	
	var num_textarea=0;
	
	var postData='';
	
	risposta='-1';
	
	for(var i=0;i<textareas.length;i++){
	
	
		if(textareas[i].name.substring(0,5)=="campo" && textareas[i].disabled!=true){
			postData+='sql[]='+textareas[i].value+'&';
			
			num_textarea++;
		}
	}
	
	x='';
	
	var myAjax = new Ajax.Request(
			'test_query_all.php', 
			{
				method: 'post', 
				parameters: postData, 
				asynchronous: false
			});
					
	return myAjax.transport.responseText;*/
}


