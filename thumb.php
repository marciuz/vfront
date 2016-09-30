<?php
/**
* Genera i thumbnail (diapositive) per le immagini allegate.
* Viene richiamato dal file {@link add.attach.php}
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: thumb.php 819 2010-11-21 17:07:24Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require("./inc/conn.php");

proteggi(1);

function printThumbnail($imgfile,$max_width=100,$max_height=100) {
       list($org_width,$org_height,$orgtype) = getimagesize($imgfile);
       $div_width = $org_width / $max_width;
       $div_height = $org_height / $max_height;
       if($div_width >= $div_height) {
           $new_width = $max_width;
           $new_height = round($org_height / $div_width);
       }
       else {
           $new_height = $max_height;
           $new_width = round($org_width / $div_height);
       }
       switch($orgtype) {
           case 1: $im = imagecreatefromgif($imgfile); break;
           case 2: $im = imagecreatefromjpeg($imgfile); break;
           case 3: $im = imagecreatefrompng($imgfile); break;
       }
       if($im) {
           $tn = imagecreatetruecolor($new_width,$new_height);
           if($tn) {
               imagecopyresized($tn,$im,0,0,0,0,$new_width,$new_height,$org_width,$org_height);
               switch($orgtype) {
                   case 1: header("Content-Type: image/gif"); imagegif($tn); break;
                   case 2: header("Content-Type: image/jpeg"); imagejpeg($tn); break;
                   case 3: header("Content-Type: image/png"); imagepng($tn); break;
               }
               imagedestroy($tn);
           }
       }
   }
   
    
   
   $ID = (int) $_GET['id'];
   
   $IMG=_PATH_ATTACHMENT."/$ID.dat";
   
   if(is_file($IMG)){
   		printThumbnail($IMG,150,150);
   }
   
?>