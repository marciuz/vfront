<?php
/**
* File RPC per il lock del record considerato attualmente. 
* Quando un utente entra in modifica su un file questo viene bloccato in scrittura per evitare 
* l'accesso concorrente da parte di altro utente. 
* Mediante la chiamata a questo file viene creato un record nella tabella "recordlock" 
* del DB di regole di VFront. In questo file sono presenti le funzioni per gestire 
* il lock dei record.
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc.recordlock.php 1088 2014-06-16 20:41:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../inc/conn.php");

proteggi(1);

/**
 * Verifica che il record sia bloccato o meno.
 * In base al tempo massimo di blocco specificato nella variabile globale _VARAIBILI => max_tempo_edit
 * � possibile che il record, anche se bloccato, sia comunque scrivibile
 *
 * @param string $tabella
 * @param string $colonna
 * @param int|string $id
 * @return bool
 */
function verifica_recordlock($tabella,$colonna,$id){
	
	global  $vmreg, $db1;
	
	
	$sql= "SELECT tempo FROM {$db1['frontend']}{$db1['sep']}recordlock 
			WHERE tabella='$tabella'
			AND colonna='$colonna'
			AND id='$id'
			";
	
	$q=$vmreg->query($sql);
	$num = $vmreg->num_rows($q);
	
	/*if(!isset($GLOBALS['_VARIABILI'])){
		
		var_frontend();
	}*/
	
	// e' bloccato, verifica da quando
	if($num>0){
		
		list($tempo_blocco)=$vmreg->fetch_row($q);
		
		$tempo_edit = (time()-$tempo_blocco);
		
		if($tempo_edit > $_SESSION['VF_VARS']['max_tempo_edit']){
			
			// e' bloccato ormai da tanto, sbloccalo e consideralo libero
			sblocca_record($tabella,$colonna,$id);
			return true;
		}
		else{
			// � bloccato
			return false;
		}
		
	}
	// e' libero (non esiste in tabella)
	else return true;
	
	
	
}


/**
 * Blocca un dato record creando una nuova riga nella tabella recordlock
 *
 * @param string $tabella
 * @param string $colonna
 * @param string $id
 * @return bool
 */
function blocca_record($tabella,$colonna,$id){
	
	global  $vmreg, $db1;
	
	$sql= "INSERT INTO {$db1['frontend']}{$db1['sep']}recordlock (tabella,colonna,id,tempo)
			VALUES	('$tabella','$colonna','$id',".time().")
			";
	
	return $vmreg->query_try($sql,false);
	
	
}


/**
 * Sblocca un dato record cancellando la riga ad esso relativa nella tabella recordlock
 *
 * @param string $tabella
 * @param string $colonna
 * @param string $id
 * @return bool
 */
function sblocca_record($tabella,$colonna,$id){
	
	global  $vmreg, $db1;
	
	$sql= "DELETE FROM {$db1['frontend']}{$db1['sep']}recordlock 
			WHERE tabella='$tabella'
			AND colonna='$colonna'
			AND id='$id'
			";
	
	return $vmreg->query_try($sql,false);
	
}



####################################

	$tabella = $vmreg->escape($_GET['tab']);
	$colonna = $vmreg->escape($_GET['col']);
	$id = $vmreg->escape($_GET['id']);


if(isset($_GET['blocca'])){
	
	$libero = verifica_recordlock($tabella,$colonna,$id);
	
	// se il record � libero
	if($libero){
		echo blocca_record($tabella,$colonna,$id);
	}
	else{		
		echo 0;
	}
}
else if(isset($_GET['sblocca'])){
	
	echo sblocca_record($tabella,$colonna,$id);
}







?>