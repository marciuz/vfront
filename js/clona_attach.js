// javascript

function clona_attach(){

	box=document.getElementById('contenitore-file');
	box2=box.cloneNode(true);
	box2.firstChild.childNodes[0].value='';
	box.parentNode.insertBefore(box2,box.nextSibling);
	
	nuoviAllegati++;
	
}

function rimuovi_attach(filediv){
	
	if(nuoviAllegati>0){
		filediv.parentNode.parentNode.removeChild(filediv.parentNode);
		nuoviAllegati--;
	}
	
}