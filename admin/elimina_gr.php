<?php
/**
 * @desc Utility per l'eliminazione di un gruppo
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: elimina_gr.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

require_once("../inc/conn.php");
require_once("../inc/layouts.php");

proteggi(3);

	// CONFERMA LA CANCELLAZIONE
	if(isset($_GET['conferma_del']) && isset($_POST['gid']) && intval($_POST['gid']>0)){
	
		
		$gid = (int) $_POST['gid'];
		
		$vmreg->begin();
		
		// Imposta tutti gli utenti di gruppo $gid apparteneti a 0
		$q_up = $vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}utente SET gid=0 WHERE gid=$gid");
		
		$q_del = $vmreg->query("DELETE FROM {$db1['frontend']}{$db1['sep']}gruppo WHERE gid=$gid");
		
		$vmreg->commit();
		
		header("Location: menu_registri.php?msg=gruppo_eliminato");
		exit;
		
		
	}


	$files = array("sty/admin.css","sty/tabelle.css");

	echo openLayout1(_("Group Administration"),$files);

	
	echo breadcrumbs(array("HOME","ADMIN",
					"menu_registri.php"=>_('Groups and registry menu'),
					_('remove group/registry')
					));
	

	echo "<h1>"._('move group/registry')."</h1>\n";
	
	################################
	#
	#	PRENDI DATI UTENTI E GRUPPO
	#
	
	if(is_numeric($_GET['gid'])){
		$GID = (int) $_GET['gid'];
	}
	else{
		
		openErrorGenerico(_("The group to be removed does not exist"), true);
	}
	
	// n utenti
	$sql_u = "SELECT count(*) FROM {$db1['frontend']}{$db1['sep']}utente WHERE gid=$GID";
	$q_u = $vmreg->query($sql_u);
	list($n_utenti)=$vmreg->fetch_row($q_u);
	
	
	
	
	// dati gruppo
	$sql_g = "SELECT * FROM {$db1['frontend']}{$db1['sep']}gruppo WHERE gid=$GID";
	$q_g = $vmreg->query($sql_g);
	$info_g = $vmreg->fetch_assoc($q_g);
	
	$data_gruppo= VFDate::date_encode($info_g['data_gruppo'],true,'string');
	
	// mostra form
	echo "<p><img src=\"../img/cancella_gruppo.gif\" alt=\""._('remove_group')."\" class=\"img-float\"/> 
			"._('Someon wants to remove the group/registry')." \"<strong>".$info_g['nome_gruppo']."</strong>\", 
			"._('created on date')." ".$data_gruppo.".<br />
			"._("Warning: this operation cannot be cancelled.")."</p>\n";
	
	if($n_utenti==0){
		echo "<p>"._('There are no users in this group')."</p>";
	}
	else{
		
		$esistono = ($n_utenti==1) ? sprintf(_("%s user exists"),"<strong>$n_utenti</strong>") 
								   : sprintf(_("%s users exist "),"<strong>$n_utenti</strong>");
		
		echo "<p><strong>"._("Warning!")."</strong> $esistono "._('in this group.')."<br />
		"._("If you wish to proceed <strong> these users will be included in the default group </ strong>.")."<br />
		"._("This will probably change their rights, as affected users will inherit the rights set in the registry for the default group (group number 0).")."<br />
		</p>";
	}
	
	echo "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?conferma_del=$GID\">
		<input type=\"hidden\" name=\"gid\" value=\"$GID\" />
		<input type=\"submit\" name=\"conferma\" value=\""._('Confirm delete')."\" />
		</form>
		";
	
	
	echo closeLayout1();
?>