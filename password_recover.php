<?php
/**
* File per la gestione della password smarrita. 
* Questa pagina viene utilizzata per gestire la password, generandone una nuova casuale.
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: password_recover.php 1076 2014-06-13 13:03:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


require_once("./inc/conn.php");
require_once("./inc/layouts.php");

/**
 * @desc Funzione di generazione di password (pseudo)casuale
 * @return string Nuova password per gli utenti
 */
function genera_password(){
	
	$consonanti=array("b","c","d","f","g","h","j","k","l","m","n","p","q","r","s","t","v","w","y","x","z");
	
	$vocali=array("a","e","i","o","u");
	
	$PWD='';
	
	for($i=0;$i<4;$i++){
		$c=rand(0,(count($consonanti)-1));
		
		$v=rand(0,4);
		
		$PWD.=$consonanti[$c];
		$PWD.=$vocali[$v];
		
		if(strlen($PWD)==4) $PWD.="-";
	}
	
	return $PWD;
}



if(isset($_POST['email'])){
	
	$_dati=$vmreg->recursive_escape($_POST);
	
	// verifica che la email esista
	$q=$vmreg->query("SELECT * from {$db1['frontend']}{$db1['sep']}utente WHERE email='".$_dati['email']."'");
	
	
	// se c'� l'email ed il codice di sicurezza � giusto...
	if($vmreg->num_rows($q)==1 && ($_SESSION['image_random_value']==md5($_dati['secure_txt']))){
		
		
		unset($_SESSION['image_random_value']);
		
		// vai avanti
		
		$RS=$vmreg->fetch_assoc($q);
		
		$new_pass= genera_password();
		
		$q_up=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}utente SET recover_passwd='".md5($new_pass)."' WHERE id_utente=".intval($RS['id_utente']));
		
		if($vmreg->affected_rows($q_up)==1){
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ok");
			
			
			// -------------------------------------------------------------
			// manda l'email -----------------------------------------------
			
			include_once("./plugins/phpmailer/class.phpmailer.php");
			
			$mail = new PHPMailer();

			$mail->Subject="[VFront] "._("Change password");
			$mail->Body=sprintf(_("You or someone for you in date %s has requested the modification of your password. Your new password is"), VFDate::date_encode(date("Y-m-d H:i"),true))." :\n$new_pass\n";
			$mail->From=$conf_mail['MAIL_SENDER'];
			$mail->FromName= ($conf_mail['MAIL_SENDER_NAME']=='') ? "VFront" : $conf_mail['MAIL_SENDER_NAME'];
			

			// se è configurato come invio da SMTP esplicitato
			if($conf_mail['SMTP_AUTH']==true){
				
				$mail->Mailer='smtp';
		
				$mail->Host=$conf_mail['SMTP'];
		
				$mail->SMTPAuth=$conf_mail['SMTP_AUTH'];
		
				// Se è impostata l'autenticazione SMTP
				$mail->Username=$conf_mail['SMTP_AUTH_USER'];
				$mail->Password=$conf_mail['SMTP_AUTH_PASSW'];
		
			}
			
			$mail->AddAddress($_dati['email']);
			$mail->Send();
			
			// -------------------------------------------------------------
			// -------------------------------------------------------------
			
			
		}
		else{
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ko");
		}
	}
	
	else{
		
			header("Location: ".$_SERVER['PHP_SELF']."?feed=n");		
	}
	
	exit;
}


echo openLayout1(_("Recover password"));


echo "<h1>"._("Recover forgotten password")."</h1>\n";

if(isset($_GET['feed'])){
	
	if($_GET['feed']=='ok'){
		
		echo "<h2 class=\"verde\">"._("Password changed correctly!")."</h2>\n";
		echo "<p>"._("You can now check your email to login with your new password")."</p>\n";
		echo "<p><a href=\"index.php\">"._("Back to login")."</p>\n";
	}
	elseif($_GET['feed']=='n'){
		
		echo "<h2 class=\"var\">".("Errore nella modifica della password!")."</h2>\n";
		echo "<p><a href=\"index.php\">"._("Back to login")."</p>\n";
	}
	else{
		
		echo "<h2 class=\"var\">".("Errore nella modifica della password!")."</h2>\n";
		echo "<p><a href=\"index.php\">"._("Back to login")."</p>\n";
	}
	
	
}
else{

	echo "<p>"._("Lost your password?<br />Enter your email address and a new password will be send to that address")."</p>\n";
	
	echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" >
	
		<label for=\"email\">"._("Email").":</label><br />
		<input type=\"text\" name=\"email\" id=\"email\" size=\"45\" maxlength=\"200\" value=\"\" />
		
		<br />
		<br />
		<label for=\"secure_txt\">"._("Security code:")."</label><br />
		<img src=\"img_rand.php\" alt=\""._("security code:")."\" /> 
		<input type=\"text\" name=\"secure_txt\" id=\"secure_txt\" size=\"10\" maxlength=\"10\" value=\"\" />
		<br />
		<br />
		<input type=\"submit\" name=\"invia\" id=\"invia\" value=\""._("Send me a new password")."\" />
		</form>\n";

	
}


echo closeLayout1();

?>