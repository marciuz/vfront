<?php
/**
* Libreria di funzioni per la validazione delle email.
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2009 M.Marcello Verona
* @version 0.95 $Id: func.validmail.php 662 2010-06-19 00:40:20Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/



function SnowCheckMail($Email,$Debug=false) 
{ 
    global $HTTP_HOST; 
    $Return =array();  
    // Variable for return. 
    // $Return[0] : [true|false] 
    // $Return[1] : Processing result save. 

    if (!preg_match("'^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$'i", $Email)) { 
        $Return[0]=false; 
        $Return[1]="${Email} "._("is E-Mail form that is not right."); 
        if ($Debug) echo "Error : {$Email} "._("is E-Mail form that is not right.")."<br />";         
        return $Return; 
    } 
    else if ($Debug) echo "Confirmation : {$Email} "._("is E-Mail form that is not right.")."<br />"; 

    // E-Mail @ by 2 by standard divide. if it is $Email this "lsm@ebeecomm.com".. 
    // $Username : lsm 
    // $Domain : ebeecomm.com 
    // list function reference : http://www.php.net/manual/en/function.list.php 
    // split function reference : http://www.php.net/manual/en/function.split.php 
    list ( $Username, $Domain ) = split ("@",$Email); 

    // That MX(mail exchanger) record exists in domain check . 
    // checkdnsrr function reference : http://www.php.net/manual/en/function.checkdnsrr.php 
    if ( checkdnsrr ( $Domain, "MX" ) )  { 
        if($Debug) echo "Confirmation : MX record about {$Domain} exists.<br>"; 
        // If MX record exists, save MX record address. 
        // getmxrr function reference : http://www.php.net/manual/en/function.getmxrr.php 
        if ( getmxrr ($Domain, $MXHost))  { 
      if($Debug) { 
                echo "Confirmation : Is confirming address by MX LOOKUP.<br>"; 
              for ( $i = 0,$j = 1; $i < count ( $MXHost ); $i++,$j++ ) { 
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Result($j) - $MXHost[$i]<BR>";  
        } 
            } 
        } 
        // Getmxrr function does to store MX record address about $Domain in arrangement form to $MXHost. 
        // $ConnectAddress socket connection address. 
        $ConnectAddress = $MXHost[0]; 
    } 
    else { 
        // If there is no MX record simply @ to next time address socket connection do . 
        $ConnectAddress = $Domain;         
        if ($Debug) echo "Confirmation : MX record about {$Domain} does not exist.<br>"; 
    } 

    // fsockopen function reference : http://www.php.net/manual/en/function.fsockopen.php 
    $Connect = fsockopen ( $ConnectAddress, 25 ); 

    // Success in socket connection 
    if ($Connect)   
    { 
        if ($Debug) echo "Connection succeeded to {$ConnectAddress} SMTP.<br>"; 
        // Judgment is that service is preparing though begin by 220 getting string after connection . 
        // fgets function reference : http://www.php.net/manual/en/function.fgets.php 
        if ( ereg ( "^220", $Out = fgets ( $Connect, 1024 ) ) ) { 
             
            // Inform client's reaching to server who connect. 
            fputs ( $Connect, "HELO $HTTP_HOST\r\n" ); 
                if ($Debug) echo "Run : HELO $HTTP_HOST<br>"; 
            $Out = fgets ( $Connect, 1024 ); // Receive server's answering cord. 

            // Inform sender's address to server. 
            fputs ( $Connect, "MAIL FROM: <{$Email}>\r\n" ); 
                if ($Debug) echo "Run : MAIL FROM: &lt;{$Email}&gt;<br>"; 
            $From = fgets ( $Connect, 1024 ); // Receive server's answering cord. 

            // Inform listener's address to server. 
            fputs ( $Connect, "RCPT TO: <{$Email}>\r\n" ); 
                if ($Debug) echo "Run : RCPT TO: &lt;{$Email}&gt;<br>"; 
            $To = fgets ( $Connect, 1024 ); // Receive server's answering cord. 

            // Finish connection. 
            fputs ( $Connect, "QUIT\r\n"); 
                if ($Debug) echo "Run : QUIT<br>"; 

            fclose($Connect); 

                // Server's answering cord about MAIL and TO command checks. 
                // Server about listener's address reacts to 550 codes if there does not exist  
                // checking that mailbox is in own E-Mail account. 
                if ( !ereg ( "^250", $From ) || !ereg ( "^250", $To )) { 
                    $Return[0]=false; 
                    $Return[1]="${Email} is address done not admit in E-Mail server."; 
                    if ($Debug) echo "{$Email} is address done not admit in E-Mail server.<br>"; 
                    return $Return; 
                } 
        } 
    } 
    // Failure in socket connection 
    else { 
        $Return[0]=false; 
        $Return[1]=_("Can not connect E-Mail server")." ({$ConnectAddress})."; 
        if ($Debug) echo _("Can not connect E-Mail server")." ({$ConnectAddress}).<br />"; 
        return $Return; 
    } 
    $Return[0]=true; 
    $Return[1]="{$Email} "._("is E-Mail address that there is no any problem."); 
    return $Return; 
}


/**
 * Controllo di validità formale e|o sostanziale di una email
 * Mediante il $controllo_destinatario=true è possibile capire se la mail esiste
 * In caso contrario la validazione è solo formale
 *
 * @param string $email
 * @param bool $controllo_destinatario
 * @return bool
 */
function valid_mail($email,$controllo_destinatario=false){

	if($controllo_destinatario){
		
		$check=SnowCheckMail($email);
		return $check[0];
	}
	else{
		return (preg_match('"^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$"', $email)) ? true:false;
	}

}


/**
 * Funzione che estrae le email da un testo
 *
 * @param string $testo
 * @return array
 */
function estrai_mail_valide($testo){
	
	
	preg_match_all('"[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})"iu',$testo,$mails);
	
	return (array) $mails[0];
}

?>