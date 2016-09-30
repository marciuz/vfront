<?php
// PUBLIC SETTINGS
$tmpdir = 'C:/WINDOWS/TEMP';			// your PHP temporary upload folder ( without last slash )
						// for this method is better set this folder in a dedicated one
						// to be sure that in that folder there isn't any other php temporary file

// APPLICATION - PLEASE DON'T MODIFY
require('./inc/UploadProgressManager.class.php');			// The class UploadProgressManager class
session_start();						// need a session to be really efficient
clearstatcache();						// and maybe a cleared stats
$UPM = new UploadProgressManager($tmpdir);			// new UploadProgressManager with temporary upload folder
if(($output = $UPM->getTemporaryFileSize()) === false)		// if UPM class cannot find the temporary file
	$output = '&filesize=undefined';			// the output for LoadVars will be undefined
else
	$output = '&filesize='.$output;				// else the output will be temporary file size
header('Content-Length: '.strlen($output));			// now headers to resolve browser cache problems
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
echo $output;							// and finally the output for LoadVars
?>