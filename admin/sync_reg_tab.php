<?php
/**
 * Sono presenti in questo file le procedure per inizializzare, inserire e eliminare le informazioni 
 * sulle tabelle nel registro di VFront per tenerlo allineato con l'information_schema del database.
 * Questo file � la versione per MySQL
 * 
 * @desc Procedure di manutenzione del registro per MySQL
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: sync_reg_tab.php 1128 2014-12-17 11:25:17Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see sync_reg_tab.postgres.php
 */


require_once("../inc/conn.php");
require_once("../inc/layouts.php");

 proteggi(3);
 
##############################################
#
#	PROCEDURA AGGIORNAMENTO DEI CAMPI DELLE TABELLE
#
#

if(isset($_POST['aggiorna'])){

	
	$ar_campi=$_POST['campo_aggiorna'];
	
	
	for($i=0;$i<count($ar_campi);$i++){
		
		list($tabella,$campo,$operazione) = explode(";",$ar_campi[$i]);
		
		$feed[] = Admin_Registry::aggiorna_campo($tabella,$campo,$operazione);
		
	}
	
	
	header("Location: ".$_SERVER['PHP_SELF']."?diff=".$tabella);
	exit;
	
}
 
##############################################
#
#	PROCEDURA ELIMINAZIONE TABELLE OBSOLETE
#
#

if(isset($_POST['elimina_obsolete'])){
	
	$tabelle=explode(",",$_POST['obsolete']);
	
	if(is_array($tabelle)){
	
		// inizia la transazione
		$vmreg->begin();
		
		$ok_del=0;
		$c=0;
		
		for($d=0;$d<count($tabelle);$d++){
			
			$sql_del="DELETE FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE table_name='".$tabelle[$d]."'";
			
			$q_del=$vmreg->query($sql_del,true);
			
			if($vmreg->affected_rows($q_del)>0){
				$ok_del++;
			}
			
			$c++;
		}
		
		// FEEDBACK
		if($ok_del==$c){
			$vmreg->commit();
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ok_del");	
		}
		else{
			$vmreg->rollback();
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ko_del");	
		}
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?feed=ko_del");	
	}
	

	exit;
}

#
#	--fine tabelle obsolete
#
################################################################################�



##############################################
#
#	PROCEDURA INSERIMENTO TABELLE NUOVE
#
if(isset($_POST['sincronizza_nuove'])){

	
	$tabelle=explode(",",$_POST['nuove']);
	
	if(is_array($tabelle)){
		
		// PRENDI GRUPPI
		$qg=$vmreg->query("SELECT gid FROM {$db1['frontend']}{$db1['sep']}gruppo");
		
		list($gruppi)=$vmreg->fetch_row_all($qg,true);
		
		
		
		
		/*
			Anche se ho a disposizione il nome delle tabelle le prendo di nuovo,
			perché mi servono anche i commenti
		*/
		
		// PRENDI nomi tabelle e commenti
		$IS=new iSchema();
		$tables_all=$IS->get_tables();
		
		$vmreg->begin();
		
		for($i=0;$i<count($tables_all);$i++){
			
			if(in_array($tables_all[$i]['table_name'], $tabelle)){
				
				// PER OGNI GRUPPO
				for($g=0;$g<count($gruppi);$g++){
					Admin_Registry::inserisci_registro($tables_all[$i],$gruppi[$g]);
				}
			
			}
		}
		
		$vmreg->commit();
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=ok_nuove");
		
	}
	else{
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=ko_nuove");
	}
	
	exit;
	
}
	
	
#
#	--fine nuove tabelle
#
################################################################################�	
	




##############################################
#
#	PROCEDURA RIPRISTIONO TABELLA
#
if(isset($_GET['azzera'])){

	$tabella=trim($_GET['azzera']);
	
	if(RegTools::is_tabella($tabella)){
        
        $R = new Registry();
        $R->cache_flush();
	
		
		// ELIMINA LE IMPOSTAZIONI DI TABELLA NEL FRONTEND
		$sql_del="DELETE FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE table_name='".$tabella."'";
		
		$vmreg->begin();
		
		$q_del=$vmreg->query($sql_del,true);
		
		if($vmreg->affected_rows($q_del)>0){
			
			$vmreg->commit();
		}
		else{
			$vmreg->rollback();
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ko_azzera1");
			exit;
		}
		
	
		// PRENDI GRUPPI
		$qg=$vmreg->query("SELECT gid FROM {$db1['frontend']}{$db1['sep']}gruppo");
		
		list($gruppi)=$vmreg->fetch_row_all($qg,true);
		
		/*
			Anche se ho a disposizione il nome delle tabelle le prendo di nuovo,
			perch� mi servono anche i commenti
		*/
		
		// PRENDI nomi tabelle e commenti
		
		$IS=new iSchema();
		
		list($info_tab)=$IS->get_tables(false,$tabella);
		
		$vmreg->begin();
			
			// PER OGNI GRUPPO
			for($g=0;$g<count($gruppi);$g++){
				
				Admin_Registry::inserisci_registro($info_tab,$gruppi[$g]);
			}
			
		
		
		$vmreg->commit();
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=ok_azzera");
		
	}
	else{
		
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=ko_azzera2");
	}
	
	
	
	
	exit;
	
}
	
	
#
#	--fine procedura ripristino tabella
#
################################################################################






##############################################
#
#	PROCEDURA VISUALIZZAZIONE DIFFERENZE
#
if(isset($_GET['diff'])){

	$tabella=trim($_GET['diff']);
	
	if(RegTools::is_tabella($tabella)){
	
		
		$colonne1=array();
		$colonne2=array();
		
	
		// INFO COLONNE DALL' information_schema
				
		$IS = new iSchema();
		
		$col_def=$IS->get_columns($tabella);
		
		
		for($j=0;$j<count($col_def);$j++){
			
			$colonne1[$col_def[$j]['column_name']]=array('column_name'=>$col_def[$j]['column_name'],
														 'column_type'=>$col_def[$j]['column_type'],
														 'character_maximum_length'=>$col_def[$j]['character_maximum_length'],
														 'is_nullable'=>$col_def[$j]['is_nullable']
														 );
			$colonne[$col_def[$j]['column_name']]=1;
		}
		
			
		
		// INFO COLONNE DAL frontend
		/*
			$SQL_confronto_colonne2="
		SELECT column_name,column_type,character_maximum_length,is_nullable
		FROM {$db1['frontend']}{$db1['sep']}registro_col c, {$db1['frontend']}{$db1['sep']}registro_tab t
		WHERE t.id_table=c.id_table
		AND c.gid=0
		AND t.table_name='$tabella'
		ORDER BY ordinal_position,column_name
			";
		
		$q_conf2=$vmreg->query($SQL_confronto_colonne2);
		 * 
		 */

		$col_front=RegTools::prendi_colonne_frontend($tabella,
					" column_name, column_type, character_maximum_length, is_nullable",
					false, 0, "assoc");

		foreach($col_front as $k=>$RSc2){

//		while($RSc2=$vmreg->fetch_assoc($q_conf2)){
			
			if($RSc2['character_maximum_length']=='0'){
				$RSc2['character_maximum_length']='';
			}
			
			$colonne2[$RSc2['column_name']]=$RSc2;
			
			$colonne[$RSc2['column_name']]=1;
		}
		
		
		
		
		
		if($colonne1!=$colonne2){
			
			$TESTO= "<table id=\"diff-tab\" summary=\""._("differences between tables")."\" border=\"1\">";
			
					
			$TESTO.= "
				<tr >
					  <th>"._("column_name A")."</th><th>"._("column_type A")."</th><th>"._("max_chars")." A</th><th>"._("is_nullable A")."</th>
					  <th style=\"background-color:#444;\" rowspan=\"".(count($colonne)+1) ."\">&nbsp;</th>
					  <th>"._("column_name B")."</th><th>"._("column_type B")."</th><th>"._("max_chars")." B</th><th>"._("is_nullable B")."</th>
					  
				</tr>\n";
			
			
			foreach($colonne as $campo=>$val){
				
				
				if(!isset($colonne1[$campo]) || !isset($colonne2[$campo]) || $colonne1[$campo]!=$colonne2[$campo]){
					
					$class="evidenza";
					
					
					
					// tipo di differenza:
					if(!isset($colonne1[$campo]) || $colonne1[$campo]['column_name']==''){
						
						$campi_diversi[]=array($campo,"DELETE") ;
						
						$TESTO.= "
						<tr class=\"$class\">
							  ".str_repeat("<td>&nbsp;</td>",4)."
							  
							  <td>".implode("</td>\t\n<td>", (array) $colonne2[$campo])."</td>
						</tr>\n";
						
					}
					elseif(!isset($colonne2[$campo]) || $colonne2[$campo]['column_name']==''){
						
						$campi_diversi[]=array($campo,"INSERT") ;
						
						$TESTO.= "
						<tr class=\"$class\">
							  <td>".implode("</td>\t\n<td>",(array) $colonne1[$campo])."</td>
							  
							  ".str_repeat("<td>&nbsp;</td>",4)."
						</tr>\n";
						
						
					}
					else{
						
						$campi_diversi[]=array($campo,"UPDATE") ;
						
						$TESTO.= "
						<tr class=\"$class\">
							  <td>".implode("</td>\t\n<td>",(array) $colonne1[$campo])."</td>
							  
							  <td>".implode("</td>\t\n<td>", (array) $colonne2[$campo])."</td>
						</tr>\n";
						
					}
					
					
				}
				else{
					
					$class="null";
					
					$TESTO.= "
						<tr class=\"$class\">
							  <td>".implode("</td>\t\n<td>",(array) $colonne1[$campo])."</td>
							  
							  <td>".implode("</td>\t\n<td>", (array) $colonne2[$campo])."</td>
						</tr>\n";
				}
				
				
				
			}
			
			
		
			$TESTO.= "</table>";	
			
			$TESTO.="<p><strong>A</strong>: information_schema<br /><strong>B</strong>: db vfront</p>\n";
			
			
			$differenza=true;
			
			
			
		}
		
		else{
		
			$TESTO=_("No inconsistencies in this table");
			
			$differenza=false;
		
		}

		
	}else{
		
		header("Location: ".$_SERVER['PHP_SELF']);
	}

	
	$files=array("sty/admin.css","sty/log.css","sty/tabelle.css");
	
	echo openLayout1(_("Synchronize database/frontend"),$files);
	
	echo breadcrumbs(array("HOME","ADMIN",
					basename($_SERVER['PHP_SELF'])=>_("syncronize db/frontend"),
					_("differences for the table")." $tabella"));


	echo "<h1>"._("Setting differences for the table")." <span class=\"var\">$tabella</span></h1>\n";
	
	echo $TESTO;
	
	if($differenza){
		
		echo "<form action=\"".$_SERVER['PHP_SELF']."?aggiorna_campi\" method=\"post\">\n";
		
		foreach($campi_diversi as $k=>$v){
			
			echo "<input type=\"hidden\" name=\"campo_aggiorna[]\" value=\"$tabella;{$v[0]};{$v[1]}\" />\n";
			
		}
		
		echo "<input type=\"submit\" name=\"aggiorna\" value=\" "._("Synchonize fields")." \" />\n";
		
		echo "</form>\n";
	}
	
	echo closeLayout1();
	
	exit;
	
}
	
	
#
#	--fine procedura visualizzazione differneze
#
################################################################################�	



	

$files=array("sty/admin.css","sty/tabelle.css","sty/linguette.css");

$OUT= openLayout1(_("Database synchronization"),$files);


	$OUT.= "
	<script type=\"text/javascript\">
	
		var divs = new Array('integrita','manuale');
	
	
		function eti(ido){
			
			for (var i in divs){
				document.getElementById('cont-eti-'+divs[i]).style.display='none';
				document.getElementById('li-'+divs[i]).className='disattiva';
			}
			
			// attiva il selezionato
			document.getElementById('cont-eti-'+ido).style.display='';
			document.getElementById('li-'+ido).className='attiva';
			
		}
	
	
	</script>
	";


	$OUT.= breadcrumbs(array("HOME","ADMIN",_("syncronize db/frontend")));

	$OUT.="<h1>"._("Synchronize database/frontend")."</h1>\n";
	
	$OUT.="<img src=\"../img/registri.gif\" class=\"img-float\" alt=\""._("registry settings")."\" />\n";
	
	echo $OUT;
			

	// FEEDBACK 
		if(isset($_GET['feed'])){
			
			if($_GET['feed']=='ok_nuove')
				echo "<div class=\"feed-mod-ok\">"._("New tables set correctly")."</div><br />";
			
			elseif($_GET['feed']=='ko_nuove')
				echo "<div class=\"feed-mod-ko\">"._("No new table set: procedure exception")."</div><br />";
			
			elseif($_GET['feed']=='ok_del')
				echo "<div class=\"feed-mod-ok\">"._("Table settings deleted correctly")."</div><br />";	
				
			elseif($_GET['feed']=='ko_del')
					echo "<div class=\"feed-mod-ko\">"._("No setting deleted from the table: procedure exception")."</div><br />";	
					
			elseif($_GET['feed']=='ko_azzera1')
				echo "<div class=\"feed-mod-ko\">"._("No changes were made in the register of the table: procedure exception")."</div><br />";
			
			elseif($_GET['feed']=='ko_azzera2')
					echo "<div class=\"feed-mod-ko\">"._("No changes were made in the register of the table: procedure exception")."</div><br />";
			
			elseif($_GET['feed']=='ok_azzera')
					echo "<div class=\"feed-mod-ok\">"._("Table settings reconfigured correctly")."</div><br />";
				
		}
	
	
	echo "<p>"._("This page contains functions for frontend maintenance and synchronizing with the database")."<br /><br />
			<strong>"._("Please note!")."</strong> "._("None of the operations listed below change the structure of the database!")."<br />
			"._("Each operation will only change the content of the frontend.")."
			</p><br />\n";
	
	echo "	
<div id=\"contenitore-variabili\">
	<div id=\"box-etichette\">
		
		<ul class=\"eti-var-gr\">

		
			<li onclick=\"eti('integrita');\" id=\"li-integrita\" class=\"attiva\">"._("Frontend integrity")."</li>
			<li onclick=\"eti('manuale');\" id=\"li-manuale\" class=\"disattiva\">"._("Manual restore")."</li>

		</ul>
	
	</div>";

	
	
	$IS=new iSchema();
	
	// IS Tables
	
	$tables_info=$IS->get_tables();
	
	// reg Tables
	$reg_tables0=RegTools::prendi_tabelle(0,false,false,true);
	
	$tables=array();
	for($i=0;$i<count($tables_info);$i++){
		
		$tables[]=$tables_info[$i]['table_name'];
	}
	
	$reg_tables=array();
	for($i=0;$i<count($reg_tables0);$i++){
		
		$reg_tables[]=$reg_tables0[$i]['table_name'];
	}

	$mat_nuove_tabelle=array();
	for($i=0;$i<count($tables);$i++){
		
		if(!in_array($tables[$i],$reg_tables)){
			$mat_nuove_tabelle[]=$tables_info[$i];
		}
	}
	
	$mat_vecchie_tabelle=array();
	for($i=0;$i<count($reg_tables0);$i++){
		
		if(!in_array($reg_tables[$i],$tables)){
			$mat_vecchie_tabelle[]=$reg_tables0[$i];
		}
	}
	
	
	
	
	// TEST 1 DB->FRONTEND
	if(count($mat_nuove_tabelle)>0){
		
		$test1=false;
		$matrice_t1 = $mat_nuove_tabelle; 
	}
	else{
		
		$test1=true;
		$matrice_t1=array();
	}
	
	
	
	
	// TEST 2 FRONTEND->DB
	if(count($mat_vecchie_tabelle)>0){
		
		$test2=false;
		$matrice_t2 = $mat_vecchie_tabelle;
	}
	else{
		
		$test2=true;
	}
	
	
###########################################################################
#
#	CONTROLLO CAMPI TABELLE
#
#
	// prendi le tabelle dal frontend
	
	$uguali = true;
	
	$IS=new iSchema();
	
	for($i=0;$i<count($reg_tables);$i++){
	
		$colonne1=array();
		$colonne2=array();
		
		$col_def=$IS->get_columns($reg_tables[$i], '', false);
		
		
		for($j=0;$j<count($col_def);$j++){
			
			$colonne1[$col_def[$j]['column_name']]=array('column_name'=>$col_def[$j]['column_name'],
														 'column_type'=>$col_def[$j]['column_type'],
														 'character_maximum_length'=>intval($col_def[$j]['character_maximum_length']),
														 'is_nullable'=>$col_def[$j]['is_nullable']
														 );
		}
			
		
		// INFO COLONNE DAL frontend
			$SQL_confronto_colonne2="
		SELECT column_name,column_type,character_maximum_length,is_nullable
		FROM {$db1['frontend']}{$db1['sep']}registro_col c, {$db1['frontend']}{$db1['sep']}registro_tab t
		WHERE t.id_table=c.id_table
		AND c.gid=0
		AND t.table_name='{$reg_tables[$i]}'
		ORDER BY ordinal_position
			";
		
		$q_conf2=$vmreg->query($SQL_confronto_colonne2);
		
		while($RSc2=$vmreg->fetch_assoc($q_conf2)){
			
			$colonne2[$RSc2['column_name']]=$RSc2;
		}
		

		if($colonne1!=$colonne2){
			
			$diversi[$reg_tables[$i]]=array($colonne1,$colonne2);
			$tab_diverse[]=$reg_tables[$i];
			$uguali=false;
		}
		
	}
	
#
#
################################################


	$OUT_TEST='';
	$TAB_SYNC1='';
	$TAB_SYNC2='';
	
	if($test1 && $test2 && $uguali){
		
		$OUT_TEST.="<p class=\"verde\"><strong>"._("All done!")."</strong><br />"._("The database is synchronized with frontend.")."</p>\n";
		$TAB_SYNC1.="";
		$TAB_SYNC2.="";
	}
	elseif(!$test1 && $test2){
		$OUT_TEST="<p><strong class=\"var\">"._("Warning!")."</strong><br />
		"._("There are <strong>new tables</strong> in the database to be synchronized with the frontend")."</p>\n";
	}
	elseif($test1 && !$test2){
		$OUT_TEST="<p><strong class=\"var\">"._("Warning!")."</strong><br />
		"._("There are <strong> outdated </strong> tables in the frontend to be removed (no longer in the database)")."</p>\n";
	}
	elseif(!$test1 && !$test2){
		$OUT_TEST="<p><strong class=\"var\">"._("Warning!")."</strong><br />
		"._("There are <strong>new tables</strong> in the database to be synchronized")." <br />
		"._("and <strong>old tables</strong> in the frontend to be removed (no longer in the database)")."</p>\n";
	}
	
	
		
		
		
	
	
	$new_t=array();
	$old_t=array();
	
	
// TABELLE NUOVE DA SINCRONIZZARE
	if(!$test1){
		
		$TAB_SYNC1="<table class=\"tab-color\" summary=\""._("Tables to synchronize")."\">\n\t<tbody>\n";
		
		$TAB_SYNC1.="
		
			<tr>
				<th>"._("new table")."</th>
				<th>"._("comment")."</th>
			</tr>
		";
		
		$VAL1="";

		for($i=0;$i<count($matrice_t1);$i++){
			
			$VAL1.=$matrice_t1[$i]['table_name'].",";
			$new_t[]=$matrice_t1[$i]['table_name'];
			
			$TAB_SYNC1.="
			<tr>
				<td>".$matrice_t1[$i]['table_name']."</td>
				<td>".$matrice_t1[$i]['comment']."</td>
			</tr>
			";
		}

		$TAB_SYNC1.="\t</tbody>\n</table>
		
		<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<input type=\"hidden\" name=\"nuove\" value=\"".substr($VAL1,0,-1)."\" />
			
			<input type=\"submit\" name=\"sincronizza_nuove\" value=\""._("Insert tables in frontend")."\" />
			<br /><br /><br />
		</form>\n";
		
	}
	
	
// TABELLE OBSOLETE DA ELIMINARE
	if(!$test2){
		
		$TAB_SYNC2="<table class=\"tab-color\" summary=\""._("Tables to synchronize")."\">\n\t<tbody>\n";
		
		$TAB_SYNC2.="
		
			<tr>
				<th class=\"arancio\">"._("obsolete table")."</th>
				<th class=\"arancio\">"._("comment")."</th>
			</tr>
		";
		
		$VAL2="";
		
		for($i=0;$i<count($matrice_t2);$i++){
			
			$VAL2.=$matrice_t2[$i]['table_name'].",";
			$old_t[]=$matrice_t2[$i]['table_name'];
			
			$TAB_SYNC2.="
			<tr>
				<td>".$matrice_t2[$i]['table_name']."</td>
				<td>".$matrice_t2[$i]['commento']."</td>
			</tr>
			";
		}

		$TAB_SYNC2.="\t</tbody>\n</table>
		
		<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<input type=\"hidden\" name=\"obsolete\" value=\"".substr($VAL2,0,-1)."\" />
			
			<input type=\"submit\" name=\"elimina_obsolete\" value=\""._("Delete obsolete tables")."\" />
			<br /><br /><br />
		</form>\n";
	}
	
	
	
	
	// differenze campi
	$OUT_TEST_TAB='';

	if(!$uguali){
		
		$mostra_diff_campi=false;
		
		
		
		$tab_string="";
		
		for($k=0;$k<count($tab_diverse);$k++){
			
				if(!in_array($tab_diverse[$k],$new_t) && !in_array($tab_diverse[$k],$old_t)){
				
					$tab_string.="<a class=\"rosso\" href=\"".$_SERVER['PHP_SELF']."?diff=".$tab_diverse[$k]."\">".$tab_diverse[$k]."</a>, <br />\n";
					
					$mostra_diff_campi=true;
				}
		}
		
		if($mostra_diff_campi){
		
			$OUT_TEST_TAB="<p><strong class=\"var\">"._("Warning!")."</strong><br />
			"._("There are fields with <strong>differences in settings </strong> between database and frontend")."</p>\n";
			
			if(count($tab_diverse)>1){
				
				$OUT_TEST_TAB.="<p>"._("The tables to synchronize are")." <br /> $tab_string \n<br />
							"._("Synchronize the tables by clicking on the table names (in red) or performing a <em>manual restore</em> for these tables")."</p>";
			}
			else{	
				$OUT_TEST_TAB.="<p>"._("The table to synchronize is")." $tab_string \n<br />
							"._("Synchronize the table by clicking on its name (in red) or perform a <em>manual reset</em> for this table")."</p>";
			}
		}
	}
	
	
	
	
	
	
	
	
	// CONTENITORE TEST INTEGRITA'
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-integrita\">
	
		$OUT_TEST
		$TAB_SYNC1
		$TAB_SYNC2
		$OUT_TEST_TAB
	</div>
	";
	
	
	// PRENDI TUTTE LE TABELLE DAL FRONTEND
	
	$q_front=$vmreg->query("SELECT table_name,data_modifica,commento FROM {$db1['frontend']}{$db1['sep']}registro_tab
						  WHERE gid=0
						 ORDER BY table_name");
	
	$matrice_front=$vmreg->fetch_assoc_all($q_front);
	
	
	$TAB_FRONT="<table class=\"tab-color\" summary=\""._("Frontend tables")."\">\n\t<tbody>\n";
	
	
	$TAB_FRONT.="
		
			<tr>
				<th class=\"arancio\">"._("table")."</th>
				<th class=\"arancio\">"._("comment")."</th>
				<th class=\"arancio\">"._("configuration data")."</th>
				<th class=\"arancio\">"._("restore configuration")."</th>
			</tr>
		";
		
		
		for($i=0;$i<count($matrice_front);$i++){
			
			$data_config = (intval($matrice_front[$i]['data_modifica'])>0) ? date("d/m/Y H:i",$matrice_front[$i]['data_modifica']) : "-";
			
			$TAB_FRONT.="
			<tr>
				<td>".$matrice_front[$i]['table_name']."</td>
				<td>".$matrice_front[$i]['commento']."</td>
				<td>".$data_config."</td>
				<td><a href=\"".$_SERVER['PHP_SELF']."?azzera=".$matrice_front[$i]['table_name']."\">"._("rollback")."</a></td>
			</tr>
			";
		}
	
	
	
		$TAB_FRONT.="\t</tbody>\n</table>";
	
	
	
	
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-manuale\" style=\"display:none;\">
	
			<p><strong>"._("Warning!")."</strong><br />
			"._("If you restore the configuration of a table this will remove all settings so far defined for that table for all groups,")."<br />
			"._("including settings for subforms. Use this feature with caution").".</p>

	$TAB_FRONT
	
	</div>
</div>

	";

	
	
echo closeLayout1();
?>