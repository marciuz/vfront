

/*

PASSO 2

*/

function colora_riga(id){
			
	if($('i'+id).value!=''){
		
		$$('.c'+id).each( 
			function(o){ 
				if($('prima_riga').checked){
					if(o.parentNode.className!='r1'){
						o.style.backgroundColor='#FFCC00';
					}
				}
				else{
					o.style.backgroundColor='#FFCC00';
				}
				
		
			});
	}
	else{
		$$('.c'+id).each( function(o){ o.style.backgroundColor='#FFFFFF';});
	}
}
		
function colora_prima_riga(){
	
	var colore = ($('prima_riga').checked) ? '#CCC':'#FFF';
	
	$$('tr.r1 td').each(function(t){ t.style.backgroundColor=colore; });
}



/*

PASSO 3

*/



var id_mod=0;
		
function modificatore(id,o){
	
	var sell='<select name="mod['+id+'][]" onchange="mostra_pref(this);" >';
	sell+='<option value="upper">to_upper</option>';
	sell+='<option value="lower">to_lower</option>';
	sell+='<option value="upperfirst">upperfirst</option>';
	sell+='<option value="upperword">upperword</option>';
	sell+='<option value="md5">genera md5</option>';
	sell+='<option value="sha1">genera sha1</option>';
	sell+='<option value="prefisso">imposta prefisso</option>';
	sell+='</select><input type="text" name="pref['+id+']" id="pref_'+id+'" style="display:none" /> <span class="fakelink small" onclick="elimina_tendina('+id+',this);">elimina<br /></span>';
	
	new Insertion.Before(o, sell);


}

function elimina_tendina(id,oo){
	
	$(oo).previous(0).remove();
	$(oo).previous(0).replace('');
	$(oo).remove();
}


function mostra_pref(oo){
	
	if(oo.value=='prefisso'){
		
		$(oo).next(0).show();
	}
	else{
		$(oo).next(0).hide();
	}
}

function set_costante(id){
	
	$('costante_'+id).down(0).enable();
	$('costante_'+id).show();
	$('costante_'+id+'_trigger').show();
}

function unset_costante(id){
	
	$('costante_'+id).down(0).disable();
	$('costante_'+id).hide();
	$('costante_'+id+'_trigger').hide();
}



/* 

PASSO ESECUZIONE

*/
