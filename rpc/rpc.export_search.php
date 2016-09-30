<?php
/**
* Crea una sessione con i risultati della ricerca con record>1
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona
* @version 0.96 $Id: rpc.export_search.php 1088 2014-06-16 20:41:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../inc/conn.php");
require_once("../inc/layouts.php");

proteggi();

$_SESSION['qresults']=array('table'=>$_POST['table'],'ids'=>$_POST['qresults']);
