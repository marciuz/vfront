<?php
/**
* Libreria di funzioni per la gestione dei campi in area di amministrazione. 
* Si tratta delle funzioni che generano le scelte possibili per le impostazioni dei campi,
* viste le caratteristiche dei campi definiti in database.
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.tratta_campo.php 1126 2014-12-17 10:52:10Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


/**
 * Utility che imposta se un option di una select debba essere selezionato
 *
 * @param string $tipo Valore dell'option nella tendina
 * @param string $def Valore definito
 * @return string 
 */
function selected_def($tipo,$def){
	
	return ($tipo==$def) ? "selected=\"selected\"" : "";	
}


/**
 * Funzione che attribuisce i tipi possibili di campo per una tabella
 * E' utilizzata nell'amministrazione dei registri/gruppi
 *
 * @param array $ar_campo
 * @param array $pk Campo/i chiave/i primaria
 * @param array $fk Campo/i chiave esterna
 * @param string $colref_fk Colonne di riferimento nella relazione delle chiavi esterne
 * @return void
 */
function tratta_campo($ar_campo,$pk,$fk,$colref_fk=array()){
	
	global  $vmsql, $vmreg,$gid;
	
	
	// estrai i valori come variabili
	extract($ar_campo);
	
	

	/*
	id_reg
	table_oid
	column_name
	column_default
	is_nullable
	data_type
	character_maximum_length
	column_type
	extra
	in_tipo
	in_default
	in_visibile
	in_richiesto
	alias_frontend
	commento
    */
	
	
	// caratteristiche di default per la visibilità
	$campo_visibile=true;
	$campo_visibile_disabled=false;
	$select_tipo_disable = false;
	$column_default = (strlen($column_default)>0) ? $column_default : "<em class=\"na\">"._("not specified")."</em>";
	$max_length= (preg_match('!char!',$data_type)) ? "(".$character_maximum_length ." "._("characters").")" : "";
	
	// Impostazioni campo obbligatorio
	if(is_null($in_richiesto) || $in_richiesto==""){
		$in_richiesto = (trim($is_nullable)=="NO") ? true:false;
		// se l'impostazione deriva dal database ed � richiesto imponi la scelta
		$campo_richiesto_disabled= $in_richiesto;
	}
	else{
		$in_richiesto = Common::is_true($in_richiesto);
		// se l'impostazione deriva del l'utente lascia la scelta
		$campo_richiesto_disabled= false;
	}
	
	
	// Impostazioni campo visibile
	$campo_visibile= Common::is_true($in_visibile);
	
	
	// Impostazioni del campo suggest
	$campo_suggest= Common::is_true($in_suggest);

	
	
	// Impostazioni del campo suggest
	$campo_in_table= Common::is_true($in_table);
	
	
	// A seconda del tipo di campo fa delle proposte:
	
	$sel=array_flip(array('int','float','double','char','password','text','mediumtext','date','datetime','bool','bit','hidden','select','select_from','select_auto','onlyread', 'geometry'));
	

    $options='';

	switch (strtolower($data_type)){
		
		case 'tinyint'  :
		case 'smallint' : $options = (preg_match("!tinyint\(1\)!i",$column_type)) ? "\t\t\t<option value=\"bool\" ".selected_def('bool',$in_tipo).">"._("boolean (true|false)")."</option>
									<option value=\"int\" ".selected_def('int',$in_tipo).">"._("integer number")."</option>\n" 
									:
									"\t\t\t<option value=\"int\" ".selected_def('int',$in_tipo).">"._("integer number")."</option>\n" ;
		break;
		case 'int' : 	
		case 'integer' : 
		case 'number' :
		case 'mediumint' : 
		case 'biginteger' :
		case 'bigint' :	
							$options = "\t\t\t<option value=\"int\" ".selected_def('int',$in_tipo).">"._("integer number")."</option>\n";
		break;
		
		case 'float' : 
		case 'double':		
		case 'double precision':		
		case 'real':		
		case 'numeric':		
		case 'decimal':		
		case 'money':		
							$options = "\t\t\t<option value=\"float\" ".selected_def('float',$in_tipo).">"._("floating point number")."</option>\n";
		break;
		
		case 'varchar' : 
		case 'varchar2' :
		case 'char' : 	
		case 'character' : 	
		case 'character varying' : 	
		case 'varbinary' : 	
		case 'bpchar' : 	$options = "\t\t\t<option value=\"char\" ".selected_def('char',$in_tipo).">"._("free tiny text")."</option>\n";
							$options.= "\t\t\t<option value=\"password\" ".selected_def('password',$in_tipo).">"._("password")."</option>\n";
        
        // break; <- continue
		
		case 'varchar2' : 
		case 'mediumtext' : 
		case 'blob' : 
		case 'longtext' :
		case 'text' :		$options.= "\t\t\t<option value=\"text\" ".selected_def('text',$in_tipo).">"._("free long text")."</option>
								  <option value=\"char\" ".selected_def('char',$in_tipo).">"._("free tiny text")."</option>
								  <option value=\"richtext\" ".selected_def('richtext',$in_tipo).">"._("HTML formatted text")."</option>
								  \n";
		break;
		
		case 'date' :		$options = "\t\t\t<option value=\"date\" ".selected_def('date',$in_tipo).">"._("formatted date")."</option>\n";
		break;
		
		case 'datetime' : 
		case 'timestamp without time zone' : 
		case 'timestamp' : 			$options = "\t\t\t<option value=\"datetime\" ".selected_def('datetime',$in_tipo).">"._("formatted date and time")."</option>
									<option value=\"date\" ".selected_def('date',$in_tipo).">"._("formatted date")."</option>\n";
		break;
		
		case 'time' : $options = "\t\t\t<option value=\"time\" ".selected_def('time',$in_tipo).">"._("formatted time")."</option>\n";
		break;
		
		case 'bool' : 
		case 'boolean' : 
		case 'bit' : $options = "\t\t\t<option value=\"bool\" ".selected_def('bool',$in_tipo).">"._("boolean (true|false)")."</option>\n";
		break;
		
		case 'enum':  $options = "\t\t\t<option value=\"select_enum\" ".selected_def('select_enum',$in_tipo).">"._("Default list")."</option>\n";
		break;
		
		default: $options="";
	}
    
    // Speciale campo geometry, che non viene riconosciuto nel data_type,
    // ma nel column_type
    
    if($column_type == 'geometry'){
            
        $options= "\t\t\t<option value=\"geometry\" ".selected_def('geometry',$in_tipo).">"._("geometry")."</option>\n";
    }
	
	
	$aggiunta_options="
				<option value=\"hidden\" ".selected_def('hidden',$in_tipo).">"._("hidden")."</option>
				<option value=\"select\" ".selected_def('select',$in_tipo).">"._("defined values")."</option>
				<option value=\"select_from\" ".selected_def('select_from',$in_tipo).">"._("values defined by table")."</option>
				<option value=\"autocompleter_from\" ".selected_def('autocompleter_from',$in_tipo).">"._("autocomplete from table")."</option>\n";
	
	$aggiunta_options.="\t\t\t\t<option value=\"onlyread\" ".selected_def('onlyread',$in_tipo).">"._("read only")."</option>\n";
	$aggiunta_options.="\t\t\t\t<option value=\"onlyread-multi\" ".selected_def('onlyread-multi',$in_tipo).">"._("read only (multiline)")."</option>\n";

	
	
	
	
	

					   
	/* Impostazioni nel caso di valore autoincrement:
	Imposta il campo come bloccato	ed invisibile in fase di insert	*/
	if($extra=="auto_increment"){
		$campo_visibile = false;
		$campo_visibile_disabled = true;
		$campo_richiesto_disabled = true;
		$select_tipo_disable = true;
		$campo_search = false;
		$campo_suggest = false;
	}
	
    
    /* Se il tipo non è riconosciuto di default non lo mostra */
    if($options==''){
        $campo_visibile = false;
        $campo_in_table = false;
    }
	
	
	
	//TODO: da mettere le indicazioni di chiave primaria, 
	// 		cos� che se il valore non � autoincrement sia comunque richiesto un inserimento
	
	$obbligatorio = ($is_nullable=="YES") ? 
					_('NO') 
					: 
					_('YES');
	
	$commento = (strlen(trim($commento))>0) ? "<br /><em class=\"commento\">".htmlentities($commento, ENT_QUOTES, FRONT_ENCODING)."</em>": "";
	
	
	
	// Immagine delle chiavette per le chiavi primarie
	if(is_array($pk) && in_array($column_name,$pk)){
		$img_campo=" campopk";
	}
	else{
		$img_campo=" camponorm";
	}
	
	
	if(in_array($column_name,$fk)){
		
		if($img_campo==" campopk"){
			$img_campo=" campopkfk";
		}
		else{
			$img_campo=" campofk";
			
		}
		
		
		###############
		#
		#	Prende le informazioni sulla chiave esterna
		#	Questa informazione è utile per chi configura la tabella al fine 
		#	di impostare la query esterna per recuperare i dati
		#
		
		
		// Cerca la chiave della FK
		$k_fk = array_search($column_name,$fk);
		
		$info_fk = " - "._("foreign key")." (rif:<strong>".$colref_fk[$k_fk]."</strong>)";
	}
	else{
		$info_fk="";
	}


	
	
	// Gestione search | suggest
	
	
	
	
	
	$OUT= "\t<div class=\"campo\">
				<div class=\"campo-head\">
					<div class=\"campo-alias\"><label for=\"alias_frontend_$id_reg\" >"._('name to display').": </label>
						<input id=\"alias_frontend_$id_reg\" size=\"22\" maxlength=\"100\" name=\"campo[$id_reg][alias_frontend]\" value=\"".stripslashes($alias_frontend)."\" />
					</div>
					<strong class=\"var\">".$column_name."</strong> - $data_type ".$max_length." ".$info_fk."
					$commento
				
				</div>
				
				";
	
	
	if($_SESSION['VF_VARS']['js_test']){
	
		if($jstest!='' && $jstest!=null){
			
			$jstest_pezzi=explode("|",$jstest);
			
			$attuale_jstest=$jstest_pezzi[1];
		}else{
			$attuale_jstest='';
		}
		
		$testo_js_control = _("Advanced data check");
		$testo_js_control .= ($attuale_jstest!='') ? " <strong>(".$attuale_jstest.")</strong>" : "";
		
		$OUT.="<div style=\"float:right;width:170px;text-align:right\" class=\"campo-controlli\">
		
				<br /><span id=\"controlli_$id_reg\" class=\"fakelink\" onclick=\"openWindow('adv_js_control.php?id_reg=".$id_reg."', 'controlli_js', 65)\">$testo_js_control</span>
				</div>
		";
	}
				
	$OUT.="\t\t<div class=\"campo-body$img_campo\">
				
					<br/>"._("required")."(sql): <strong>$obbligatorio</strong>
					<br/>"._("default")."(sql): <strong>$column_default</strong>\n";
		
		// inizia la parte form
		
		// Disabilitazioni checkbox
		$att_dis_visibile=($campo_visibile_disabled) ? "disabled=\"disabled\"" : "";
		$att_dis_richiesto=($campo_richiesto_disabled) ? "disabled=\"disabled\"" : "";
		$att_dis_select = ($select_tipo_disable) ? "disabled=\"disabled\"" : "";
		
		
		// vero|falso dei checkbox
		$att_visibile_checked=($campo_visibile) ? "checked=\"checked\"" : "";
		$att_richiesto_checked=($in_richiesto) ? "checked=\"checked\"" : "";
		
				
	
		$valore_visibile=($campo_visibile_disabled) ? 0:1;
		$valore_obbligatorio=($att_dis_richiesto) ? 1:0;
		
		$OUT.="\t\t<input type=\"hidden\" name=\"campo[$id_reg][in_visibile]\" id=\"in_visibile_hid_$id_reg\" value=\"0\" />\n";
		$OUT.="\t\t<br/><label for=\"in_visibile_$id_reg\">"._("visible field")."</label>
		\t\t\t<input type=\"checkbox\" name=\"campo[$id_reg][in_visibile]\" id=\"in_visibile_$id_reg\" value=\"1\" $att_dis_visibile $att_visibile_checked  />\n";
				
		$OUT.="\t\t<input type=\"hidden\" name=\"campo[$id_reg][in_richiesto]\" id=\"in_richiesto_hid_$id_reg\" value=\"0\" />\n";
		$OUT.="\t\t<br/><label for=\"in_richiesto_$id_reg\">"._("required field")."</label>
		\t\t\t<input type=\"checkbox\" name=\"campo[$id_reg][in_richiesto]\" id=\"in_richiesto_$id_reg\" value=\"1\" $att_richiesto_checked $att_dis_richiesto/>\n";
	
		
		// Campi search
		
				
		/*$valore_search=($campo_search) ? 1:0;
		$att_valore_search=($campo_search) ? "checked=\"checked\"" : "";
		 *
		 */
		
		
		$valore_suggest=($campo_suggest) ? 1:0;
		$att_valore_suggest=($campo_suggest) ? "checked=\"checked\"" : "";
		
		$valore_table=($campo_in_table) ? 1:0;
		$att_valore_table=($campo_in_table) ? "checked=\"checked\"" : "";
	
		
		// campo con suggerimenti di ricerca
		
		$OUT.="\t\t<input type=\"hidden\" name=\"campo[$id_reg][in_suggest]\" id=\"in_suggest_hid_$id_reg\" value=\"0\" />\n";
		$OUT.="\t\t<br/><label for=\"in_suggest_$id_reg\">"._("set autocomplete")."</label>
		\t\t\t<input type=\"checkbox\" name=\"campo[$id_reg][in_suggest]\" id=\"in_suggest_$id_reg\" value=\"1\" $att_valore_suggest />\n";
		
	
		// campo in_table: cosa mostrare nella vista tabella
		
		$OUT.="\t\t<input type=\"hidden\" name=\"campo[$id_reg][in_table]\" id=\"in_table_hid_$id_reg\" value=\"0\" />\n";
		$OUT.="\t\t<br/><label for=\"in_table_$id_reg\">"._("visible in grid view")."</label>
		\t\t\t<input type=\"checkbox\" name=\"campo[$id_reg][in_table]\" id=\"in_table_$id_reg\" value=\"1\" $att_valore_table />\n";
		
		
		$OUT.="\t\t<br/><label for=\"in_tipo_$id_reg\">"._("Input type")."</label> 
			<select name=\"campo[$id_reg][in_tipo]\" id=\"in_tipo_$id_reg\" onchange=\"mostra_hid(this.value,$id_reg)\" $att_dis_select>\n";
		
	//			$selected = ($in_tipo==$k) ? "selected=\"selected\" " : "";
			
		$OUT.= $options.$aggiunta_options;
		
		$OUT.="\t\t</select>\n";
		
		
		
		
		$OUT.="\t\t<div id=\"hid_$id_reg\" >\n";
		
		
		
		// TIPO SELECT 
		$value_select = ($in_tipo=='select')? str_replace("[|]","\n",$in_default) : "";
		$style_select = ($in_tipo=='select')? "" : "display:none;";
		$disabled_select = ($in_tipo=='select')? "" : "disabled=\"disabled\"";
		
		$OUT.="
			
			<div id=\"default-select-$id_reg\" style=\"$style_select\">
				<label class=\"var\">"._("Insert, one per line, possible values for the selection")." - <a href=\"javascript:;\" onclick=\"openWindow('help.select.php',60,40)\">"._('Rules')."</a></label>
				<br/>
				<textarea name=\"campo[$id_reg][tipo_altro]\" cols=\"50\" rows=\"7\" $disabled_select>$value_select</textarea>
			</div>\n";
			
		
		// TIPO PASSWORD 
		$style_password = ($in_tipo=='password')? "" : "display:none;";
		$val_password_0 = ($in_tipo=='password' && ($in_default=='' || $in_default=='null'))? "checked=\"checked\"" : "";
		$val_password_1 = ($in_tipo=='password' && $in_default=='md5')? "checked=\"checked\"" : "";
		$val_password_2 = ($in_tipo=='password' && $in_default=='sha1')? "checked=\"checked\"" : "";
		
		$OUT.="
			
			<div id=\"default-password-$id_reg\" style=\"$style_password\">
				<label class=\"var\">"._("Insert password encoding type")."</label>
				<br />
				<input type=\"radio\" name=\"campo[$id_reg][tipo_altro]\" value=\"null\" $val_password_0 /> "._('No encoding')." <br />
				<input type=\"radio\" name=\"campo[$id_reg][tipo_altro]\" value=\"md5\" $val_password_1 /> "._('MD5 Hash ')."<br />
				<input type=\"radio\" name=\"campo[$id_reg][tipo_altro]\" value=\"sha1\" $val_password_2 /> "._('SHA1 Hash ')."
			</div>\n";
			
		
		// TIPO select_from 
		// TIPO autocompleter_from 
		$value_select_from = ($in_tipo=='select_from' || $in_tipo=='autocompleter_from')? $in_default : "";
		$style_select_from = ($in_tipo=='select_from'  || $in_tipo=='autocompleter_from')? "" : "display:none;";
		$disabled_select_from = ($in_tipo=='select_from'  || $in_tipo=='autocompleter_from')? "" : "disabled=\"disabled\"";
		
		$OUT.="
			<div id=\"default-selectfrom-$id_reg\" style=\"$style_select_from\">
				
				<label class=\"var\">"._("Insert SQL to obtain values for this field")."</label>
				<input type=\"button\" value=\""._("Editor")."\" onclick=\"openWindow('query_editor.php?gid={$_GET['gid']}&amp;id_campo=$id_reg&amp;id_table=$id_table','query_editor','50');\" />				
				<input type=\"button\" value=\""._("Test")."\" onclick=\"try_query(document.getElementById('tipo_altro_".$id_reg."').value,".$id_reg.")\" />				
				<span id=\"feed_altro_".$id_reg."\" class=\"feed_altro\">&nbsp;</span>
				<br/><textarea name=\"campo[".$id_reg."][tipo_altro]\" id=\"tipo_altro_".$id_reg."\" cols=\"50\" rows=\"7\" $disabled_select_from >$value_select_from</textarea>
			</div>";
		
		
		
		// Tipo ENUM (select_enum)
		
		if($data_type=='enum'){
			
			$style_select_enum = ($in_tipo=='select_enum')? "" : "display:none;";
			
			$tipo_str=str_replace("enum(",'',substr(trim($column_type),0,-1));
			$tipo_str=str_replace("'",'',$tipo_str);
			$tipo_str_show=str_replace(",","\n",$tipo_str);
		
			$OUT.="
			<div id=\"enum-$id_reg\" style=\"$style_select_enum\">
				
				<br/>
				<textarea name=\"campo[$id_reg][tipo_altro]\" cols=\"50\" rows=\"7\" disabled=\"disabled\">$tipo_str_show</textarea>
				<input type=\"hidden\" name=\"campo[$id_reg][hid_enum]\" value=\"".$vmsql->escape($tipo_str)."\" />
			</div>\n";
		
		}
		
		
		
		// Tipo Hidden
		$value_hidden = ($in_tipo=='hidden')? $in_default : "";
		$style_hidden = ($in_tipo=='hidden')? "" : "display:none;";
		$disabled_hidden = ($in_tipo=='hidden')? "" : "disabled=\"disabled\"";
		$sovrascrivi_extra= ($in_tipo=='hidden' && $extra=='1') ? "checked=\"checked\"" : "";
		
	
			
		$OUT.="
			<div id=\"default-hidden-$id_reg\" style=\"$style_hidden\">
				<br />
				<label class=\"var\">"._("Insert default value for hidden field")." - <a href=\"javascript:;\" onclick=\"openWindow('help.variabili_hidden.php',60,40)\">"._('show variables')."</a></label>
				<br/><input type=\"text\" name=\"campo[$id_reg][tipo_altro]\" $disabled_hidden value=\"$value_hidden\" />	
				<br /><br />
				<input type=\"hidden\" name=\"campo[$id_reg][extra]\" value=\"0\" />	
				<input type=\"checkbox\" name=\"campo[$id_reg][extra]\" value=\"1\" id=\"extra_".$id_reg."\" $sovrascrivi_extra />	
				<label for=\"extra_".$id_reg."\">"._("Overwrite with default value even if record is modified")."</label>
			</div>
		
		</div>\n";
		
		
		
		
	$OUT.="	
	
		
		
		</div>
	
	</div>\n\n";
	
	
	
	return $OUT;
}

?>