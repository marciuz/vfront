<?php
/**
 * Si tratta di una utility per scrivere le query pi� semplici.
 * Prende le tabelle ed i campi per le query "id, etichetta" per la generazione delle tendine dinamiche.
 * 
 * @desc Finestra popup di editor visuale del'SQL per i campi
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: query_editor.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

 proteggi(3);

// TABELLA
$id_table = (int) $_GET['id_table'];

// id_campo
$id_campo = (int) $_GET['id_campo'];

$GID = $_GET['gid'];


$tabelle = RegTools::prendi_tabelle($GID);
//echo "<pre>";
//
//print_r($_GET);
//
//print_r($tabelle);


echo openLayout1(_("Query Editor"),array(),'popup');

$JS="<script type=\"text/javascript\">

	var campi = new Array();
	
	
\n";


foreach($tabelle as $k=>$val){
	
	$campi[$val['id_table']]=RegTools::prendi_colonne_frontend($val['table_name'],'column_name',false);

	if(is_array($campi[$val['id_table']][0])){
		$JS.="campi['".$val['table_name']."']=new Array('".implode("','",$campi[$val['id_table']][0])."');\n";
	}
	
//	$t.="nome_tab['{$val['id_table']} = '{$campi[$val['id_table']][0]}';\n";
	
}

$JS.="


function q_tendina(id){

	var t_id = document.getElementById('tendina_id');
	var t_val = document.getElementById('tendina_val');
	
	t_id.options.length=0;
	t_val.options.length=0;
	
	
	var opzioni = campi[id];
	
	for(i in opzioni){
	
		t_id.options[i]=new Option(campi[id][i],campi[id][i]);
		t_val.options[i]=new Option(campi[id][i],campi[id][i]);
	}
	

}

function genera(){

	val_tabella = document.getElementById('q_tabella').value;
	
	val_c_id = document.getElementById('tendina_id').value;
	
	val_c_val = document.getElementById('tendina_val').value;

	
	sql = 'SELECT ' + val_c_id +',' + val_c_val + ' FROM ' + val_tabella ;
	
	if(confirm('La tua query:\\n'+sql+'\\n\\nConfermi?')){
	
		window.opener.document.getElementById('tipo_altro_".$_GET['id_campo']."').value=sql;
		window.opener.document.getElementById('tipo_altro_".$_GET['id_campo']."').focus();
		
		self.close();
	}
}



";

$JS.="</script>\n";

//print_r($campi);

echo $JS;

echo "<h1 style=\"margin-top:50px;\">"._("Query Editor")."</h1>";

echo "<form id=\"q_editor\">\n";

echo "<label for=\"tabella\">"._("Select table")."</label>\n";

echo "<select name=\"tabella\" id=\"q_tabella\" onchange=\"q_tendina(this.value);\">\n";

	echo "<option value=\"\">"._("Select")."--&gt;</option>\n";

	foreach($tabelle as $k=>$val) echo "<option value=\"{$val['table_name']}\">{$val['table_name']}</option>\n";

echo "</select>\n
<br /><br />";

echo "<label for=\"tendina_id\">"._("Set the VALUE field:")."</label><br />\n";
echo "<select id=\"tendina_id\"><option value=\"\">&nbsp;&nbsp;&nbsp;&nbsp;</option></select><br /><br />\n";


echo "<label for=\"tendina_val\">"._("Set the LABEL field:")."</label><br />\n";
echo "<select id=\"tendina_val\"><option value=\"\">&nbsp;&nbsp;&nbsp;&nbsp;</option></select><br /><br />\n";

echo "<input type=\"button\" name=\"crea\" value=\""._("Create query")."\" onclick=\"genera();\"/>\n";

echo "</form>";

echo closeLayout1();


?>