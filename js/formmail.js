var nuoviAllegati=0;

var testoHTML=false;


function Trim() {
	return this.replace(/\s+$|^\s+/g,'');
}

function LTrim() {
	return this.replace(/^\s+/,'');
}

function RTrim() {
	return this.replace(/\s+$/,'');
}



   
String.prototype.Trim=Trim;   
String.prototype.RTrim=RTrim;   
String.prototype.LTrim=LTrim;   


function nascondi_attach(){

	$('aggiungifile').style.display='none';
	$('allega1').style.display='';
	$('contenitore-file').style.display='none';
	
	nuoviAllegati--;

}


Ajax.Autocompleter.extract_value = 
  function (value, className) {
    var result;

    var elements = 
      document.getElementsByClassName(className, value);
    if (elements && elements.length == 1) {
      result = elements[0].innerHTML.unescapeHTML();
    }

    return result;
  };



function clona_attach_mail(){

	box=$('contenitore-file').childNodes[0];
	box2=box.cloneNode(true);
	box2.firstChild.value='';
	box.parentNode.insertBefore(box2,box.nextSibling);
	
	nuoviAllegati++;
	
}

function rimuovi_attach_mail(filediv){
	
	if(nuoviAllegati>0){
		filediv.parentNode.parentNode.removeChild(filediv.parentNode);
		nuoviAllegati--;
	}
	
}

function invia_mail(o){
	
	
	if($F('txt-dest').Trim()=='' && $F('txt-cc').Trim()=='' && $F('txt-bcc').Trim()=='' && $('sel_gruppo').value.length==0 && $F('csv')==0){
	
		alert('Si sta cencando di spedire una mail senza alcun destinatario');
		return false;
		
	}
	else{
		
		var oEditor = FCKeditorAPI.GetInstance('txtCorpoHtml') ;
		
		var HTML = oEditor.GetXHTML();
		
	
		if($F('txt-oggetto').Trim()=='' && ($F('txt-corpo').Trim()=='' && HTML.Trim()=='')){
		
			alert('Si sta cencando di spedire una mail senza oggetto e senza corpo del testo');
			return false;
		}
		
		else if($F('txt-oggetto').Trim()==''){
			if(!confirm('Si sta spedendo una mail senza oggetto, si vuole procedere?')){
			
				return false;
			}
		}
		else if($F('txt-corpo').Trim()=='' && HTML.Trim()==''){
			if(!confirm('Si sta spedendo una mail senza testo, si vuole procedere?')){
			
				return false;
			}
		}
	
		
	}

	var idProcedura= $F('id_procedura');
	
	o.disabled=true;
	
	if($('invii_multipli_1').checked==true){
	
		winref = window.open('marmail_popup.php?id=' +idProcedura ,'stato_mail', 'toolbar=no,location=no,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=400,height=350');
		
		winref.focus();
	}
		
	$('form1').submit();

}



function switch_tipo_txt(){

	
	var oEditor = FCKeditorAPI.GetInstance('txtCorpoHtml') ;
	
	oEditor.EditorDocument.body.style.backgroundColor='#FFFFFF';
	
	oEditor.EditorDocument.body.contentEditable = true;
	
	var TXT=$F('txt-corpo');
	
	var HTML = oEditor.GetXHTML();
	
	
	if(testoHTML){
	
		if(TXT.Trim()!=''){
			if(!confirm('Attenzione! Alcune formattazioni di testo avanzato potrebbero andare perdute. Continuare?')){ 
				return false;
			}
		}
		
		$('txt-corpo').style.display='';
		$('txt-corpo-html0').style.display='none';
		
		$('txt-corpo').value=html2txt(HTML);
		
		$('link_switch').innerHTML='&raquo; Formattazione avanzata';
		$('testo_html').value=0;
		
	}
	else{
	
		$('txt-corpo').style.display='none';
		$('txt-corpo-html0').style.display='';
		
		oEditor.SetHTML(txt2html(TXT));
		
		$('link_switch').innerHTML='&raquo; Testo semplice';
		
		$('testo_html').value=1;
	}
	
	testoHTML=!testoHTML;
	
}


function html2txt(Html){

	// converti i BR e i P
	var Txt= Html.replace(/<br ?\/?>|<\/?p>|<\/li>/ig,'\\n');
	
	// togli tags
	var Txt2= Txt.replace(/<\/?[^>]+>/g,'');
	
	// converti entità semplici
	Txt2=Txt2.replace('&nbsp;',' ');
	Txt2=Txt2.replace('&amp;','&');
	Txt2=Txt2.replace('&agrave;','à');
	Txt2=Txt2.replace('&egrave;','è');
	Txt2=Txt2.replace('&eacute','é');
	Txt2=Txt2.replace('&igrave;','ì');
	Txt2=Txt2.replace('&ugrave;','ù');
	Txt2=Txt2.replace('&ograve;','ò');
	
	return Txt2;
}

function txt2html(Txt){

	// converti entità semplici
	Txt=Txt.replace('&','&amp;');
	Txt=Txt.replace('à','&agrave;');
	Txt=Txt.replace('è','&egrave;');
	Txt=Txt.replace('à','&eacute');
	Txt=Txt.replace('ì','&igrave;');
	Txt=Txt.replace('ù','&ugrave;');
	Txt=Txt.replace('ò','&ograve;');

	return Txt.replace(/\\n/g,'<br \/>');
}