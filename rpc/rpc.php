<?php
/**
* File RPC per le chiamate AJAX della scheda. 
* Questo file viene chiamato da funzioni javascript per eseguire le normali operazioni
* sulla tabella, come inserimento, modifica, cancellazione e selezione dei record.
* Se esiste una chimata post viene incluso il file {@link func.rpc_query.php}
* con le funzioni di interazione con il database
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc.php 1133 2014-12-17 14:31:28Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


require("../inc/conn.php");
require("../inc/func.browser_detection.php");


//proteggi(1);

$browser = browser_detection('full');
$info_browser=substr($browser[0]." ".$browser[1]." ".$browser[4]." ".$browser[5],0,20);
$info_browser=$vmsql->escape($info_browser);

// Set quiet query
$vmsql->quiet=true;

$_GET['action']= preg_replace("|[^0-9a-z_-]+|i","",$_GET['action']);

if(isset($USE_JSON) && $USE_JSON!==true){
    $outputType = 'XML';
}
else{
    $outputType = 'JSON';
}

$RPC = new Rpc($_GET['action'], $outputType);

$res = new stdClass();

$Log = new Log();



if(count($_POST)>0){

	require_once("../inc/func.rpc_query.php");
	
	// UPDATE--------------------------------
	
	if($_GET['post']=='update'){

		// Prepara l'SQL
		$sql_update = $RPC->rpc_query_update($_POST['dati'],$_POST['pk']);

		// INSERISCO IL LOG E PRENDO L'ID
		$last_id_log= $Log->rpc_log('update',$_GET['action'],$_SESSION['user']['uid'],$_SESSION['gid'],$_POST['pk'],true,'',$sql_update,$info_browser); 
		
		// Esegui la modifica
		$q_update=$vmsql->query($sql_update);

		$obj = new stdClass();

		$obj->error_code=$vmsql->get_error();
		
		if($obj->error_code!=''){

		    $obj->error=true;
		    if($GLOBALS['DEBUG_SQL'])
			    $res->error_debug_sql=$sql_insert;
		}
		else{
		    $obj->error=false;
		}

		// se OK
		if($vmsql->affected_rows($q_update)==1){
			
			$obj->aff_rows=1;
		}
		// se KO cancella la riga di log
		else{
			
			$vmreg->query("DELETE FROM {$db1['frontend']}{$db1['sep']}log WHERE id_log=".intval($last_id_log));
			
			$obj->aff_rows=0;
		}

		echo json_encode($obj);

	}
	
	
	
	// INSERT--------------------------------
	
	else if($_GET['post']=='new'){
	    
		$sql_insert= $RPC->rpc_query_insert($_POST['dati']);
		
		$result=$vmsql->query($sql_insert);
				
		// manda l'id appena inserito
		if($vmsql->affected_rows($result)>0){
			
			$PK_tab = $RPC->PK();
            
            if(is_array($PK_tab)){
                $PK_tab = $PK_tab[0];
            }
			
			if(RegTools::is_autoincrement($_GET['action'],$PK_tab)){
				$last_id= $vmsql->insert_id($_GET['action'], $PK_tab);
				
			}
			else{
				$last_id=$_POST['dati'][$PK_tab];
			}
			
			
			$Log->rpc_log('insert',$_GET['action'],$_SESSION['user']['uid'],$_SESSION['gid'],$last_id,true,'',$sql_insert,$info_browser);
			
			$res->id=$last_id;
			$res->error=false;

		}
		else{
			$res->error=true;
			$res->error_code=$vmsql->get_error();

		}

		echo json_encode($res);
	}
	
	
	
	// DELETE--------------------------------
	
	else if($_GET['post']=='delete'){
	    
		$sql_delete = $RPC->rpc_query_delete($_POST['pk']);
		
		$Log->rpc_log('delete',$_GET['action'],$_SESSION['user']['uid'],$_SESSION['gid'],$_POST['pk'],true,'','',$info_browser);
		
		$test_result=$vmsql->query_try($sql_delete,false,true);
		
		if($test_result){
			
			// eliminazione link e allegati se ci sono
			rpc_delete_attach($_GET['action'],implode("",$_POST['pk']));
			rpc_delete_link($_GET['action'],implode("",$_POST['pk']));
			
		}
		
		echo $test_result;
	}
	
	
	
	
	// SEARCH--------------------------------
	
	else if($_GET['post']=='cerca'){
	    
	    if(isset($_GET['fromsub']) && intval($_GET['fromsub'])>0){
		
		$risultati_ricerca = rpc_query_search_from_sub($_POST['dati'],$_GET['action'],$_GET['fromsub']);
	    }
	    else{
	    
		$risultati_ricerca = rpc_query_search($_POST['dati'],$_GET['action']);
	    }
		echo (is_array($risultati_ricerca)) ? implode("|",$risultati_ricerca) : "";
	}

	
	
	
	
	// DUPLICA --------------------------------
		
	else if($_GET['post']=='duplica'){
		$risultati_duplicazione = rpc_query_insert_duplicato($_POST['pk'],$_GET['action'],$_GET['oid_sub'],$_GET['da'],$_GET['dl']);
		
		$last_id= $vmsql->insert_id($_GET['action'], RegTools::prendi_PK($_GET['action']));
		
		list($campo_id,$valore_id)=each($_POST['pk']);
		
		$Log->rpc_log('duplicazione',$_GET['action'],$_SESSION['user']['uid'],$_SESSION['gid'],$last_id,true,'','DUPLICAZIONE '.$_GET['action'].":".$valore_id,$info_browser);
		
		echo $risultati_duplicazione;

	}
	


}else{


	if(isset($_GET['c'])) {
        
        $RPC->set_default_where();

	    if(isset($_GET['w']) && is_array($_GET['w'])){
            $RPC->set_where($_GET['w']);
	    }

		// CASO RISULTATO DI RICERCA-------------------------------------------------------
		// SE c'è l'id in GET prendi calcola a che punto dell'elenco si è arrivati

		if(isset($_GET['id']) && (
				(is_numeric($_GET['id']) && intval($_GET['id'])>0)
				|| (!is_numeric($_GET['id']) && trim($_GET['id'])!='')) ){

			$offset = $RPC->get_offset_1($_GET['id']);
		}
		else{
			$offset= (int) $_GET['c'];
		}

		if(is_numeric($_GET['c'])){
		    
			$OUTPUT = $RPC->get_output_1($offset);
		}
		elseif($_GET['c']=='all'){
			$OUTPUT = $RPC->get_output_all();
			
		}

		$RPC->send_header();
		
		echo $OUTPUT;
	}
}

