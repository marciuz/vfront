<?php
/**
* File di gestione della tabella dinamica dhtmlGrid. 
* Viene richiamato dallo script {@link scheda.php} e dalle funzioni javascript.
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc.xmlgrid.php 1129 2014-12-17 11:28:34Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../inc/conn.php");

proteggi(1);


	
	$tabella = $_REQUEST['t'];
	
	if(!RegTools::is_tabella($tabella)){
		
		echo _("Non-existent table");
		exit;
	}

	$RPC = new RPCGrid($tabella);
    
    $RPC->set_default_where();
	
	if(isset($_REQUEST['w'])){
	    $RPC->set_where($_REQUEST['w']);
	}
	
	// prendi la chiave primaria:
	$PK = $RPC->PK();
	
	$OFFSET = (isset($_REQUEST['rowsLoaded'])) ? intval($_REQUEST['rowsLoaded']) : 0;
    
	// altro modo di prendere l'offset... e sovrascrivo.
	$OFFSET = (isset($_REQUEST['of'])) ? intval($_REQUEST['of']) : $OFFSET;
	
	$q_info_campi_tab = $RPC->get_grid_rules();
	
	$column_name=array();
    
	
	foreach($q_info_campi_tab as $C){
		
		if($C->in_tipo=='select_from' || $C->in_tipo=='autocompleter_from' && $C->in_default!=''){

			$C->in_default=preg_replace("|\s+|", " ", $C->in_default);
			
			// key field
			preg_match("|SELECT +([^,]+) *,?(.*) *FROM *([a-z0-9_]+)(.*)|iu",$C->in_default,$fff);
			
			// if the label not exists... use the value
			if($fff[2]==''){
			    $fff[2]=$fff[1];
			}

			// Cerca alias per k
			$k =(preg_match("'AS +([\w]+) *$'i",trim($fff[1]),$alias_k)) ? $alias_k[1] : 'k';
			$print_k = ($k=='k') ? 'AS k' : '';

			// Cerca alias per v
			$v =(preg_match("'AS +([\w]+) *$'i",trim($fff[2]),$alias_v)) ? $alias_v[1] : 'v';
			$print_v = ($v=='v') ? 'AS v' : '';

			$pre_query = "SELECT {$fff[1]} $print_k , {$fff[2]} $print_v FROM {$fff[3]} {$fff[4]} ";

			$column_name[]="(SELECT $v FROM ($pre_query) t2 WHERE $k=t1.{$C->column_name}) as {$C->column_name}";
		}
		
		else $column_name[]=$C->column_name;
	}
	
	

	// Prendi la variabile di LIMIT
	$LIMIT = (isset($_SESSION['VF_VARS']['n_record_tabella']) && ($_SESSION['VF_VARS']['n_record_tabella']>0)) 
			? $_SESSION['VF_VARS']['n_record_tabella'] : 20;
			
	// Prendi la variabile di LIMIT RICERCA
	$LIMIT_SEARCH = (isset($_SESSION['VF_VARS']['search_limit_results']) && ($_SESSION['VF_VARS']['search_limit_results']>0)) 
			? $_SESSION['VF_VARS']['search_limit_results'] : 1000;
	
	$campi_vis=implode(',',$column_name);
	
	
	// Genera l'order BY
	if(isset($_REQUEST['ord'])){
	
		$orderby_val = $vmsql->escape($_REQUEST['ord']);
		$sort_val = (isset($_REQUEST['sort']) && in_array($_REQUEST['sort'], array('asc', 'des', 'desc'))) ? $vmsql->escape($_REQUEST['sort']) : '';
        
        // fix value from dhtmlxgrid
        $sort_val = ($sort_val == 'des') ? 'desc' : $sort_val;
		$ORDERBY = (RegTools::is_campo($orderby_val)) ? "ORDER BY ".$orderby_val ." ".$sort_val : "";
	}
	else{
		$orderby_val = RegTools::prendi_orderby($tabella,$_SESSION['gid']);		
		$ORDERBY = ($orderby_val!="") ? "ORDER BY ".$orderby_val : "";	
	}
	
	
	

		
	######################################################################
	#
	#	RISPOSTA DI RICERCA CON MOLTI RISULTATI:
	#
	#
	
	if(isset($_REQUEST['q'])){
	    
	    if(strlen($_REQUEST['q'])==32 && isset($_SESSION['search'][$_REQUEST['q']])){
		
            $ids = str_replace("|" , "','" ,  $vmsql->escape($_SESSION['search'][$_REQUEST['q']] ));
	    }
	    else{
	    
            $ids = str_replace("|" , "','" , $vmsql->escape($_REQUEST['q']));
	    }
	
        
        
		$WHERE = " WHERE ".$PK[0]." IN ('".$ids."') ";
		
		$sql ="SELECT ".$PK[0]." as pk, $campi_vis ". 
		"
		  FROM $tabella t1
		  $WHERE
		  ".$RPC->get_string_where(true)."
		  $ORDERBY 
		  ".$vmsql->limit($LIMIT_SEARCH,$OFFSET);
		
	}
	
	

	######################################################################
	#
	#	TABELLA NORMALE
	
	else {
        
		  $sql="SELECT ".$PK[0]." as pk, $campi_vis 
		  FROM $tabella t1
		  ".$RPC->get_string_where()."
		  $ORDERBY 
		  ".$vmsql->limit($LIMIT,$OFFSET);
	}
	
	
	 
	if(isset($_GET['ty']) && $_GET['ty']=='dhtmlxgrid_json'){
        $RPC->json_dhtmlx_grid($sql,$OFFSET,null,true,$PK);
    }
    else{
        $RPC->xmlize_grid($sql,$OFFSET,null,true,$PK);
    }

