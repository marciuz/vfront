<?php
/**
* Adminer wrapper
*
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona - Jakub Vrána (for Adminer code)
* @version 0.96 $Id: index.php 1163 2016-04-24 22:27:39Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../../inc/conn.php");

proteggi(3);

if(!isset($_SESSION['VF_VARS']['enable_adminer'])
	|| $_SESSION['VF_VARS']['enable_adminer']!=1){

   require_once("../../inc/layouts.php");

	$OUT=openLayout1(_("Adminer plugin"));

	$OUT.= breadcrumbs(array("HOME","ADMIN","adminer"));

	$OUT.="<h1>"._("Adminer plugin")."</h1>\n";

	$OUT.="<p>".sprintf(_('Adminer is not enabled. %s Please check your variables %s'),
				"<a href=\"".FRONT_DOCROOT."/admin/variabili.php\">","</a>")."</p>\n";

	$OUT.=closeLayout1();

	print $OUT;
	exit;
}
// vfdb = (data|reg)
else if(isset($_GET['vfseldb'])){


	require_once("../../inc/layouts.php");

	$files=array('sty/admin.css');

	$OUT=openLayout1(_("Adminer plugin"),$files);

	$OUT.= breadcrumbs(array("HOME","ADMIN","adminer"));

	$OUT.="<h1>"._("Adminer plugin")."</h1>\n";

	$OUT.="<p>".sprintf(_('Adminer is a PHP based DB client. For further information see the official site %s'),
				"<a href=\"http://www.adminer.org\">http://www.adminer.org</a>")."</p>";

	$OUT.="<div id=\"box-adminer1\" class=\"box-db\">
		<h2>"._("Adminer on data DB")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"?vfty=data\">"._("Open adminer")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>

		</ul>
	</div>

 	<div id=\"box-adminer2\" class=\"box-db\">
		<h2>"._("Adminer on VFront rules DB")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"?vfty=reg\">"._("Open adminer")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>

		</ul>
	</div>";


	$OUT.=closeLayout1();

	print $OUT;
	exit;
}

if(isset($_GET['vfty']) && $_GET['vfty']=='data'){

        $TYPE_ADMINER=$db1['dbtype'];
}
else{
    // the second definition is for the internal pages
    if((isset($_GET['vfty']) && $_GET['vfty']=='reg' && USE_REG_SQLITE) 
            || (isset($_GET['db']) && $_GET['db']==$db1['filename_reg'])
            || (isset($_GET['db']) && $_GET['db']==$db1['frontend'])
            ){

        $_GET['vfty']='reg';
        if(USE_REG_SQLITE){
            $_GET['db']=$db1['filename_reg'];
            if(VERSION_REG_SQLITE==3) {
                $_GET['sqlite']='';
            }
            else {
                $_GET['sqlite2']='';
            }

            $TYPE_ADMINER='sqlite';
        }
        else{
            $TYPE_ADMINER=$db1['dbtype'];
        }

    }
    else{

		$_GET['db']= (isset($_GET['vfty']) && $_GET['vfty']=='reg') ? $db1['frontend']:$db1['dbname'];
		$_GET['ns']= (isset($_GET['vfty']) && $_GET['vfty']=='reg') ? $db1['frontend']:$db1['dbname'];


        $TYPE_ADMINER=$db1['dbtype'];
    }
}

switch($TYPE_ADMINER){

	case 'postgres':
			$_GET['pgsql']=$db1['host'];
			if($db1['port']!='') $_GET['pgsql'].=":".$db1['port'];
			$_GET['db']=$db1['postgres_dbname'];
			$_GET['ns']= (isset($_GET['ns']) && in_array($_GET['ns'],array($db1['dbname'],$db1['frontend'])))
						 ? $_GET['ns']:$db1['dbname'];
	break;

	case 'sqlite':
			if( (isset($db1['dbsqlite_version']) && $db1['dbsqlite_version']==3) ||
                           (defined('USE_REG_SQLITE') && VERSION_REG_SQLITE==3)) {

                            $_GET['sqlite']='';
                        }
			else {
                            $_GET['sqlite2']='';
                        }

                        $_GET['db']= (USE_REG_SQLITE) ? $db1['filename_reg']:$db1['filename'];
	break;

	case 'oracle':
			$_GET['oracle']=$db1['host'];
			if($db1['port']!='') $_GET['oracle'].=":".$db1['port'];
			$_GET['oracle'].='/'.$db1['service'];
			//if($db1['dbname']=='') $_GET['db']='USERS';
	break;

	case 'mysql':
	default:
			$_GET['mysql']=$db1['host'];
			if($db1['port']!='') $_GET['mysql'].=":".$db1['port'];
			$_GET['db']=(isset($_GET['db']) && in_array($_GET['db'],array($db1['dbname'],$db1['frontend'])))
						? $_GET['db']:$db1['dbname'];
	break;


}

$_GET['username']='';

if(!isset($_GET['lang'])){

	$_GET['lang']=substr($_SESSION['VF_VARS']['lang'],0,2);
}


function adminer_object() {

    class AdminerSoftware extends Adminer {

        function name() {
            // custom name in title and heading
            return 'Adminer VFront';
        }

        function permanentLogin($j = false) {
            // key used for permanent login
            return "5672b42d1665f922d76bcfc820287280";
        }

        function credentials() {
            // server, username and password for connecting to database
            return array($GLOBALS['db1']['host'], $GLOBALS['db1']['user'], $GLOBALS['db1']['passw']);
        }

        function login($login, $password) {
            // validate user submitted credentials
            return true; //($login == 'admin' && $password == '');
        }

    }

    return new AdminerSoftware;
}

include "./src/adminer.php.noparse";
