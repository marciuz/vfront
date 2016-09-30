<?php
/**
* File di gestione del refrash degli iframe. 
* Gli Iframe vengono utilizzati da VFront per mostrare le tendine nel 
* caso di campi che mostrano contenuti di altre tabelle.
* Gli iframe sono archiviati nella directory /html e per evitare consumo di risorse
* e di banda da parte del client vengono ricreati solo se il contenuto della tabella
* non � cambiato, in modo da utilizzare potenzialmente gli stessi file archiviati 
* e, lato client, la cache del browser.
* Qualora invece ci fosse necessit� di aggiornarli questo file ricrea il file da mostrare negli iframe.
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc.refresh_iframe.php 1088 2014-06-16 20:41:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../inc/conn.php");


proteggi(1);

if(isset($_REQUEST['campo']) && isset($_REQUEST['tabella'])){
	
	if(RegTools::is_campo($_REQUEST['campo']) && RegTools::is_tabella($_REQUEST['tabella'])){
	
		
		$sql="SELECT c.in_default 
			FROM ".$db1['frontend'].$db1['sep']."registro_col c, ".$db1['frontend'].$db1['sep']."registro_tab t 
			WHERE c.column_name='".$_REQUEST['campo']."'
			AND t.table_name='".$_REQUEST['tabella']."'
			AND t.gid='".$_SESSION['gid']."'
			AND c.id_table=t.id_table
			";
		$q=$vmsql->query($sql);
		
		list($sql_campo)=$vmsql->fetch_row($q);
		
		
		$NEW_IFRAME = new hash_iframe($_REQUEST['campo'],$sql_campo);
		
		echo $NEW_IFRAME->hash_html;
	
	}
}














?>