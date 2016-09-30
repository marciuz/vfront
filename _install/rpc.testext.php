<?php
/**
 * VFront Web Installer - Utility di installazione dell'applicazione VFront 
 * Caratteristiche richieste: PHP5.x , MySQL 5.x, php_mysqli 
 * Oppure: PHP5.x , Postgres 8.x, php_pgsql
 * @package VFront
 * @subpackage VFront_Web_Installer
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: rpc.testext.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

$_data=Common::pulisci_dom($_POST['var']);

require_once("../inc/vmsql.".$_data['dbtype'].".php");



##########################################
#
#	TEST CONNECTION
#

if($_data['authtype']=='db'){
	
	if($_data['dbtype']=='mysql'){

		if(function_exists('mysqli_connect')){
			$vmsql = new mysqli_vmsql();
		}
		else{
			$vmsql = new mysql_vmsql();
		}
		
		$msg_error_conn="0@<span class=\"ko\">Please set the DB user and DB password in the <a href=\"#DBconnection\">DB connection block</a></span>";
		
		$db2=array('host'=>$_data['dbhost1'],
					'user'=>$_data['dbuser1'],
					'passw'=>$_data['dbpassw1'],
					'dbname'=>$_data['dbname1'],
					'port'=>$_data['dbport1']);
		
		$test_conn = $vmsql->connect($db2) or die($msg_error_conn);
		
		$authdb_dbname=preg_replace("|[^a-z0-9_]|i","",$_data['authdb_dbname']);
		$authdb_usertable=preg_replace("|[^a-z0-9_]|i","",$_data['authdb_usertable']);
		
		$testqtable=@$vmsql->query("SELECT * FROM $authdb_dbname.$authdb_usertable LIMIT 1");
		
		if($testqtable==false){
			
			echo "0@<span class=\"ko\"><strong>Test failed!</strong> The table $authdb_dbname.$authdb_usertable not exist or is not accessible</span>";
			exit;
		}
		else{
			
			$authext_nick=preg_replace("|[^a-z0-9_]|i","",$_data['authext_nick']);
			$authext_passwd=preg_replace("|[^a-z0-9_]|i","",$_data['authext_passwd']);
			
			$testqemail=@$vmsql->query("SELECT $authext_nick FROM $authdb_dbname.$authdb_usertable LIMIT 1");
			
			if($testqemail==false){
				
				echo "0@<span class=\"ko\"><strong>Test failed!</strong> The table $authdb_dbname.$authdb_usertable is ok, but the email/nick field '$authext_nick' is not in the table...</span>";
				exit;
			}
			
			$testqpassword=@$vmsql->query("SELECT $authext_passwd FROM $authdb_dbname.$authdb_usertable LIMIT 1");
			
			if($testqpassword==false){
				
				echo "0@<span class=\"ko\"><strong>Test failed!</strong> The table $authdb_dbname.$authdb_usertable is ok, but the password field '$authext_passwd' is not in the table...</span>";
				exit;
			}
			
			
			// controlli opzionali: nome e cognome
			
			
			// mando un ok
			echo "1@<span class=\"ok\"><strong>Test ok!</strong> 	Every little things gonna be alright... ;-)</span>";
		}
		
		
		
	}
	
	else echo '0@<span class="ko">'._('Please set the database type').'</span>';
}
// fine test DB




elseif($_data['authtype']=='db_ext'){
	
	$msg_error_conn="0@<span class=\"ko\">Please check the DB host, port, user and password</span>";
	
	
	
	if($_data['authdb_ext_type']=='mysql'){
		
		$db2=array('host'=>$_data['authdb_ext_host'],
					'user'=>$_data['authdb_ext_user'],
					'passw'=>$_data['authdb_ext_passwd'],
					'dbname'=>$_data['authdb_ext_dbname'],
					'port'=>$_data['authdb_ext_port']);

		if(function_exists('mysqli_connect')){
			$vmsql = new mysqli_vmsql();
		}
		else{
			$vmsql = new mysql_vmsql();
		}
		
		
								
//		$func_query= (function_exists('mysqli_query')) ? 'mysqli_query' : 'mysql_query';
		
	}
	elseif($_data['authdb_ext_type']=='postgres'){
		
		$db2=array('host'=>$_data['authdb_ext_host'],
					'user'=>$_data['authdb_ext_user'],
					'passw'=>$_data['authdb_ext_passwd'],
					'postgres_dbname'=>$_data['authdb_ext_dbname'],
					'port'=>$_data['authdb_ext_port']);
		
		$vmsql = new postgres_vmsql();
		
	}
	elseif($_data['authdb_ext_type']=='odbc'){
		
	}
	
	$tes_conn = @$vmsql->connect($db2) or die($msg_error_conn);
	
	
	// TEST VARI SULLE TABELLE /CAMPI (condivisi tra le tipologie di connessione)
	
		$authdb_dbname=preg_replace("|[^a-z0-9_]|i","",$_data['authdb_ext_dbname']);
		$authdb_usertable=preg_replace("|[^a-z0-9_]|i","",$_data['authdb_ext_table']);
		
		$testsql="SELECT * FROM $authdb_usertable LIMIT 1";
		$testqtable=@$vmsql->query($testsql);
		
		if($testqtable==false){
			
			echo "0@<span class=\"ko\"><strong>Test failed!</strong> The table <em>$authdb_usertable</em> do not exist or is not accessible in <em>$authdb_dbname</em></span>";
			exit;
		}
		else{
			
			$authext_nick=preg_replace("|[^a-z0-9_]|i","",$_data['authext_nick']);
			$authext_passwd=preg_replace("|[^a-z0-9_]|i","",$_data['authext_passwd']);
			
			$testqemail=@$vmsql->query("SELECT $authext_nick FROM $authdb_usertable LIMIT 1");
			
			if($testqemail==false){
				
				echo "0@<span class=\"ko\"><strong>Test failed!</strong> The table $authdb_usertable is ok, but the email/nick field '$authext_nick' is not in the table...</span>";
				exit;
			}
			
			$testqpassword=@$vmsql->query("SELECT $authext_passwd FROM $authdb_usertable LIMIT 1");
			
			if($testqpassword==false){
				
				echo "0@<span class=\"ko\"><strong>Test failed!</strong> The table $authdb_usertable is ok, but the password field '$authext_passwd' is not in the table...</span>";
				exit;
			}
			
			
			// controlli opzionali: nome e cognome
			
			
			// mando un ok
			echo "1@<span class=\"ok\"><strong>Test ok!</strong> Wow.</span>";
		}
		
		
		
		
		
}

elseif($_data['authtype']=='ldap'){
	
	if(!function_exists('ldap_connect')){
		
		die(-4);
	}
	
	// connessione
	if($_data['ldap_port']==''){
		$ds=ldap_connect($_data['ldap_host']) or die(0); 
	}
	else{
		$ds=ldap_connect($_data['ldap_host'],$_data['ldap_port']) or die(0); 
	}
	
	
	if($ds){
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
		
		if(isset($_data['ldap_anonymus_bind']) && $_data['ldap_anonymus_bind']=='1'){
			$ldapbind = @ldap_bind($ds); 
		}
		else{

			$string_bind= $_data['authext_nick']."=".$_data['ldap_bind_user'];
			
			// fist try
			$ldapbind = @ldap_bind($ds, $string_bind ,$_data['ldap_bind_passw']); 
			
			if(!$ldapbind){
				
				// 2nd try
				$string_bind.=",{$_data['ldap_basedn']}";
				$ldapbind = @ldap_bind($ds, $string_bind ,$_data['ldap_bind_passw']); 
			}
			
		}
				
		
		
		echo ($ldapbind) 
			? "1@<span class=\"ok\">"._('LDAP test ok!')."</span>"
			: "0@<span class=\"ko\">"._('LDAP test failed')."</span>";
		
		ldap_close($ds);
	}
	
	
}
else{
	
	echo "0@No type selected";
}

