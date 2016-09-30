<?php
/**
 * @package VFront
 * @subpackage Authentication
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: auth.ldap.php 1075 2014-06-13 13:01:01Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see auth
 * 
 */

	
	function ldap_escape($str, $for_dn = false)
	{
	   
	    // see:
	    // RFC2254
	    // http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
	    // http://www-03.ibm.com/systems/i/software/ldap/underdn.html       
	       
	    if  ($for_dn)
	        $metaChars = array(',','=', '+', '<','>',';', '\\', '"', '#');
	    else
	        $metaChars = array('*', '(', ')', '\\', chr(0));
	
	    $quotedMetaChars = array();
	    foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
	    $str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
	    return ($str);
	}


	/**
	 * Sistema di autenticazione attraverso LDAP
	 * Funzione richiamata dalla classe {@link auth}
	 *
	 * @param string $sn cn per LDAP
	 * @param string $password Password
	 * @return mixed
	 */
	function auth_ldap($sn,$password){
		
		global $conf_auth;
		
		$ds=@ldap_connect($conf_auth['ldap']['host'],$conf_auth['ldap']['port']) or die(openErrorGenerico(_("Could not connect to LDAP server").": ".__LINE__ ,true));   // must be a valid LDAP server!
		
		
		
		if ($ds){ 
			
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
			
			 // binding to ldap server
			 if($conf_auth['ldap']['anonymus_bind']==1){
			 	
			 	$ldapbind = @ldap_bind($ds); 
			 }
			 else{
			 	
			 	$dn=$conf_auth['campo_nick']."=".ldap_escape($conf_auth['bind_user']);
			 	
			 	// 1rst try
			 	$ldapbind = @ldap_bind($ds,$dn,$password); 
			 	
			 	if(!$ldapbind){
			 		
			 		// 2nd try
			 		$dn.=", ".$conf_auth['ldap']['base_dn'];
			 		$ldapbind = @ldap_bind($ds,$dn,$password); 
			 	}
			 }
			 
			 
					
			 if(!$ldapbind) return array('response'=>false);
			 
			 // Search surname entry
			 $filter="(".$conf_auth['campo_nick']."=".ldap_escape($sn).")";
			
		
			// 1o test con uid
		    $sr=@ldap_search($ds, $conf_auth['ldap']['base_dn'], $filter) or die(openErrorGenerico(_("Could not connect to LDAP server").": ".__LINE__));  
		    $n_trovati=ldap_count_entries($ds, $sr);
		    
	    	 if($n_trovati==0){
    	    	return array('response'=>false);
	    	 }
			
		    
		    $info = ldap_get_entries($ds, $sr);
   
			
			foreach($info[0] as $k=>$val){
				
				$RS[strtolower($k)]=$val[0];
			}
			
			//	Closing connection
		    ldap_close($ds);
		    
		    return array('response'=>true,$RS);
		
		} else {
			
			openErrorGenerico(_("Could not connect to LDAP"),true);
		}
	
	}




?>