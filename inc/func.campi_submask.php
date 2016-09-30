<?php
/**
* Libreria di funzioni per i campi delle sottomaschere. 
* Queste funzioni generano il codice per i campi delle sottomaschere 
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.campi_submask.php 1145 2015-04-24 14:57:32Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
* @todo Verificare i comportamenti sui campi in postgres
* @todo In assenza di record il primo "Nuovo" mostra l'etichetta del campo hidden
*/





/**
 * Funzione di analisi e generazione del codice per i campi delle sottomaschere
 *
 * @param int $n
 * @param string $valore
 * @param array $info
 * @param string $tipo_vista (tabella | vista)
 * @return string HTML
 */
function tipo_campo_submask($n,$valore,$info,$tipo_vista='tabella'){
	
	global $DATE_FORMAT;
	
	$FType = new FieldType();
	
	
	extract($info);
	
	if(!isset($maxsize)) $maxsize=0;

	

	// SE IMPOSTATO, sovrascrivo il tipo sovraimposto a quello di default
	
 	$data_type = ($in_tipo=='' || $in_tipo==null) ? $data_type : $in_tipo;
	

//	var_dump($data_type);
 	
	// Impostazioni del campo
	
	
	// tipo speciale TINYINT 1 (BOOLEANI)
	if($data_type=='bool' || ($data_type=='tinyint' && $column_type=='tinyint(1)')){
		
		$check=($valore) ? "checked=\"checked\"" : "";
		
		$input="<input type=\"checkbox\" onclick=\"this.value=(this.value==0 || this.value=='')?1:0; mod(this.id);\"  class=\"off ty-bool\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"0\" disabled=\"disabled\" $check />";
			   
		$riga_singola=false;
		
	}
	
	
	
	// INTEGER O DOUBLE
	elseif($FType->is_numeric($data_type)){
		
		$size=10;
		$v=$valore;
		
		$input="<input onkeypress=\"mod(this.id);\" class=\"off ty-int\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"$valore\" size=\"$size\" readonly=\"readonly\" type=\"text\" />";
	}
	
	
	
	// TESTO
	
	elseif($FType->is_shortchar($data_type)){
		
		if($maxsize>100){
			if($maxsize<=80) $size=$maxsize;
			else $size=135;
			$riga_singola=true;
		}
		else $size=59;
		
		$input="<input onkeypress=\"mod(this.id);\" class=\"off ty-short\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"$valore\" size=\"$size\" readonly=\"readonly\" type=\"text\" />";
			   
			   
		// IMPOSTAZIONI SUGGEST PER LA RICERCA -----------------------
		
		
		
		
		
		// -----------------------------------------------------------
			   
	}
	
	
	// TESTO LUNGO
	
	elseif($FType->is_longchar($data_type)){
		
		$riga_singola=true;
		
		$input="<textarea onkeypress=\"mod(this.id);\" class=\"off ty-long\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" cols=\"132\" rows=\"9\" readonly=\"readonly\" >$valore</textarea>";
	}
	
	// PASSWORD
	
	elseif($data_type=='password'){
		
		$riga_singola=false;
		
		$input="<input onkeypress=\"mod(this.id);\" class=\"off ty-passwd\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"$valore\" size=\"59\" readonly=\"readonly\" type=\"password\" />";
	}
	
	
	
	
	// tipo speciale hidden
	elseif($data_type=='hidden'){
		
		$label=false;
		
		$valore_hidden=false;
		
		// Cerca variabili nel campo hidden
		if(isset($in_default)){
			
			$valore_hidden = RegTools::variabili_campi($in_default);
			
			
			if($valore_hidden==false){
				$valore_hidden=$in_default;
			}
				
		}
		
		$input="<input name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"$valore_hidden\" type=\"hidden\" />";
	}
	// tipo speciale SELECT
	elseif($data_type=='select'){
		
		$valori=array();
		
		$input="<select onchange=\"mod(this.id);\" class=\"off ty-select\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" disabled=\"disabled\" >";
			   
		
			   
		$valori = explode("[|]",$in_default);
		
		foreach($valori as $k=>$val){
						
			// se sono stati messi i separatori chiave, valore
			if(preg_match('!=!',$val)){
				list($kk,$val)=explode("=",$val);
			}
			else $kk=$val;
			
			$sel =($kk==$valore) ? "selected=\"selected\"" : "";
			
			$input.="<option value=\"$kk\" $sel>$val</option>";
		}
		
		$input.="</select>";
		
	}
	
	// tipo speciale SELECT FROM
	elseif($data_type=='select_from'){
		
		$valori=array();
		
		$riga_singola=true;
        
        $SV = new Select_Values();
        
        $SV->set_data($in_default, true);
        $input= "<div class=\"select_values\" data-target=\"".$column_name."\" data-require=\"$SV->hash_js\">";
        $input.="<select disabled=\"disabled\" name=\"dati[$n][".$column_name."]\" id=\"dati__{$n}__{$column_name}\" class=\"toup-{$column_name}\" data-startval=\"".$valore."\"></select>";
        $input.="</div>\n";
		
	}
	//- FINE SELECT_FROM -----------------------------------------------------------------------------
	
	
	
	elseif($data_type=='date'){
		
		
		$valore= ($valore=='') ? '' : ($DATE_FORMAT=='iso') ? $valore : VFDate::date_encode($valore);
		
		$input="<input onkeypress=\"mod(this.id);\" class=\"off data\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"".$valore."\" size=\"24\" readonly=\"readonly\" type=\"text\" />";
		

		if($_SESSION['VF_VARS']['usa_calendari']==1){
		
			$carica_calendario = true;
			
			// Formato data calendarietti
			switch ($DATE_FORMAT) {
				
				case 'ita':	$formato_data="%d/%m/%Y";
				break;
			
				case 'eng':	$formato_data="%m/%d/%Y";
				break;
			
				default: $formato_data="%Y-%m-%d";
				break;
			}
			
			
			// Pulsante calendario
			
			$input.=" <img src=\"img/cal_small.gif\" id=\"trigger__{$n}__{$column_name}\"
		     style=\"cursor: pointer; vertical-align:middle;\"
		     title=\"Date selector\"
		     alt=\"Date selector\"
		     class=\"date\"
		     onmouseover=\"this.style.background='red';\"
		     onmouseout=\"this.style.background=''\" />";
					   
				$input.=<<<CAL
				
		   <script type="text/javascript">
		   
		   /* <![CDATA[ */
		    
		   Calendar.setup({
		        inputField     :    "dati__{$n}__{$column_name}",   // id of the input field
		        button	       :    "trigger__{$n}__{$column_name}",   // id of the img field
		        firstDay	   :    1,
		        ifFormat       :    "{$formato_data}",       // format of the input field
		        showsTime      :    false,
		        timeFormat     :    "24",
		        disableFunc    :    caldis,
		        onUpdate       :    catcalc
		    });    
		    
		    /* ]]> */
		
		    </script>
  
CAL;
		
		}
	
	}
	
	elseif($data_type=='datetime'){
	
		$valore= ($valore=='') ? '' : ($DATE_FORMAT=='iso') ? $valore : VFDate::date_encode($valore,true);
		
		$input="<input onkeypress=\"mod(this.id);\" class=\"off data\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"".$valore."\" size=\"24\" readonly=\"readonly\" type=\"text\" />";
		
		if($_SESSION['VF_VARS']['usa_calendari']==1){
			
			
			$carica_calendario = true;
			
			
			// Formato data calendarietti
			switch ($DATE_FORMAT) {
				
				case 'ita':	$formato_dataora="%d/%m/%Y %H:%M";
				break;
			
				case 'eng':	$formato_dataora="%m/%d/%Y %H:%M";
				break;
			
				default: $formato_dataora="%Y-%m-%d %H:%M";
				break;
			}
				   
			// Pulsante calendario	   
			$input.=" <img src=\"img/cal_small.gif\" id=\"trigger__{$n}__{$column_name}\"
		     style=\"cursor: pointer; vertical-align:middle;\" 
		     title=\"Date selector\"
		     class=\"timedate\"
		     alt=\"Date selector\"
		     onmouseover=\"this.style.background='red';\"
		     onmouseout=\"this.style.background=''\" />";
					   
			
				$input.=<<<CAL
				
		   <script type="text/javascript">
		    
		   /* <![CDATA[ */
		   
		   Calendar.setup({
		        inputField     :    "dati__{$n}__{$column_name}",   // id of the input field
		        button	       :    "trigger__{$n}__{$column_name}",   // id of the img field
		        firstDay	   :    1,
		        ifFormat       :    "{$formato_dataora}",       // format of the input field
		        showsTime      :    true,
		        timeFormat     :    "24",
		        disableFunc    :    caldis,
		        onUpdate       :    catcalc
		    });    
		    
		    /* ]]> */
		
		   </script>
			  
CAL;


		}
		
	}
	
	
	// caso onlyread
	else if($data_type=="onlyread"){
		$label=true;
		$input="<div class=\"onlyread-field-sub\" id=\"dati__{$n}__{$column_name}\">".$valore."</div>";
		
	}
	
	// Caso sconosciuto
	else {
		$size=30;
		$label=true;
		$input="<input onkeypress=\"mod(this.id);\" class=\"off ty-short\" name=\"dati[$n][".$column_name."]\" ".
			   "id=\"dati__{$n}__{$column_name}\" value=\"$valore\" size=\"$size\" readonly=\"readonly\" type=\"text\" />";
	}
	

	if($tipo_vista=='tabella'){
		return "<td>".$input."</td>";
	}
	else{
		return $input;
	}
	
	
}




?>