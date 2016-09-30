<?php
/**
* File RPC per le chiamate AJAX delle sottomaschere. 
* Questo file viene chiamato da funzioni javascript per eseguire le normali operazioni
* sulla sottomaschere, come inserimento, modifica, cancellazione e selezione dei record.
* Se esiste una chimata post viene incluso il file {@link func.rpc_query.php}
* con le funzioni di interazione con il database.
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc_sub.php 1107 2014-09-28 21:33:49Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require("../inc/conn.php");

proteggi(1);


if(count($_POST)>0){

//	$fp=fopen("test.txt","a");
	
	require_once("../inc/func.rpc_query.php");
	
	
	if($_GET['post']=='update'){

		// IMPOSTAZIONI CHIAVE PRIMARIA:
		$attribuzione_PK = $_POST['campo_pk_indip']."='".$vmsql->escape($_POST['valore_pk_indip'])."' ";
		
		// case new record
		if(!isset($_POST['hash'])) $_POST['hash']='';

		if(!isset($_POST['dati'])){
		    $_POST['dati']=array();
		}
		
		$sql_update = rpc_sub_query_update($_POST['dati'],$attribuzione_PK,$_GET['action'],$_POST['hash']);

		// contatore per successo delle operazioni 
		$c=0;
		
		for($i=0;$i<count($sql_update);$i++){
			
			
			$result=$vmsql->query_try($sql_update[$i],false);
			
			Common::rpc_debug($sql_update[$i]);
			
			if($result) $c++;
		}
		
		
		// CONTROLLO I RISULTATI
		
		$ris = $i-$c;
		
		if($ris==0){
			
			echo 1;	 // tutte le operazioni sono andate a buon fine		
		}
		elseif($ris<$i){
			
			echo 2; // alcune operazioni sono andate a buon fine, altre sono fallite
		}
		else{
			
			echo 3; // tutte le operazioni sono fallite
		}
		
		
		
	}
	
	 elseif($_GET['post']=='delete'){

	 	// array
	 	$sql_delete = rpc_sub_query_delete($_GET['action'],$_POST['hash']);
	 	
	 	$c=0;
	 	
	 		for($i=0;$i<count($sql_delete);$i++){
	 			$result=$vmsql->query_try($sql_delete[$i],false);
	 			$c++;
	 		}
	 	
	 	$ris = $i-$c;
		
		if($ris==0){
			
			echo 1;	 // tutte le operazioni sono andate a buon fine		
		}
		elseif($ris<$i){
			
			echo 2; // alcune operazioni sono andate a buon fine, altre sono fallite
		}
		else{
			
			echo 3; // tutte le operazioni sono fallite
		}
	 }
	 
	 else{
	 	
	 	echo "BOH";
	 }

}

