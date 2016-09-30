// javascript

function mostra_nascondi(id){

	
	if(document.getElementById(id).style.display==''){
		document.getElementById(id).style.display='none';
	}
	
	else if(document.getElementById(id).style.display=='none'){
		document.getElementById(id).style.display='';
	}

}

function mostra(obj){
	document.getElementById(obj).style.display='';
}

function nascondi(obj){
	document.getElementById(obj).style.display='none';
}