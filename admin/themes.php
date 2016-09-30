<?php
/**
 * @desc Themes selector
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: themes.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

proteggi(2);
 
 // prendi cartelle tema:
 
$dh  = opendir("../themes");
$themes=array();
 
while (false !== ($dirtheme = readdir($dh))) {
    if(!in_array($dirtheme,array(".","..",".svn","_original")) && is_dir("../themes/$dirtheme")){
    	
    	$themes[]=$dirtheme;
    }
}


if(isset($_POST['theme']) && in_array($_POST['theme'],$themes)){
	
	$sql="UPDATE ".$db1['frontend'].$db1['sep']."variabili SET valore='{$_POST['theme']}' WHERE variabile='layout'";
	$q=$vmreg->query($sql);


	$_SESSION['VF_VARS']=var_frontend('session','session');

	header("Location: ".$_SERVER['PHP_SELF']);
	exit;
}






echo openLayout1(_("Select themes"), array("sty/admin.css"));

echo breadcrumbs(array("HOME","ADMIN",strtolower(_("Select themes"))));

echo "<h1>"._("Select themes")."</h1>\n";
 


$c=0;

$T="<table summary=\"theme table\" id=\"theme-table\" border=\"1\" >";

foreach ($themes as $theme){
	
	$col=(($c%2)==1) ? " class=\"gr\"":"";
	$sel=($theme==$_SESSION['VF_VARS']['layout']) ? "checked=\"checked\"" : "";
	
	$T.= "
	<tr{$col}>
		<td class=\"theme-thumb\"><img src=\"../themes/$theme/thumb.png\" alt=\"thumb\" /></td>
		<td class=\"theme-name\">".$theme."</td>
		<td class=\"theme-sel\"><input type=\"radio\" name=\"theme\" value=\"$theme\" $sel /></td>
	</tr>\n";
	
	$c++;
	
}

$T.="</table>\n";

echo "<form id=\"theme-form\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" >\n";
echo $T;
echo "<p><input type=\"submit\" value=\""._('Set theme')."\" /></p>\n";
echo "</form>\n";

echo closeLayout1();

?>