<?php
/**
 * @package VFront
 * @subpackage Authentication
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: auth.db_ext.php 1075 2014-06-13 13:01:01Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see auth
 */






	/**
	 * Funzione di autenticazione mediante un database esterno, presente su un server remoto o locale
	 * Viene richiamata dalla classe {@link auth}
	 *
	 * @param string $user
	 * @param string $passw
	 * @see auth
	 * @return array
	 */
	function auth_db_ext($user, $passw){
		
		global $conf_auth;
	
		$sql_auth=sprintf("SELECT * FROM {$conf_auth['db_ext']['tabella']} 
						   WHERE {$conf_auth['campo_email']}='%s' AND {$conf_auth['campo_password']}='%s'",
						trim($vmsql->escape($user)),
						personal_hash_db_ext(trim($passw))
						);
		
		
		if($conf_auth['db_ext']['dbtype']=='mysql'){
			
			
			
							
				if(function_exists('mysqli_connect')){
					$link_tmp = @mysqli_connect($conf_auth['db_ext']['host'],
											   $conf_auth['db_ext']['user'],
											   $conf_auth['db_ext']['passw'],
											   $conf_auth['db_ext']['dbname'],
											   $conf_auth['db_ext']['port']) or die(_("Could not connect to server for authentication"));
											   
											   
					$q_auth=mysqli_query($link_tmp,$sql_auth,MYSQLI_STORE_RESULT);
		
			
					if($q_auth!==false && mysqli_num_rows($q_auth)==1){
						
						$RS=mysqli_fetch_assoc($q_auth);
						
						
						return array('response'=>true,$RS);
					
					}
					else{
						
						return array('response'=>false);
					}
				}
				else die(sprintf(_("Could not connect to %s server for authentication: php extension missing"),$conf_auth['db_ext']['dbtype']));
		}	
		else if($conf_auth['db_ext']['dbtype']=='postgres'){		
							
				if(function_exists('pg_connect')){
					$link_tmp = @pg_connect("host={$conf_auth['db_ext']['host']} port={$conf_auth['db_ext']['port']} dbname={$conf_auth['db_ext']['dbname']} user={$conf_auth['db_ext']['user']} password={$conf_auth['db_ext']['passw']}")  or die("Could not connect to server for authentication");
					
					$q_auth=pg_query($link_tmp,$sql_auth);
		
		
					if(pg_num_rows($q_auth)==1){
						
						$RS=pg_fetch_assoc($q_auth);
						
						return array('response'=>true,$RS);
					
					}
					else{
						
						return array('response'=>false);
					}
				}
					else die(sprintf(_("Could not connect to %s server for authentication: php extension missing"),$conf_auth['db_ext']['dbtype']));
		}
		
		
		else if($conf_auth['db_ext']['dbtype']=='odbc'){		
							
				if(function_exists('odbc_connect')){
					$link_tmp = @odbc_connect($conf_auth['db_ext']['odbc_dsn'],$conf_auth['db_ext']['user'],$conf_auth['db_ext']['passw'])  or die("Could not connect to server for authentication");
					
					$q_auth=odbc_exec($link_tmp,$sql_auth);
		
		
						$RS=odbc_fetch_array($q_auth);
		
						if(is_array($RS) && count($RS)>0){
							
							return array('response'=>true,$RS);
						}
						else{
							return array('response'=>false);
						}
				}
					else die(sprintf(_("Could not connect to %s server for authentication: php extension missing"),$conf_auth['db_ext']['dbtype']));
		}
	
	}
	
	/**
	 * Funzione di hash o crypt personalizzata, richiama il tipo di codifica impostata nel file CONF 
	 * per le password in caso di autenticazione esterna tramite database
	 *
	 * @param string $passw
	 * @return string
	 */
	function personal_hash_db_ext($passw){
		
		global $conf_auth;
		
		if($conf_auth['password_crypt']=='md5'){
			
			return md5($passw);
		}
		else if($conf_auth['password_crypt']=='sha1'){
			
			return sha1($passw);
		}
		
		// ... altri metodi
		
		else{
			
			return $passw;
		}
	}

?>