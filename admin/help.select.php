<?php
/**
 * File promemoria per l'amministratore 
 * che mostra come compilare i campi di tipo select
 * 
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: help.select.php 819 2010-11-21 17:07:24Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

proteggi(3);



echo openLayout1(_("Options for field selection"),array('sty/lista.css'),'popup');

echo "<h1 style=\"margin-top:50px;\">"._("Options for drop down menu fields")."</h1>";

echo "<p>"._("'defined values' lists can be set in two ways:")."</p>

<ol>
	<li><strong>"._("List values")."</strong><br /><br />
		"._("Insert one desired value per line. For example")."<br />
		<code>
		"._("Consultant")."<br />
		"._("Employee for an indefinite period")."<br />
		"._("Employee for a fixed period")."<br />
		</code>
	</li>
	
	<br /><br />
	
	<li><strong>"._("List of values and labels")."</strong><br />
		"._("Insert, one per line, the key and value pairs separated by an <em>=</em>.")." <br />
		"._("In this case the values, not the labels, will be inserted in databases. For example:")."<br /><br />
		
		<code>
		c="._("Consultant")."<br />
		di="._("Employee for an indefinite period")."<br />
		dd="._("Employee for a fixed period")."<br />
		</code>
		<br />
		
		"._("Or:")."<br /><br />
		
		<code>
		1="._("Consultant")."<br />
		2="._("Employee for an indefinite period")."<br />
		3="._("Employee for a fixed period")."<br />
		</code>
	
	</li>
</ol>

<br /><br />

"._("The result will be the first case:")."<br />
<select name=\"test1\">
	<option value=\""._("Consultant")."\">"._("Consultant")."</option>
	<option value=\""._("Employee for an indefinite period")."\">"._("Employee for an indefinite period")."</option>
	<option value=\""._("Employee for a fixed period")."\">"._("Employee for a fixed period")."</option>
</select>

<br />
<br />
"._("In the second case (see the HTML code to appreciate the difference):")."<br />
<select name=\"test2\">
	<option value=\"c\">"._("Consultant")."</option>
	<option value=\"di\">"._("Employee for an indefinite period")."</option>
	<option value=\"dd\">"._("Employee for a fixed period")."</option>
</select>

<br />
"._("or")."
<br />
<select name=\"test3\">
	<option value=\"1\">"._("Consultant")."</option>
	<option value=\"2\">"._("Employee for an indefinite period")."</option>
	<option value=\"3\">"._("Employee for a fixed period")."</option>
</select>


";


echo closeLayout1();


?>