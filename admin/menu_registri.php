<?php
/**
 * Sono qui riportati i registri/gruppi e le operazioni eseguibili sugli stessi. 
 * 
 * @desc File di pagina dei registri
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: menu_registri.php 1139 2015-04-23 21:04:10Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require_once("../inc/conn.php");
require_once("../inc/layouts.php");

 proteggi(3);


	#######################################################
	#
	#	OPZIONE DI MODIFICA DEL GRUPPO
	#

	if(isset($_GET['modifica_gid']) && isset($_GET['gid'])){
		
		$files = array("sty/admin.css","sty/tabelle.css");
		
		$OUT = openLayout1(_("Group Administration"),$files);
	
		$OUT.= breadcrumbs(array("HOME","ADMIN",
				basename($_SERVER['PHP_SELF'])=>_("Groups and registry menu"),
				_("modify group ")));


		$OUT.= "<h1>"._("Modify group/registry ")."</h1>\n";
		
		$OUT.="<img src=\"../img/registri.gif\" class=\"img-float\" alt=\""._("registry settings")."\" />\n";
		
		echo $OUT;
		
		// Prendi i dati del gruppo registro
		
		$GID = intval($_GET['gid']);
		
		$qg = $vmreg->query("SELECT *
					  FROM {$db1['frontend']}{$db1['sep']}gruppo g
				      WHERE gid=$GID");
		
		if($vmreg->num_rows($qg)!=1){
			
			echo "<p><strong>"._("Warning!")."</strong> "._("Non-existent group")."<br/>";
		}
		else{
			$RS = $vmreg->fetch_assoc($qg);
			
			// FEEDBACK NOME ERRATO
			if(isset($_GET['feed'])){
				
				if($_GET['feed']=='nome_ko')
					echo "<div class=\"feed-mod-ko\">"._("Warning!")." "._("The name must contain only letters, numbers and the underscore character - no spaces or accents")."</div><br />";
				
				elseif($_GET['feed']=='mod_ok')
					echo "<div class=\"feed-mod-ok\">"._("Modifications carried out correctly")."</div><br />";	
					
				elseif($_GET['feed']=='mod_ko')
					echo "<div class=\"feed-mod-ko\">"._("Modification not carried out ")."</div><br />";
				
			}
			
			
			echo "
			
		<form action=\"".$_SERVER['PHP_SELF']."?gid=$GID&amp;esegui_modifica\" method=\"post\">
			
			<label for=\"nome_g\" >"._("Group name (alphanumeric and <em>underscore</em> characters only) ")."</label><br />
			<input type=\"text\" name=\"nome_g\" id=\"nome_g\" value=\"".$RS['nome_gruppo']."\" />
			<br /><br />
			
			<label for=\"descrizione_g\" >"._("Group description")."</label><br />
			<input type=\"text\" name=\"descrizione_g\" id=\"descrizione_g\" value=\"".$RS['descrizione_gruppo']."\" size=\"80\" />
			<br /><br />
			
			<input type=\"hidden\" name=\"gid\" value=\"$GID\" />
			<input type=\"submit\" name=\"Modifica\" value=\" "._("Modify")." \" />
			
		</form>
			
			";
		}
		echo closeLayout1();
		
		exit;
		
	}
	elseif(isset($_GET['esegui_modifica']) && isset($_POST['gid'])){
		
		$GID = intval($_POST['gid']);
		
		
		if(trim($_POST['nome_g'])!="" &&  !preg_match("'[\W]+'",trim($_POST['nome_g']))){
			
			$sql = "UPDATE ".$db1['frontend'].$db1['sep']."gruppo SET nome_gruppo='".trim($_POST['nome_g'])."'
					, descrizione_gruppo='".$vmreg->escape(trim($_POST['descrizione_g']))."'
					WHERE gid=".$GID;
			
			$q=$vmreg->query($sql);
			
			if($vmreg->affected_rows($q)==1){
				header("Location: ".$_SERVER['PHP_SELF']."?gid=".$GID."&modifica_gid&feed=mod_ok");
			}
			else{
				header("Location: ".$_SERVER['PHP_SELF']."?gid=".$GID."&modifica_gid&feed=mod_ko");
			}
			
		}
		else{
			header("Location: ".$_SERVER['PHP_SELF']."?gid=".$GID."&modifica_gid&feed=nome_ko");
			
		}
		
		exit;
		
	}




	$files = array("sty/admin.css","sty/tabelle.css");

	$OUT = openLayout1(_("Group Administration"),$files);
	
	$OUT.= breadcrumbs(array("HOME","ADMIN",_("Groups and registry menu")));


	$OUT.= "<h1>"._("Groups/registries menu")."</h1>\n";
	

	// Messaggio di feedback nel caso si stiano eliminando un gruppo
	if(isset($_GET['msg']) && $_GET['msg']=='gruppo_eliminato'){
		
		$OUT.= "<p class=\"feed-mod-ok\">"._("Record deleted correctly")."</p>\n";
	}
	
	$OUT.="<img src=\"../img/registri.gif\" class=\"img-float\" alt=\""._("registry settings")."\" />\n";
	
	
	// test per inizializzare
	$q_init= $vmreg->query("SELECT g.gid
					  FROM {$db1['frontend']}{$db1['sep']}gruppo g
				      WHERE g.gid=-1
				      ");
	
	$num_init = $vmreg->num_rows($q_init);
	
	
	
	if($num_init==1){
		
		
		// No groups: Start the init procedure
		
		if(isset($_GET['initreg'])){
			
			$init_reg = Admin_Registry::inizializza_registro();
			
			if($init_reg){
				header("Location: ".$_SERVER['PHP_SELF']."?feed=ok");
				exit;
			}
			else{
				header("Location: ".$_SERVER['PHP_SELF']."?feed=ko");
				exit;
			}
			
		}
		
		
		$OUT.= "<p>"._("No groups in database")."<br/>
		<a href=\"".$_SERVER['PHP_SELF']."?initreg\">"._("Initialize registry")."</a></p>\n";
	}
	
	else{
	
	
	// prendi i gruppi
	$qg = $vmreg->query("SELECT g.gid, g.nome_gruppo, g.descrizione_gruppo, g.data_gruppo , count(u.gid) as n
					  FROM {$db1['frontend']}{$db1['sep']}gruppo g
				      LEFT JOIN  {$db1['frontend']}{$db1['sep']}utente u ON u.gid=g.gid
				      WHERE g.gid>=0
				      GROUP BY g.gid, g.nome_gruppo, g.descrizione_gruppo, g.data_gruppo
				      ORDER BY g.gid");
	
	
	
	
		
		// se ci sono gruppi
		if($vmreg->num_rows($qg)>0){
			
			
			
			$OUT.= "<p><a href=\"nuovo_gruppo.php\">"._("Create new group")."</a></p>\n";
			
			$matrice_gr = $vmreg->fetch_assoc_all($qg);
			
			$OUT.= "<table class=\"tab-color\" summary=\"Tabella gruppi\">\n";
			
			$OUT.= "\t<tr>
				<th>gid</th>
				<th>"._("name")."</th>
				<th>"._("description")."</th>
				<th>"._("users")."</th>
				<th>"._("update date")."</th>
				<th>"._("settings")."</th>
				<th class=\"arancio\">"._("modify")."</th>
				<th class=\"arancio\">"._("delete")."</th>
			</tr>\n";
			
			
			foreach($matrice_gr as $k=>$val){
				
				
				$OUT.= "\t<tr>\n";
				$OUT.= "\t\t<td>".$val['gid']."</td>\n";
				$OUT.= "\t\t<td>".$val['nome_gruppo']."</td>\n";
				$OUT.= "\t\t<td>".$val['descrizione_gruppo']."</td>\n";
				$OUT.= "\t\t<td>".$val['n']."</td>\n";
				$OUT.= "\t\t<td>".VFDate::date_encode($val['data_gruppo'],true)."</td>\n";
				$OUT.= "\t\t<td><a href=\"gestione_tabelle_gruppi.php?gid=".$val['gid']."\">"._("administer")."</a></td>\n";
				$OUT.= "\t\t<td><a href=\"".$_SERVER['PHP_SELF']."?modifica_gid=1&amp;gid=".$val['gid']."\">"._("modify")."</a></td>\n";
				$OUT.= ($val['gid']!=0) ? "\t\t<td><a href=\"elimina_gr.php?gid=".$val['gid']."\">"._("delete")."</a></td>\n" : "\t\t<td> - </td>\n";
				$OUT.= "\t</tr>\n";
			}
		
			$OUT.= "</table>\n";
			
		}
	
	}
	
		
	$OUT.= closeLayout1();


	echo $OUT;