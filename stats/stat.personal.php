<?php
/**
 * Mediante questo file si possono creare, modificare e cancellare nuove statistiche sul database.
 * Le statistiche sono impostata come query e sono registrate nella tabella di registro "stats"
 * 
 * @desc File di gestione delle statistiche impostate dall'utente.
 * @package VFront
 * @subpackage Stats
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: stat.personal.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
 */


require_once("../inc/conn.php");
require_once("../inc/layouts.php");
require_once("../inc/func.stat.php");
//require_once("./stat.graph.php");
require_once("./stat.graph2.php");

proteggi(1);




############################################
#
#	REGISTRA NUOVA
#

if(isset($_GET['crea']) && count($_POST)>0){

	proteggi(2);
	
	$_dati = $vmreg->recursive_escape($_POST);
	
	
	$sql=sprintf("INSERT INTO {$db1['frontend']}{$db1['sep']}stat 
				  (nome_stat, desc_stat, def_stat, auth_stat , autore, tipo_graph, published, data_stat)
				  VALUES
				  ('%s','%s','%s',%d,%d,'%s',%d,'%s')",
				  $_dati['nome_stat'],
				  $_dati['desc_stat'],
				  $_dati['def_stat'],
				  $_dati['auth_stat'],
				  $_SESSION['user']['uid'],
				  trim($_dati['tipo_graph']),
				  $_dati['publish'],
				  date('Y-m-d H:i:s')
				  );
				  
	$q=$vmreg->query($sql);
	
	if($vmreg->affected_rows($q)==1){
		
		$id=$vmreg->insert_id($db1['frontend'].".stat",'id_stat');
		
		header("Location: ".$_SERVER['PHP_SELF']."?id_s=$id");
		
	}
	else{
		
		header("Location: index.php?feed=konew");
	}
				 
	
	exit;
	
}








############################################
#
#	APPLICA MODIFICA 
#

if(isset($_GET['applica_mod']) && count($_POST)>0){

	proteggi(2);
	
	$_dati = $vmreg->recursive_escape($_POST);
	
	
	$sql=sprintf("UPDATE {$db1['frontend']}{$db1['sep']}stat 
				  SET nome_stat='%s', desc_stat='%s', def_stat='%s', 
					auth_stat=%d , tipo_graph='%s', published=%d, data_stat='%s'
				  WHERE id_stat=%d
				  ",
				  $_dati['nome_stat'],
				  $_dati['desc_stat'],
				  $_dati['def_stat'],
				  $_dati['auth_stat'],
				  trim($_dati['tipo_graph']),
				  $_dati['publish'],
				  date("Y-m-d H:i:s"),
				  $_dati['id_stat']);
				  
	$q=$vmreg->query($sql);
	
	if($vmreg->affected_rows($q)==1){
		
		header("Location: index.php?feed=okmod");
		
	}
	else{
		
		header("Location: index.php?feed=komod");
	}
				 
	
	exit;
	
}









############################################
#
#	APPLICA ELIMINA
#

if(isset($_POST['elimina_stat']) && intval($_POST['elimina_stat'])>0){
	
	proteggi(2);
	
	$sql=sprintf("DELETE FROM  {$db1['frontend']}{$db1['sep']}stat 
				  WHERE id_stat=%d
				  ",
				  $_POST['elimina_stat']);
				  
	$q=$vmreg->query($sql);
	
	if($vmreg->affected_rows($q)==1){
		
		header("Location: index.php?feed=okdel");
		
	}
	else{
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=kodel");
	}
				 
	
	exit;
	
}















####################################################
#
#	CREA UNA NUOVA STATISTICA
#
#

if(isset($_GET['new']) || isset($_GET['modifica'])){
	
	proteggi(2);
	
	// Caso modifica
	if(isset($_GET['modifica'])){
		
		// prendi i dati
		$q=$vmreg->query("SELECT nome_stat, desc_stat, def_stat, autore, auth_stat, tipo_graph , published
						FROM {$db1['frontend']}{$db1['sep']}stat WHERE id_stat=".intval($_GET['modifica']));
		
		$RS=$vmreg->fetch_assoc($q);
		
		
		// Verifica il diritto di modifica!
		if($RS['autore']!=$_SESSION['user']['uid'] && $_SESSION['user']['livello']<3){
			
			header("Location: ".$_SERVER['PHP_SELF']."?errore=noauth");
			exit;
		}
		
		$azione='applica_mod';
		$titolo=_('Modify statistics');
		$titolo2=$titolo." <span class=\"var\">".$RS['nome_stat']."</span>";
		
		$txt_button=_("Modify statistics");
		
	}
	else{
		
		// valori predefiniti
		
		$RS=array('nome_stat'=>'', 
				  'desc_stat'=>'',
				  'def_stat'=>'',
				  'auth_stat'=>1,
				  'autore'=>'',
				  'tipo_graph'=>'barre');
				  
				  
		$azione='crea';
		
		$titolo=$titolo2=_("Create new statistic");
		
		$txt_button=_("Register a new statistic");
	}
	
	
	
	$chk_auth[1] = ($RS['auth_stat']==1) ? "checked=\"checked\"" : "";
	$chk_auth[2] = ($RS['auth_stat']==2) ? "checked=\"checked\"" : "";
	$chk_auth[3] = ($RS['auth_stat']==3) ? "checked=\"checked\"" : "";
	
	$chk_tipo_graph[1] = (trim($RS['tipo_graph'])=='barre') ? "checked=\"checked\"" : "";
	$chk_tipo_graph[2] = (trim($RS['tipo_graph'])=='torta') ? "checked=\"checked\"" : "";
	
	
	
	
	
	
	
	$files=array('js/test_query.js','js/yav/yav.js','js/yav/yav-config-it.js');
	
	echo openLayout1($titolo,$files);
	
	echo breadcrumbs(array("HOME","ADMIN",
						   "index.php"=>_("statistics"),
							strtolower($titolo)));
	
	echo "<h1>$titolo2</h1>\n";
	
	echo "<script type=\"text/javascript\">
	
	var rules=new Array();
	
	rules[0]='nome_stat|required|"._("The name of the statistic is required")."';
	rules[1]='def_stat|required|"._("No SQL definition for the statistics")."';
	
	</script>\n";
	
	
	echo "<form name=\"f1\" action=\"".$_SERVER['PHP_SELF']."?$azione\" method=\"post\" onsubmit=\"return performCheck('f1', rules, 'classic');\" >\n";
	
	echo "<fieldset style=\"width:70%; padding:20px;\">\n";
	echo "<legend style=\"font-weigth:bold\">"._("Parameter for new statistic")."</legend>\n";
	
	
	$input_modifica = (isset($_GET['modifica'])) ? "<input type=\"hidden\" name=\"id_stat\" value=\"".intval($_GET['modifica'])."\" />\n" : "";

	$chk_publish = (isset($_GET['modifica']) && $RS['published']==1) ? 'checked="checked"' : '';
	
	echo "
	
		<label for=\"nome_stat\">"._("Name of statistic:")."</label><br />
		<input type=\"text\" id=\"nome_stat\" name=\"nome_stat\" size=\"40\" maxlength=\"240\" value=\"".$RS['nome_stat']."\" />
		<div class=\"info-campo\" >"._("Name for custom statistic")."</div>
		
		<label for=\"desc_stat\">"._("Description of statistic:")."</label><br />
		<textarea id=\"desc_stat\" name=\"desc_stat\" cols=\"50\" rows=\"4\" >".$RS['desc_stat']."</textarea>
		<div class=\"info-campo\" >"._("Description of custom statistic")."</div>
		
		<br />
		
		<p>"._("Access method for new statistics")."<br />
			<input type=\"radio\" id=\"auth_stat1\" name=\"auth_stat\" value=\"1\" ".$chk_auth[1]." /> <label for=\"auth_stat1\">"._("Public")."</label><br />
			<input type=\"radio\" id=\"auth_stat2\" name=\"auth_stat\" value=\"2\" ".$chk_auth[2]." /> <label for=\"auth_stat2\">"._("Accessible only to my group")."</label><br />
			<input type=\"radio\" id=\"auth_stat3\" name=\"auth_stat\" value=\"3\" ".$chk_auth[3]." /> <label for=\"auth_stat3\">"._("Allow only by me")."</label><br />
		</p>
		
		
		<p>
			<input type=\"hidden\" id=\"publish_0\" name=\"publish\" value=\"0\" />
			<input type=\"checkbox\" id=\"publish_1\" name=\"publish\" value=\"1\" ".$chk_publish." /> <label for=\"publish_1\">"._("Published in home page")."</label><br />
		</p>
		
		
		<p>"._("Type of graph:")."<br />
			<input type=\"radio\" id=\"graph_type1\" name=\"tipo_graph\" value=\"barre\" ".$chk_tipo_graph[1]." /> <label for=\"graph_type1\">"._("Bar graph")."</label><br />
			<input type=\"radio\" id=\"graph_type2\" name=\"tipo_graph\" value=\"torta\" ".$chk_tipo_graph[2]." /> <label for=\"graph_type2\">"._("Pie graph")."</label><br />
		</p>
		
		
		<label for=\"def_stat\">"._("Definition of statistic")."</label> 
			<input value=\""._("Test")."\" onclick=\"try_query(document.getElementById('def_stat').value,2)\" type=\"button\" />
			<span id=\"feed_altro_2\" class=\"feed_altro\">&nbsp;</span>
			<br />
		<textarea  id=\"def_stat\" name=\"def_stat\" cols=\"60\" rows=\"7\" >".$RS['def_stat']."</textarea>
		<div class=\"info-campo\" >"._("SQL Query to definie the statistic")."</div>
				
		$input_modifica
		
		<input type=\"submit\"  name=\"invia\" value=\"  $txt_button  \" />\n";
	
	
	echo "</fieldset>\n";
	
	echo "</form>\n";
	
	echo closeLayout1();
	
	exit;
	
}










#####################################################
#
#	MOSTRA UNA STATISTICA DA ID
#
#####################################################



if(isset($_GET['id_s'])){
	
	$errore=false;
		
	$ID_STAT= (int) $_GET['id_s'];
	
	$sql="SELECT s.nome_stat, s.desc_stat, s.def_stat, s.auth_stat , s.autore, s.tipo_graph, s.data_stat, u.gid
		  FROM {$db1['frontend']}{$db1['sep']}stat s, {$db1['frontend']}{$db1['sep']}utente u 
		  WHERE id_stat=$ID_STAT
		  AND u.id_utente=s.autore";
	
	$q=$vmreg->query($sql);

	$RS=$vmreg->fetch_assoc($q);


	if($vmreg->num_rows($q)==0){
			
			$errore='norecord';
	}
	
	
	
	// Controllo di propriet� validi per i non admin
	
	
	
	if($_SESSION['user']['livello']<3){
	
		if($RS['auth_stat']==2 && $RS['gid']!=$_SESSION['gid']){
			
			$errore='nogid';
		}
		
		if($RS['auth_stat']==3 && $RS['autore']!=$_SESSION['user']['uid']) {
			
			$errore='nouser';
		}
	
	}
	
	
	// testo la query
	$test_q1=$vmsql->query_try($RS['def_stat']);
	
	if($test_q1==0){
		
		$errore='noquery';
	}
	else if($test_q1==-1){
		$errore='danger';
	}
	else{
	
	
		// Non ci sono errori fino a qui, vai avanti
		
		$q_stat=$vmsql->query($RS['def_stat']);
		
		list($etichette, $frequenze)=$vmsql->fetch_row_all($q_stat,true);
		
		
		// se non ci sono dati
		if(count($etichette)==0 || count($frequenze)==0){
			
			$errore='nodata';
		}
	}
	
	
	
	// se non ci sono errori
	if($errore===false){
	
		// prendo la frequenza più grande per la scala
		$duplicato_freq=$frequenze;	
		rsort($duplicato_freq);
		$scala=$duplicato_freq[0]*1.1;
		
		$scala = ($scala<5) ? 5: $scala;
		
		$stima_altezza = round(count($frequenze)*22.5,0);
		
		$stima_altezza = ($stima_altezza<300) ? 300: $stima_altezza;
		
		$nome_file_tmp = 'img_pers';
		
		// Grafico a barre
		if(trim($RS['tipo_graph'])=='barre'){
			
			//$grafico=barre($frequenze,$etichette,$scala,$RS['nome_stat'],$nome_file_tmp,550,$stima_altezza);
			$grafico=barre_pchart($frequenze,$etichette,$scala,$RS['nome_stat'],$nome_file_tmp,550,$stima_altezza);
		}
		
		// Grafico a torta
		else if(trim($RS['tipo_graph'])=='torta'){
			
			//$grafico=torta($frequenze,$etichette,$RS['nome_stat'],$nome_file_tmp);
			$grafico=torta_pchart($frequenze,$etichette,$RS['nome_stat'],$nome_file_tmp);
		}
		
		
		
		
		if($grafico){
	
			$OUT='';
			
			// SCRIVE UNA TABELLA:
			
			$OUT.="<table summary=\"cont\" class=\"tab-cont\">\n<tr>";
			
			if($grafico){
				
				$OUT.= "<td><img src=\""._PATH_TMP_HTTP."/$nome_file_tmp.png?".time()."\" alt=\"test\"   class=\"img-stat\" /></td>\n";
			}
			
			if(trim($RS['tipo_graph'])=='torta'){
				$OUT.= "<td>".stat_tabella($etichette,$frequenze,array("valore","n"),true) . "</td>";
			}
			else{
				$OUT.= "<td>".stat_tabella($etichette,$frequenze,array("valore","n")) . "</td>";
			}
			
			$OUT.="</tr></table>\n";
			
		}
		
		
		$nome_stat_html=htmlentities(stripslashes($RS['nome_stat']), ENT_QUOTES, FRONT_ENCODING);
		
		$files=array("sty/stat.css");
		
		// prendi autore
		$q_a=$vmreg->query("SELECT ".$vmreg->concat("nome, ' ' , cognome")." FROM {$db1['frontend']}{$db1['sep']}utente WHERE id_utente=".intval($RS['autore']));
		
		list($nome_autore) = $vmreg->fetch_row($q_a);
		
		echo openLayout1("Statistica ".$RS['nome_stat'],$files);
		
		
		if(isset($_GET['ref']) && $_GET['ref']=='home'){
			
			echo breadcrumbs(array("HOME",$nome_stat_html));
			
		}
		else{

			echo breadcrumbs(array("HOME","ADMIN","index.php"=>_("statistics"),$nome_stat_html));
		}
		
	    
	    
		echo "<h1>$nome_stat_html</h1>\n";
		
		echo "<div style=\"padding:10px; background-color:#FFF2CF;width:40em;margin:3px 3px 16px 3px;border:1px solid #444;\">\n";
		
		echo "<p><b>"._("Description")."</b>: ".htmlentities($RS['desc_stat'],ENT_QUOTES,FRONT_ENCODING)."</p>";
		echo "<p><b>"._("Author")."</b>: ".$nome_autore."</p>";
		echo "<p><b>"._("Definition date")."</b>: ".VFDate::date_encode($RS['data_stat'])."</p>";
		
		echo "</div>\n";
		
		echo $OUT;
		
		echo closeLayout1();
	}
	
	else{
		
		$files=array("sty/stat.css");
		
		echo openLayout1(_("Statistic")." ".$RS['nome_stat'],$files);
		
		if($_GET['ref']=='home'){
			
			echo breadcrumbs(array("HOME",$nome_stat_html));
			
		}
		else{
			echo breadcrumbs(array("HOME","ADMIN","index.php"=>_("statistics"),$nome_stat_html));
		}
	    
		echo "<h1>"._("Cannot display the data")."</h1>\n";
		
		switch($errore){
			
			case 'nogid' : echo "<p><strong>"._("Warning!")."</strong> "._("You are trying to access a public statistic for a group of users of which you are not a member.")."</p>\n";
			break;
			
			case 'nouser' : echo "<p><strong>"._("Warning!")."</strong> "._("You are trying to access a private statistic of which you are not the author.")."</p>\n";
			break;
			
			case 'noquery' : echo "<p><strong>"._("Warning!")."</strong> "._("You are trying to access a statistic which seems to have a mistake in the SQL. Please check the query definition")."</p>\n";
			break;
			
			case 'nodata' : echo "<p>"._("No data returned for this statistic")."</p>\n";
			break;
			
			case 'danger' : echo "<p>"._("The SQL query contains unsafe words and was not performed.")."</p>\n";
			break;
			
			case 'norecord' : echo "<p><strong>"._("Warning!")."</strong> "._("You are asking for a non-existent statistic")."</p>\n";
			break;
			
			default : echo "<p><strong>"._("Warning!")."</strong> "._("There is a generic error in displaying statistics ")."</p>\n";
			
		}
		
			
		
		echo closeLayout1();
		
	}
	
	
	
}


if(isset($_GET['elimina'])){

		proteggi(2);
	
		$ID_STAT= (int) $_GET['elimina'];
	
		$sql="SELECT s.nome_stat, s.desc_stat, s.def_stat, s.auth_stat ,
			  s.autore, s.tipo_graph, s.data_stat, u.gid, "
			  .$vmreg->concat("u.nome, ' ' , u.cognome", 'nomecognome')."
			  FROM {$db1['frontend']}{$db1['sep']}stat s, {$db1['frontend']}{$db1['sep']}utente u 
			  WHERE id_stat=$ID_STAT
			  AND u.id_utente=s.autore";
		
		$q=$vmreg->query($sql);
	
		$RS=$vmreg->fetch_assoc($q);
	
		echo openLayout1(_("Statistic")." ".$RS['nome_stat']);
		
		echo breadcrumbs(array("HOME","ADMIN","index.php"=>_("statistics"),_("delete statistic")));
		
		echo "<h1>"._("Delete statistic")."</h1>\n";
		
		echo "<div style=\"padding:10px; background-color:#FFF2CF;width:40em;margin:3px 3px 16px 3px;border:1px solid #444;\">\n";
		
		echo "<p><b>ID</b>: $ID_STAT</p>";
		echo "<p><b>"._("Description")."</b>: ".htmlentities($RS['desc_stat'],ENT_QUOTES,FRONT_ENCODING)."</p>";
		echo "<p><b>"._("Author")."</b>: ".$RS['nomecognome']."</p>";
		echo "<p><b>"._("Graph type")."</b>: ".trim($RS['tipo_graph'])."</p>";
		echo "<p><b>"._("Definition date")."</b>: ".VFDate::date_encode($RS['data_stat'])."</p>";
		
		echo "</div>\n";
		
		
		echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" >\n";
		
		// info statistica...
		
		echo "<p><strong>"._("Warning!")."</strong> "._("Do you really want to remove this statistic? This operation cannot be undone")."</p>\n";
		
		echo "<input type=\"hidden\" name=\"elimina_stat\" value=\"".intval($_GET['elimina'])."\" />\n";
		echo "<input type=\"submit\" name=\"esegui_elimina_stat\" value=\""._("Delete statistic")."\" />\n";
		
		echo "&nbsp; "._("or")." &nbsp; <a href=\"index.php\">"._("go back")."</a>";
		
		echo "</form>\n";
		
		
		echo closeLayout1();
	
	
}


?>