<?php
/**
* File di gestione della tabella dinamica dhtmlGrid. 
* Viene richiamato dallo script {@link scheda.php} e dalle funzioni javascript.
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc.xmlgrid.php 1058 2012-12-14 16:23:48Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../inc/conn.php");

proteggi(1);




/**
 * Da una query SQL viene restituito l'XML generato per la griglia dxhtmlGrid 
 *
 * @param string $sql
 * @param int $offset
 * @param string $filename
 * @param bool $header
 * @param string $PK
 * @return void
 */
function json_grid($sql,$offset=0,$filename=null,$header=true,$PK=null){
	
	global  $vmsql, $vmreg;
	
	$q = $vmsql->query($sql);
	
	if($vmsql->num_rows($q)==0){
		
		return null;	
	}
	
	// Inizia a fare l'xml
	$offset++;
	
	$data = array();
	
	while($RS=$vmsql->fetch_assoc($q)){
		
		$temp = array();
		
		foreach($RS as $k=>$val){
			
			// Il campo speciale prendi valore imposta l'offset per la ricerca
			if($k!='pk'){
				$val = trim($val);
				
				if(isset($_SESSION['VF_VARS']['max_char_tabella']) && intval($_SESSION['VF_VARS']['max_char_tabella'])>0){
					if(strlen($val)>$_SESSION['VF_VARS']['max_char_tabella']) 
                        $val = substr($val,0,$_SESSION['VF_VARS']['max_char_tabella'])."...";
				}
				
				$temp[]=$val;
			}
		}
		
		$data[] = $temp;
	}
	
	print json_encode(array('data'=>$data));
}


/**
 * Genera l'XML dal campo dato con gli eventuali attributi
 *
 * @param string $tag
 * @param array $attr
 * @return string
 */
function xmlize_campo_grid($tag,$attr){
	
    $attributi="";

    foreach($attr as $k=>$val){
			
        $attributi .=" $k=\"$val\"";
    }

    return "<".$tag.$attributi.">";
	
}


##########################################################################################################

	
	$tabella = $_REQUEST['t'];
	
	if(!RegTools::is_tabella($tabella)){
		
		echo _("Non-existent table");
		exit;
	}

	$RPC = new Rpc($tabella);
    
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
	
	while($RS=$vmreg->fetch_assoc($q_info_campi_tab)){
		
		if($RS['in_tipo']=='select_from' || $RS['in_tipo']=='autocompleter_from' && $RS['in_default']!=''){

			$RS['in_default']=preg_replace("|\s+|", " ", $RS['in_default']);
			
			// key field
			preg_match("|SELECT +([^,]+) *,?(.*) *FROM *([a-z0-9_]+)(.*)|iu",$RS['in_default'],$fff);
			
			// if the label not exists... use the value
			if($fff[2]==''){
			    $fff[2]=$fff[1];
			}

			// Cerca alias per k
			$k =(preg_match("' +([\w]+) *$'",trim($fff[1]),$alias_k)) ? $alias_k[1] : 'k';
			$print_k = ($k=='k') ? 'k' : '';

			// Cerca alias per v
			$v =(preg_match("' +([\w]+) *$'",trim($fff[2]),$alias_v)) ? $alias_v[1] : 'v';
			$print_v = ($v=='v') ? 'v' : '';

			$pre_query = "SELECT {$fff[1]} AS $print_k , {$fff[2]} AS $print_v FROM {$fff[3]} {$fff[4]} ";

			$column_name[]="(SELECT $v FROM ($pre_query) t2 WHERE $k=t1.{$RS['column_name']}) as {$RS['column_name']}";
		}
		
		else $column_name[]=$RS['column_name'];
	}
	
	
	unset($info_campi);


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
		$ORDERBY = (RegTools::is_campo($orderby_val)) ? "ORDER BY ".$orderby_val : "";
		
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
	
		
		
		$WHERE = " WHERE $PK IN ('".$ids."') ";
		
		$orderby_campo = str_replace(array("ASC","DESC"),"",$orderby_val);
		
		// Imposto un alias per 
		//$campi_vis_rep = str_replace("$orderby_campo", "$orderby_campo as prendi_valore", $campi_vis);
		
		$sql ="SELECT $PK as pk, $campi_vis ". 
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
		
		  $sql="SELECT $PK as pk, $campi_vis 
		  FROM $tabella t1
		  ".$RPC->get_string_where()."
		  $ORDERBY 
		  ".$vmsql->limit($LIMIT,$OFFSET);
	}
	
	
	 
	
	json_grid($sql,$OFFSET,null,true,$PK);
	


