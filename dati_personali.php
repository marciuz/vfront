<?php
/**
* Dati personali dell'utente.  
* Viene mostrata una tabella con i dati personali dell'utente che ha eseguito il login.
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: dati_personali.php 1076 2014-06-13 13:03:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("./inc/conn.php");
require_once("./inc/layouts.php");

proteggi(1);


echo openLayout1(_("Personal info"),array("sty/tabelle.css"));

echo breadcrumbs(array("javascript:history.back();"=>_('Back'),""));

echo "<h1>"._("User profile")."</h1>\n";


echo "<table summary=\"dati utente\" class=\"tab-color\">

	<tr>
		<th>ID</th>
		<td>".User_Session::id()."</td>
	</tr>
	<tr>
		<th>"._("Name")."</th>
		<td>".User_Session::firstname()."</td>
	</tr>
	<tr>
		<th>"._("Surname")."</th>
		<td>".User_Session::lastname()."</td>
	</tr>
	<tr>
		<th>"._("email")."</th>
		<td>".User_Session::email()."</td>
	</tr>
	<tr>
		<th>"._("last modified date")."</th>
		<td>".VFDate::date_encode(User_Session::attr(User_Session::FIELD_INSERT_DATE),false)."</td>
	</tr>

	<tr>
		<th>"._("group")."</th>
		<td>".User_Session::gid()." - ". Common::gid2group_name(User_Session::gid())."</td>
	</tr>

	<tr>
		<th>"._("level")."</th>
		<td>".User_Session::level()."</td>
	</tr>		

</table>
";


echo closeLayout1();
