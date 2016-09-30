<?php
/**
* Script che mostra una stringa alfanumerica casuale sotto forma di immagine. 
* Lo script viene utilizzato nel caso di modifica della password 
* ed � richiamato dalla pagina {@link password_recover.php}
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: img_rand.php 819 2010-11-21 17:07:24Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require("./inc/conn.php");

$rand='';

for($i=49;$i<122;$i++){
	$a[$i]=chr($i);
}

unset($a[96],$a[95],$a[94],$a[93],$a[92],$a[91]);

$x=array_rand($a,6);

shuffle($x);

for($i=0;$i<count($x);$i++){
	$rand.=$a[$x[$i]];
}


// create the hash for the random number and put it in the session
$_SESSION['image_random_value'] = md5($rand);

// create the image
$image = imagecreate(70, 20);

// use white as the background image
$bgColor = imagecolorallocate ($image, 255, 255, 255);

// the text color is black
$textColor = imagecolorallocate ($image, 0, 0, 0);

// write the random number
imagestring($image, 6, 5, 5, $rand, $textColor);

// send several headers to make sure the image is not cached
// taken directly from the PHP Manual

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");


// send the content type header so the image is displayed properly
header('Content-type: image/jpeg');

// send the image to the browser
imagejpeg($image);

// destroy the image to free up the memory
imagedestroy($image);
?>