<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../inc/conn.php");
require_once("../inc/layouts.php");

proteggi(3);



if(file_exists(FRONT_ERROR_LOG)){
	$ERRFILE=file_get_contents(FRONT_ERROR_LOG);
}
else{
	$ERRFILE='';
}


if(isset($_GET['src'])){

	header("Content-type: text/plain");
	print $ERRFILE;
	exit;
}

$ERR=array();

foreach(explode("\n",$ERRFILE) as $linea){

	$ERR[]=explode("\t",$linea);
}


$OUT= "<p>Content of "."<code>".FRONT_ERROR_LOG."</code>"." file. "."<a href=\"?src\">"._("See the source")."</a></p>";

$OUT.= "<table summary=\"error-log\" id=\"error-log\" class=\"tab-stat\">\n";

$OUT.="<tr>
		<th>#</th>
		<th>"._('Date')."</th>
		<th>"._('File')."</th>
		<th>"._('Host')."</th>
		<th>"._('SQL type')."</th>
		<th>"._('DB Library')."</th>
		<th>"._('Error code')."</th>
		<th>"._('Error msg')."</th>
		<th>"._('SQL')."</th>
	</tr>
	";

for($i=0;$i<count($ERR);$i++){

	if(count($ERR[$i])<7) continue;

	$cl=($i%2==0) ? "c1":"c2";

	$OUT.="\t<tr class=\"$cl\">\n";

	$OUT.="\t<td class=\"grigio\">".($i+1)."</td>\n";

	for($j=0;$j<count($ERR[$i]);$j++){

		

		if($j==0 || $j==3){

			$str=substr($ERR[$i][$j],1,-1);
		}
		else $str=$ERR[$i][$j];

		$OUT.="\t<td>".$str."</td>\n";
	}

	$OUT.="</tr>\n";
}

$OUT.="</table>\n";

$title=_("DB Error log");

$files=array("sty/stat.css");

echo openLayout1($title, $files);

echo breadcrumbs(array("HOME","ADMIN",$title=>''));

echo "<h1>".$title."</h1>\n";

echo $OUT;

echo closeLayout1();

?>