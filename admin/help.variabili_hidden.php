<?php
/**
 * File promemoria per l'amministratore 
 * che mostra come gestire i campi hidden e le variabili possibili
 * 
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: help.variabili_hidden.php 819 2010-11-21 17:07:24Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

proteggi(3);



echo openLayout1(_("Variables for hidden fields"),array('sty/lista.css'),'popup');

echo "<h1 style=\"margin-top:50px;\">"._("Variables for hidden fields")."</h1>";

echo "<p>"._("You can use variables for hidden fields.")."<br />
"._("To do this simply enter one of these variables which will be included automatically in the form:")."</p>

<dl>
	<dt>%nick</dt>
	<dd>"._("Nickname (email) of the logged-in user")."</dd>
	
	<dt>%email</dt>
	<dd>"._("Email of the logged-in user")."</dd>
	
	<dt>%uid</dt>
	<dd>"._("ID of logged-in user")."</dd>
	
	<dt>%nome</dt>
	<dd>"._("Name of logged-in user")."</dd>
	
	<dt>%cognome</dt>
	<dd>"._("Surname of logged-in user")."</dd>
	
	<dt>%nomecognome</dt>
	<dd>"._("Name and surname of the logged-in user (separated by space)")."</dd>
	
	<dt>%cognomenome</dt>
	<dd>"._("Surname and name of the logged-in user (separated by space)")."</dd>
	
	
	<dt>%gid</dt>
	<dd>"._("Group ID of the logged-in user")."</dd>
	
	<dt>%gruppo</dt>
	<dd>"._("Group name of the logged-in user")."</dd>
	
	<dt>%now</dt>
	<dd>"._("Current date in yyyy-mm-dd format")."</dd>
	
	<dt>%timestamp</dt>
	<dd>"._("Current date and time in yyyy-mm-dd HH:mm:ss")."</dd>
	
	<!--<dt>%istitutocomp</dt>
	<dd>"._("ID of institution of logged-in user")."</dd>-->
</dl>
";


echo closeLayout1();


?>