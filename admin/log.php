<?php
/**
 * Il questo file sono presenti funzioni e procedure per la gestione del log. 
 * E' possibile vedere la tabella dei log, impostare i filtri, eseguire operazioni di rollback, ecc.
 * 
 * @desc File di gestione dei log
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: log.php 1174 2017-05-12 21:44:50Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

 proteggi(2);

$Log = new Log();


if(isset($_POST['id_log']) && isset($_GET['ripristino'])){

	$id_log = (int)$_POST['id_log'];
	if($id_log>0){
		$Log->ripristina($id_log);
	}
	else{
		openErrorGenerico("Nessun riferimento per l'operazione da ripristinare");
		exit;
	}
}

else if(isset($_GET['dettaglio']) && (intval($_GET['dettaglio'])>0)){

	$Log->mostra_dettaglio_log($_GET['dettaglio']);
}
else if(isset($_GET['id_record']) && (intval($_GET['id_record'])>0)){

	$Log->show_history($_GET['id_record'], $_GET['table_name']);
}
else{

	$Log->mostra_log();
}


