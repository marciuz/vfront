<?php
/**
 * Update Check
 * 
 * 
 * @desc Find VFront availables updates
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

require_once("../inc/conn.php");

	
$URL="http://www.vfront.org/version.rss";

$RSS=@join("",@file($URL));

if($RSS!==null){
	
	$xml = new SimpleXMLElement($RSS);

	foreach(@$xml->channel->item as $val){

		if(preg_match("|\.zip|",$val->title)){

			$last_data=(string) $val->pubDate;

			preg_match("|/([a-z0-9_.-]+)\.zip|u",$val->title,$version_name);

			break;
		}
	}

	// Condition .svn?

	$totime=strtotime($last_data);

	$data_last = date("Ymd",$totime);

	// Compare to...

	$XML_VERSION=join('',file(FRONT_ROOT."/vf_version.xml"));

	$xml2 = new SimpleXMLElement($XML_VERSION);

	preg_match("|([0-9]{4}-[0-9]{2}-[0-9]{2})|",$xml2->subversion_date[0],$ff);

	$data_version=str_replace("-",'',$ff[0]);

	if(strlen($data_last)==8 && strlen($data_version)==8 && (intval($data_last) > intval($data_version))){

		$find=array('last_data'=>date("Y-m-d",$totime), 'name'=>$version_name[1]);
	}
	else{
		$find=array();
	}


	if(count($find)==0){

		echo "";
	}
	else{

		echo sprintf(_("Warning: your VFront version is out to date. The %s version is online (%s)."),
					"<strong>".$find['name']."</strong>",
					"<strong>".VFDate::date_encode($find['last_data'])."</strong>");
	}


}











?>