<?php
require_once("../inc/conn.php");

proteggi();

$GeoJSON = Scheda_View_Geom::rpc_type_geom($_POST['pk'], $_POST['pkvalue'], $_POST['t'], $_POST['f']);

print_r($GeoJSON);