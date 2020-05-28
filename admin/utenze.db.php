<?php
/**
 * Poiché VFront può essere gestito tramite collegamento esterno le utenze non sono 
 * completamente gestibili da qui. In compenso è possibile eseguire numerose operazioni
 * di gestione di appartenenza a gruppi e livello di amministrazione da questo script.
 * 
 * @desc File di gestione delle utenze
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: utenze.db.php 1172 2017-05-12 18:33:50Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

include("../inc/conn.php");
include("../inc/layouts.php");
include("../inc/func.magic_excel.php");

 proteggi(2);


/**
 * @desc Funzione di ordinamento della tabella degli utenti 
 * @param string $eti Etichetta di colonna
 * @param int $n Numero identificativo di colonna
 * @return string HTML
 */
function tab_ord($eti,$n){

	global $_ORD;

	$GID = (isset($_GET['gid'])) ? (int) $_GET['gid'] : 0;

	if($_ORD==$n) return $eti;
	else{

		return "<a href=\"".basename(Common::phpself())."?gid=". $GID ."&amp;ord=$n\" class=\"link-ord\">$eti</a>";
	}
}


if(isset($_POST['id_utente']) && isset($_GET['mod'])){

	$_dati=$vmreg->recursive_escape($_POST);

	if(User_Session::level()<$_dati['livello']){

		header("Location: ?msg=no_lev");
		exit;
	}

	$sql_upd=sprintf("UPDATE {$db1['frontend']}{$db1['sep']}utente SET gid=%d, livello=%d" 
						." WHERE id_utente=%d",
						$_dati['gid'],
						$_dati['livello'],
						$_dati['id_utente']
						);
	$q_upd = $vmreg->query($sql_upd);

	$esito=$vmreg->affected_rows($q_upd);


	// in caso di richiesta dell'email prendi l'email dell'interessato
	if(isset($_dati['feed_mail']) && ($_dati['feed_mail'] || $_dati['feed_mail']==2)){

		$q_mail = $vmreg->query("SELECT email from {$db1['frontend']}{$db1['sep']}utente WHERE id_utente=".intval($_dati['id_utente']));

		list($email_ut)=$vmreg->fetch_row($q_mail);

	}
	else{
		$_dati['feed_mail']=null;
	}


	if($esito==1){

		switch($_dati['feed_mail']){

			case 1: // invia la mail con messaggio standards
					$feedok='ok_mail1';
			break;

			case 2: // invia la mail con messaggio personalizzato
					$feedok='ok_mail2';
			break;

			case 3: 
			default : /// Non inviare la mail
					$feedok='ok_mail3';
			break;
		}


		header("Location: ?feed=$feedok&mod=".$_dati['id_utente']);
		exit;
	}
	else{
		header("Location: ?feed=ko&mod=".$_dati['id_utente']);
		exit;
	}


}
else if(isset($_GET['insert_new']) && $_POST['nome']){

	$_data = $vmreg->recursive_escape($_POST);

    // No CSRF
    if(isset($_SESSION['code_rand_name']) 
            && isset($_SESSION['code_rand_value']) 
            && isset($_POST[$_SESSION['code_rand_name']])
            && $_POST[$_SESSION['code_rand_name']] === $_SESSION['code_rand_value']
            ){

        // Ok
        unset($_SESSION['code_rand_name']);
        unset($_SESSION['code_rand_value']);
    }
    else{
        header("Location: ?CSRF");
		exit;
    }


	// ripeto la clausola di sicurezza
	if(User_Session::level()<2 || $conf_auth['tipo_external_auth']!=''){

		header("Location: ?noauth");
		exit;
	}
	// completezza lato server dei dati
	else if($_data['nome']=='' || 
		   $_data['cognome']=='' ||
		   $_data['email']=='' ||
		   $_data['passwd']=='' ||
		   $_data['passwd']!=$_data['passwd1']
		){

		header("Location: ?nodata");
		exit;
	}

	// procedo all'inserimento

	$sql=sprintf("INSERT INTO {$db1['frontend']}{$db1['sep']}utente (nick, nome, cognome, email, passwd,data_ins)
				 VALUES ('%s','%s','%s','%s','%s','%s')",
				$_data['email'],
				$_data['nome'],
				$_data['cognome'],
				$_data['email'],
				md5($_data['passwd']),
				date("Y-m-d")
				);

	$q=$vmreg->query($sql);

	$id_new = $vmreg->insert_id("{$db1['frontend']}{$db1['sep']}utente",'id_utente');

	if($vmreg->affected_rows($q)==1){

		header("Location: ?mod=$id_new");
		exit;
	}
	else{

		header("Location: ?feed=noinsert");
		exit;
	}
}


// UPDATE USER DATA
else if(isset($_POST['ch_data']) && isset($_POST['id_utente']) && intval($_POST['id_utente'])>0){


	$_data=$vmreg->recursive_escape($_POST);

	$ID_UT=intval($_data['id_utente']);

	require_once("../inc/func.validmail.php");

	if(!valid_mail($_data['ch_email'])){

		header("Location: ?mod=$ID_UT&feed=ko_mail");
		exit;
	}
	else{


		$q=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}utente
						SET email='".$_data['ch_email']."' ,
						nick='".$_data['ch_email']."',
						nome='".$_data['ch_nome']."' , 
						cognome='".$_data['ch_cognome']."' 
						WHERE id_utente=$ID_UT");

		if($vmreg->affected_rows($q)==1){

			header("Location: ?mod=$ID_UT&feed=ok_chuser");
			exit;
		}
		else{
			header("Location: ?mod=$ID_UT&feed=ko_chuser");
			exit;
		}
	}


}


// UPDATE PASSWORD
else if(isset($_POST['ch_submit_passwd']) && isset($_POST['id_utente']) && intval($_POST['id_utente'])>0){


	$_data=$vmreg->recursive_escape($_POST);

	$ID_UT=intval($_data['id_utente']);

	if($_data['ch_passwd']==''){

		header("Location: ?mod=$ID_UT&feed=ko_no_passwd");
		exit;

	}
	else if($_data['ch_passwd']!=$_data['ch_passwd2']){

		header("Location: ?mod=$ID_UT&feed=ko_pass_equal");
		exit;
	}
	else{


		$q=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}utente
						SET passwd='".md5($_data['ch_passwd'])."'
						WHERE id_utente=".$ID_UT);

		if($vmreg->affected_rows($q)==1){

			header("Location: ?mod=$ID_UT&feed=ok_passwd");
			exit;
		}
		else{
			header("Location: ?mod=$ID_UT&feed=ko_passwd");
			exit;
		}
	}


}


else if (isset($_GET['mod'])){


	// FORM PER MODIFICARE L'UTENTE


	echo openLayout1(_("Personal info"),array("sty/tabelle.css","js/mostra_nascondi_id.js","sty/admin.css"));

	$UID = intval($_GET['mod']);

	// PRTENDI DATI UTENTE IN ESAME
	$q_ut=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}utente WHERE id_utente=".$UID
					 ." AND livello<=".intval(User_Session::level()) );

	$ut_info=$vmreg->fetch_assoc($q_ut);

	$sel_liv1 =($ut_info['livello']==1) ? "checked=\"checked\"" : "";
	$sel_liv2 =($ut_info['livello']==2) ? "checked=\"checked\"" : "";
	$sel_liv3 =($ut_info['livello']==3) ? "checked=\"checked\"" : "";


	echo breadcrumbs(array("HOME","ADMIN",
					"utenze.db.php"=>_("frontend users' administration"),
					_("update profile")
					));


	echo "<h1>"._("Update user profile")." <span class=\"var\">".$ut_info['nome']." ".$ut_info['cognome']."</span></h1>\n";


		// Feedback
	if(isset($_GET['feed'])){
		switch($_GET['feed']){


			// OK----------
			case 'ok_mail1': $feed_str="<p class=\"feed-mod-ok\">"._("User settings have been properly modified, standard notification message has been sent")."</p>\n";
			break;

			case 'ok_mail2': $feed_str="<p class=\"feed-mod-ok\">"._("User settings have been properly modified, custom notification message has been sent")."</p>\n";
			break;

			case 'ok_mail3': $feed_str="<p class=\"feed-mod-ok\">"._("User settings have been properly modified, no notification message has  been sent")."</p>\n";
			break;

			case 'del_ok': $feed_str="<p class=\"feed-mod-ok\">"._("User deleted correctly")."</p>\n";
			break;


			case 'ok_passwd': $feed_str="<p class=\"feed-mod-ok\">"._("Password modified correctly")."</p>\n";
			break;			

			case 'ok_chuser': $feed_str="<p class=\"feed-mod-ok\">"._("User data modified correctly")."</p>\n";
			break;	


			// KO----------
			case 'ko': $feed_str="<p class=\"feed-mod-ko\">"._("Error in user settings update")."</p>\n";
			break;			

			case 'del_ko': $feed_str="<p class=\"feed-mod-ko\">"._("Error in user deletion")."</p>\n";
			break;			

			case 'ko_passwd': $feed_str="<p class=\"feed-mod-ko\">"._("No changes to the password")."</p>\n";
			break;			

			case 'ko_no_passwd': $feed_str="<p class=\"feed-mod-ko\">"._("Not be given a blank password")."</p>\n";
			break;			

			case 'ko_pass_equal': $feed_str="<p class=\"feed-mod-ko\">"._("The two password don't match")."</p>\n";
			break;			

			case 'ko_chuser': $feed_str="<p class=\"feed-mod-ko\">"._("No changes for user data")."</p>\n";
			break;			

			case 'ko_mail': $feed_str="<p class=\"feed-mod-ko\">"._("The email you entered does not appear to be valid")."</p>\n";
			break;			


			default: $feed_str="";
		}

		echo $feed_str;
	}


	echo "

	<div style=\"float:left;\">

	<form action=\"" . Common::phpself() . "?mod\" method=\"post\">

	<fieldset  class=\"chuser\">
	<legend>"._("Administration level")." </legend>

		<input type=\"radio\" name=\"livello\" value=\"1\" $sel_liv1/> "._("User")."
		<div class=\"info-campo\">"._("No administration options")."</div>

		<input type=\"radio\" name=\"livello\" value=\"2\" $sel_liv2/> "._("Local administrator")."
		<div class=\"info-campo\">"._("Log and same level user administration")."</div>
	";


	// Se � amministratore pu� pare amministratori
	if(User_Session::level()==3){

		echo "
		<input type=\"radio\" name=\"livello\" value=\"3\" $sel_liv3/> "._("Administrator")."
		<div class=\"info-campo\">"._("Administrator")."</div>
		";
	}


	echo "\n\t</fieldset>\n";


	// PRENDI GRUPPI
	$q_g=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}gruppo ORDER BY gid");
	$matrice_g=$vmreg->fetch_assoc_all($q_g);


	// User Group
	echo "

	<fieldset class=\"chuser\">
	<legend>"._("User group")."</legend>\n";

	for($i=0;$i<count($matrice_g);$i++){

		$sel_gid = ($ut_info['gid']==$matrice_g[$i]['gid']) ? "checked=\"checked\"" : "";

		echo "<input type=\"radio\" name=\"gid\" value=\"".$matrice_g[$i]['gid']."\" $sel_gid />".$matrice_g[$i]['nome_gruppo']."
		<div class=\"info-campo\">".$matrice_g[$i]['descrizione_gruppo']."</div>\n";

	}


	echo "
		</fieldset>
		";


	echo "
		<div id=\"opzioni_modifica1\" style=\"position:absolute;width:100%;height:100%;top:20%;left:0;display:none;\">
			<div id=\"opzioni_modifica2\"  style=\"margin:auto auto auto auto;width:400px;height:400px;border:1px solid #000; background-color: #DFECFF; padding:30px; \">

			<h2>"._("Send a notification to user?")."</h2>

				<input type=\"radio\" name=\"feed_mail\" value=\"3\" id=\"feed_mail3\" onclick=\"document.getElementById('feed_mail_txt').disabled=true;\"/> <label for=\"feed_mail3\">"._("Do not send anything")." </label><br />
				<input type=\"radio\" name=\"feed_mail\" value=\"1\" id=\"feed_mail1\" onclick=\"document.getElementById('feed_mail_txt').disabled=true;\"/> <label for=\"feed_mail1\">"._("Sends a standard notification to the user")."</label><br />
				<input type=\"radio\" name=\"feed_mail\" value=\"2\" id=\"feed_mail2\" onclick=\"document.getElementById('feed_mail_txt').disabled=false;\"/> <label for=\"feed_mail2\">"._("Sends a custom-made message to the user")."</label><br />
				<textarea name=\"feed_mail_txt\" id=\"feed_mail_txt\" disabled=\"disabled\" cols=\"35\" rows=\"10\"></textarea><br />
				<p align=\"center\"><input type=\"submit\" name=\"prosegui\" value=\" "._("Continue")." \" /> <input type=\"submit\" name=\"annulla\" value=\" Annulla \" onclick=\"mostra_nascondi('opzioni_modifica1');\" /></p>

			</div>
		</div>
		<br />

		<input type=\"hidden\" name=\"id_utente\" value=\"".$ut_info['id_utente']."\" />


		<input type=\"button\" onclick=\"mostra_nascondi('opzioni_modifica1');\" name=\"invia\" value=\""._("Modify the profile")."\" />

		<br />


		</form>

		</div>
	";


	// change user & passwd

	if(User_Session::level()>=2 && $conf_auth['tipo_external_auth']==''){

		echo "<div id=\"change_userdata\" style=\"float:left;\" >

		<form id=\"f_change_userdata\" action=\"" . Common::phpself() . "\" method=\"post\">


			<fieldset class=\"chuser\">
				<legend>"._("Change user data")."</legend>

				<p>
					<label for=\"ch_email\">"._('Email').":</label><br />
					<input type=\"text\" id=\"ch_email\" name=\"ch_email\" value=\"".$ut_info['email']."\" size=\"40\"/>
				</p>

				<p>
					<label for=\"ch_nome\">"._('Name').":</label><br />
					<input type=\"text\" id=\"ch_nome\" name=\"ch_nome\" value=\"".$ut_info['nome']."\" />
				</p>

				<p>
					<label for=\"ch_cognome\">"._('Surname').":</label><br />
					<input type=\"text\" id=\"ch_cognome\" name=\"ch_cognome\" value=\"".$ut_info['cognome']."\" />
				</p>
				<p>

					<input type=\"hidden\" name=\"id_utente\" value=\"".$ut_info['id_utente']."\" />
					<input type=\"submit\" id=\"ch_submit\" name=\"ch_data\" value=\" "._('Send')." \" />
				</p>

			</fieldset>


			<fieldset class=\"chuser\">
				<legend>"._("Change user password")."</legend>

				<p>
					<label for=\"ch_passwd\">"._('Password').":</label><br />
					<input type=\"password\" id=\"ch_passwd\" name=\"ch_passwd\" value=\"\" />
				</p>

				<p>
					<label for=\"ch_passwd2\">"._('Re-enter the password').":</label><br />
					<input type=\"password\" id=\"ch_passwd2\" name=\"ch_passwd2\"  value=\"\" />
				</p>

				<p>

					<input type=\"hidden\" name=\"id_utente\" value=\"".$ut_info['id_utente']."\" />
					<input type=\"submit\" id=\"ch_submit2\" name=\"ch_submit_passwd\" value=\" "._('Send')." \" />
				</p>

			</fieldset>

		</form>
		</div>
		";

	}


	echo closeLayout1();
	exit;
}
else if (isset($_GET['del'])){

	$id_utente = (int) $_GET['del'];

	if($id_utente>0){

		$q_del=$vmreg->query("DELETE FROM {$db1['frontend']}{$db1['sep']}utente
							WHERE id_utente=$id_utente AND livello<=".intval(User_Session::level()));

		if($vmreg->affected_rows($q_del)==1){

			header("Location: ?feed=del_ok");
			exit;
		}
		else{
			header("Location: ?feed=del_ko");
			exit;
		}
	}else{
		header("Location: ?feed=del_ko");
		exit;
	}


}

#################################################
#
#	NEW USER
#

else if(isset($_GET['new']) && User_Session::level()>=2 && $conf_auth['tipo_external_auth']==''){


	echo openLayout1(_("Personal info"),array("sty/tabelle.css","js/mostra_nascondi_id.js","sty/admin.css"));

	echo breadcrumbs(array("HOME","ADMIN",
					"utenze.db.php"=>_("frontend users' administration"),
					_("create new user")
					));

    echo "<h1>"._("Create new user")." </h1>\n";

    echo "<p>"._('All data are required')."</p>\n";

    echo "
    <form id=\"f1\" action=\"" . Common::phpself() . "?insert_new\" method=\"post\">
    <fieldset style=\"width:70%\">
    <legend>"._('New user settings')."</legend>\n";

    $obb = "<span class=\"rosso\">*</span>";

    echo "<p><label for=\"nome\" >"._('First name')." $obb</label><br />
    <input type=\"text\" size=\"40\" maxlength=\"200\" value=\"\" name=\"nome\" id=\"cognome\" /></p>
    ";

    echo "<p><label for=\"cognome\" >"._('Last name')." $obb</label><br />
    <input type=\"text\" size=\"40\" maxlength=\"200\" value=\"\" name=\"cognome\" id=\"cognome\" /></p>
    ";

    echo "<p><label for=\"email\" >"._('Email')." $obb</label><br />
    <input type=\"text\" size=\"40\" maxlength=\"200\" value=\"\" name=\"email\" id=\"email\" /></p>
    ";

    echo "<p><label for=\"passwd\" >"._('Password')." $obb</label><br />
    <input type=\"password\" size=\"40\" maxlength=\"200\" value=\"\" name=\"passwd\" id=\"passwd\" /></p>
    ";

    echo "<p><label for=\"passwd1\" >"._('Re-enter the password')." $obb</label><br />
    <input type=\"password\" size=\"40\" maxlength=\"200\" value=\"\" name=\"passwd1\" id=\"passwd1\" /></p>
    ";

    // no CSRF
    $_SESSION['code_rand_name'] = uniqid('__');
    $_SESSION['code_rand_value'] = sha1(microtime(true));

    echo "<input type=\"hidden\" name=\"".$_SESSION['code_rand_name']."\" value=\"".$_SESSION['code_rand_value']."\" />\n";

    echo "<p><br />
    <input type=\"submit\" value=\"   "._('Send')."   \" id=\"invia\" /></p>
    ";


    echo "
    </fieldset>
    </form>\n";

    echo closeLayout1();

    exit;

}


 echo openLayout1(_("User management of frontend"), array("sty/admin.css","sty/tabelle.css"));


 echo breadcrumbs(array("HOME","ADMIN",
					_("frontend users' administration")
					));

 echo "<h1>"._("User management of frontend")."</h1>\n";


 if(isset($_GET['feed'])){
		switch($_GET['feed']){

			case 'ok_mail1': $feed_str="<p class=\"feed-mod-ok\">"._("User settings have been properly modified, standard notification message has been sent")."</p>\n";
			break;

			case 'ok_mail2': $feed_str="<p class=\"feed-mod-ok\">"._("User settings have been properly modified, custom notification message has been sent")."</p>\n";
			break;

			case 'ok_mail3': $feed_str="<p class=\"feed-mod-ok\">"._("User settings have been properly modified, no notification message has  been sent")."</p>\n";
			break;

			case 'ko': $feed_str="<p class=\"feed-mod-ko\">"._("Error in user settings update")."</p>\n";
			break;			

			case 'del_ok': $feed_str="<p class=\"feed-mod-ok\">"._("User deleted correctly")."</p>\n";
			break;

			case 'del_ko': $feed_str="<p class=\"feed-mod-ko\">"._("No users deleted")."</p>\n";
			break;			

			default: $feed_str="";
		}

		echo $feed_str;
	}


 echo "<img src=\"../img/utenti.gif\" class=\"img-float\" alt=\"impostazioni registri\" />\n";


 // FILTRI:
echo "<form action=\"" . Common::phpself() . "\" method=\"get\" >\n";


	// FILTRO PER GRUPPO -------------------------
		echo "<p>"._('Filter by group').": <select name=\"gid\" id=\"gid\" onchange=\"submit()\" >
		";

		// PRENDI I GRUPPI
		$q_g=$vmreg->query("SELECT gid, nome_gruppo FROM ".$db1['frontend'].$db1['sep']."gruppo ORDER BY gid");


		echo "\t\t<option value=\"99999\">"._("All groups")."</option>\n";


		while($RSg=$vmreg->fetch_assoc($q_g)){


			$sel_gruppo= (isset($_GET['gid']) && $_GET['gid']==$RSg['gid']) ? "selected=\"selected\"" : "";

			echo "\t\t<option value=\"".$RSg['gid']."\" $sel_gruppo>".$RSg['gid']." - ".$RSg['nome_gruppo']."</option>\n";
		} 
		echo "</select>\n";


		// NEW USER 
		if(User_Session::level()>=2 && $conf_auth['tipo_external_auth']==''){

			echo " | <a href=\"?new\">"._('Create new user')."</a>\n";
		}


		echo "</p>\n";

		// ORDINAMENTO
		$_ORD = (isset($_GET['ord'])) ? intval($_GET['ord']) : '';

		echo "<input type=\"hidden\" name=\"ord\" value=\"".$_ORD."\" />\n";

	// --- fine gruppo


echo "</form>\n"; 


 $TAB= "<table class=\"tab-color\" summary=\"Tabella utenze\">\n";

 $TAB.= "
 	<tr>
		<th>".tab_ord('id',1)."</th>
		<th>".tab_ord(_('group'),11)."</th>
		<th>".tab_ord(_('level'),9)."</th>
		<th>".tab_ord(_('Nickname'),2)."</th>
		<th>".tab_ord(_('surname'),5)."</th>
		<th>".tab_ord(_('name'),4)."</th>".
//		"<th>email</th>".
//		"<th>istituto</th>".
		"<th>".tab_ord(_('insert data'),7)."</th>
		<th class=\"arancio\">"._("modify")."</th>
		<th class=\"arancio\">"._("delete")."</th>
	</tr>
	";

 // FILTRI 
 if(isset($_GET['gid']) && $_GET['gid']!=99999){

 	 $WHERE="AND g.gid=". intval($_GET['gid']);
 }
 else{

 	$WHERE="";
 }


 // ORDINAMENTO
 $ORD= ($_ORD=='') ? 5 /*cognome*/ : $_ORD;
 $SORT='ASC';

 // LIMIT 
 $LIMIT="";


  $sql = "SELECT u.*, g.nome_gruppo FROM {$db1['frontend']}{$db1['sep']}utente u
 		INNER JOIN {$db1['frontend']}{$db1['sep']}gruppo g ON g.gid=u.gid
 		WHERE 1=1
 		$WHERE
 		ORDER BY $ORD $SORT
 		$LIMIT";

 $q=$vmreg->query($sql);

 while($RS=$vmreg->fetch_assoc($q)){


 	switch($RS['livello']){

 		case 3 : $LIVELLO ='<span class="rosso">'._("admin").'</span>';
 		break;

 		case 2 : $LIVELLO ='<span style="color:#F90">'._("intermediate").'</span>';
 		break;

 		default : $LIVELLO ='<span style="color:#060">'._("user").'</span>';

 	}


 // Controllo sul livello di chi sta operando... se � 2 non fa modificare i 3
 	$link_modifica = ($RS['livello']<=User_Session::level()) 
 		? "<a href=\"".Common::phpself()."?mod={$RS['id_utente']}\">"._("modify")."</a>" 
 		: "&nbsp;";

 // Controllo sul livello di chi sta operando... se � 2 non fa modificare i 3
 	$link_delete = ($RS['livello']<=User_Session::level()) 
 		? "<a href=\"".Common::phpself()."?del={$RS['id_utente']}\">"._("delete")."</a>" 
 		: "&nbsp;";

 $TAB.= " 	
 	<tr>
		<td>{$RS['id_utente']}</td>
		<td>{$RS['nome_gruppo']}</td>
		<td>$LIVELLO</td>
		<td>{$RS['nick']}</td>
		<td>{$RS['cognome']}</td>
		<td>{$RS['nome']}</td>\n".
//		"<td>{$RS['email']}</td>".
		"<td>".VFDate::date_encode($RS['data_ins'])."</td>
		<td>$link_modifica</td>
		<td>$link_delete</td>
	</tr>";
 }


 $TAB.= "</table>\n";


 	echo magic_excel($TAB,'utenti-'.date('Y-m-d').'.xls', sprintf(_('Frontend\'s user at %s'),date('d/m/Y')) , FRONT_DOCROOT."/mexcel.php");

 	echo "<br />\n";

	echo $TAB;

echo closeLayout1();
