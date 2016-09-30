<?php
/**
* File che recupera i valori da mostrare nei suggerimenti dei campi. 
* Viene richiamato dallo script {@link scheda.php} e dalle funzioni di scriptaculous
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2014 M.Marcello Verona
* @version 0.96 $Id: rpc.suggest.php 880 2010-12-14 12:43:47Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

include("../inc/conn.php");

proteggi(1);

$id_table=$_REQUEST['oid'];

$id_record=$_REQUEST['id_record'];

$field=$_REQUEST['field'];


$sql="SELECT in_default FROM {$db1['frontend']}{$db1['sep']}registro_col
		WHERE id_table=".$vmsql->escape($id_table)." AND column_name='".$vmsql->escape($field)."'";

// get sql if exists
$q0=$vmreg->query($sql);

$o = new stdClass();

if($vmreg->num_rows($q0)==1){
    
    list($sql_1)=$vmreg->fetch_row($q0);
    
    // campo id
    preg_match("|SELECT *([^,]+),|i",$sql_1,$ff);
    
    if(strpos($sql_1, "WHERE")===false){
        $sql_1.=" WHERE ".$ff[1]."='".$vmsql->escape($id_record)."'";
    }
    else{
        $sql_1.=" AND ".$ff[1]."='".$vmsql->escape($id_record)."'";
    }
    
    if($vmsql->query_try($sql_1)){
        $q2=$vmsql->query($sql_1);
        $RS=$vmsql->fetch_row($q2);
        $o->field = $field;
        $o->value = $RS[1];
    }
}

Json_Output::stream($o);

/*
$sql=

$q=$vmsql->
 * 
 * 
 */