<?php
/**
* Paypal donate system v.1
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona
* @version 0.96 $Id: func.ppal.php 819 2010-11-21 17:07:24Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

function ppal(){
	
	$iii=(time()%2);
	
	$ppal=array();
	
	// Coffees donate
	$ppal[0]="
	<form name=\"_xclick\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
		<input type=\"hidden\" name=\"business\" value=\"marcelloverona@gmail.com\">
		<input type=\"hidden\" name=\"item_name\" value=\"VFront coffee donation\">
		<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
		
		<select name=\"amount\" >
			<option value=\"2.00\">1 "._('coffee')."</option>
			<option value=\"4.00\" selected=\"selected\">2 "._('coffees')."</option>
			<option value=\"10.00\">5 "._('coffees')."</option>
			<option value=\"20.00\">10 "._('coffees')."</option>
			<option value=\"40.00\">20 "._('coffees')."</option>
		</select>
		
		<input type=\"image\" src=\"http://www.vfront.org/img/paypal_bt1.png\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
	</form>

	";
	
	// Money donate
	$ppal[1]="
	<form name=\"_xclick\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
		<input type=\"hidden\" name=\"business\" value=\"marcelloverona@gmail.com\">
		<input type=\"hidden\" name=\"item_name\" value=\"VFront donation.\">
		<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
		<input type=\"hidden\" name=\"amount\" value=\"2.00\">
		<input type=\"image\" src=\"http://www.vfront.org/img/paypal_bt_2.png\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
	</form>
	
	<form name=\"_xclick\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
		<input type=\"hidden\" name=\"business\" value=\"marcelloverona@gmail.com\">
		<input type=\"hidden\" name=\"item_name\" value=\"VFront donation.\">
		<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
		<input type=\"hidden\" name=\"amount\" value=\"25.00\">
		<input type=\"image\" src=\"http://www.vfront.org/img/paypal_bt_25.png\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
	</form>
	
	<form name=\"_xclick\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
		<input type=\"hidden\" name=\"business\" value=\"marcelloverona@gmail.com\">
		<input type=\"hidden\" name=\"item_name\" value=\"VFront donation.\">
		<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
		<div class=\"valuta\"><input type=\"text\" name=\"amount\" value=\"\" id=\"custom_amount\" size=\"6\"> &euro; </div>
		<input type=\"image\" src=\"http://www.vfront.org/img/paypal_bt1.png\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
	</form>
	";
	
	
	$ppal[2]="
	<form name=\"_xclick\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
		<input type=\"hidden\" name=\"business\" value=\"marcelloverona@gmail.com\">
		<input type=\"hidden\" name=\"item_name\" value=\"VFront donation\">
		<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
		
		<select name=\"amount\" >
			<option value=\"2.00\">2 &euro;</option>
			<option value=\"5.00\" selected=\"selected\">5 &euro;</option>
			<option value=\"10.00\">10 &euro;</option>
			<option value=\"25.00\">25 &euro;</option>
			<option value=\"50.00\">50 &euro;</option>
		</select>
		
		<input type=\"image\" src=\"http://www.vfront.org/img/paypal_bt1.png\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
	</form>

	";
	
	return $ppal[$iii];
		
		
}

?>