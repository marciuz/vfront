<?php
/**
 * Show log activities in time
 *
 * @desc Log stats visualization
 * @package VFront
 * @subpackage Stats
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2011 M.Marcello Verona
 * @version 0.96 $Id: stat.log.php 949 2011-04-23 23:22:10Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
 */
require_once("../inc/conn.php");
require_once("../inc/layouts.php");

proteggi(2);

$get_ym = (isset($_GET['ym'])) ? $_GET['ym']:'';

$A = new LogAnalysis($get_ym);
$cont=$A->act_create_matrix_month(null);
$png=$A->graph_act();

$page_title = _('Log stats');
$files=array("sty/stat.css");

$OUT= openLayout1($page_title,$files);

$OUT.=  breadcrumbs(array("HOME","ADMIN",
					   "index.php"=>_("statistics"),
						strtolower($page_title)));

$OUT.="<h1>".$page_title."</h1>\n";


/**
 * TODO: filter on date
 *

$OUT.="<form action=\"".$_SERVER['PHP_SELF']."\" method=\"get\">

	<div><label for=\"ym\">Start month:</label>
		<select id=\"ym\" name=\"ym\">\n";

	foreach($A->possible_months as $ym){
		$sel=($ym==$get_ym) ? "selected=\"selected\"" : '';

		list($y,$m)=explode("-",$ym);

		$OUT.="<option $sel value=\"".$ym."\">".date("M Y", mktime(0,0,0,$m,1,$y))."</option>\n";
	}

$OUT.="
		</select>
	</div>

	<div><input type=\"submit\" value=\" "._('Refresh')." \" /></div>
</form>\n";
*/


$OUT.= "<p><img src=\"$png?".time()."\" alt=\"activities\" /></p>\n";

$OUT.=  $A->table_cont($cont);

$OUT.=closeLayout1();


print $OUT;

?>