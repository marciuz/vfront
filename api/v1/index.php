<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once("../../inc/conn.php");

$api = new API();

// Load the information schema
$out=$api->dispacher();

            