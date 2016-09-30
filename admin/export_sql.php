<?php
/**
 * @desc Utility per l'esportazione dell'SQL (DUMP)
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: export_sql.php 1079 2014-06-14 09:19:03Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

 proteggi(3);
 


	########################################################################
	#
	#
	#	ESPORTAZIONE REGISTRI
	
	$IS_UNIX = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 0:1;
	
	if(isset($_POST['exp_dati'])){

			
		if($IS_UNIX){
			$zippa="| gzip -c ";
			$estensione_zip=".gz";
		}
		else {
			$zippa ="";
			$estensione_zip="";
		}
		
		
		// tipo di dati richiesti
		
		if($_GET['exp']=='registri'){

			if(USE_REG_SQLITE){

				$sqlite_name=basename($db1['filename_reg']);
				$sqlite_gzip="/tmp/$sqlite_name.gz";
				$sh="gzip -c ".$db1['filename_reg']." > $sqlite_gzip";
				$exec = passthru($sh);

				if(is_file($sqlite_gzip)){
				   header('Pragma: anytextexeptno-cache', true);
				   header('Content-type: application/x-gzip');
				   header('Content-Transfer-Encoding: Binary');
				   header('Content-length: '.filesize($sqlite_gzip));
				   header('Content-disposition: attachment; filename='.basename($sqlite_gzip));
				   readfile($sqlite_gzip);

				   unlink($sqlite_gzip);
				}

				exit;
			}
			else{

				$DB = $db1['frontend'];
				$opzioni_pg=' --schema=frontend ';

			}
		}
		else{
			$DB = $db1['dbname'];
			$opzioni_pg=' --schema=public ';
		}
			
		// opzioni dati/schema
		
		if($_POST['schema_only']=='1'){
			$opzioni = "--no-data ";
			$opzioni_pg = '-s ';
		}

		else{
			$opzioni = "";
		}
		
			
		
			if(VFRONT_DBTYPE=='mysql'){
				
				$nome_file = _PATH_TMP."/$DB.sql{$estensione_zip}";	
			
				$bat= 	_PATH_MYSQLDUMP.
						" --add-drop-table --force ".
						"-u{$db1['user']} ".
					   	"-p{$db1['passw']} ".
					   	"-P {$db1['port']} ".
					   	"-h {$db1['host']} ".
					   	"$opzioni ".
					   	"$DB ".
					   	"$zippa ".
					   	">$nome_file";
			}
			elseif (VFRONT_DBTYPE=='postgres'){
				
				$nome_file = _PATH_TMP."/{$db1['postgres_dbname']}.$DB.sql{$estensione_zip}";	
				
				$bat= 	"export PGPASSWORD=\"{$db1['passw']}\" ".
						"&& "._PATH_PG_DUMP." ".
						"-U{$db1['user']} ".
						"-p {$db1['port']} ".
						"-h {$db1['host']} ".
						"$opzioni_pg ".
						"{$db1['postgres_dbname']} ".
						" $zippa > $nome_file";
				
			}
				   	
			$exec = passthru($bat);
				   	
			

			if(is_file($nome_file)){
			   header('Pragma: anytextexeptno-cache', true);
			   header('Content-type: application/x-gzip');
			   header('Content-Transfer-Encoding: Binary');
			   header('Content-length: '.filesize($nome_file));
			   header('Content-disposition: attachment; filename='.basename($nome_file));
			   readfile($nome_file);
			   
			   unlink($nome_file);
			}
			else{
				die(_('Error: can\'t create dump file'));
			}
			
		
			
		exit;
	}
	
	
	#
	#
	########################################################################
	

 
 echo openLayout1(_("Database export"), array("sty/admin.css"));

 $DBREG_NAME=(USE_REG_SQLITE) ? basename($db1['filename_reg']) : $db1['frontend'];

 $OPTION_REG=(USE_REG_SQLITE) ? ''
 : "<input type=\"radio\" name=\"schema_only\" id=\"schema_reg_only_1\" 
		value=\"1\" checked=\"checked\"/>
	<label for=\"schema_reg_only_1\">"._("Schema only")."</label><br />
	<input type=\"radio\" name=\"schema_only\" 
		id=\"schema_reg_only_2\" value=\"2\" />
	<label for=\"schema_reg_only_2\">"._("Schema and Data")."</label><br />";


 echo breadcrumbs(array("HOME","ADMIN",_("database administration")));

 echo "<h1>"._("Database export")."</h1>

 	<img src=\"../img/db_export.gif\" class=\"img-float\" alt=\""._("registry settings")."\" />
 
 	<div class=\"box-db\">
		<h2>"._("Database data export")." (<span class=\"var\">{$db1['dbname']}</span>)</h2>
		<form action=\"".$_SERVER['PHP_SELF']."?exp=dati\" method=\"post\">
		
			<br />
		
			<input type=\"radio\" name=\"schema_only\" id=\"schemaonly_1\" value=\"1\" checked=\"checked\"/><label for=\"schemaonly_1\">"._("Schema only")."</label><br />
			<input type=\"radio\" name=\"schema_only\" id=\"schemaonly_2\" value=\"2\" /><label for=\"schemaonly_2\">"._("Schema and Data")."</label><br />
		
			<br />
			
			<input type=\"submit\" name=\"exp_dati\" value=\""._("Export")."\" />
			
		</form>
		
	</div>	
	
 
 	<div class=\"box-db\">
		<h2>"._("Registries database export")." (<span class=\"var\">$DBREG_NAME</span>)</h2>
			
		<form action=\"".$_SERVER['PHP_SELF']."?exp=registri\" method=\"post\">
		
			<br />
		
			$OPTION_REG
		
			<br />
			
			<input type=\"submit\" name=\"exp_dati\" value=\""._("Export")."\" />
			
		</form>
	</div>	\n";
 
 
 echo closeLayout1();
 
?>