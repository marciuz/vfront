<?php
/**
 * VFront Web Installer - Utility di installazione dell'applicazione VFront 
 * Caratteristiche richieste: PHP5.x , MySQL 5.x, php_mysqli 
 * Oppure: PHP5.x , Postgres 8.x, php_pgsql
 * @package VFront
 * @subpackage VFront_Web_Installer
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: rpc.getdb.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

$_data=Common::pulisci_dom($_POST['var']);


require_once("../inc/vmsql.".$_data['dbtype'].".php");


##########################################
#
#	TEST CONNECTION
#

	
	if($_data['dbtype']=='mysql'){
		
		$db2=array('host'=>$_data['dbhost1'],
				   'user'=>$_data['dbuser1'],
				   'passw'=>$_data['dbpassw1'],
				   'dbname'=>$_data['dbname1'],
				   'port'=>$_data['dbport1']);
		
		$test_conn = $vmsql->connect($db2) or die(0);
		
		if($_GET['a']=='db'){
			
				
			$sql="SELECT SCHEMA_NAME 
					FROM information_schema.SCHEMATA s
					WHERE SCHEMA_NAME NOT IN ('information_schema','mysql')
					ORDER BY SCHEMA_NAME";
			
			if($q=$vmsql->query($sql)){
				
				$JSON="[ ";
			
				$n=$vmsql->num_rows($q);
				if($n==0){
					echo -1;
					exit;
				}
				else{
					while($RS=$vmsql->fetch_row($q)){
						$JSON.="{'db':'".$RS[0]."'},";
						}
					
				}
				$JSON= substr($JSON,0,-1)."]";
				
				echo $JSON;
			}
			else{
				echo -1;
			}
		}
		else if($_GET['a']=='tab'){
			
				
			$sql="SELECT TABLE_NAME 
					FROM information_schema.TABLES s
					WHERE TABLE_SCHEMA='{$_data['authdb_dbname']}'
					ORDER BY TABLE_NAME";
			
			if($q=$vmsql->query($sql)){
				
				$JSON="[ ";
			
				$n=$vmsql->num_rows($q);
				if($n==0){
					echo -1;
					exit;
				}
				else{
					while($RS=$vmsql->fetch_row($q)){
						$JSON.="{'tab':'".$RS[0]."'},";
						}
					
				}
				$JSON= substr($JSON,0,-1)."]";
				
				echo $JSON;
			}
			else{
				echo -1;
			}
		}
		else if($_GET['a']=='field'){
			
				
			$sql="SELECT COLUMN_NAME 
					FROM information_schema.COLUMNS s
					WHERE TABLE_SCHEMA='{$_data['authdb_dbname']}'
					AND TABLE_NAME='{$_data['authdb_usertable']}'
					ORDER BY COLUMN_NAME";
			
			if($q=$vmsql->query($sql)){
				
				$JSON="[ ";
			
				$n=$vmsql->num_rows($q);
				if($n==0){
					echo -1;
					exit;
				}
				else{
					while($RS=$vmsql->fetch_row($q)){
						$JSON.="{'f':'".$RS[0]."'},";
						}
					
				}
				$JSON= substr($JSON,0,-1)."]";
				
				echo $JSON;
			}
			else{
				echo -1;
			}
		}
	
	}
	
	
	else{
		echo -2;
	}
	
?>