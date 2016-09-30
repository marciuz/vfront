<?php
/**
* Script per la ricerca via RPC di allegati e link. 
* Lo script viene eseguito dalla pagina {@link scheda.php} e cerca allegati e link
* per il record attualmente visualizzato nella maschera.
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc.allegati_link.php 1088 2014-06-16 20:41:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require("../inc/conn.php");

proteggi(1);


/**
 * Ricerca di allegati. 
 * Data tabella e valore della Primary Key viene restituito il numero di allegati trovati.
 *
 * @param tabella $tabella Tabella nella quale effettuare la ricerca
 * @param id|string $valore_id Valore del campo Primary Key per cui cercare allegati
 * @return int Numero di allegati trovati per ID e Tabella considerati
 */
function cerca_allegati($tabella,$valore_id){
	
	global  $vmsql, $vmreg, $db1;
	
	$sql="SELECT count(*) FROM "._TABELLA_ALLEGATO." WHERE tipoentita='$tabella' AND codiceentita='$valore_id'";
	$q=$vmreg->query($sql);
	list($n_allegati)=$vmreg->fetch_row($q);
	
	return (int) $n_allegati;
	
}



/**
 * Ricerca di link di un record. 
 * Data tabella e valore della Primary Key viene restituito il numero di link trovati.
 *
 * @param tabella $tabella Tabella nella quale effettuare la ricerca
 * @param id|string $valore_id Valore del campo Primary Key per cui cercare link
 * @return int Numero di link trovati per ID e Tabella considerati
 */
function cerca_link($tabella,$valore_id){
	
	global  $vmsql, $vmreg, $db1;
	
	$sql="SELECT count(*) FROM "._TABELLA_LINK." WHERE tipoentita='$tabella' AND codiceentita='$valore_id'";
//	Common::rpc_debug($sql);
	$q=$vmreg->query($sql);
	list($n_link)=$vmreg->fetch_row($q);
	
	return (int) $n_link;
	
}



$allegati_trovati = cerca_allegati($_GET['action'],$_GET['id']);
$link_trovati = cerca_link($_GET['action'],$_GET['id']);

echo $allegati_trovati.",".$link_trovati;
?>