<?php
/**
 * @package VFront
 * @subpackage Authentication
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: auth.db.php 1075 2014-06-13 13:01:01Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see auth
 */

	/**
	 * Funzione di autenticazione mediante l'uso di un database diverso da VFront 
	 * ma presente sullo stesso server
	 * Viene richiamata dalla classe {@link auth}
	 *
	 * @param string $user Username (email)
	 * @param string $passw Password
	 * @return array
	 */
	function auth_db($user, $passw){
		
		global  $vmsql, $vmreg, $db1, $conf_auth;
	
	
		$sql_auth=sprintf("SELECT * FROM {$conf_auth['db']['database']}.{$conf_auth['db']['tabella']} 
						   WHERE {$conf_auth['campo_email']}='%s' AND {$conf_auth['campo_password']}='%s'",
						trim($vmsql->escape($user)),
						personal_hash_db(trim($passw))
						);
						
		$q_auth=$vmsql->query($sql_auth);
		
		
		if($vmsql->num_rows($q_auth)==1){
			
			$RS=$vmsql->fetch_assoc($q_auth);
			
			return array('response'=>true,$RS);
		
		}
		else{
			
			return array('response'=>false);
		}
		
	}
	
	/**
	 * Funzione di hash o crypt personalizzata 
	 * richiama il tipo di codifica impostata nel file CONF 
	 * per le password in caso di autenticazione esterna tramite database
	 *
	 * @param string $passw
	 * @return string Stringa codificata con il metodo impostato in $conf_auth['password_crypt']
	 */
	function personal_hash_db($passw){
		
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