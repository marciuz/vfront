<?php
/**
* Get information from DB schema for VFront compatibility
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona
* @version 0.96 $Id: check_db.php 1116 2014-12-16 00:42:49Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../inc/conn.php");
require_once("../inc/func.checkdb.php");


function color_res($res){
	
	if($res==0){
		
		return "<strong class=\"verde\">0</strong>\n";
	}
	else{
		
		return "<strong class=\"var\">$res</strong>\n";
	}
}

if(isset($_GET['json'])){
	
	$check=check_db('json');
	
	exit;
}

// else ...

require_once("../inc/layouts.php");

$check=check_db();


$check_html=array();

foreach($check as  $ty=>$arr){
	
	if($check['n']>0){

		if(is_array($arr) && count($arr)>0){

			$check_html[$ty]="<td><em>".implode("</em><br /><em>",$arr)."</em></td>";
		}
		else{
			$check_html[$ty]="<td>&nbsp;</td>\n";
		}
	}
	else{
		$check_html[$ty]="<td>&nbsp;</td>\n";
	}

}

proteggi(3);

$files = array("sty/admin.css","sty/tabelle.css","js/confirm_delete.js");

$title=_("VFront compatibility test");

$OUT=openLayout1($title,$files);

$OUT.=breadcrumbs(array("HOME","ADMIN",$title));

$OUT.="<h1>".$title."</h1>\n";

$OUT.="<p>"._('VFront to work properly, needs primary keys in tables and regular tables names and field names.')."<br />";

$OUT.=_('This page has shown abnormalities in the database that could affect the proper functioning of VFront with the database.')."</p>";

// General info

$OUT.="<h3>"._('General check')."</h3>\n";

$OUT.="<table summary=\"check\" border=\"1\" id=\"theme-table\">

	<tr>
		<td>"._('Errors in table names')."</td>
		<td>".color_res(count($check['tables']))."</td>".$check_html['tables']."
	</tr>
	
	<tr>
		<td>"._('Errors in table\'s columns names')."</td>
		<td>".color_res(count($check['cols']))."</td>".$check_html['cols']."
	</tr>
	
	<tr>
		<td>"._('Errors in view names')."</td>
		<td>".color_res(count($check['views']))."</td>".$check_html['views']."
	</tr>
	
	<tr>
		<td>"._('Errors in view\'s columns names')."</td>
		<td>".color_res(count($check['colsv']))."</td>".$check_html['colsv']."
	</tr>
	
	<tr>
		<td>"._('Missing primary keys')."</td>
		<td>".color_res(count($check['pk']))."</td>".$check_html['pk']."
	</tr>
	
	
	<tr>
		<td><strong>"._('Total errors')."</strong></td>
		<td colspan=\"2\">".color_res($check['n'])."</td>
	</tr>
	
</table>\n";	


$OUT.=closeLayout1();


print $OUT;
