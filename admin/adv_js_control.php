<?php
/**
 * Questo file crea un'interfaccia per utilizzare i controlli avanti javascript per la validazione dei campi. 
 * A seconda del campo presentato vengono mostrate diverse opzioni di vincoli sull'input dell'utente. 
 * Il sistema mostrerà poi i controlli con il pacchetto Javascript YAV {@link http://yav.sourceforge.net/it/index.html} 
 * 
 * @desc File per il popup dei controlli javascript per la validazione dei campi
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: adv_js_control.php 1096 2014-06-19 09:16:31Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

include("../inc/conn.php");
include("../inc/layouts.php");

proteggi(3);


/**
 * @desc Funzione parser per il controllo sui campi in javascript
 * @param string $str Il controllo secondo la sintassi YAV
 * @return array Array con i frammenti di controllo (viene usato il carattere | per separare)
 */
function parser_controllo_js($str){
	
	if(trim($str)!=''){
		$arr_controlli=explode("\n",trim($str));
		
		for($i=0;$i<count($arr_controlli);$i++){
			
			$mat_controlli[$i] = explode("|",trim($arr_controlli[$i]));
		}
		return $mat_controlli;
	}
	else return array();
	
}

// cancellazione del controllo
if(isset($_GET['del_jstest'])){
	
	$ID_REG= (int) $_GET['del_jstest'];
	
	$q_del = $vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET jstest='' WHERE id_reg=$ID_REG ");
	
	if($vmreg->affected_rows($q_del)==1){
		
		header("Location: ".$_SERVER['PHP_SELF']."?id_reg=".$ID_REG."&feed_del=ok");
	}
	else{
		
		header("Location: ".$_SERVER['PHP_SELF']."?id_reg=".$ID_REG."&feed_del=ko");

	}

	exit;
}


if(isset($_POST['control_1'])){
	
	
	switch($_POST['tipo_sub']){
		
		case "integer" : $r="dati[".$_POST['column_name']."]|integer|".$_POST['column_name']." "._('must be an integer number');
		break;
		
		case "notequal" : $r="dati[".$_POST['column_name']."]|notequal|".$_POST['control_1']."|".$_POST['column_name']." "._('must be different from')." \"{$_POST['control_1']}\"";
		break;
		
		case "numrange" : $r="dati[".$_POST['column_name']."]|numrange|".$_POST['control_1']."-".$_POST['control_2']."|".$_POST['column_name']." ".sprintf(_('must be between %s and %s'),$_POST['control_1'],$_POST['control_1']);
		break;
		
		case "numchar" : $r="dati[".$_POST['column_name']."]|numchar|".intval($_POST['control_1'])."|".$_POST['column_name']." ".sprintf(_('must be exactly %s characters'),$_POST['control_1']);
		break;
		
		// ----------------------------------
		
		case 'alphabetic' : $r="dati[".$_POST['column_name']."]|alphabetic|{$_POST['column_name']} "._('Only letters allowed (uppercase and lowercase)');
		break;
		
		case 'alphanum' : $r="dati[".$_POST['column_name']."]|alphanum|{$_POST['column_name']} "._('only alphanumeric characters are allowed');
		break;
		
		case 'alnumhyphen' : $r="dati[".$_POST['column_name']."]|alnumhyphen|{$_POST['column_name']} "._('only alphanumeric, "-" and "_" characters are allowed');
		break;
		
		case 'alphaspace' : $r="dati[".$_POST['column_name']."]|alphaspace|{$_POST['column_name']} "._('only alphanumeric, spaces, "-" and "_" characterss are allowed');
		break;
		
		case 'email' : $r="dati[".$_POST['column_name']."]|email|{$_POST['column_name']} "._('must be a valid email');
		break;
		
		case 'maxlength' : $r="dati[".$_POST['column_name']."]|maxlength|{$_POST['control_1']}|".sprintf(_('The value of %s must not exceed %s characters long'),$_POST['column_name'],$_POST['control_1']);
		break;
		
		case 'minlength' : $r="dati[".$_POST['column_name']."]|minlength|{$_POST['control_1']}|".sprintf(_('The value of %s must be at least %s characters long'),$_POST['column_name'],$_POST['control_1']);
		break;
		
		
		case 'iva_ita' : $r="dati[".$_POST['column_name']."]|iva_ita|{$_POST['column_name']} "._('must be an Italian IVA');
		break;
		
		case 'cf_ita' : $r="dati[".$_POST['column_name']."]|cf_ita|{$_POST['column_name']} "._('must be an Italian fiscal code');
		break;
		
		
		case 'regexp' : $r="dati[".$_POST['column_name']."]|regexp|{$_POST['control_1']}|".sprintf(_('The value of %s must match the character pattern %s'),$_POST['column_name'],$_POST['control_1']);
		break;
		
		
		
		
		//-----------------------------------
		
		case 'date' : $r="dati[".$_POST['column_name']."]|date|".sprintf(_('The value of %s must be a valid date'),$_POST['column_name']);
		break;
		
		case 'date_lt' : $r="dati[".$_POST['column_name']."]|date|{$_POST['control_1']}|{$_POST['column_name']} ".sprintf(_('must be a date before %s'),$_POST['control_1']);
		break;
		
		case 'date_lt' : $r="dati[".$_POST['column_name']."]|date|{$_POST['control_1']}|{$_POST['column_name']} ".sprintf(_('must be a date before or equal to %s'),$_POST['control_1']);
		break;
		
		//-----------------------------------
		
		case 'orario_sec' : $r="dati[".$_POST['column_name']."]|regexp|[0-9]{2}:[0-9]{2}:[0-9]{2}|{$_POST['column_name']} "._('must be a time in HH:mm:ss (hours, minutes, seconds) format ');
		break;
		
		case 'orario' : $r="dati[".$_POST['column_name']."]|regexp|[0-9]{2}:[0-9]{2}|{$_POST['column_name']} "._('must be a time in HH:mm (hours, minutes) format ');
		break;
		
		//-----------------------------------
		
		case 'double' : $r="dati[".$_POST['column_name']."]|double|{$_POST['column_name']} "._('must be a floating point number');
		break;
		
		
		default : $r='';
		
		
	}
	
	
	if($r!=''){
		
		$sql_up = "UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET jstest='".str_replace("'","\'",stripslashes($r))."'
					WHERE id_reg=".intval($_POST['id_reg']);
		
		// Esegue l'update
		$q_up=$vmreg->query($sql_up);
		
		if($vmreg->affected_rows($q_up)==1){
		
			header("Location: ".$_SERVER['PHP_SELF']."?id_reg=".intval($_POST['id_reg'])."&feed=ok");
		}
		else{
			
			header("Location: ".$_SERVER['PHP_SELF']."?id_reg=".intval($_POST['id_reg'])."&feed=ko");
		}
	}
	
	
	exit;
}


$ID_REG=(int) $_GET['id_reg'];

// PRENDI INFORMAZIONI SUL CAMPO
$q_c=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}registro_col WHERE id_reg=".intval($ID_REG));

$RS=$vmreg->fetch_assoc($q_c);

$mat_controlli = parser_controllo_js($RS['jstest']);

$a_tipo=array();

echo openLayout1(_("JS fields check options"),array('sty/lista.css','sty/admin.css','js/scriptaculous/lib/prototype.js'),'popup');

//echo "<h1 style=\"margin-top:50px;\">Opzioni controlli campi JS</h1>";

echo "<h2 style=\"margin-top:50px;\">".sprintf(_('Check options for field %s of type'),"<span class=\"var\">".$RS['column_name']."</span>")." <em style=\"color:#666;\">".$RS['data_type']."</em></h2>\n";


switch($RS['data_type']){
	
	case 'int':
        case 'tinyint':
	case 'integer':
	case 'biginteger':
	case 'bigint':
	case 'mediumint':
	case 'NUMBER':
	case 'smallint':
		$a_tipo['integer']=_("integer number");
		$a_tipo['notequal']=_("must be not equal to");
		$a_tipo['numrange']=_("number interval");
		$a_tipo['numchar']=_("integer number with n chars");
	break;
		
	case 'varchar':
	case 'varchar2':
	case 'VARCHAR2':
	case 'char':
	case 'character':
	case 'text' :
	case 'mediumtext':
	case 'longtext':
	case 'character varying':
	
		$a_tipo['alphabetic']=_("alphabetic");
		$a_tipo['alphanum']=_("alphanumeric");
		$a_tipo['alnumhyphen']=_("alphanumeric with dashes");
		$a_tipo['alphaspace']=_("alphanumeric with dashes and spaces");
		$a_tipo['email']=_("email");
		$a_tipo['notequal']=_("must be not equal to");
		$a_tipo['maxlength']=_("max length");
		$a_tipo['minlength']=_("min length");
		$a_tipo['regexp']=_("regular expression");		
		$a_tipo['numchar']=_("text with exactly n chars");
		$a_tipo['iva_ita']=_("Italian IVA");
		$a_tipo['cf_ita']=_("Italian fiscal code");
		
	break;
	
	case 'date':
	case 'DATE':
	case 'timestamp without time zone':
	case 'timestamp with time zone':
	case 'timestamp':
	
		$a_tipo['date']=_("formatted date");
		$a_tipo['date_le']=_("date before or equal to");
		$a_tipo['date_lt']=_("date after or equal to");
	break;
	
	case 'time':

	
		$a_tipo['orario_sec']=_("formatted time (HH:mm:ss)");
		$a_tipo['orario']=_("formatted time (HH:mm)");
	break;
	
	case 'double':
	case 'float':
	case 'numeric':
	
		$a_tipo['double']=_("floating point number");
		$a_tipo['integer']=_("integer number");
		$a_tipo['numrange']=_("number interval");
		$a_tipo['numchar']=_("floating point number with exactly this many digits:");
		
	break;	

	
		
	
}


/*$a_tipo['integer']="numerico intero";
$a_tipo['double']="numerico con virgola";
$a_tipo['alpahnum']="alfanumerico";
$a_tipo['alpahnum']="alfanumerico con trattini";
$a_tipo['alpahnum']="alfanumerico con spazi";
$a_tipo['email']="email";
$a_tipo['maxlength']="lunghezza massima";
$a_tipo['minlength']="lunghezza minima";
$a_tipo['notequal']="diverso da";
$a_tipo['numrange']="intervallo numerico";
$a_tipo['date']="data formattata";
$a_tipo['date_lt']="data precendente o uguale a";
$a_tipo['date_le']="data successiva o uguale a";
$a_tipo['orario_sec']="orario formattato (con la forma HH:mm:ss)";
$a_tipo['orario']="orario formattato (con la forma HH:mm)";
$a_tipo['regexp']="espressione regolare";*/

if(isset($_GET['feed_del'])){
	
	if($_GET['feed_del']=='ok'){
		
		echo "
		<script type=\"text/javascript\" >
		
		window.opener.document.getElementById('controlli_".$ID_REG."').innerHTML='"._('Advanced data check')."';
		
		</script>\n";
		
		echo "<p class=\"feedok\">"._('Check deleted correctly')."<br /><br /><span class=\"fakelink\" onclick=\"window.close();\">"._('Close')."</span></p>\n";
	}
	else{
		echo "<p class=\"feedko\">"._('No checks deleted')."</p>\n";
		
	}
	
	
}


if(isset($_GET['feed'])){
	
	if($_GET['feed']=='ok'){
		
		echo "
		<script type=\"text/javascript\" >
		
		window.opener.document.getElementById('controlli_".$ID_REG."').innerHTML='"._('Advanced data check')." <strong>(".$mat_controlli[0][1].")</strong>';
		
		</script>\n";
		
		echo "<p class=\"feedok\">"._('Check added correctly')." <br /><br /><span class=\"fakelink\" onclick=\"window.close();\">"._('Close')."</span></p>\n";
	}
	else{
		echo "<p class=\"feedko\">"._('No check deleted')."</p>\n";
		
	}
	
	
}


echo "<form name=\"controlli\" action=\"".$_SERVER['PHP_SELF']."\" method=\"get\">\n";


if(count($a_tipo)>0){
	
	if(count($mat_controlli)==0){
		
		echo "<p>"._('No check set for this field')."</p>\n";
	}
	else{
		
		echo "<p>"._('There is already a check of type')." <strong>".$mat_controlli[0][1]."</strong>: <a href=\"?del_jstest=$ID_REG\">"._('Delete data check')."</a></p>\n";
	}
	
	
	echo "<label for=\"tipo\">"._('Available checks for this field type:')."</label><br />\n";
	
	
	
	
	echo "<select name=\"tipo\" id=\"tipo\" onchange=\"submit();\">\n";
	
	
		echo "<option value=\"null\" >"._('Select the check')." ---&gt;</option>\n";
	
	foreach($a_tipo as $k=>$v){
		
		if(isset($_GET['tipo'])){
			$sel0 = ($_GET['tipo']==$k) ? "selected=\"selected\"" : "";
		}
		
		else if($mat_controlli>0){
			
			$sel0=(isset($mat_controlli[0]) && $mat_controlli[0][1]==$k) ? "selected=\"selected\"" : "";
		}
		
		
		echo "<option $sel0 value=\"$k\">$v</option>\n";
	}
	
	echo "</select>\n";
}
else{
	
	echo "<p><strong>"._('Warning!')."</strong> "._('No controls for this field type')."</p>\n";
}

switch($_GET['tipo']){
	
	case 'maxlength':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Insert the max length value allowed:')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n";
	break;	
	
	
	
	case 'minlength':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Insert the min length value allowed:')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n";
	break;
	
	
	case 'notequal':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Insert the value to exclude')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n";
	break;
	
	case 'numrange':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Insert the min value:')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n
					<br /><br />
					<label for=\"val2\">"._('Insert the max value:')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val2\" />\n";
	break;	
	
	
	case 'date_lt':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Date before:')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n
					\n";
	break;
	
	case 'date_le':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Date before or equal to :')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n
					\n";
	break;
	
	
	case 'regexp':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Regular expression:')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n
					\n";
	break;
	
	
	case 'numchar':
		
			$aggiungi="\t\t\t<label for=\"val1\">"._('Number of characters/digitsrequired:')." </label>
					<input type=\"text\" name=\"val1\" value=\"\" id=\"val1\" />\n
					\n";
	break;
	
	default : $aggiungi='';
	
}

echo "<br /><br />". $aggiungi;
echo "<input type=\"hidden\" name=\"id_reg\" value=\"".$ID_REG."\" />\n";

echo "</form>\n";


if(isset($_GET['tipo']) && $_GET['tipo']!='null'){
	
	$onclick="if($('val1')){ $('control_1').value=\$F('val1');};";
	$onclick.="if($('val2')){ $('control_2').value=\$F('val2');};";
	$onclick.="if($('tipo')){ $('tipo_sub').value=\$F('tipo');};";
	$onclick.="submit();";
	
	echo "<br /><br /><form name=\"invia\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" >
	<input type=\"hidden\" name=\"tipo_sub\" id=\"tipo_sub\" value=\"\" />
	<input type=\"hidden\" name=\"control_1\" id=\"control_1\" value=\"\" />
	<input type=\"hidden\" name=\"control_2\" id=\"control_2\" value=\"\" />
	<input type=\"hidden\" name=\"id_reg\" id=\"id_reg\" value=\"".$ID_REG."\" />
	<input type=\"hidden\" name=\"column_name\" id=\"column_name\" value=\"".$RS['column_name']."\" />
	<input type=\"button\" name=\"invia\" id=\"invia\" value=\""._('Save data check')."\" onclick=\"$onclick\" />
	
	</form>\n";
	
	
	
	
}

echo closeLayout1();


?>