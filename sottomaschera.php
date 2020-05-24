<?php
/**
* File che genera le sottomaschere. 
* Vengono lette le opzioni dai registri di regole e viene generato l'html e il javascript necessario
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: sottomaschera.php 1150 2015-05-06 19:41:33Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("./inc/conn.php");
require_once("./inc/layouts.php");
require_once("./inc/func.campi_submask.php");

proteggi(1);


###############################################################
#
#	INFO GENERALI SOTTOMASCHERA
#

$ID_SUBMASK=intval($_GET['id_submask']);

$sql_info_sub="SELECT * 
				FROM ".$db1['frontend'].$db1['sep']."registro_submask 
				WHERE id_submask=".$ID_SUBMASK;

$q_info_sub=$vmreg->query($sql_info_sub);

$info_sub = $vmreg->fetch_assoc($q_info_sub);

 $SM_INSERT = ($info_sub['sub_insert']=='1') ? true:false;
 $SM_UPDATE = ($info_sub['sub_update']=='1') ? true:false;
 $SM_DELETE = ($info_sub['sub_delete']=='1') ? true:false;
 $MAX_RECORD_SUB = ($info_sub['max_records']>0) ? $info_sub['max_records']:5;
 $SM_TIPO_VISTA = $info_sub['tipo_vista'];


 
 
 
	
 

 


###############################################################
#
#	INFO COLONNE SOTTOMASCHERA
#
#

$sql_info_campi_sub = "SELECT * 
						FROM ".$db1['frontend'].$db1['sep']."registro_submask_col 
						WHERE id_submask=".$ID_SUBMASK."
						ORDER BY ordinal_position
						";


$q_info_campi_sub = $vmreg->query($sql_info_campi_sub);

$info_campi_sub = $vmreg->fetch_assoc_all($q_info_campi_sub);


$CAMPI_TENDINE=array();


// CERCA LE TENDINE:
for($i=0;$i<count($info_campi_sub);$i++){
	
	if($info_campi_sub[$i]['in_tipo']=='select_from'){
		
		$CAMPI_TENDINE[]= 	$info_campi_sub[$i]['column_name'];
	}
}


// NOME della sottomaschera
$nome_sub = (trim($info_sub['nome_frontend'])!="") ? $info_sub['nome_frontend'] : $info_sub['nome_tabella'];


 
###############################################################
#
#	CHIAVI PRIMARIE SOTTOMASCHERA
# 	si assume che ci siano due chiavi primarie, 
#	una riferita al valore della tabella esterna qui considerato come indipendente, 
#	l'altra (chiave) riferita al valore della tabella esterna dipendente
#	Serve per preservare in caso di modifica il riferimento al record.

	/*$PKS = prendi_all_PK_submask_oid(intval($_GET['id_submask']));
	$array_PK_dipendente = $PKS;
*/
	/*// cerca la chiave riferita alla tabella indipendente
	$K_PK_tab_indipendente = array_search($info_sub['campo_pk_parent'],$array_PK_dipendente);
	
	$PK_tab_indipendente=$array_PK_dipendente[$K_PK_tab_indipendente];*/
	
	// Cerca in tutti i campi quello definito come "parent_ref"
	
	
	$CAMPO_PARENT_REF='';
	for($k=0;$k<count($info_campi_sub);$k++){
		
		if($info_campi_sub[$k]['in_tipo']=='parent_ref'){
			$CAMPO_PARENT_REF=$info_campi_sub[$k]['column_name'];
		}
		
	}
	
	if($CAMPO_PARENT_REF==''){
		openErrorGenerico(_('Settings error for subform')." :".__LINE__,false,_('Invalid reference to foreign key. Check the settings'));
	}
	else{
		$PK_tab_indipendente=$CAMPO_PARENT_REF;
	}

	
	/*
	// togli la variabile indipendente
	unset($array_PK_dipendente[$K_PK_tab_indipendente]);
	
	// resta (su 2 assunte) la PK dipendente
	list($PK_tab_dipendente)=array_values($array_PK_dipendente);*/


// Campi da prendere

for($i=0;$i<count($info_campi_sub);$i++){
	
	if($info_campi_sub[$i]['in_visibile']=='1' && $info_campi_sub[$i]['column_name']!=$info_sub['campo_fk_sub']){

		
		$campi[] = $info_campi_sub[$i]['column_name'];
		$campi_alias_frontend[] = $info_campi_sub[$i]['alias_frontend'];
		$info_campo_mostrare[] = $info_campi_sub[$i];
	
	}
	elseif($info_campi_sub[$i]['in_tipo']=='parent_ref'){
		
		$CAMPO_REF_PARENT = $info_campi_sub[$i]['in_default'];
		
		
	}
}


###############################################################
#
#	ETICHETTA RECORD
#
#

$ETICHETTA = "";

if(isset($CAMPO_REF_PARENT)){

$sql_ETICHETTA = "SELECT parent.$CAMPO_REF_PARENT 
		  FROM ".$info_sub['nome_tabella']." child
		  LEFT JOIN ".RegTools::oid2name($info_sub['id_table'])." parent ON parent.".$info_sub['campo_pk_parent']."=child.".$info_sub['campo_fk_sub']."
		  WHERE parent.".$info_sub['campo_pk_parent']."='".$vmsql->escape($_GET['pk'])."'
		  LIMIT 1";

	$q_ETICHETTA = $vmsql->query($sql_ETICHETTA);
	
	if($vmsql->num_rows($q_ETICHETTA)==1){
		
		list($ETICHETTA) = $vmsql->fetch_row($q_ETICHETTA);
	}
	else $ETICHETTA = "<em>"._('Data not available')."</em>";
	
	

}


###############################################################
#
#	SQL PER PRENDERE I DATI
#
#

$n_campi = count($campi);

if($n_campi==0){
	
	openErrorGenerico(_("Error in subform definition")." l:".__LINE__,false);
	exit;
}

 $sql_dati = "SELECT ".implode(",",$campi)." FROM ".$info_sub['nome_tabella']."
		  WHERE ".$info_sub['campo_fk_sub']."='".$vmsql->escape($_GET['pk'])."'
		  ORDER BY ".$info_sub['orderby_sub']." ".$info_sub['orderby_sub_sort'];

 
if($vmsql->query_try($sql_dati,true,true)){
	
	// PRENDO I DATI
	
	$q_dati = $vmsql->query($sql_dati);
	
	$n_dati = $vmsql->num_rows($q_dati);
	
	if($n_dati>0){
		
		$dati_sub = $vmsql->fetch_row_all($q_dati);	
	}
	
}
else{
	
	openErrorGenerico(_("Error in subform definition")." :".__LINE__,false);
	exit;
}	

/*
if($vmsql->query_try($sql_chiavi)){
	
	// PRENDO I VALORI DELLE CHIAVI primaerie dipendenti
	
	$q_chiavi = $vmsql->query($sql_chiavi);
	
	$n_chiavi = $vmsql->num_rows($q_chiavi);
	
	if($n_dati>0){
		
		list($dati_chiavi_sub) = $vmsql->fetch_row_all($q_chiavi,true);	
	}
	
}*/


// SPERIMENTALE.

// PRENDO TUTTI i dati per tutti i record per metterli in un campo nascosto, magari serializzati?


$sql_all_data ="SELECT * FROM ".$info_sub['nome_tabella']."
		  WHERE ".$info_sub['campo_fk_sub']."='".$vmsql->escape($_GET['pk'])."'
		  ORDER BY ".$info_sub['orderby_sub']." ".$info_sub['orderby_sub_sort'];


// PRENDO TUTTI I DATI
	
	$q_all_dati = $vmsql->query($sql_all_data);
	
	$n_all_dati = $vmsql->num_rows($q_all_dati);
	
	if($n_all_dati>0){
		
		$dati_all_sub = $vmsql->fetch_assoc_all($q_all_dati);	
	}
	
/*else{
	
	openErrorGenerico("Errore nella impostazione della sottomaschera: Numero di campi attesi per la chiave primaria incongruente (attesi 2 campi)",false);
	exit;
}*/


#############################################################################################################
#
#	INIZIO A SCRIVERE
#
#

$files=array("js/scriptaculous/lib/prototype.js",'js/sottomaschera.js');

$carica_calendario=true;

	// SE ci sono campi data , datetime o timestamp, prendi il calendario
	if($carica_calendario){
		$files[]="js/jscalendar/calendar.js";
		$files[]="js/jscalendar/lang/calendar-it.js";
		$files[]="js/jscalendar/calendar-setup.js";
		$files[]="sty/jscalendar/calendar-win2k-cold-1.css";
	}

// FOrmattazione date
$DATE_FORMAT = (isset($_SESSION['VF_VARS']['force_isodate_on_mask']) 
					  && ($_SESSION['VF_VARS']['force_isodate_on_mask']==1))
				 ? 'iso'
				 : FRONT_DATE_FORMAT;
	
	
	
$INIZIO_LAYOUT = openLayout1(_("Subform")." ".$nome_sub,$files,'sottomaschera');

$OUT= str_replace("<body>","<body onload=\"self.focus();\">",$INIZIO_LAYOUT);   unset($INIZIO_LAYOUT);

$pathRelativo = Common::dir_name();

$js_manuale = "
	<script type=\"text/javascript\">

		var n_righe=$n_dati;
		var max_righe=$MAX_RECORD_SUB;
		var modifiche_attive=false;
		var eliminazione_attiva=false;
		var oid_parent={$_GET['oid_parent']};
		var id_submask=$ID_SUBMASK;
		var pk_parent='{$_GET['pk']}';
		var campo_pk_indipendente = '$PK_tab_indipendente';
		".//var campo_pk_dipendente = '$PK_tab_dipendente';
		"var nome_tabella='".$info_sub['nome_tabella']."';
		var righe_mod= new Array();
		var tipo_vista= '$SM_TIPO_VISTA';
		var campi_iframe= new Array('".implode("','",$CAMPI_TENDINE)."');
		var dateEncode='".$DATE_FORMAT."';
        var basePath='".$pathRelativo."';
		
		
		";

 $sotto_array_js="";
 
 for($i=0;$i<$MAX_RECORD_SUB;$i++){
 	
 	$js_manuale.="var a$i = new Array(); ";
 	
 	$sotto_array_js.= "a$i,";
 	
}

$sotto_array_js=substr($sotto_array_js,0,-1);

$js_manuale.="

	var campi_mod= new Array();
	var valori_del = new Array();
	
	
	
	</script>\n";

$OUT.= $js_manuale;

$OUT.="<h1><span class=\"var\">".ucfirst($nome_sub)."</span> - "._("subform of")." <span class=\"var\">".RegTools::oid2name($info_sub['id_table'])."</span></h1>";


// Spazio per i feedback
$OUT.= "
	<div id=\"feedback\">
		<span id=\"risposta\"></span>
	</div>
";

	if($SM_INSERT || $SM_UPDATE){
		
		$OUT.="<div id=\"pulsanti-azioni\">\n";
	}

// SE NON CI SONO DATI:

	if($SM_INSERT){ 
		$OUT.="<input title=\""._('New record')."\" type=\"button\" id=\"p_insert\" name=\"insert\" value=\" "._('New record')." \" onclick=\"nuovo_record();\" accesskey=\"n\" />\n";
	}
	else {
            $OUT.="<input type=\"hidden\" id=\"p_insert\" />\n"; 
        }
        
	if($SM_INSERT || $SM_UPDATE){ 
	 	$OUT.="<input disabled=\"disabled\" title=\""._('Cancel')."\" type=\"button\" id=\"p_annulla\" name=\"annulla\" value=\" "._('Cancel')." \" onclick=\"annulla();\" accesskey=\"a\" />\n";
	} 
	else {
            $OUT.="<input type=\"hidden\" id=\"p_annulla\" />\n";
        }
        
	if($SM_INSERT || $SM_UPDATE){ 
		$OUT.="<input disabled=\"disabled\"  title=\""._('Save records')."\" type=\"button\" id=\"p_save\" name=\"save\" value=\" "._('Save')." \" onclick=\"salva(nome_tabella);\" accesskey=\"s\" />\n";
	} 
	else $OUT.="<input type=\"hidden\" id=\"p_save\" />\n";
	
	
	$OUT.=" <input type=\"button\" title=\""._('Close window')
            ."\" name=\"Chiudi\" value=\" "._('Close')
            ." \" onclick=\"if(modifiche_attive || eliminazione_attiva){if(confirm('"
            ._('There are pending operations: do you want to close the window anyway without saving the modifications?')."'))"
            ."{self.close(); PARENT_WINDOW.Shadowbox.close(); }}else{self.close(); PARENT_WINDOW.Shadowbox.close();}\" />\n";
	

	if($SM_INSERT || $SM_UPDATE){
		
		$OUT.="</div>\n";
	}
		

	
	
	
	$OUT.="<div class=\"etichetta-record\">".Common::vf_utf8_encode($ETICHETTA)
            ." <span style=\"color:#000\">(<span id=\"numero_record\">$n_dati</span> "
            ._('record').")</span></div>\n";
	
	

	
	
	$TRIGGER_ASSEGNAZIONE="";


	######################################################################################################################
	#
	#
	#	OPZIONE VISTA TABELLA
	#
	#

	if($SM_TIPO_VISTA=='tabella'){

		// crea le celle di tabella

		$RIGA="";

		$TR_TH = "\t\t<tr>\n";


		if($n_dati>0){


			//	for($i=0;$i<$MAX_RECORD_SUB;$i++){
			for($i=0;$i<$n_dati;$i++){


				$RIGA.="<tr id=\"riga_$i\" style=\"display:none;\">";

				for($j=0;$j<$n_campi;$j++){

					if($i==0) {
						if($SM_UPDATE && $SM_DELETE &&  $j==0) $TR_TH.="<th class=\"nocolor-r\">&nbsp;</th>";
						elseif($SM_UPDATE && $j==0) $TR_TH.="<th class=\"nocolor\">&nbsp;</th>";
						if($SM_DELETE && $j==0) $TR_TH.="<th class=\"nocolor\">&nbsp;</th>";

						
						$nome_campo = (trim($campi_alias_frontend[$j])=='') ? $campi[$j] : $campi_alias_frontend[$j];
						
						$TR_TH.="<th>".$nome_campo."</th>";
					}

					//				$chiave_record = (isset($dati_chiavi_sub[$i])) ? $dati_chiavi_sub[$i] : "n".$i;
					$chiave_record = ($n_all_dati>=$i) ? $i : "n".$i;

					if($SM_UPDATE && $j==0)	$RIGA.="<th class=\"sm-update\"> <a href=\"javascript:;\" onclick=\"modifica($i);\">"._('modify')."</a> </th>";

					if($SM_DELETE && $j==0)	$RIGA.="<th class=\"sm-delete\"> <a href=\"javascript:;\" onclick=\"elimina($i);\">"._('delete')."</a> </th>";

					$dato_ins = (isset($dati_sub[$i][$j])) ? $dati_sub[$i][$j] : "";

					$RIGA.=tipo_campo_submask($chiave_record,$dato_ins,$info_campo_mostrare[$j]);


					// TRIGGER ASSEGNAZIONE VALORI

				}

				$dati_serializzati = (isset($dati_all_sub[$i])) ? base64_encode(serialize($dati_all_sub[$i])) : "";

				$RIGA.="<td style=\"display:none;\"><span id=\"value_riga_$i\">".$dati_serializzati."</span></td>";


				$RIGA.="</tr>";

			}
		}
		else{


			$RIGA.="\t\t<tr id=\"riga_x\" style=\"display:none;\">\n";

			for($j=0;$j<$n_campi;$j++){
				
				// impostazione eventuale alias
				$nome_campo = (trim($campi_alias_frontend[$j])=='') ? $campi[$j] : $campi_alias_frontend[$j];
						
				$TR_TH.="\t\t\t<th>".$nome_campo."</th>\n";

				$chiave_record = "x";

				$dato_ins = (isset($dati_sub[$i][$j])) ? $dati_sub[$i][$j] : "";

				$RIGA.=tipo_campo_submask($chiave_record,$dato_ins,$info_campo_mostrare[$j]);


			}

			$RIGA.="</tr>\n";

		}

		$TR_TH .= "\t\t</tr>\n";


		$TABLE = "<table class=\"table-submask\" border=\"0\" summary=\"dati sottomaschera\">\n<tbody>\n";

		$TABLE.= $TR_TH;

		$TABLE.= $RIGA;

		$TABLE.="</tbody></table>\n\n";


	}	

	######################################################################################################################
	#
	#
	#	OPZIONE VISTA EMBEDDED
	#
	#

	else if($SM_TIPO_VISTA=='embed'){

		// crea le celle di tabella

		$RIGA="";

		$TR_TH = "\t\t<tr>\n";


		if($n_dati>0){


			//	for($i=0;$i<$MAX_RECORD_SUB;$i++){
			for($i=0;$i<$n_dati;$i++){


				$RIGA.="<tr id=\"riga_$i\" >";

				for($j=0;$j<$n_campi;$j++){

					if($i==0) {
						// if($SM_UPDATE && $SM_DELETE &&  $j==0) $TR_TH.="<th class=\"nocolor-r\">&nbsp;</th>";
						// elseif($SM_UPDATE && $j==0) $TR_TH.="<th class=\"nocolor\">&nbsp;</th>";
						// if($SM_DELETE && $j==0) $TR_TH.="<th class=\"nocolor\">&nbsp;</th>";

						
						$nome_campo = (trim($campi_alias_frontend[$j])=='') ? $campi[$j] : $campi_alias_frontend[$j];
						
						$TR_TH.="<th>".$nome_campo."</th>";
					}

					//				$chiave_record = (isset($dati_chiavi_sub[$i])) ? $dati_chiavi_sub[$i] : "n".$i;
					$chiave_record = ($n_all_dati>=$i) ? $i : "n".$i;

					// if($SM_UPDATE && $j==0)	$RIGA.="<th class=\"sm-update\"> <a href=\"javascript:;\" onclick=\"modifica($i);\">"._('modify')."</a> </th>";

					// if($SM_DELETE && $j==0)	$RIGA.="<th class=\"sm-delete\"> <a href=\"javascript:;\" onclick=\"elimina($i);\">"._('delete')."</a> </th>";

					$dato_ins = (isset($dati_sub[$i][$j])) ? $dati_sub[$i][$j] : "";

					$RIGA.=tipo_campo_submask($chiave_record,$dato_ins,$info_campo_mostrare[$j]);


					// TRIGGER ASSEGNAZIONE VALORI

				}

				// $dati_serializzati = (isset($dati_all_sub[$i])) ? base64_encode(serialize($dati_all_sub[$i])) : "";

				// $RIGA.="<td style=\"display:none;\"><span id=\"value_riga_$i\">".$dati_serializzati."</span></td>";


				$RIGA.="</tr>";

			}


			$TR_TH .= "\t\t</tr>\n";


			$TABLE = "<table id=\"sm-oid-$ID_SUBMASK\" class=\"table-submask table-submask-vis\" border=\"0\" summary=\"subform data\">\n<tbody>\n";

			$TABLE.= $TR_TH;

			$TABLE.= $RIGA;

			$TABLE.="</tbody></table>\n\n";


		}
		// caso nessun valore
		else{

		    $i=0;
		    
		    $RIGA.="<tr id=\"riga_$i\" >";

			for($j=0;$j<$n_campi;$j++){

				$nome_campo = (trim($campi_alias_frontend[$j])=='') ? $campi[$j] : $campi_alias_frontend[$j];

				$TR_TH.="<th>".$nome_campo."</th>";

				$chiave_record = ($n_all_dati>=$i) ? $i : "n".$i;

				$dato_ins = (isset($dati_sub[$i][$j])) ? $dati_sub[$i][$j] : "";

				$RIGA.=tipo_campo_submask($chiave_record,$dato_ins,$info_campo_mostrare[$j]);

				// TRIGGER ASSEGNAZIONE VALORI
			}


			$TR_TH .= "\t\t</tr>\n";

			$RIGA.="</tr>";

			$TABLE = "<table id=\"sm-oid-$ID_SUBMASK\" class=\"table-submask sub-search\" border=\"0\" style=\"display:none\" summary=\"subform data\">\n<tbody>\n";

			$TABLE.= $TR_TH;

			$TABLE.= $RIGA;

			$TABLE.="</tbody></table>\n\n";
			
			$TABLE.="<div class=\"embed-nodata\">".sprintf(_('No data available for the subform "%s"'), $nome_sub)."</div>\n";
		}

		
               
	}	
	
	######################################################################################################################
	#
	#
	#	OPZIONE VISTA SCHEDE
	#
	#
	else{
		
		
		$DIVR="";
		
		if($n_dati>0){


			//	for($i=0;$i<$MAX_RECORD_SUB;$i++){
			for($i=0;$i<$n_dati;$i++){


				$DIVR.="<div id=\"riga_$i\" style=\"display:none;\" class=\"entry-record\">";
				
				$chiave_record = ($n_all_dati>=$i) ? $i : "n".$i;

				$controlli="";
				
					if($SM_UPDATE)	$controlli.="<a href=\"javascript:;\" onclick=\"modifica($i);\">"._('modify')."</a>";
					if($SM_UPDATE && $SM_DELETE) $controlli.=" - ";
					if($SM_DELETE)	$controlli.="<a href=\"javascript:;\" onclick=\"elimina($i);\">"._('delete')."</a>";

					if($SM_UPDATE || $SM_DELETE) $DIVR.="<span class=\"controlli\">$controlli</span>\n";
				
				$DIVR.="<table summary=\"tabella di formattazione record\" border=\"0\" class=\"tab-format\">";

				for($j=0;$j<$n_campi;$j++){

					
					$dato_ins = (isset($dati_sub[$i][$j])) ? $dati_sub[$i][$j] : "";

					$DIVR.="<tr>";
					$DIVR.= ($j==0) ? "<td class=\"numerone\" rowspan=\"$n_campi\">". ($i+1) ."</td>" : "";
					
					// impostazioni per l'alias
					$nome_campo = (trim($campi_alias_frontend[$j])=='') ? $campi[$j] : $campi_alias_frontend[$j];
					
					$DIVR.= ($info_campo_mostrare[$j]['in_tipo']=='hidden') ? "" : "<td class=\"label\">".$nome_campo."</td>";
					
					$DIVR.= tipo_campo_submask($chiave_record,$dato_ins,$info_campo_mostrare[$j]);
					
					$DIVR.="</tr>";

					// TRIGGER ASSEGNAZIONE VALORI

				}

				$dati_serializzati = (isset($dati_all_sub[$i])) ? base64_encode(serialize($dati_all_sub[$i])) : "";

				$DIVR.="<span id=\"value_riga_$i\" style=\"display:none;\">".$dati_serializzati."</span>";

			
				$DIVR.="</table>";
				$DIVR.="</div>";

			}
		}
		else{


			$DIVR.="\t\t<div id=\"riga_x\" style=\"display:none;\" class=\"entry-record\">\n";
			$DIVR.="<table summary=\"tabella di formattazione record\" border=\"0\">";

			for($j=0;$j<$n_campi;$j++){

//				$TR_TH.="\t\t\t<th>".$campi[$j]."</th>\n";

				$chiave_record = "x";

				$dato_ins = (isset($dati_sub[$i][$j])) ? $dati_sub[$i][$j] : "";
				
				// impostazioni per l'alias
				$nome_campo = (trim($campi_alias_frontend[$j])=='') ? $campi[$j] : $campi_alias_frontend[$j];

				$DIVR.="<tr>";
				$DIVR.= ($j==0) ? "<td class=\"numerone\" rowspan=\"$n_campi\">0</td>" : "";				
				$DIVR.="<td class=\"label\">".$nome_campo."</td>".tipo_campo_submask($chiave_record,$dato_ins,$info_campo_mostrare[$j]);
				$DIVR.="</tr>";

			}

			$DIVR.="</table>\n";
			$DIVR.="</div>\n";

		}
		
		
		
		
	}


$JS="";
	
$OUT.= ($SM_TIPO_VISTA=='scheda' || $SM_TIPO_VISTA=='schedash') ? $DIVR:$TABLE;

$JS.="
<script type=\"text/javascript\">
		
		/* <![CDATA[ */

		for(j=0;j<$MAX_RECORD_SUB;j++){
		
			if(j<n_righe){
				id_riga_considerata = 'riga_'+j;
				document.getElementById(id_riga_considerata).style.display='';
			}
		}
		
		/* ]]> */
		
</script>\n";


if($SM_TIPO_VISTA=='embed'){
    
    print $TABLE;
    print $JS;
    
}
else{ 
    $OUT.=$JS;
    $OUT.=closeLayout1();
    
    print $OUT;
}

