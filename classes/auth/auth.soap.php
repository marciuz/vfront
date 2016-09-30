<?php
/**
 * @package VFront
 * @subpackage Authentication
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: auth.soap.php 1075 2014-06-13 13:01:01Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see auth
 * 
 */




	/**
	 * Funzione sperimentale di autenticazione tramite SOAP
	 * Viene richiamata dalla classe {@link auth}
	 *
	 * @param string $user Nome utente
	 * @param string $passw Password
	 * @return array
	 */
	function auth_soap($user, $passw){
		
		global $conf_auth;

		
		$client = new SoapClient($conf_auth['soap']['wsdl'], array(
			    'soap_version' => SOAP_1_1,
			    "trace"      => 1,
			    "exceptions" => 0)
			   
			   );
			
			$GetUser = $client2->$conf_auth['soap']['function_get_user'](array($conf_auth['campo_email']=>$user,$conf_auth['campo_password']=>$passw));
			
			
			if(isset($GetUser->$conf_auth['ldap']['function_get_user_results'])){
	
				$parametri[$conf_auth['campo_email']] = $GetUser->GetUserResult->$conf_auth['campo_email'];
				$parametri[$conf_auth['campo_nome']] = $GetUser->GetUserResult->$conf_auth['campo_nome'];
				$parametri[$conf_auth['campo_cognome']] = $GetUser->GetUserResult->$conf_auth['campo_cognome'];
				
				
				return array('response'=>true,$parametri);
			}
			else{
				return array('response'=>false,$parametri);
			}
			
		
		}
		

?>