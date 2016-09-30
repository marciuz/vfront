<?php
/**
* Function for get the VFront XML
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.getxml.php 1078 2014-06-13 15:35:53Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/





/**
 * Get the XML from vfront settings
 *
 * @param array $_INFO ('querybased'=>1|0, 'action'=>'table_name', 'id'=> int, 'c'=> int, 'type'=>'XML'|)
 * @return array 'XML','XSL','DTD'
 */
function get_vfront_xml($_INFO){
	
	global  $vmsql, $vmreg, $db1;

	$RAW=false;

	// query based?
	$QB = (isset($_INFO['querybased']) && $_INFO['querybased']=='@') ? 1:0;



	if($QB){
		
		$nome_report = preg_replace("'[^a-z0-9_-]'i","",$_INFO['action']);
	}
	else{
		$tabella = preg_replace("'[^a-z0-9_]'i","",$_INFO['action']);
	}
		
	
	
	########################################
	#
	#	DIRITTI XML
	#
	########################################
	
	
	if($QB){
		
		// cerca i diritti come report tabella
		$q=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE nome_report='$nome_report' ORDER BY lastData DESC LIMIT 1");
	}
	else{
		// cerca i diritti come report tabella
		$q=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE tabella='$tabella' ORDER BY lastData DESC LIMIT 1");
		
	}
		
	if($vmreg->num_rows($q)==0 && !Common::is_admin()){
		echo "<h1>"._("Access forbidden")."</h1><!-- ".__LINE__."-->\n"; exit;
	}
	else $RS_rules=$vmreg->fetch_assoc($q);
	
	if($RS_rules['accesso']=='PUBLIC'){
		
		// non fa niente e continua con lo script
	}
	elseif($RS_rules['accesso']=='FRONTEND'){
		proteggi(1);
	}
	elseif($RS_rules['accesso']=='GROUP'){
		
		$gruppi=explode(",",$RS_rules['accesso_gruppo']);
		
		if(is_array($gruppi) && in_array($_SESSION['gid'],$gruppi)){
			
			// va avanti
		}
		else{
			echo "<h1>"._("Access forbidden")."</h1><!-- ".__LINE__."-->\n"; exit;
		}
	}
	else if (!Common::is_admin()){ // RESTRICT o altro...
		
		echo "<h1>"._("Access forbidden")."</h1><!-- ".__LINE__."-->\n"; exit;
	}
	
	
	
	
	
	if($QB){
		
	}
	else{
	
		$PK = RegTools::prendi_PK($tabella,intval($_SESSION['gid']));
		
		$orderby = RegTools::prendi_orderby($tabella,intval($_SESSION['gid']));
		
		if(trim($orderby)=='ASC') $orderby = 1;
		
		//aggiungi l'ID all'order by 
		if($PK!=''){
			$orderby_doppio=$orderby.", $PK ";
		}
		else{
			$orderby_doppio=$orderby;
		}
		
		$campo_orderby = str_replace(array(" ASC"," DESC"),"",$orderby);
		$operatore_orderby = preg_match('| DESC|i',$orderby) ? " > " : " < ";
		
	
	
		// CASO RISULTATO DI RICERCA-------------------------------------------------------
		// SE c'� l'id in GET prendi calcola a che punto dell'elenco si � arrivati
		if(isset($_INFO['id']) && intval($_INFO['id'])>0){
	
	
	
			// Prendi il moe del campo orderby ed il vaolre relativo all'id
	
			/*$sql_calcola_sub = "SELECT s.$campo_orderby 
									FROM $tabella s WHERE s.".$PK."='".$_INFO['id']."'";
	
			$sql_calcola = "SELECT count(*) FROM ".$_INFO['action']." t
									WHERE t.$campo_orderby $operatore_orderby ($sql_calcola_sub) OR t.$campo_orderby IS NULL";
	
			Common::rpc_debug($sql_calcola);
	
			$query_calcola = $vmsql->query($sql_calcola);
	
			list($offset)=$vmreg->fetch_row($query_calcola);*/
			
			$offset=0;
	
		}
		else{
			$offset= (int) $_INFO['c'];
		}
	
	
		list($tot_records) = $vmsql->fetch_row($vmsql->query("SELECT count(*) FROM $tabella"));
	
		// OPZIONE PER AVERE I CAMPI ROW
		// i campi row sono quelli non elaborati, in caso contrario mostra i campi richiesti in tabella con le subquery
		if($RAW){
			
			$campi_tabella="*";
			
		}
		else{
			
			$campi_tabella = RegTools::campi_elaborati($tabella,true);
			$tabella = $tabella . " t ";
		}
		
	} // report basato su tabella
	
	
	
	
	
	
	
	
	
	
	// foglio di stile personalizzato
	if($RS_rules['xsl']!='' && is_file(_PATH_XSL."/".$RS_rules['xsl'])){
		
		$xsl=  (isset($_INFO['type']) && $_INFO['type']=='XML') ? "" : _PATH_WEB_XSL."/".$RS_rules['xsl'];
	}
	// default
	else{
		
		// caso querybased
		$chiocciola = ($QB) ? "@":"";
		
		$xsl = (isset($_INFO['type']) && $_INFO['type']=='XML') ? "" : FRONT_DOCROOT."/xml/".$chiocciola.$_INFO['action']."/stile.xml";
	}
	
	
	
	// DTD
	$dtd = FRONT_DOCROOT."/xml/DTD/1/".$_INFO['action'].".dtd";
	
	
	//generazione da query
	if($QB){
		
		$query_test=$vmsql->query_try(stripslashes($RS_rules['def_query']));
		
		if($query_test){
			
			if(preg_match("'([0-9]+),([0-9]+)'",$_INFO['c'],$match)>0 && !preg_match("| LIMIT|i",$RS_rules['def_query'])){
				
				$offset=intval($match[1]);
				$limit=intval($match[2]);
				
				$XML = xmlize(stripslashes($RS_rules['def_query'])." ".$vmsql->limit($limit,$offset),null,true,$offset,0,$xsl);
			}
			else if(is_numeric($_INFO['c']) && !preg_match("| LIMIT|i",$RS_rules['def_query'])){
				
				$offset= (int) $_INFO['c'];
				
				$XML = xmlize(stripslashes($RS_rules['def_query'])." ".$vmsql->limit(1,$offset),null,true,$offset,0,$xsl);
			}
			else{
				
				$XML = xmlize(stripslashes($RS_rules['def_query']),null,true,0,0,$xsl);
			}
		}
	}
	
	// SINGOLO RECORD BASATO SULLA SERIE ORDINATA
	else if(is_numeric($_INFO['c'])){
		$XML = xmlize("SELECT ".$campi_tabella." FROM $tabella  ORDER BY $orderby_doppio ".$vmsql->limit(1,$offset),null,true,$offset,$tot_records,$xsl,$dtd);
		
	}
	
	
	// INTERVALLO DI RECORD BASATO SULLA SERIE ORDINATA
	elseif(preg_match("'([0-9]+),([0-9]+)'",$_INFO['c'],$match)>0){
		
		$offset=intval($match[1]);
		$limit=intval($match[2]);
		
		$XML = xmlize("SELECT ".$campi_tabella." FROM $tabella ORDER BY $orderby_doppio ".$vmsql->limit($limit,$offset),null,true,$offset,$tot_records,$xsl,$dtd);
	}
	
	
	// TUTTI I RECORD 
	elseif($_INFO['c']=='all'){
		
		$XML = xmlize("SELECT ".$campi_tabella." FROM $tabella ORDER BY $orderby_doppio ",null,true,false,$tot_records,$xsl);
	}
	
	
	// RECORD PER SINGOLO ID (DA CAMPO PK)		
	elseif($_INFO['id']>0){
		$XML = xmlize("SELECT ".$campi_tabella." FROM $tabella WHERE $PK=".intval($_INFO['id'])."  ",null,true,false,$tot_records,$xsl,$dtd);
	}
	
	return array('XML'=>$XML,'XSL'=>$xsl,"DTD"=>$dtd);
}





/**
 * Funzione che lancia l'eseguibile FOP e manda a video il file generato. 
 * Il parametro $debug se impostato TRUE, mostra il codice di ritorno della shell e non manda in stream il file
 *
 * @param string $input_fo Eventuale file FO (non usato)
 * @param string $input_xml File di input XML
 * @param string $input_xsl File di input XSL
 * @param string $output_file Nome per il file di output
 * @param string $tipo Tipo di file da generare (default: pdf)
 * @param bool $debug Parametro di debug: se attivo non viene generato il file ma l'output di FOP
 */
function fop_exec($input_fo='',$input_xml='',$input_xsl='',$output_file='',$tipo='pdf',$debug=false){
	
	global $types;
	
	if(!_FOP_ENABLED){
		
		die(_("FOP is not active!<br />Modify the configuration file to use the FOP executable."));
	}
	
	if($input_fo=='' && ($input_xml=='' && $input_xsl=='')){
		
		die(_("Input files not specified"));
	}
	
	if(!is_file(_PATH_FOP)){
	
		die(_("FOP file does not exist"));
	}
	
	if(!is_executable(_PATH_FOP)){
		
		die(_("FOP file is not executable"));
	}
	
	if(in_array($tipo,$types)){
	
		$str_exec=_PATH_FOP
		." -xml ".$input_xml
		." -xsl ".$input_xsl
		." -$tipo ".$output_file;
		
		exec($str_exec
		,$output
		,$ret);
	}
	else{
		
		die(_('Output type not supported'));
	}
	
	
	// SEZIONE DEBUG
	$output=implode("\n",$output);
	
	if($debug){
		print "<pre>";
		echo $str_exec;
		echo "\n";
		var_dump($output);
		print "\n";
		var_dump($ret);
		print "</pre>";
	}
	elseif($ret==0){
		header("Content-type: application/$tipo");
		$pezzi=explode("/",$output_file);
		header ("Content-Disposition: inline; filename=".$pezzi[(count($pezzi)-1)]);
		print join('',file($output_file));
		unlink($output_file);
	}
	
}


?>