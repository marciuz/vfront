// javascript
var PARENT_WINDOW=(window.opener) ? window.opener : window.parent;
var reg_json_loaded={};

function annulla_colori(){
	
	for(var i=0;i<n_righe;i++){
		
		var id_riga='riga_'+i;
	
		if(tipo_vista=='tabella'){
			$(id_riga).className='riga-annulla';
		}
				
	}
}



function date_encode(mydate){
	
	if(dateEncode=='iso') return mydate;
	
	var tk0=mydate.split(' ');
	
	var d0=tk0[0].split('-');
	
	if(d0.length!=3) return mydate;
	
	var ora0=(tk0.length==2) ? ' '+tk0[1] : '';
	
	switch(dateEncode){
		
		case 'ita': return d0[2]+"/"+d0[1]+"/"+d0[0]+ora0;
		break;
			
		case 'eng': return d0[1]+"/"+d0[2]+"/"+d0[0]+ora0;
		break;
	}
	
}



function date_decode(mydate){
	
	if(dateEncode=='iso') return mydate;
	
	var tk0=mydate.split(' ');
	
	var d0=tk0[0].split('/');
	
	if(d0.length!=3) return mydate;
	
	var ora0=(tk0.length==2) ? ' '+tk0[1] : '';
	
	switch(dateEncode){
		
		case 'ita': return d0[2]+"-"+d0[1]+"-"+d0[0]+ora0;
		break;
			
		case 'eng': return d0[2]+"-"+d0[0]+"-"+d0[1]+ora0;
		break;
	}
	
}


function modifica(riga){
	
	annulla_colori();
	
	$('p_annulla').disabled=false;
	
	righe_mod[righe_mod.length]=riga;
	
	abilita_campi(riga);
	
}

function blocca(){
	
	for(var i=0;i<n_righe;i++){
		
		disabilita_campi(i);
				
	}
}


function abilita_campi(id_riga){
	
	tr = document.getElementById('riga_'+id_riga);
	
	if(tipo_vista=='tabella'){
		tr.className='riga-modifica';
	}
	else{
		
		tr.className='divs-modifica';
	}
	
	array_input=tr.getElementsByTagName('input');
	
	for(var x=0;x<array_input.length;x++){

		if(array_input[x].type=='checkbox'){
			
			array_input[x].disabled='';
		}
		else{
			array_input[x].readOnly=false;
		}
		
		
	}
	
	array_select=tr.getElementsByTagName('select');
		
	for(var x=0;x<array_select.length;x++){
		array_select[x].disabled=false;
	}
	
	array_textarea=tr.getElementsByTagName('textarea');
		
	for(var x=0;x<array_textarea.length;x++){
		array_textarea[x].readOnly=false;
	}
}



function disabilita_campi(id_riga){
	
	tr = document.getElementById('riga_'+id_riga);
	
	
	if(tipo_vista=='tabella'){
		tr.className='riga-annulla';
	}
	else{
		
		tr.className='entry-record';
	}
	
	array_input=tr.getElementsByTagName('input');
	
	for(var x=0;x<array_input.length;x++){

		array_input[x].readOnly=true;
	}
	
	array_select=tr.getElementsByTagName('select');
		
	for(var x=0;x<array_select.length;x++){
		array_select[x].disabled=true;
	}
	
	array_textarea=tr.getElementsByTagName('textarea');
		
	for(var x=0;x<array_textarea.length;x++){
		array_textarea[x].readOnly=true;
	}
}

/**
 * Add id to mod field list
 * @param string id
 * @returns void
 */
function mod(id){
	
	modifiche_attive=true;
	$('p_save').disabled=false;
	
	$('p_annulla').disabled=false;
	
	trovato=false;
	
	// debug
	for(t=0;t<campi_mod.length;t++){
		if(campi_mod[t]==id){
			trovato=true;
		}
	}
	
	if(!trovato){
		campi_mod[campi_mod.length]=id;
	}
}


function annulla(){
	
	window.location=window.location;
}



function carica_valore(id,valore){
	
	
	var IdFrame = 'dati__'+id;
//	alert(IdFrame);
	document.getElementById(IdFrame).value=valore;
//	alert(IdFrame+'='+valore);
}


function nuovo_record(){
	
	if(n_righe<max_righe){
		
		/*riga_attuale = 'riga_'+n_righe;
		
		$(riga_attuale).style.display='';*/
		
		duplica_riga();
		
		modifiche_attive=true;
		$('p_save').disabled=false;
		$('p_annulla').disabled=false;
		
		abilita_campi(n_righe);
		
		
		n_righe++;
	}
	else{
		
		alert(_('The maximum number of records for this subform is set by the administrator to')+' '+max_righe+'.\n'+_('Can\'t add record'));
	}
	
}


function debug_var(){
	
	txt="n_righe: "+n_righe+"\n";
	txt+="max_righe: "+max_righe+"\n";
	txt+="modifiche_attive: "+modifiche_attive+"\n";
	txt+="eliminazione_attiva: "+eliminazione_attiva+"\n";
	txt+="oid_parent: "+oid_parent+"\n";
	txt+="id_submask: "+id_submask+"\n";
	txt+="pk_parent: "+pk_parent+"\n";
	
	valori_debug_campi_mod='';
	valori_debug_valori_del='';
	valori_debug = '';
	valori_righe_debug='';
	
	for(i=0;i<campi_mod.length;i++){
		
//		for(k in campi_mod[i]){
//			val_campi_mod+=campi_mod[i][k]+', ';
	//alert(k);
//		}
		
		valori_debug_campi_mod+=campi_mod[i]+', ';
		
		if(campi_mod[i]!=''){
			
			valori_debug+=$(campi_mod[i]).value + ',';
		}
	}
	
	
	for(i=0;i<righe_mod.length;i++){
		
		valori_righe_debug+=righe_mod[i] + ',';
	}
	
	txt+="righe_mod: "+valori_righe_debug+"\n";
	txt+="campi_mod: "+valori_debug_campi_mod+"\n";
	txt+="campi_mod valori: "+valori_debug+"\n";
	txt+="n record da eliminare: "+valori_del.length+"\n";
	
	for(e=0;e<valori_del.length;e++){
		valori_debug_valori_del+=valori_del[e]+', ';
	}
	txt+="record da eliminare (ID PK dipendente): "+valori_debug_valori_del+"\n";
	alert(txt);
	
	
	
	
}


function salva(nome_tab){
	
	if(modifiche_attive){
		sndReqSubUpdate(nome_tab);
	}
	
	if(eliminazione_attiva){
		sndReqSubDelete(nome_tab,valori_del);
	}
	
	
}


   function setStatus(messaggio,tempo,classe) {
      $('feedback').style.visibility = "visible";
      $('risposta').innerHTML = messaggio;
      $('risposta').className = classe;
      setTimeout( "$('feedback').style.visibility = 'hidden'; ", tempo );
   }

   
    function setStatusAndReload(messaggio,tempo,classe) {
      $('feedback').style.visibility = "visible";
      $('risposta').innerHTML = messaggio;
      $('risposta').className = classe;
      setTimeout( "$('feedback').style.visibility = 'hidden'; annulla(); ", tempo );
   }

/* 

#################################################################

		-- funzioni Rpc --

#################################################################

*/

function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        ro = new XMLHttpRequest();
    }
    return ro;
}





var http = createRequestObject();
var http2 = createRequestObject();



function sndReqSubUpdate(action) {
		
	
    http.open('POST', 'rpc/rpc_sub.php?post=update&action='+action, true);
    http.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    http.onreadystatechange = handleResponseSubPostUpdate;


    var post_string='';
    
	hidd = $$('input[type="hidden"]');
	
	for(i=0;i<campi_mod.length;i++){
		
		valore_post_s= ($(campi_mod[i]).hasClassName('data')) ? date_decode($F(campi_mod[i])) : $F(campi_mod[i]);
		
			post_string += $(campi_mod[i]).name + "=";
			post_string +=  encodeURIComponent(valore_post_s)+ "&";
			
			n_row_ar = campi_mod[i].split('__');
			
			
			for(j=0;j<hidd.length;j++){
				
				test = hidd[j].id.split('__');
				
				if(test[1]==n_row_ar[1]){
			
					// add hiddens
					post_string += $(hidd[j]).name + "=";
					post_string +=  encodeURIComponent($F(hidd[j]))+ "&";
				}
			
			}
			
	}
	
	
	
	
	// aggiungo la PK indipendente
	post_string +="campo_pk_indip="+campo_pk_indipendente;
	post_string +="&valore_pk_indip="+pk_parent; 
//	post_string +="&campo_pk_dip="+campo_pk_dipendente; 
	
	
	for(k=0;k<righe_mod.length;k++){
	
		hash=document.getElementById('value_riga_'+righe_mod[k]).innerHTML;
		
		post_string +="&hash["+righe_mod[k]+"]="+ hash;
		
		// add hidden values
		
	}
	
    http.send(post_string);
}


function handleResponseSubPostUpdate(){
	 if(http.readyState == 4){
	 	var risposta_sql = http.responseText;
	 	
	 	if(risposta_sql==1){
	 		
			campi_mod=new Array();
			modifiche_attive=false;
	 		
	 		if(eliminazione_attiva==false){
				$('p_save').disabled=true;
				$('p_annulla').disabled=true;
				blocca();
				setStatusAndReload(_('Record(s) updated correctly'),800,'risposta-giallo');
			}
			else{
				
				setStatusAndReload(_('Record(s) updated correctly'),800,'risposta-giallo');
			}

			
			
			


		}
		else if(risposta_sql==2){
			setStatusAndReload(_('Some records changed correctly'),800,'risposta-giallo');
			campi_mod=new Array();
		}
		else{
			setStatusAndReload(_('Error updating record'),63500,'risposta-arancio');
			campi_mod=new Array();
		}
		
		
		 // reinizializzo l'oggetto request
//	 	http = createRequestObject();

		// aggiornamento della tabella padre
		PARENT_WINDOW.richiediSUB();

		var sm_embed= PARENT_WINDOW.VF.sm_embed;

		// aggiornamento della tabella padre
		if(sm_embed.length>0){
			for(var h=0; h<sm_embed.length; h++){

				PARENT_WINDOW.richiediEMBED(VF.sm_embed[h]);
			}		
		}

	 }
	 
	
}


function elimina(n_riga_del){
	
	valori_del[valori_del.length]=n_riga_del;
	
	var nome_riga = 'riga_'+n_riga_del;
	
	
	eliminazione_attiva=true;
	$('p_save').disabled=false;
	
	
	
	$('p_annulla').disabled=false;
	
	$(nome_riga).style.display='none';
	
	n_righe--;

	
}



function sndReqSubDelete(action,valori_pk_dip_del) {
		
	
    http2.open('post', 'rpc/rpc_sub.php?post=delete&action='+action, true);
    http2.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    http2.onreadystatechange = handleResponseSubPostDelete;


    var post_string='';
    
	// aggiungo la PK indipendente
	/*post_string +="campo_pk_indip="+campo_pk_indipendente;
	post_string +="&valore_pk_indip="+pk_parent; 
	post_string +="&campo_pk_dip="+campo_pk_dipendente; */
	
	for(var d=0;d<valori_del.length;d++){
		
		var hash=$('value_riga_'+valori_del[d]).innerHTML;
		
		post_string +="&hash[]="+hash; 
	}
    
	http2.send(post_string);
}

function handleResponseSubPostDelete(){
	
	
	 if(http2.readyState == 4){
	 	var risposta_sql = http2.responseText;

	 	
	 	if(risposta_sql==1){
	 		
			valori_del= new Array();
			eliminazione_attiva=false;			
//			$('numero_record').innerHTML=n_righe;
			
			setStatusAndReload(_('Record(s) deleted correctly'),800,'risposta-giallo');
			
			
		}
		else{
			setStatusAndReload(_('Error deleting records'),3500,'risposta-arancio');
		}
		
		// aggiornamento della tabella padre
		PARENT_WINDOW.richiediSUB();


		var sm_embed=PARENT_WINDOW.sm_embed;

		// aggiornamento della tabella padre
		if(sm_embed.length>0){
			for(var h=0; h<sm_embed.length; h++){

				PARENT_WINDOW.richiediEMBED(sm_embed[h]);
			}		
		}
	 }
}

function caldis(cal){
	
//	return (modifiche_attive) ? false:true;
}

function catcalc(cal) {

		mod(cal.params.inputField.id);
}



function duplica_riga(){
	
	if(n_righe==0){
		 
		TR=document.getElementById('riga_x');
	}
	else{

		TR=document.getElementById('riga_'+(n_righe-1));
	}
	
	 TR2=TR.cloneNode(true);
	TR2.id='riga_'+n_righe;
	TR2.style.display='';
	
	// elimino l'iframe... risparmio tempo e problemi.
	iframeTR2 = TR2.getElementsByTagName('iframe');
	for(i=0;i<iframeTR2.length;i++){
		iframeTR2[i].src='iframe.html';
	}
	
	
	// Inserisco la riga
	TR.parentNode.insertBefore(TR2,TR.nextSibling);
	
	
	if(tipo_vista=='tabella'){
	
		// cancello le eventuali etichette modifica ed elimina
		 TH2=TR2.getElementsByTagName('th');
		for(i=0;i<TH2.length;i++){ TH2[i].innerHTML=''}
		
		// opero sulle celle
		 TD2=TR2.getElementsByTagName('td');
		 
		 for(i=0;i<TD2.length;i++){ 
	
		 	var nodoTD2 = TD2[i].firstChild;
		 	rinumera_nodo(nodoTD2);
		 
		 }
		 abilita_campi(n_righe);
	}
	else{
		
		
		// PRENDI IL NUMERONE ED INCREMENTA
		var TR0=TR2.getElementsByTagName('td');
		var tdNumerone=TR0[0];
		
		if (isNaN(tdNumerone.innerHTML - 0)){
			tdNumerone.innerHTML=1;
		}else{
			tdNumerone.innerHTML=(tdNumerone.innerHTML - 0) +1;
		}
		
		
		// cancello le eventuali etichette modifica ed elimina e gli span onlyread
		 TH2=TR2.getElementsByTagName('span');
		for(i=0;i<TH2.length;i++){ TH2[i].innerHTML='&nbsp;'}
		
				
		// opero sulle celle
		 var sub_input=TR2.getElementsByTagName('input');
		 var sub_select=TR2.getElementsByTagName('select');
		 var sub_textarea=TR2.getElementsByTagName('textarea');
		 
		 for(var i=0;i<sub_input.length;i++){ 
	
		 	rinumera_nodo(sub_input[i]);
		 }
		 
		 for(var i=0;i<sub_select.length;i++){ 
	
		 	rinumera_nodo(sub_select[i]);
		 }
		 
		 for(var i=0;i<sub_textarea.length;i++){ 
			
		 	rinumera_nodo(sub_textarea[i]);
		 }
		 
		 var img_calendar=TR2.getElementsByTagName('img');
		 for(var i=0;i<img_calendar.length;i++){ 
			
		 	rinumera_nodo(img_calendar[i]);
		 }
		 
		 abilita_campi(n_righe);
		
	}
	 
	 
	 
}

function rinumera_nodo(nodo){
	
	// prendi gli input
	if(nodo.nodeName=='INPUT' || nodo.nodeName=='SELECT'){
		
		nodo.value='';
		
		var nodoConsiderato=nodo;
		
	}
	else if(nodo.nodeName=='TEXTAREA'){
		
		nodo.innerHTML='';
		
		var nodoConsiderato=nodo;
		
	}
	else if(nodo.nodeName=='IMG'){
		
		var nodoConsiderato=nodo;
		
	}
	// dento i div a volte ci sono tendine, a volte (l'ultimo c'� l'HASH in base64)
	else if(nodo.nodeName=='DIV'){
		
		
		var selectInDiv=nodo.getElementsByTagName('select');
		
		if(selectInDiv.length>0){
			for(p=0;p<selectInDiv.length;p++){
				
				if(selectInDiv[p].nodeName=='SELECT'){
					
					var nodoConsiderato=selectInDiv[p];
					nodoConsiderato.value='';
				}
			}
			
		}
		else {
			// Caso HASH... lo cancello
			var nodoConsiderato=false;
			nodo.id='value_riga_'+n_righe;
			nodo.innerHTML='';
			
		}
	}
	
	
	
	if(nodoConsiderato!=false){
	
		var re=/(dati|trigger)__[0-9x]+__([a-z0-9_]+)/ig
		
		
		s1 = new String(nodoConsiderato.id);
		
		nuovoID= s1.replace(re,function(match,sub1,sub2){
			return sub1+'__'+n_righe + '__' + sub2;
		});
		
		s2 = new String(nodoConsiderato.id);
		nuovoName= s2.replace(re,function(match,sub1,sub2){
			return 'dati['+n_righe+']['+sub2+']';
		});
		
		
		nodoConsiderato.id=nuovoID;
		
		if(nodoConsiderato.nodeName=='IMG'){
			var type_time=(nodo.className=='timedate') ? true:false;
			
			var column_name=re.exec(s2);
			
			// attiva un nuovo calendario
			makeCalendar(n_righe,column_name[2],type_time);
		}
		else{
			
			nodoConsiderato.name=nuovoName;
		}
	}
	
	
}


function erroreDBNum(n){
	
	n=n-0;
	
	// Codici Errori di Postgres
	if(PGdb){
		
		if(n==1451){
			return _('Can not delete the record <br/> there are related records');
		}
		else if(n==23505){
			return _('Can\'t add record - Duplicate key');
		}	
		else if(n==23503){
			return _('Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata');
		}	
		else if(n==1345){
			return _('Can not delete from join view');
		}
		else{
			return _('Can not do this <br/> (Error Code:')+n+')';
		}
	}
	// Codici Errori di MYSQL
	else{
		
		if(n==1451){
			return _('Can not delete the record <br/> there are related records');
		}
		else if(n==1022){
			return _('Can\'t add record - Duplicate key');
		}	
		else if(n==1452){
			return _('Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata');
		}	
		else if(n==1345){
			return _('Can not delete from join view');
		}
		else{
			return _('Can not do this <br/> (Error Code:')+n+')';
		}
	}
	
}


function makeCalendar(n,column_name,show_time){
	
	if(dateEncode=='ita'){
		var date_format_str='%d/%m/%Y';
	}
	else if(dateEncode=='eng'){ 
		var date_format_str='%m/%d/%Y';
	}
	else{
		var date_format_str='%Y-%m-%d';
	}
	
	if(show_time){
		date_format_str+=' %H:%M';
	}
	
	
	
	 Calendar.setup({
        inputField     :    "dati__"+ n + "__" + column_name ,   // id of the input field
        button	       :    "trigger__" + n + "__" + column_name ,   // id of the img field
        firstDay	   :    1,
        ifFormat       :    date_format_str, // format of the input field
        showsTime      :    show_time,
        timeFormat     :    "24",
        disableFunc    :    caldis,
        onUpdate       :    catcalc
    });   
}

jQuery(document).ready( function (){
    
    jQuery('.select_values').each(function(i, el){
        var hash_js = jQuery(el).data('require');
        var target = jQuery(el).data('target');
        
        if(reg_json_loaded[hash_js]!==undefined){
            return null;
        }
        
        jQuery.getJSON( basePath+'/files/html/'+hash_js+'.json', function (data){
            
            jQuery('.toup-'+target).each( function(j, subel){
                // Blank value
                jQuery(subel).append(jQuery('<option>', { value: '', text : '' }));
                // values
                for(var i=0;i<data.length;i++){
                    jQuery(subel).append(jQuery('<option>', { 
                        value: data[i][0],
                        text : data[i][1]
                    }));
                }
                // Set value
                jQuery(subel).val(jQuery(subel).data('startval'));
            });
            
        });
        jQuery('#feed_'+target).hide();
        reg_json_loaded[hash_js]=1;
    });
    
    
    
});

jQuery(document).on('change', 'select', function(){ 
    mod(this.id); 
});

jQuery(document).on('paste', 'input,textarea', function(){ 
    mod(this.id); 
});