<?php
/**
* Register the search session
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.97 $Id$
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


require("../inc/conn.php");

proteggi(1);

if(isset($_POST['q'])){
    
    $ids= $_POST['q'];
    $hash=md5($_POST['q']);
    
    if(isset($_SESSION['search'])) unset($_SESSION['search']);
    $_SESSION['search'][$hash]=$ids;
    
    
}
else{
    $ids=array();
    $hash=false;
}

echo $hash;

