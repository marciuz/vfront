<?php
/**
 * Da questo script è possibile creare un nuovo gruppo.
 * I nuovi gruppi posso essere cloni di gruppi esistenti per la creazione.
 * Vengono comunque creati record nuvoi del tutto indipendenti dal gruppo origine per la clonazione.
 * 
 * @desc File di creazione di un nuovo gruppo
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2014 M.Marcello Verona
 * @version 0.99 $Id: nuovo_gruppo.php 1098 2014-06-19 23:31:51Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require_once("../inc/conn.php");
require_once("../inc/layouts.php");

 proteggi(3);

	// Se � inviato
	if(isset($_POST['gid_old'])){
		
		$_var=$vmreg->recursive_escape($_POST);
		
		// controlli:
		if(strlen($_var['nome_gr'])==0){
			$feed=_("The group name is required");
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ko&msg=1");
			exit;
		}
		elseif($vmreg->test_id('nome_gruppo',"'".$_var['nome_gr']."'","{$db1['frontend']}{$db1['sep']}gruppo")){
			$feed=_("A record with this name already existd");
			header("Location: ".$_SERVER['PHP_SELF']."?feed=ko&msg=1");
			exit;
		}
		
		
		// fin qui tutto bene... procedi con la creazione del gruppo
		
			$vmreg->begin();
			
			// prendi il prossimo valore di gid:
			$q_gid = $vmreg->query("SELECT MAX(gid)+1 FROM {$db1['frontend']}{$db1['sep']}gruppo");
			
			list($NEW_GID) = $vmreg->fetch_row($q_gid);
			
			// test sul numero di nuovo gruppo
			if(!is_numeric($NEW_GID)) openErrorGenerico(_("Error in group creation"),true);
		
			$sql_ins_gr1="INSERT INTO {$db1['frontend']}{$db1['sep']}gruppo (gid,nome_gruppo,descrizione_gruppo,data_gruppo)
							VALUES ($NEW_GID,
									'".$_var['nome_gr']."',
									'".$_var['descrizione_gr']."',
									'".date("Y-m-d H:i:s")."')";
		
			// Passo1 crea il gruppo
			$q_ins1 = $vmreg->query($sql_ins_gr1);
			
			// Passo2 a seconda del tipo di clonazione fa cose diverse:
			
			if(intval($_var['gid_old'])=='-1'){
				
				// ricrea da zero la struttura (vero | falso)
				$esito_clonazione = Admin_Registry::genera_registro_vuoto($NEW_GID);
			}
			elseif(intval($_var['gid_old'])>=0){
				
				// clona la struttura (vero | falso)
				$esito_clonazione = Admin_Registry::clona_settaggio($NEW_GID,intval($_var['gid_old']));
				
				if($_var['anche_submask']=='1'){
					
					Admin_Registry::clona_sottomaschere($NEW_GID,intval($_var['gid_old']));
				}

				Admin_Registry::clona_buttons($NEW_GID,intval($_var['gid_old']));
				
			}
			
			if($esito_clonazione){
                $vmreg->commit();
				header("Location: menu_registri.php?feed=ok&msg=1");
				exit;
			}
			else{
                $vmreg->rollback();
				header("Location: menu_registri.php?feed=ko&msg=0");
				exit;
			}
		
	}


	$files = array("sty/admin.css","sty/tabelle.css");

	echo openLayout1("Crea nuovo gruppo",$files);
	
	echo breadcrumbs(array("HOME","ADMIN",
				"menu_registri.php"=>_("menu groups/registries"),
				strtolower(_("Create new group/registry"))));


	echo "<h1>"._("Create new group/registry")."</h1>\n";
	
	
	echo "
	<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
		<fieldset style=\"width:90%\" >
		
		
			<legend>"._("Registry/group settings")."</legend>
		
			<img src=\"../img/registri.gif\" class=\"img-float\" alt=\""._("registry settings")."\" style=\"padding: 15px 20px 400px 15px;\" />
		
			<br />
			<label for=\"nome_gr\">"._("group name")."</label><br />
			<input type=\"text\" name=\"nome_gr\" id=\"nome_gr\" maxlength=\"50\" size=\"25\" /><br />
			<div class=\"desc-campo\">"._("the name of the user group. It is a mandatory field: duplicates not allowed")."</div>
			<br /><br />
				
			
			<label for=\"descrizione_gr\">"._("group description")."</label><br />
			<textarea name=\"descrizione_gr\" id=\"descrizione_gr\" cols=\"65\" rows=\"5\" ></textarea><br />
			<div class=\"desc-campo\">"._("description of users group. Used by administrators.")."</div>
			<br /><br />
			
			<label for=\"gid_old\">"._("clone the registry settings from group:")."</label><br />
			
			<select id=\"gid_old\" name=\"gid_old\">";
	
			// prendi i gruppi
			$qg = $vmreg->query("SELECT gid, nome_gruppo FROM {$db1['frontend']}{$db1['sep']}gruppo ORDER BY gid");
	
			while($RSgr= $vmreg->fetch_assoc($qg)){
				
				echo "\t\t\t<option value=\"".$RSgr['gid']."\">".$RSgr['gid']." - ".$RSgr['nome_gruppo']."</option>\n";
			}
			
			echo "\t\t\t<option value=\"-1\">"._("create empty registry")."</option>\n";
			
			echo "\t\t</select><br />\n";
			
			echo "
			<div class=\"desc-campo\">"._("Set table access privileges and other group settings.")."<br />
			"._("Through this option you can assign group rights from an existing setup.")."<br />
			"._("All the settings of the new group can then be modified independently.")."</div>
			<br /><br />
			
			<input type=\"checkbox\" value=\"1\" id=\"anche_submask\" name=\"anche_submask\" checked=\"checked\" />
			<label for=\"anche_submask\">"._("clone subforms")."</label><br />
			<div class=\"desc-campo\">"._("If active, allows you to clone properties of subforms of the selected group.")."</div>
			<br /><br />
			
			
			<input type=\"button\" name=\"crea\" value=\""._("Create new group")."\" onclick=\"submit();\"/>
			<br /><br /> 
		</fieldset>
	</form>
	";
		
		
		
		
	echo closeLayout1();





?>