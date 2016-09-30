<?php
########################################################################
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


/**
* File di index di VFront.
* Questo file viene richiamato per fare il login e|o come Home per l'utente
* Il file di index mostra il login qualora non ci fosse una sessione valida.
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2008 Mario Marcello Verona
* @version 0.96 $Id: index.php 1127 2014-12-17 10:56:54Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
 * @todo Escape data in external auth
*/



require_once("./inc/conn.php");
require_once("./inc/layouts.php");



$INPUTS="";



if(isset($_GET['login'])){
	
	$_dati = $vmreg->recursive_escape($_POST);
	
	$LOGIN = new Auth($_dati['nick'],$_dati['passw']);
	
	exit;
}
elseif(isset($_GET['logout'])){
	
	$_SESSION=array();
	
	header("Location: ".FRONT_DOCROOT."/index.php");

	exit;
}
elseif(isset($_GET['nolog'])){
	
	$_SESSION=array();
	
	// reload global vars
	$_SESSION['VF_VARS']=var_frontend('session','session');
	
	mostra_login();

	exit;
}
elseif (isset($_SESSION['user']) && is_array($_SESSION['user'])){
	

	mostra_loggato();
	
	
}
else{

	mostra_login();
}




##########################################################################################################














/**
 * Questa funzione genera la pagina di login nel caso il file index sia stato richiamato senza 
 * la presenza di una sessione valida.
 * Pu� presentare il link per il recupero della password se l'opzione � stata definita tra le variabili
 * di VFront e se l'autenticazione non avviene mediante strumenti esterni.
 *
 */
function mostra_login(){
	
	global $conf_auth;
	
	// MODULO PRINCIPALE
	
	$apertura= str_replace("<body>","<body onload=\"document.getElementById('nick').focus();\">",openLayout1(_NOME_PROJ));
	echo $apertura;
	
	if(isset($_GET['nolog'])){
		echo "<div id=\"nologin\"><p>"._('Error in username or password, please verify')."</p></div>\n";
	}
	
	if(isset($_SESSION['VF_VARS']['recupero_password']) 
	      && $_SESSION['VF_VARS']['recupero_password']==1){


		if($conf_auth['tipo_external_auth']==null){
			

			$PASSW_RECOVER="<p><a href=\"password_recover.php\">"._('I\'ve forgotten my password')."</a></p>";
		}
		else if(isset($_SESSION['VF_VARS']['recupero_password_url'])
			&& $_SESSION['VF_VARS']['recupero_password_url']!=''){

			$PASSW_RECOVER="<p><a href=\"{$_SESSION['VF_VARS']['recupero_password_url']}\">"._('I\'ve forgotten my password')."</a></p>";
		}
		else{
			$PASSW_RECOVER='';
		}
	}
	else{
		$PASSW_RECOVER='';
	}
	
	$access_label= ($conf_auth['tipo_external_auth']=='' || ($conf_auth['campo_nick']==$conf_auth['campo_mail'])) 
				? _('E-mail')
				: _('Username');
	
	echo "
		<div id=\"login\" align=\"center\">
			<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?login\" >
				<fieldset>
	
					<legend>"._('System access')."</legend>
					<p>		
						<label for=\"nick\">".$access_label."</label><br />
						<input type=\"text\" name=\"nick\" size=\"30\" maxlength=\"200\" id=\"nick\" /><br />
					</p>
					<p>
						<label for=\"passw\">"._('Password')."</label><br />
						<input type=\"password\" name=\"passw\" size=\"30\" maxlength=\"100\" id=\"passw\" /><br />
					</p>
					<p><input type=\"submit\" name=\"accedi\" value=\"  "._('Access')."  \" id=\"accedi\" /></p>
					$PASSW_RECOVER
				</fieldset>
	
			</form>
		</div>
	";
	
	
	
	
	echo closeLayout1();
	
	exit;
}






/**
 * Funzione che genera il codice per produrre la home page.
 * Qualora non ci fosse una sessione valida viene invece creata la pagina di login,
 * mediante la funzione mostra_login()
 * @see function mostra_login
 *
 */
function mostra_loggato(){
	
	global  $vmsql, $vmreg, $db1;
	
	proteggi(1);
	
	$files=array("js/scriptaculous/lib/prototype.js",
				 "js/scriptaculous/src/scriptaculous.js",
				 "js/home.js");
	
	echo openLayout1(_NOME_PROJ,$files);
		

	
	echo "<h1 style=\"padding-left:4px;\">"._('Main menu')."</h1>\n";
	
	if($_SESSION['gid']==0 && (isset($_SESSION['VF_VARS']['alert_login_default']) && $_SESSION['VF_VARS']['alert_login_default']==1)){
		
		echo "<div class=\"info\"><strong>"._('Warning!')."</strong><br />
		"._('You\'re attempting to  login via the <strong>default group')."</strong>.
		"._("If you login for the first time, this is correct; contact your system administrator to set the privileges correctly")."</div>\n";
	
	}
	
	
	// TEst configurazione
	if($_SESSION['user']['livello']>=2){
		
		// prendi il test
		$testvfront=@join('',@file('./conf/.testvfront'));
		
		if($testvfront=='1' && $_SESSION['VF_VARS']['alert_config']==1){
		
			echo "<div class=\"alertbox\" id=\"alert_config\"><strong>"._('Warning!')."</strong><br />\n";
			echo sprintf(_('There are some problems in configuring VFront which may affect the full functioning of the application. See %s'),'<a href="admin/vfront.info.php">'._('diagnostic page').'</a>');
			echo " - \n";
			echo "<span id=\"hide_alert_config\" class=\"fakelink\" onclick=\"$('alert_config').fade();\">"._('Do not show this message again')."</span> ".
			"(".sprintf(_('you can always restore from page %s'),"<a href=\"admin/variabili.php\">"._('variables')."</a>").")";
			echo "</div>\n";
		
		}
	
	}

######################################################################################################################
	
	// RECUPERO LE TABELLE
	$matrice_tab= RegTools::prendi_tabelle($_SESSION['gid'], true, true, true);
	
	// RECUPERO LE VISTE
	$matrice_view= RegTools::prendi_viste($_SESSION['gid'], true, true);
	
	
	// RECUPERO LE STATISTICHE
	
	if(Common::is_admin()){
		$sql_add_stat='';
	}
	else{
		$sql_add_stat=" AND ( (auth_stat=1) ";
		$sql_add_stat.="   OR (auth_stat=2  AND u.gid=".$_SESSION['gid']." )";
		$sql_add_stat.="   OR (auth_stat=3  AND autore=".$_SESSION['user']['uid']." ))";
	}
	
	
	$sql_stat="SELECT id_stat, nome_stat, desc_stat, u.gid
			  FROM {$db1['frontend']}{$db1['sep']}stat s
			  INNER JOIN {$db1['frontend']}{$db1['sep']}utente u ON s.autore=u.id_utente
			  WHERE published=1
			  $sql_add_stat
			  ORDER BY nome_stat
			  ";
	$q_stat = $vmreg->query($sql_stat);
	
	$matrice_stat = $vmreg->fetch_assoc_all($q_stat);
	
	
	
	$LI1 = "";
		
		for($i=0;$i<count($matrice_tab);$i++){
			
			$comment1 = preg_replace("/;? ?InnoDB.*|/ui","",$matrice_tab[$i]['commento']);
			
			$tab_name = ($matrice_tab[$i]['table_alias']=='') ? $matrice_tab[$i]['table_name'] : $matrice_tab[$i]['table_alias'];
            
            $default_view_hash = (isset($matrice_tab[$i]['default_view']) && $matrice_tab[$i]['default_view'] == 'table') ? '#tab' : '';
			
			$LI1.= "
			<li>
				<a href=\"scheda.php?oid=".$matrice_tab[$i]['id_table'].$default_view_hash."\">".$tab_name."</a>
				<div class=\"desc-tab\">".htmlentities(Common::vf_utf8_decode($comment1),ENT_QUOTES, FRONT_ENCODING)."</div>
			</li>\n";
		}
		
	$LI2 = "";
		
		for($i=0;$i<count($matrice_view);$i++){
			
			$view_name = ($matrice_view[$i]['table_alias']=='') ? $matrice_view[$i]['table_name'] : $matrice_view[$i]['table_alias'];
			
			$LI2.= "
			<li>
				<a href=\"scheda.php?oid=".$matrice_view[$i]['id_table']."\">".$view_name."</a>
				<div class=\"desc-tab\">&nbsp;</div>
			</li>\n";
		}
		
	$LI3 = "";
		
		for($i=0;$i<count($matrice_stat);$i++){
			
			$LI3.= "
			<li>
				<a href=\"stats/stat.personal.php?id_s=".$matrice_stat[$i]['id_stat']."&amp;ref=home\">".$matrice_stat[$i]['nome_stat']."</a>
				<div class=\"desc-tab\">".$matrice_stat[$i]['desc_stat']."</div>
			</li>\n";
		}
		
######################################################################################################################

	

	if($LI1==''){
		
		if($_SESSION['user']['livello']==3){

			$tabs=RegTools::prendi_tabelle();
			if(count($tabs)==0){
				
				$inizializza=" <a href=\"admin/menu_registri.php?initreg\">"._("Initialize registry")."</a>\n";
			}
			else $inizializza='';
		}
		else{
			$inizializza='';
		}
		
		$LI1="<li>"._('At the moment there are no available tables').$inizializza."</li>";
	}
	
	
	$files_add_index=glob("{./usr/add_index.php,./usr/*/add_index.php}",GLOB_BRACE);
	
	//if(file_exists("./usr/add_index.php")){
	foreach($files_add_index as $file_add){	
	    
		include($file_add);

		if(function_exists('add_index_top')){
			echo add_index_top();
		}
		
		if(preg_match("|./usr/([\w]+)/.+|",$file_add,$dirusrname)){
		    
		    $fname=$dirusrname[1].'__add_index_top';
		    
		    if(function_exists($fname)){
			echo $fname();
		    }
		}
		
	}
	
	
	
	echo "
		<div id=\"box-tabelle\" class=\"box-home\">
		
			<div class=\"box-home-txt\">
				<h2>"._('Available tables')."</h2>
				<ul class=\"lista-tabelle\">
					$LI1
				</ul>
			</div>
		</div>	
			";
			
	if(count($matrice_view)>0){
	
		echo "
			<div id=\"box-viste\" class=\"box-home\">
			
				<div class=\"box-home-txt\">
					<h2>"._('Data views')."</h2>
					<ul class=\"lista-tabelle\">
						$LI2
					</ul>
				</div>
			</div>	
				";
	}
	
	
	if(count($matrice_stat)>0){
	
		echo "
			<div id=\"box-stat-home\" class=\"box-home\" style=\"clear:both;\">
			
				<div class=\"box-home-txt\">
					<h2>"._('Database statistics')."</h2>
					<ul class=\"lista-tabelle\">
						$LI3
					</ul>
				</div>
			</div>	
				";
	}
	
	
	echo "
		<div id=\"box-info\" class=\"box-home\" style=\"clear:both;\">
	
			<div class=\"box-home-txt\">
				<h2>"._('FAQs (Frequently Asked Questions) and answers')."</h2>
				<p><a href=\"helpdocs.php\">"._('Useful documents')."</a></p>
				<p><a href=\"credits.php\">"._('Credits')."</a></p>
			</div>
	
		</div>
			
		<p style=\"clear:both;\">&nbsp;</p>
		";
		
	echo closeLayout1();
	
	
}



?>