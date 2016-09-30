<?php

/*
 * This is a firt example of user script,
 * used eg. from a special button
 * 
 */

// Add the conf file and first bootstrap
require_once("../inc/conn.php");

// remove the comment if the script is not public
// proteggi(); // authenticated users
// proteggi(2); // level 2 users
// proteggi(3); // level 3 users (administrator only)

if(isset($_GET['yourid'])){
    
    $sql="SELECT * FROM youtable WHERE yourid='".$vmsql->escape($_GET['yourid'])."'";
    
    //exec the query
    $q=$vmsql->query($sql);
    
    // if have results
    if($vmsql->num_rows($q) > 0){
	
	// get the recordset
	$RS=$vmsql->fetch_assoc($q);
	
	// do what you want with results
	var_dump($RS);
	
    }
    else{
	
	
	// no results case
	
    }
    
}

