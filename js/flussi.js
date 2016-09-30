// javascript

function add_tendina_fl(){

	box=document.getElementById('modello');
	box2=box.cloneNode(true);
	box.parentNode.insertBefore(box2,box.nextSibling);
	t2=box2.parentNode.parentNode.getElementsByTagName('select')[1];
	t2.options.length=0;
	
}



function del_tendina_fl(obj){

//	  obj.parentNode.parentNode.removeChild(obj.parentNode.getElementsByTagName('li')[0]);

	lis=obj.parentNode.parentNode.getElementsByTagName('li');
	
	if(lis.length>1){
		obj.parentNode.parentNode.removeChild(obj.parentNode);
		
	}
	else{
		alert(_('Why do you want to delete the last drop down menu?'));
	}
}


function scegli_campi_tab(o){
	
	alert(o.value);
	t2=o.parentNode.parentNode.getElementsByTagName('select')[1];
	for(i=0;i<campi[o.value].length;i++){
		t2[i]=new Option(campi[o.value][i]);
	}
	
	t2.style.display='';
	
}