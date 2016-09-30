// javascript

function clona_link(){

	box=document.getElementById('contenitore-link');
	box2=box.cloneNode(true);
	box2.firstChild.childNodes[0].value='http://';
	box.parentNode.insertBefore(box2,box.nextSibling);
	
	nuoviLink++;
	
}

function rimuovi_link(filediv){
	
	if(nuoviLink>0){
		filediv.parentNode.parentNode.removeChild(filediv.parentNode);
		nuoviLink--;
	}
	
}