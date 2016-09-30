<?php
/**
 * Sono presenti in questo file le procedure per inizializzare, inserire e eliminare le informazioni 
 * sulle tabelle nel registro di VFront per tenerlo allineato con l'information_schema del database.
 * Questo file � la versione per Postgres
 * 
 * @desc Procedure di manutenzione del registro per Postgres
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2009 M.Marcello Verona
 * @version 0.95 $Id: sync_reg_tab.postgres.php 1108 2014-10-20 20:25:04Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see sync_reg_tab.mysql.php
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
		vmsql_begin($link);
		
		$ok_del=0;
		$c=0;
		
		for($d=0;$d<count($tabelle);$d++){
			
			$sql_del="DELETE FROM {$db1['frontend']}.registro_tab WHERE table_name='".$tabelle[$d]."'";
			
			$q_del=vmsql_query($sql_del,$link,true);
			
			if(vmsql_affected_rows($link,$q_del)>0){
				$ok_del++;
			}
			
			$c++;
		}
		
//		echo $c."<br/>$ok_del";
		
		// FEEDBACK
		if($ok_del==$c){
			vmsql_commit($link);
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ok_del");	
		}
		else{
			vmsql_rollback($link);
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
		$qg=vmsql_query("SELECT gid FROM {$db1['frontend']}.gruppo",$link);
		
		list($gruppi)=vmsql_fetch_row_all($qg,true);
		
		
		/*
			Anche se ho a disposizione il nome delle tabelle le prendo di nuovo,
			perché mi servono anche i commenti
		*/
		
		// PRENDI nomi tabelle e commenti
		$sql_info="SELECT t.table_name, t.table_type, obj_description(c.oid, 'pg_class'::name) AS comment 
							FROM information_schema.tables AS t,  pg_catalog.pg_class AS c, pg_namespace AS ns
							WHERE t.table_name IN ('".implode("','",$tabelle)."')
							AND t.table_schema='{$db1['dbname']}'
							AND ns.nspname = t.table_schema
							AND  t.table_name=c.relname ";
		
		$q_info=vmsql_query($sql_info,$link);
		
		vmsql_begin($link);
		
		// PER OGNI TABELLA...
		while($info_tab=vmsql_fetch_assoc($q_info)){
			
			// PER OGNI GRUPPO
			for($g=0;$g<count($gruppi);$g++){
				
				Admin_Registry::inserisci_registro($info_tab,$gruppi[$g]);
			}
			
		}
		
		
		
		vmsql_commit($link);
		
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
	
		
		// ELIMINA LE IMPOSTAZIONI DI TABELLA NEL FRONTEND
		$sql_del="DELETE FROM {$db1['frontend']}.registro_tab WHERE table_name='".$tabella."'";
		
		vmsql_begin($link);
		
		$q_del=vmsql_query($sql_del,$link,true);
		
		if(vmsql_affected_rows($link,$sql_del)>0){
			
			vmsql_commit($link);
		}
		else{
			vmsql_rollback($link);
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ko_azzera1");
			exit;
		}
		
		// PRENDI GRUPPI
		$qg=vmsql_query("SELECT gid FROM {$db1['frontend']}.gruppo",$link);
		
		list($gruppi)=vmsql_fetch_row_all($qg,true);
		
		/*
			Anche se ho a disposizione il nome delle tabelle le prendo di nuovo,
			perché mi servono anche i commenti
		*/
		
		// PRENDI nomi tabelle e commenti
		$sql_info="SELECT t.table_name, t.table_type, obj_description(c.oid, 'pg_class'::name) AS comment 
							FROM information_schema.tables AS t, pg_catalog.pg_class AS c
							WHERE t.table_name='$tabella'
							AND t.table_schema='{$db1['dbname']}' 
							AND  t.table_name=c.relname
							LIMIT 1";
		
		Common::rpc_debug($sql_info);
		
		$q_info=vmsql_query($sql_info,$link);
		
		vmsql_begin($link);
		
		// PER OGNI TABELLA...
		$info_tab=vmsql_fetch_assoc($q_info);
			
			// PER OGNI GRUPPO
			for($g=0;$g<count($gruppi);$g++){
				
				Admin_Registry::inserisci_registro($info_tab,$gruppi[$g]);
			}
			
		
		
		vmsql_commit($link);
		
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
			$SQL_confronto_colonne1 = "
		SELECT column_name,udt_name as column_type,data_type,is_nullable
		FROM information_schema.columns
		WHERE table_name='$tabella'
		AND table_schema='{$db1['dbname']}'
		ORDER BY ordinal_position,column_name";
			
			$q_conf1=vmsql_query($SQL_confronto_colonne1,$link);
			
			while($RSc1=vmsql_fetch_assoc($q_conf1)){
				
				$colonne1[$RSc1['column_name']]=$RSc1;
				
				$colonne[$RSc1['column_name']]=1;
			}
			
		
		// INFO COLONNE DAL frontend
			$SQL_confronto_colonne2="
		SELECT column_name,column_type,data_type,is_nullable
		FROM {$db1['frontend']}.registro_col c, {$db1['frontend']}.registro_tab t
		WHERE t.id_table=c.id_table
		AND c.gid=0
		AND t.table_name='$tabella'
		ORDER BY ordinal_position,column_name
			";
		
		$q_conf2=vmsql_query($SQL_confronto_colonne2,$link);
		
		while($RSc2=vmsql_fetch_assoc($q_conf2)){
			
			$colonne2[$RSc2['column_name']]=$RSc2;
			
			$colonne[$RSc2['column_name']]=1;
		}
		
		
		
		
		
		if($colonne1!=$colonne2){
			
			$TESTO= "<table id=\"diff-tab\" summary=\"differenze tra le tabelle\" border=\"1\">";
			
					
			$TESTO.= "
				<tr >
					  <th>"._("column_name A")."</th><th>"._("column_type A")."</th><th>"._("data_type A")."</th><th>"._("is_nullable A")."</th>
					  <th style=\"background-color:#444;\" rowspan=\"".(count($colonne)+1) ."\">&nbsp;</th>
					  <th>"._("column_name B")."</th><th>"._("column_type B")."</th><th>"._("data_type B")."</th><th>"._("is_nullable B")."</th>
					  
				</tr>\n";
			
			
			foreach($colonne as $campo=>$val){
				
				
				if($colonne1[$campo]!=$colonne2[$campo]){
					
					$class="evidenza";
										
					// tipo di differenza:
					if($colonne1[$campo]['column_name']==''){
						
						$campi_diversi[]=array($campo,"DELETE") ;
						
						$TESTO.= "
						<tr class=\"$class\">
							  ".str_repeat("<td>&nbsp;</td>",4)."
							  
							  <td>".implode("</td>\t\n<td>", (array) $colonne2[$campo])."</td>
						</tr>\n";
						
					}
					elseif($colonne2[$campo]['column_name']==''){
						
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
			
			$TESTO.="<p><strong>A</strong>: "._("information_schema")."<br /><strong>B</strong>: "._("db vfront")."</p>\n";
			
			
			$differenza=true;
			
			
			
		}
		
		else{
		
			$TESTO=_("Non sembra ci siano incongruenze in questa tabella");
			
			$differenza=false;
		
		}

		
	}else{
		
		header("Location: ".$_SERVER['PHP_SELF']);
	}

	
	$files=array("sty/admin.css","sty/log.css","sty/tabelle.css");
	
	echo openLayout1(_("Sincronizzazione database/frontend"),$files);
	
 	echo "<div id=\"briciole\"><a href=\"index.php\">"._("home amministrazione")."</a> &raquo; "
 	    ."<a href=\"".basename($_SERVER['PHP_SELF'])."\">"._("sincronizzazione db/frontend")."</a> &raquo; "._("differenze per la tabella")." $tabella</div>";

	echo "<h1>"._("Differenze di impostazione per la tabella")." <span class=\"var\">$tabella</span></h1>\n";
	
	echo $TESTO;
	
	if($differenza){
		
		echo "<form action=\"".$_SERVER['PHP_SELF']."?aggiorna_campi\" method=\"post\">\n";
		
		foreach($campi_diversi as $k=>$v){
			
			echo "<input type=\"hidden\" name=\"campo_aggiorna[]\" value=\"$tabella;{$v[0]};{$v[1]}\" />\n";
			
		}
		
		echo "<input type=\"submit\" name=\"aggiorna\" value=\" "._("Synchronize fields")." \" />\n";
		
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

echo openLayout1(_("Sincronizzazione database"),$files);


	echo "	
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


	$OUT="<div id=\"briciole\"><a href=\"index.php\">"._("home amministrazione")."</a> &raquo; "._("sincronizzazione db/frontend")."</div>";

	$OUT.="<h1>"._("Sincronizzazione database/frontend")."</h1>\n";
	
	$OUT.="<img src=\"../img/registri.gif\" class=\"img-float\" alt=\"impostazioni registri\" />\n";
	
	echo $OUT;
			

	// FEEDBACK 
		if(isset($_GET['feed'])){
			
			if($_GET['feed']=='ok_nuove')
				echo "<div class=\"feed-mod-ok\">"._("New tables set correctly")."</div><br />";
			
			elseif($_GET['feed']=='ko_nuove')
				echo "<div class=\"feed-mod-ko\">"._("Nessuna nuova tabella impostata: eccezione della procedura")."</div><br />";
			
			elseif($_GET['feed']=='ok_del')
				echo "<div class=\"feed-mod-ok\">"._("Impostazioni tabelle eliminate correttamente")."</div><br />";	
				
			elseif($_GET['feed']=='ko_del')
					echo "<div class=\"feed-mod-ko\">"._("Nessuna impostazione tabella eliminata: eccezione della procedura")."</div><br />";	
					
			elseif($_GET['feed']=='ko_azzera1')
				echo "<div class=\"feed-mod-ko\">"._("Nessuna modifica effettuata sul registro della tabella: eccezione della procedura")."</div><br />";
			
			elseif($_GET['feed']=='ko_azzera2')
					echo "<div class=\"feed-mod-ko\">"._("Nessuna modifica effettuata sul registro della tabella: eccezione della procedura")."</div><br />";
			
			elseif($_GET['feed']=='ok_azzera')
					echo "<div class=\"feed-mod-ok\">"._("Impostazioni della tabella riconfigurate correttamente")."</div><br />";
				
		}
	
	
	echo "<p>"._("In questa pagina sono presenti funzioni per la manutenzione del frontend e la sincronizzazione con il database")."<br /><br />
			<strong>"._("Nota bene!")."</strong> "._("Nessuna delle operazioni sotto elencate modifica la struttura del database!")."<br />
			"._("Ogni operazione sar&agrave; solo sul contenuto del frontend.")."
			</p><br />\n";
	
	echo "	
<div id=\"contenitore-variabili\">
	<div id=\"box-etichette\">
		
		<ul class=\"eti-var-gr\">

		
			<li onclick=\"eti('integrita');\" id=\"li-integrita\" class=\"attiva\">Integrit&agrave; frontend</li>
			<li onclick=\"eti('manuale');\" id=\"li-manuale\" class=\"disattiva\">Ripristino manuale</li>

		</ul>
	
	</div>";

	
	
	// TEST 1 DB->FRONTEND
	
	$sql1="SELECT t.table_name , obj_description(c.oid, 'pg_class'::name) AS table_comment 
					FROM information_schema.tables t
					INNER JOIN pg_catalog.pg_class AS c ON t.table_name=c.relname
					INNER JOIN pg_namespace AS ns ON ns.oid=c.relnamespace
					LEFT OUTER JOIN  {$db1['frontend']}.registro_tab r ON t.table_name=r.table_name
					WHERE t.table_schema='{$db1['dbname']}'
					AND ns.nspname = t.table_schema
					AND (t.table_type='BASE TABLE' OR t.table_type='VIEW') 
					AND t.table_name NOT IN (SELECT r.table_name FROM {$db1['frontend']}.registro_tab r)";
	
	$q1=vmsql_query($sql1,$link);
	

	
	
	$n_row_t1=vmsql_num_rows($q1);
	
	if($n_row_t1>0){
		
		$test1=false;
		$matrice_t1 = vmsql_fetch_assoc_all($q1);
	}
	else{
		
		$test1=true;
	}
	
	
	
	
	// TEST 2 FRONTEND->DB
	$sql2="SELECT r.id_table, r.table_name, r.commento
					FROM {$db1['frontend']}.registro_tab r
					WHERE r.table_name NOT IN (
						SELECT t.table_name FROM information_schema.tables t
						WHERE t.table_schema='{$db1['dbname']}' AND (t.table_type='BASE TABLE' OR t.table_type='VIEW'))
					AND gid=0";
	
	$q2=vmsql_query($sql2,$link);
	
	
	$n_row_t2=vmsql_num_rows($q2);
	
	if($n_row_t2>0){
		
		$test2=false;
		$matrice_t2 = vmsql_fetch_assoc_all($q2);
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
	
		
	$q_tabelle=vmsql_query("SELECT table_name FROM {$db1['frontend']}.registro_tab WHERE gid=0 ORDER BY table_name",$link);
	
	list($tabelle_presenti) = vmsql_fetch_row_all($q_tabelle,true);
	
	
	$uguali = true;
	
	for($i=0;$i<count($tabelle_presenti);$i++){
	
		$colonne1=array();
		$colonne2=array();
		
	
		// INFO COLONNE DALL' information_schema
		$SQL_confronto_colonne1 = "
		SELECT column_name,data_type,is_nullable
		FROM information_schema.columns
		WHERE table_name='{$tabelle_presenti[$i]}'
		AND table_schema='{$db1['dbname']}'
		ORDER BY ordinal_position,column_name";
			
			$q_conf1=vmsql_query($SQL_confronto_colonne1,$link);
			
			while($RSc1=vmsql_fetch_assoc($q_conf1)){
				
				$colonne1[$RSc1['column_name']]=$RSc1;
			}
		
		
		// INFO COLONNE DAL frontend
		$SQL_confronto_colonne2="
		SELECT column_name,data_type,is_nullable
		FROM {$db1['frontend']}.registro_col c, {$db1['frontend']}.registro_tab t
		WHERE t.id_table=c.id_table
		AND c.gid=0
		AND t.table_name='{$tabelle_presenti[$i]}'
		ORDER BY ordinal_position,column_name
			";
		
		$q_conf2=vmsql_query($SQL_confronto_colonne2,$link);
		
		while($RSc2=vmsql_fetch_assoc($q_conf2)){
			
			$colonne2[$RSc2['column_name']]=$RSc2;
		}
		
		
		
		if($colonne1!=$colonne2){
			
			$diversi[$tabelle_presenti[$i]]=array($colonne1,$colonne2);
			$tab_diverse[]=$tabelle_presenti[$i];
			$uguali=false;
	
		
		
		}

		
		
	}
	
#
#
################################################

	$OUT_TEST='';
	
	
	if($test1 && $test2 && $uguali){
		
		$OUT_TEST.="<p class=\"verde\"><strong>"._("Tutto bene!")."</strong><br />"._("Il database &egrave; sincronizzato con il frontend")."</p>\n";
		$TAB_SYNC1="";
		$TAB_SYNC2="";
	}
	elseif(!$test1 && $test2){
		$OUT_TEST.="<p><strong class=\"var\">"._("Attenzione!")."</strong><br />
		"._("Ci sono <strong>nuove tabelle</strong> nel database da sincronizzare con il frontend")."</p>\n";
	}
	elseif($test1 && !$test2){
		$OUT_TEST.="<p><strong class=\"var\">"._("Attenzione!")."</strong><br />
		"._("Ci sono <strong>tabelle obsolete</strong> nel frontend da eliminare (non pi&ugrave; presenti in database)")."</p>\n";
	}
	elseif(!$test1 && !$test2){
		$OUT_TEST.="<p><strong class=\"var\">"._("Attenzione!")."</strong><br />
		"._("Ci sono <strong>nuove tabelle</strong> nel database da sincronizzare")." <br />
		"._("e <strong>tabelle obsolete</strong> nel frontend da eliminare (non pi&ugrave; presenti in database)")."</p>\n";
	}
	
	if(!$uguali){
		$OUT_TEST.="<p><strong class=\"var\">"._("Attenzione!")."</strong><br />
		"._("Ci sono <strong>campi impostati diversamente</strong> nel database e nel frontend")."</p>\n";
		
		$tab_string="";
		
		for($k=0;$k<count($tab_diverse);$k++){
				
				$tab_string.="<a class=\"rosso\" href=\"".$_SERVER['PHP_SELF']."?diff=".$tab_diverse[$k]."\">".$tab_diverse[$k]."</a>, <br />\n";
		}
		
		if(count($tab_diverse)>1){
			
			
			
			$OUT_TEST.="<p>"._("Le tabelle da sincronizzare sono")." <br /> $tab_string </p>";
		}
		else{	
			$OUT_TEST.="<p>"._("La tabella da sincronizzare &egrave;")." $tab_string </p>";
		}
		
	}
	
	
	
// TABELLE NUOVE DA SINCRONIZZARE
	if(!$test1){
		
		$TAB_SYNC1="<table class=\"tab-color\" summary=\""._("Tabelle da sincronizzare")."\">\n\t<tbody>\n";
		
		$TAB_SYNC1.="
		
			<tr>
				<th>"._("nuova tabella")."</th>
				<th>"._("commento")."</th>
			</tr>
		";
		
		$VAL1="";
		
		for($i=0;$i<count($matrice_t1);$i++){
			
			$VAL1.=$matrice_t1[$i]['table_name'].",";
			
			$TAB_SYNC1.="
			<tr>
				<td>".$matrice_t1[$i]['table_name']."</td>
				<td>".$matrice_t1[$i]['table_comment']."</td>
			</tr>
			";
		}

		$TAB_SYNC1.="\t</tbody>\n</table>
		
		<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<input type=\"hidden\" name=\"nuove\" value=\"".substr($VAL1,0,-1)."\" />
			
			<input type=\"submit\" name=\"sincronizza_nuove\" value=\""._("Inserisci tabelle nel frontend")."\" />
			<br /><br /><br />
		</form>\n";
		
	}
	
	
// TABELLE OBSOLETE DA ELIMINARE
	if(!$test2){
		
		$TAB_SYNC2="<table class=\"tab-color\" summary=\""._("Tabelle da sincronizzare")."\">\n\t<tbody>\n";
		
		$TAB_SYNC2.="
		
			<tr>
				<th class=\"arancio\">"._("tabella obsoleta")."</th>
				<th class=\"arancio\">"._("commento")."</th>
			</tr>
		";
		
		$VAL2="";
		
		for($i=0;$i<count($matrice_t2);$i++){
			
			$VAL2.=$matrice_t2[$i]['table_name'].",";
			
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
			
			<input type=\"submit\" name=\"elimina_obsolete\" value=\""._("Elimina tabelle obsolete")."\" />
			<br /><br /><br />
		</form>\n";
	}
	
	
	
	
	
	
	
	
	
	
	
	// CONTENITORE TEST INTEGRITA'
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-integrita\">
	
		$OUT_TEST
		$TAB_SYNC1
		$TAB_SYNC2
	</div>
	";
	
	
	// PRENDI TUTTE LE TABELLE DAL FRONTEND
	
	$q_front=vmsql_query("SELECT table_name,data_modifica,commento FROM {$db1['frontend']}.registro_tab
						 GROUP BY table_name,data_modifica,commento
						 ORDER BY table_name",$link);
		
	$matrice_front=vmsql_fetch_assoc_all($q_front);
	
	
	$TAB_FRONT="<table class=\"tab-color\" summary=\""._("Tabelle del frontend")."\">\n\t<tbody>\n";
	
	
	$TAB_FRONT.="
		
			<tr>
				<th class=\"arancio\">"._("tabella")."</th>
				<th class=\"arancio\">"._("commento")."</th>
				<th class=\"arancio\">"._("data configurazione")."</th>
				<th class=\"arancio\">"._("ripristina configurazione")."</th>
			</tr>
		";
		
		
		for($i=0;$i<count($matrice_front);$i++){
			
			$data_config = (intval($matrice_front[$i]['data_modifica'])>0) ? date("d/m/Y H:i",$matrice_front[$i]['data_modifica']) : "-";
			
			$TAB_FRONT.="
			<tr>
				<td>".$matrice_front[$i]['table_name']."</td>
				<td>".$matrice_front[$i]['commento']."</td>
				<td>".$data_config."</td>
				<td><a href=\"".$_SERVER['PHP_SELF']."?azzera=".$matrice_front[$i]['table_name']."\">"._("ripristina")."</a></td>
			</tr>
			";
		}
	
	
	
		$TAB_FRONT.="\t</tbody>\n</table>";
	
	
	
	
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-manuale\" style=\"display:none;\">
	
			<p><strong>"._("Attenzione!")."</strong><br />
			"._("Se si ripristina la configurazione di una tabella si annulleranno tutte le impostazioni finora definite per quella tabella per tutti i gruppi,")."<br />
			"._("comprese le impostazioni per le sottomaschere. Usare questa funzione con cautela.")."</p>

	$TAB_FRONT
	
	</div>
</div>

	";

	
	
echo closeLayout1();
?>