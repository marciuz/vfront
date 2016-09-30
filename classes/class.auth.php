<?php
#################################################
#
#	 This file is part of VFront.
#
#    VFront is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    any later version.
#
#    VFront is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#




/**
 * Classe generica per l'autenticazione a VFront. 
 * L'accesso pu� essere in 1 o 2 passi
 * In 1 passo se si usa l'autenticazione mediante il database di VFront
 * In 2 mediante strumenti esterni (LDAP, altro DB, ecc) per l'autenticazione
 * e il DB di VFRont per l'accreditamento dei diritti
 * @package VFront
 * @subpackage Authentication
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: class.auth.php 1075 2014-06-13 13:01:01Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */
class Auth{
	
	/**
	* @desc Utente oggetto dell'autenticazione
	* @var string
	*/
	private $user;
	
	/**
	* @desc Password oggetto dell'autenticazione
	* @var string
	*/
	private $passw;
	
	/**
	* Array restituito dalla classe 
	* 'response' => true | false 
	* 0 => (array) info_account | null 
	* @var array
	*/
	private $auth_obj = array('response'=>false);
	
	/**
	* Presenza dell'utente nel frontend
	* @var array
	*/
	private $utente_in_frontend = array('response'=>false);
	
	/**
	* Autenticazione esterna, impostata nel file CONF
	* @var string soap | ldap | db | db_ext | null=dbvfront
	*/
	private $tipo_external_auth = '';  // soap | ldap | db | db_ext | null=dbvfront
	
	
	/**
	*	Tipo di autenticazione. 
	* 	La modalità di accesso può essere in 2 step o 1 step. 
	*	Nella modalità 1 step l'autorizzazione e l'accreditamento sono congiunti,
	*	nel 2 step sono separati
	* 	Variabile obsoleta, mantenuta per compatibilità
	* @var string 1step | 2step
	*/
	private $modalita_auth = ''; // 1step | 2step
        
        
        /**
         * Redirect 
         */
	private $home_redirect;
	
	/**
	 *  Requested URL
	 */
	private $urlreq='';
	
	/**
	 * Autenticazione ed accreditamento dei diritti
	 *
	 * @param string $user Email dell'utente
	 * @param string $passw Password
	 * @return auth
	 */
	function  __construct($user,$passw, $urlreq=''){
		
		global  $vmsql, $vmreg, $db1,$conf_auth;
		
		if(strlen($urlreq)>0 && strlen($urlreq)<500 && strpos($urlreq, "http")===false){
		    $this->urlreq=$urlreq;
		}
		
		$this->tipo_external_auth = $conf_auth['tipo_external_auth'];
		
		$this->user = trim($user);
		$this->passw = trim($passw);
		$this->home_redirect=FRONT_DOCROOT."/";
		
		switch($this->tipo_external_auth){
			
			case 'soap': require_once(FRONT_REALPATH.'/classes/auth/auth.soap.php');
						$this->auth_obj = auth_soap($this->user , $this->passw);
						$this->modalita_auth='2step';
			break;
			
			case 'db': require_once(FRONT_REALPATH.'/classes/auth/auth.db.php');
						$this->auth_obj = auth_db($this->user , $this->passw);
						$this->modalita_auth='2step';
			break;
				
			case 'db_ext': require_once(FRONT_REALPATH.'/classes/auth/auth.db_ext.php');
						$this->auth_obj = Auth_DB_Ext($this->user , $this->passw);
						$this->modalita_auth='2step';
			break;
			
			case 'ldap': require_once(FRONT_REALPATH.'/classes/auth/auth.ldap.php');
						$this->auth_obj = auth_ldap($this->user , $this->passw);
						$this->modalita_auth='2step';
			break;
			
			case null: $this->modalita_auth='1step';
					   $this->__step_1();
		}
		
		
		// l'uente non esiste, mandalo via
		if($this->auth_obj['response']===false){
			
			
			$this->__respingi_utente(0);
		}
		
		// L'utente esiste in autorizzazione, ora esiste anche in DB? (2 passi)
		else{
			
			if($this->tipo_external_auth!==null){
			
				$this->__step_2();
			}
		
		
		}
		
	} //-- Fine funzione AUTH
	
	
	
	
	
	
	/**
	 * @desc Funzione di autenticazione basata sul DB di VFront, tabella utenti
	 *
	 */
	function __step_1(){
		
		global  $vmsql, $vmreg, $db1,$conf_auth;
		
					// Verifico che esista nel db Frontend
					$this->utente_in_frontend = $this->__frontend_user($this->user , $this->passw);
					
					// L'utente esiste anche nel DB!
					// prende i dati, li mette in sessione, lo fa andare avanti
					if($this->utente_in_frontend['response']){
						
						$this->__metti_in_sessione();
                        $this->vfront_redirect(); 
						exit;
					}
					else{
						
						// recover password procedure
						if($conf_auth['tipo_external_auth']==''){
							
							$this->utente_in_frontend = $this->__frontend_user_recover_passw($this->user , $this->passw);
							
							if($this->utente_in_frontend['response']){
								
								$this->__metti_in_sessione();
								
								if($this->urlreq!=''){
								    header("Location: ".$this->urlreq);
								}
								else{
								    header("Location: ".$this->home_redirect);
								}
								
								//$this->vfront_redirect(); 
                                                                exit;
							}
						}
						
						$this->__respingi_utente(0);
						
					}
	}
	
	
	
	
	/**
	 * @desc Funzione di accreditamento e inserimento se non esistono i diritti
	 *
	 */
	function __step_2(){
		
		global  $vmsql, $vmreg, $db1,$conf_auth;
		
		
			// Verifico che esista nel db Frontend, senza password, l'utente e' gia' autenticato
				$this->utente_in_frontend = $this->__frontend_user($this->user , $this->passw, false);
				
					// L'utente esiste anche nel DB!
					// prende i dati, li mette in sessione, lo fa andare avanti
					if($this->utente_in_frontend['response']){
						
						$this->__metti_in_sessione();
						if($this->urlreq!=''){
						    header("Location: ".$this->urlreq);
						}
						else{
						    header("Location: ".$this->home_redirect);
						}
						exit;
					}
					
					// L'utente e' autorizzato ma non c'e' nel DB.
					// Prendo i dati a disposizioni e creo un record nel DB con livello di accesso default
					else{
						
							// test primo utente:
							// se non ci sono utenti il primo utente e' amministratore
							$q_test1=$vmreg->query("SELECT count(*) FROM {$db1['frontend']}{$db1['sep']}utente");
							list($n_users)=$vmreg->fetch_row($q_test1);
							
							$livello_insert=($n_users==0) ? 3 : 1;
							
							$DEFAULT_GROUP_EXT=(isset($_SESSION['VF_VARS']['default_group_ext']))
												? intval($_SESSION['VF_VARS']['default_group_ext']) : 0;

							$sql_ins = sprintf("INSERT INTO {$db1['frontend']}{$db1['sep']}utente 
												(nick, nome, cognome, email, passwd, gid, livello, data_ins)
										        VALUES ('%s','%s','%s','%s','%s',%d,%d,'%s')", //,%d)",
												$this->auth_obj[0][$conf_auth['campo_nick']],
												$this->auth_obj[0][$conf_auth['campo_nome']],
												$this->auth_obj[0][$conf_auth['campo_cognome']],
												$this->auth_obj[0][$conf_auth['campo_email']],  // email in questo caso = nick
												$this->auth_obj[0][$conf_auth['campo_password']], 
												$DEFAULT_GROUP_EXT, // Imposta nel gruppo default 0
												$livello_insert, // Imposta il livello di amministrazione a 1 (nessuna amministrazione)
												date("Y-m-d")
												);
							
							// INSERISCE NEL DB
							$q_ins = $vmreg->query($sql_ins);
							if($vmreg->affected_rows($q_ins)!=1){
								
								openErrorGenerico(_('Authorization management error'),true);
								exit;
							}
							
							
							
							
							
							else{
								
								$this->utente_in_frontend = $this->__frontend_user($this->user , $this->passw, false);
								
								$this->__metti_in_sessione();
								
								if($this->urlreq!=''){
								    header("Location: ".$this->urlreq);
								}
								else{
								    header("Location: ".$this->home_redirect);
								}
								exit;
							}
							
							
						
					} 
		
	}
	
	
	
	
	
	
	/**
	 * Funzione di logout
	 *
	 * @param int $tipo_err 0=errore nell'utente password, 1=livello troppo basso per vedere la pagina
	 */
	function __respingi_utente($tipo_err=0){
		
		switch($tipo_err){
			
			case 0: // errore nell'utente password
				unset($_SESSION['user']);
				unset($_SESSION['gid']);				
				header("Location: ".FRONT_DOCROOT."/index.php?nolog"); exit;
			break;
			
			
			case 1: // livello troppo basso per vedere la pagina
				header("Location: ".FRONT_DOCROOT."/index.php?accesso_vietato=1&urlreq=".$this->urlreq); exit;
			break;
		}
	}
	
	
	
	
	
	
	/**
	 * @desc Cerca l'utente nel DB di VFront
	 * @param string $user Nome utente
	 * @param string $passw Password
	 * @param bool $use_passw Utilizza la password per l'autenticazione (default=true)
	 * @return array Array con le informazioni sull'utente
	 */
	function __frontend_user($user,$passw='',$use_passw=true){
		
		global  $vmsql, $vmreg, $db1;
		
		// cerca l'utente in database
		$sql = "SELECT * FROM {$db1['frontend']}{$db1['sep']}utente 
				WHERE nick='".$vmreg->escape($user)."' ";
		
		if($use_passw)	$sql.=" AND passwd='".md5($passw)."'";
		
		$sql.=" LIMIT 1";
		
		$q= $vmreg->query($sql);
		
		// L'utente esiste anche nel DB!
		// prende i dati, li mette in sessione, lo fa andare avanti
		if($vmreg->num_rows($q)==1){

			$RS= $vmreg->fetch_assoc($q);

			return array('response'=>true,$RS);
		}
		
		// L'utente è autorizzato ma non c'è nel DB.
		else{
			
			return array('response'=>false);
		}
		
	}
	
	
	
	
	/**
	 * @desc Recover password procedure
	 * @param string $user Nome utente
	 * @param string $recover_passw Password
	 * @return array Array con le informazioni sull'utente
	 */
	function __frontend_user_recover_passw($user,$recover_passw){
		
		global  $vmsql, $vmreg, $db1;
		
		// cerca l'utente in database
		$sql = "SELECT * FROM {$db1['frontend']}{$db1['sep']}utente 
				WHERE nick='".$vmreg->escape($user)."' ";
		
		$sql.=" AND recover_passwd='".md5($recover_passw)."'";
		
		$sql.=" LIMIT 1";
		
		$q= $vmreg->query($sql);
		
		
		// L'utente esiste anche nel DB!
		// prende i dati, li mette in sessione, lo fa andare avanti
		if($vmreg->num_rows($q)==1){
			
			$RS= $vmreg->fetch_assoc($q);
			
			$sql_up="UPDATE {$db1['frontend']}{$db1['sep']}utente 
								SET passwd=recover_passwd ,
								recover_passwd='' 
								WHERE id_utente=".intval($RS['id_utente']);
			
			// UPDATE USER DATA:
			$q_up= $vmreg->query($sql_up);
			
			
			
			return array('response'=>true,$RS);
		}
		
		// L'utente è autorizzato ma non c'è nel DB.
		else{
			
			return array('response'=>false);
		}
		
	}
	
	
	
	/**
	 * @desc Inserisce in sessione le variabili relative all'utente quando autenticato e accreditato
	 *
	 */
	function __metti_in_sessione(){
		
		$_SESSION['user']=array(
						'uid'	=> $this->utente_in_frontend[0]['id_utente'],
						'nick'	=> $this->utente_in_frontend[0]['nick'],
						'email'	=> $this->utente_in_frontend[0]['email'],
						'nome'	=> $this->utente_in_frontend[0]['nome'],
						'cognome'=>$this->utente_in_frontend[0]['cognome'],
						'data_ins'=>$this->utente_in_frontend[0]['data_ins'],
						'gid'     =>$this->utente_in_frontend[0]['gid'],
						'livello' =>$this->utente_in_frontend[0]['livello']
						);
						
		$_SESSION['gid']=$this->utente_in_frontend[0]['gid'];
	}
        
    protected function vfront_redirect() {

        // renew the VF_VARS
        $_SESSION['VF_VARS'] = var_frontend('session', 'session');

        if (isset($_SESSION['VF_VARS']['home_redirect']) &&
                strlen(trim($_SESSION['VF_VARS']['home_redirect'])) > 0
        // && ($_SESSION['VF_VARS']['home_redirect']!=0) 
        ) {

            $redirect = FRONT_DOCROOT . "/" . $_SESSION['VF_VARS']['home_redirect'];
        } else if ($this->urlreq != '') {

            $redirect = $this->urlreq;
        } else {
            $redirect = FRONT_DOCROOT . "/";
        }

        header("Location: " . $redirect);
        exit;
    }
}


