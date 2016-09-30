<?php
	$h=preg_replace("|[^a-z0-9\.]+|i","",$_GET['h']);

	$fp=fopen("./files/tmp/$h.stop","w");
	fwrite($fp,'');
	fclose($fp);
	
	echo 1;
	
?>