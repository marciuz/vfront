<?php
/**
* Libreria di funzioni per la gestione dei campi delle sottomascherein area di amministrazione. 
* Si tratta delle funzioni che generano le scelte possibili per le impostazioni dei campi,
* viste le caratteristiche dei campi definiti in database.
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.tratta_campo_submask.php 1078 2014-06-13 15:35:53Z marciuz $
* @see func.tratta_campo.php
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/




/**
 * Funzione che determina i possibili tipi di input per un campo di una sottomaschera
 *
 * @param array $ar_campo Informazioni sul campo in esame
 * @param array $pk Campo/i chiave/i primaria
 * @param array $fk Campo/i chiave esterna
 * @param array $colref_fk Colonne di riferimento nella relazione delle chiavi esterne
 * @param string $campo_FK
 * @param string $tabella_parent Nome della tabella parent nella relazione sottomaschera/maschera
 * @return string HTML
 */
function tratta_campo_submask($ar_campo,$pk,$fk,$colref_fk=array(),$campo_FK=false, $tabella_parent=false){
	
	global  $vmsql, $vmreg, $db1;
	
	
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
	commento
    */
	
	// caratteristiche di default per la visibilit�
	$campo_visibile=true;
	$campo_visibile_disabled=false;
	$select_tipo_disable = false;
	$column_default = (strlen($column_default)>0) ? $column_default : "<em class=\"na\">"._("not specified")."</em>";
	$max_length= (preg_match('|char|',$data_type)) ? "(".$character_maximum_length ." "._("characters").")" : "";
	
	// Impostazioni campo obbligatorio
	if(is_null($in_richiesto) || $in_richiesto==""){
		$in_richiesto = (trim($is_nullable)=="NO") ? true:false;
		// se l'impostazione deriva dal database ed � richiesto imponi la scelta
		$campo_richiesto_disabled= $in_richiesto;
	}
	else{
		$in_richiesto = ($in_richiesto=="1") ? true:false;
		// se l'impostazione deriva del l'utente lascia la scelta
		$campo_richiesto_disabled= false;
	}
	
	
	// Impostazioni campo visibile
	if($in_visibile=='1'){
		$campo_visibile=true;
	}
	elseif($in_visibile=='0'){
		$campo_visibile=false;
	}
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	$options='';
	
	
	
	// A seconda del tipo di campo fa delle proposte:
	
	$sel=array_flip(array('int','float','char','text','password','date','datetime','bool','hidden','select','select_from'));
	
	
	switch (strtolower($data_type)){
		
		case 'integer' :
		case 'tinyint' :
		case 'smallint' :
		case 'bigint' :
		case 'number' :
		case 'mediumint' :
		case 'int' : 	$options = "\t\t\t<option value=\"int\" ".selected_def('int',$in_tipo).">"._("integer number")."</option>\n";
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
		//break;
		
		case 'varchar2' :
		case 'mediumtext' :
		case 'blob' :
		case 'longtext' :
		case 'text' :
						$options.= "\t\t\t<option value=\"text\" ".selected_def('text',$in_tipo).">"._("free long text")."</option>
								  <option value=\"char\" ".selected_def('char',$in_tipo).">"._("free tiny text")."</option>\n";
		break;
		
		case 'date' : $options = "\t\t\t<option value=\"date\" ".selected_def('date',$in_tipo).">"._("formatted date")."</option>\n";
		break;
		
				
		case 'time' : $options = "\t\t\t<option value=\"time\" ".selected_def('time',$in_tipo).">"._("formatted time")."</option>\n";
		break;
		
		case 'datetime' : 
		case 'timestamp without time zone' : 
		case 'timestamp' : $options = "\t\t\t<option value=\"datetime\" ".selected_def('datetime',$in_tipo).">"._("formatted date and time")."</option>
									<option value=\"date\" ".selected_def('date',$in_tipo).">"._("formatted date")."</option>\n";
		break;
		
		case 'bool' : 
		case 'boolean' : 
		case 'bit' : 	$options = "\t\t\t<option value=\"bool\" ".selected_def('bool',$in_tipo).">"._("boolean (true|false)")."</option>\n";
		break;
		
		default: $options="";
	}
	
	
	
	
	$aggiunta_options="
				<option value=\"hidden\" ".selected_def('hidden',$in_tipo).">"._("hidden")."</option>
				<option value=\"select\" ".selected_def('select',$in_tipo).">"._("defined values")."</option>
				<option value=\"select_from\" ".selected_def('select_from',$in_tipo).">"._("values defined by table")."</option>
				<option value=\"onlyread\" ".selected_def('onlyread',$in_tipo).">"._("read only")."</option>
		";

	
	
	
	
	

					   
	/* Impostazioni nel caso di valore autoincrement:
	Imposta il campo come bloccato	ed invisibile in fase di insert	*/
	if($extra=="auto_increment"){
		$campo_visibile = false;
		$campo_visibile_disabled = true;
		$campo_richiesto_disabled = true;
		$select_tipo_disable = true;
	}
	
	
	
	
	//TODO: da mettere le indicazioni di chiave primaria, 
	// 		cos� che se il valore non � autoincrement sia comunque richiesto un inserimento
	
	$obbligatorio = ($is_nullable=="YES") ? 
					_("NO")
					:
					_("YES");
	
	$commento = (strlen(trim($commento))>0) ? "<br /><em class=\"commento\">".htmlentities($commento, ENT_QUOTES, FRONT_ENCODING)."</em>": "";
	
	$pk= (array) $pk;
	
	// Immagine delle chiavette per le chiavi primarie
	if(in_array($column_name,$pk)){
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
		#	Questa informazione � utile per chi configura la tabella al fine 
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
					<div class=\"campo-alias\"><label for=\"alias_frontend_$id_reg_sub\" >"._('name to display').": </label><input name=\"campo_sub[$id_reg_sub][alias_frontend]\" value=\"".stripslashes($alias_frontend)."\" /></div>
					<strong class=\"var\">".$column_name."</strong> - $data_type ".$max_length." ".$info_fk."
					$commento
				
				</div>";
	
	
	
	
	
	
	
	
		
	
	##########################################################################################################
	#
	#	IMPOSTAZIONI PER IL CAMPO IMPOSTATO COME RIFERIMENTO AL PARENT
	#
	#
	
	if($campo_FK!=false){
		
		
		
		// PRENDI i campi della tabella parent
		
		$sql_parent = "SELECT column_name FROM ".$db1['frontend'].$db1['sep']."registro_col
						WHERE id_table='".$tabella_parent."'
						ORDER BY ordinal_position
						";
		
		$q_parent = $vmreg->query($sql_parent);
		
		list($campi_parent) = $vmreg->fetch_row_all($q_parent,true);
		
		
		
		$OUT.="<div class=\"campo-body$img_campo\">
		
			<p class=\"grigio\">"._("This field will be the identification label of the record will not be subject to amendment by the insertion or subform, but must show a meaningful name.")."</p>";
		
		$OUT.="
		<input type=\"hidden\" name=\"campo_sub[$id_reg_sub][in_visibile]\" value=\"1\" />
		<input type=\"hidden\" name=\"campo_sub[$id_reg_sub][in_richiesto]\"  value=\"0\" />
		<input type=\"hidden\" name=\"campo_sub[$id_reg_sub][in_tipo]\"  value=\"parent_ref\" />
		
		
		";
		
		$OUT.="<label for=\"in_default_$id_reg_sub\">"._("Field to create subform label:")."</label><br />
			<select name=\"campo_sub[$id_reg_sub][in_default]\" id=\"in_default_$id_reg_sub\">\n";
		
		
		
		
		for($i=0;$i<count($campi_parent);$i++){
			
			$selected_parent= ($campi_parent[$i]==$in_default) ? "selected=\"selected\"" : "";
			
			$OUT.="\t\t<option value=\"".$campi_parent[$i]."\" $selected_parent>".$campi_parent[$i]."</option>\n";
		}
		
		$OUT.="\t</select>";
		
		
		$OUT.="</div>\n";
		
		
		$OUT.="\t</div>\n";
		
		return $OUT;
	}
		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
	
	
	$OUT.="
				<div class=\"campo-body$img_campo\">
				
					<br />"._("required")."(sql): <strong>$obbligatorio</strong>
					<br />"._("default")."(sql): <strong>$column_default</strong>\n";
		
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
		
		$OUT.="\t\t<input type=\"hidden\" name=\"campo_sub[$id_reg_sub][in_visibile]\" id=\"in_visibile_hid_$id_reg_sub\" value=\"0\" />\n";
		$OUT.="\t\t<br/><label for=\"in_visibile_$id_reg_sub\">"._("visible field")."</label>
		\t\t\t<input type=\"checkbox\" name=\"campo_sub[$id_reg_sub][in_visibile]\" id=\"in_visibile_$id_reg_sub\" value=\"1\" $att_dis_visibile $att_visibile_checked  />\n";
				
		$OUT.="\t\t<input type=\"hidden\" name=\"campo_sub[$id_reg_sub][in_richiesto]\" id=\"in_richiesto_hid_$id_reg_sub\" value=\"0\" />\n";
		$OUT.="\t\t<br/><label for=\"in_richiesto_$id_reg_sub\">"._("required field")."</label>
		\t\t\t<input type=\"checkbox\" name=\"campo_sub[$id_reg_sub][in_richiesto]\" id=\"in_richiesto_$id_reg_sub\" value=\"1\" $att_richiesto_checked $att_dis_richiesto/>\n";
	
		
		
		
		$OUT.="\t\t<br/><label for=\"in_tipo_$id_reg_sub\">Tipo di input</label> 
			<select name=\"campo_sub[$id_reg_sub][in_tipo]\" id=\"in_tipo_$id_reg_sub\" onchange=\"mostra_hid(this.value,$id_reg_sub)\" $att_dis_select>\n";
		
	//			$selected = ($in_tipo==$k) ? "selected=\"selected\" " : "";
			
		$OUT.= $options.$aggiunta_options;
		
		$OUT.="\t\t</select>\n";
		
		
		
		
		$OUT.="\t\t<div id=\"hid_$id_reg_sub\" >\n";
		
		// TIPO SELECT 
		$value_select = ($in_tipo=='select')? str_replace("[|]","\n",$in_default) : "";
		$style_select = ($in_tipo=='select')? "" : "display:none;";
		$disabled_select = ($in_tipo=='select')? "" : "disabled=\"disabled\"";
		
		$OUT.="
			
			<div id=\"default-select-$id_reg_sub\" style=\"$style_select\">
				<label class=\"var\">"._("Insert, one per line, possible values for the selection")." - <a href=\"javascript:;\" onclick=\"openWindow('help.select.php',60,40)\">Regole</a></label>
				<br/>
				<textarea name=\"campo_sub[$id_reg_sub][tipo_altro]\" cols=\"50\" rows=\"7\" $disabled_select>$value_select</textarea>
			</div>\n";
			
		
		// TIPO select_from 
		$value_select_from = ($in_tipo=='select_from')? $in_default : "";
		$style_select_from = ($in_tipo=='select_from')? "" : "display:none;";
		$disabled_select_from = ($in_tipo=='select_from')? "" : "disabled=\"disabled\"";

		$id_table=RegTools::name2oid($tabella_parent);
		
		$OUT.="
			<div id=\"default-selectfrom-$id_reg_sub\" style=\"$style_select_from\">
				
				<label class=\"var\">"._("Insert SQL to obtain values for this field")."</label>
				<input type=\"button\" value=\""._("Editor")."\" onclick=\"openWindow('query_editor.php?gid={$_GET['gid']}&amp;id_campo=$id_reg_sub&amp;id_table=$id_table','query_editor','50');\" />	".			
				"<input type=\"button\" value=\""._("Test")."\" onclick=\"try_query(document.getElementById('tipo_altro_".$id_reg_sub."').value,".$id_reg_sub.")\" />				".
				"<span id=\"feed_altro_".$id_reg_sub."\" class=\"feed_altro\">&nbsp;</span>
				<br/><textarea name=\"campo_sub[".$id_reg_sub."][tipo_altro]\" id=\"tipo_altro_".$id_reg_sub."\" cols=\"50\" rows=\"7\" $disabled_select_from >$value_select_from</textarea>
			</div>";
		
		
		// Tipo Hidden
		$value_hidden = ($in_tipo=='hidden')? $in_default : "";
		$style_hidden = ($in_tipo=='hidden')? "" : "display:none;";
		$disabled_hidden = ($in_tipo=='hidden')? "" : "disabled=\"disabled\"";
			
		$OUT.="
			<div id=\"default-hidden-$id_reg_sub\" style=\"$style_hidden\">
				
				<label class=\"var\">"._("Insert default value for hidden field")." - <a href=\"javascript:;\" onclick=\"openWindow('help.variabili_hidden.php',60,40)\">vedi le variabili</a></label>
				<br/><input type=\"text\" name=\"campo_sub[$id_reg_sub][tipo_altro]\" $disabled_hidden value=\"$value_hidden\" />	
			</div>
		
		</div>\n";
		
		$OUT.="
	
		
		</div>
	
	</div>\n\n";
	
	
	
	return $OUT;
}

?>