<?php
/**
* Script generatore di tabella Excel dinamica.
* La generazione della tabella excel avviene mediante uso di una semplice tabella HTML 
* e l'header XLS. Questo file � richiamato dal tasto "scarica la tabella in excel" creato
* a sua volta dalla funzione magic_excel.
* @see function magic_excel 
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: mexcel.php 1106 2014-09-27 17:40:51Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require("./inc/conn.php");

proteggi(1);

if(isset($_POST['mexcel'])){
	
	
	$_data=unserialize(base64_decode($_POST['mexcel']));
	
   $filename= (isset($_data['fn']) && $_data['fn']!='') ? $_data['fn'] : "sheet.xls";
   $titolo= (isset($_data['tit']) && $_data['tit']!='') ? $_data['tit'] : _("Table");
   header ("Content-Type: application/vnd.ms-excel");
   header ("Content-Disposition: inline; filename=$filename");
$OUT=<<<XLS
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang=it><head>
<title>$titolo</title></head>
<style type="text/css">
body{ font-family:Arial, Sans; font-size:0.8em;}
</style>
<body>
XLS;



$OUT.=$_data['tab'];

$OUT.=<<<XLS
</body></html>
XLS;

print $OUT;
}

?>