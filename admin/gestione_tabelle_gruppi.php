<?php
/**
 * Sono qui presenti le procedure fondamentali per la gestione del registro di VFront.
 * Il file è organizzato per aree e svolge numerose funzioni
 * 
 * @desc File per la gestione del registro di VFront
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: gestione_tabelle_gruppi.php 1146 2015-04-24 15:33:10Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */



include("../inc/conn.php");
include("../inc/layouts.php");
include("../inc/func.tratta_campo.php");

proteggi(3);

if(isset($_GET['conf_sub'])){
	
	include("../inc/func.tratta_campo_submask.php");
}




###############################################################################################
#
#		Da qui mostra le impostazioni della tabella
#		Con richiamo di funzione esterna per ogna campo.
#
###############################################################################################





/**
 * Da qui è possibile accedere alle impostazioni delle tabelle e eseguire operazioni rapide
 * sulla loro configurazione
 * 
 * @desc Genera il menu delle tabelle
 * @param unknown_type $gid
 * @param unknown_type $order
 */
function tab_menu($gid=0,$order='table_name'){
	
	global  $vmsql, $vmreg, $db1;
	
	
	switch($order){
		case 0: $order = "table_name"; break;
		case 1: $order = "data_modifica"; break;
		case 2: $order = "visibile"; break;
		case 3: $order = "id_table"; break;
		case 4: $order = "commento"; break;
		case 5: $order = "in_insert"; break;
		case 6: $order = "in_update"; break;
		case 7: $order = "in_delete"; break;
		case 8: $order = "n_sottomaschere"; break;
		default: $order = 'table_name'; 
	}
	
	if(isset($_GET['sort'])){
		
		$sort = ($_GET['sort']=="d") ?  "DESC" : "ASC";
	}
	else{
		$sort = "ASC";
	}
	
	// Richiama tutte le tabelle dati
	$sql_tab = "SELECT t.table_name,  t.visibile,  t.in_insert,  t.in_update, 
				 t.in_delete,  t.data_modifica,   t.id_table,  t.commento , t.table_type
				, (SELECT count(*) FROM {$db1['frontend']}{$db1['sep']}registro_submask s WHERE s.id_table=t.id_table) as n_sottomaschere
				FROM {$db1['frontend']}{$db1['sep']}registro_tab t
				WHERE gid=$gid
				ORDER BY t.table_type,  $order $sort ";
	$q_tab= $vmreg->query($sql_tab);
	$matrice_tab = $vmreg->fetch_assoc_all($q_tab);


	echo openLayout1(_("Table administration"),array("sty/admin.css","js/mostra_nascondi_id.js"));

	
	
	echo breadcrumbs(array("HOME","ADMIN","menu_registri.php"=>_("menu groups/registries"),""));

	echo "<h1>"._("Rules table group")." <span class=\"var\">$gid::". Common::gid2group_name($gid)."</span></h1>\n";
	

	#############################################
	#
	#
	#	FUNZIONI RAPIDE
	#
	#
	
	echo "<div style=\"padding:5px 0 20px 0;\"><a href=\"javascript:;\" onclick=\"mostra_nascondi('tab-menu-rapide');\">"._("Quick functions")."</a></div>\n";
	
	echo "
	
		<div id=\"tab-menu-rapide\"  style=\"display:none\">
			<fieldset style=\"padding:10px;margin:10px;width:85%;\">
				<legend>"._("Quick settings")."</legend>
				<p><strong>"._("Visibility (SELECT)")."</strong>
					<a href=\"?gid=$gid&amp;mass=visibile_all\">"._("Allow from all tables")."</a> | 
					<a href=\"?gid=$gid&amp;mass=visibile_none\">"._("Deny from all tables")."</a></p>
				<p><strong>"._("INSERT")."</strong>
				<a href=\"?gid=$gid&amp;mass=insert_all\">"._("Allow in all tables")."</a> | 
					<a href=\"?gid=$gid&amp;mass=insert_none\">"._("Deny from all tables")."</a></p>
				<p><strong>"._("UPDATE")."</strong>
				<a href=\"?gid=$gid&amp;mass=update_all\">"._("Allow in all tables")."</a> | 
					<a href=\"?gid=$gid&amp;mass=update_none\">"._("Deny from all tables")."</a></p>
				<p><strong>"._("DELETE")."</strong>
				<a href=\"?gid=$gid&amp;mass=delete_all\">"._("Allow in all tables")."</a> | 
					<a href=\"?gid=$gid&amp;mass=delete_none\">"._("Deny from all tables")."</a>
				</p>
			</fieldset>
			<br/>
			<br/>
		</div>
		";
	
	
	
	
	echo "<table summary=\"tabelle db per utenti\" id=\"tab-tabelle\" >\n";
	
	$c=0;
	
	
	echo "\t<tr>\n";
	
	echo "\t\t<th>". Common::table_sort(_('visible'),2,_('Table visibility setting'))."</th>\n";
	echo "\t\t<th>". Common::table_sort(_('table'),0,_('Table name'))."</th>\n";
	echo "\t\t<th>". Common::table_sort(_('Last settings'),1,_('Last date for this table setting'))."</th>\n";
	echo "\t\t<th>". Common::table_sort(_('subforms'),8,_('number of subforms for this table'))."</th>\n";
	echo "\t\t<th>". Common::table_sort(_('comment'),4,_('Table comment'))."</th>\n";
	echo "\t\t<th>". Common::table_sort('insert',5,_('Permission for data insertion for this table by this group'))."</th>\n";
	echo "\t\t<th>". Common::table_sort('update',6,_('Permission for data editing for this table by this group'))."</th>\n";
	echo "\t\t<th>". Common::table_sort('delete',7,_('Permission for deleting data from this subform by this group'))."</th>\n";
	
	
	echo "\t</tr>\n";
	
	$inizia_vista=true;
	
	foreach($matrice_tab as $tab){
		
		$colore=(($c%2)==0) ? "c1":"c2";
		$c++;
		
		if($tab['data_modifica']>0) {
			$data_mod = date("Y-m-d H:i",$tab['data_modifica']);
			$color_href="";
			$title_href='';
		}
		else{
			$data_mod = "-";
			$color_href=" class=\"dafare\"";
			$title_href="title=\""._("This table is not configured yet")."\"";
		}
		
		 $n_sottomaschere = ($tab['n_sottomaschere']>0) ? "<strong>".$tab['n_sottomaschere']."</strong>" : "<span class=\"grigio\">".$tab['n_sottomaschere']."</span>";
		
		 
		 // SEPARATORE PER LE VISTE
		 
		 if($tab['table_type']=='VIEW' && $inizia_vista==true){
		 	
		 	echo "\t\t<tr class=\"separatore\" style=\"padding-top:50px;margin-top:10px;\">
		 		<td colspan=\"7\"><hr /><h2>"._("Tables views")."</h2></td>
		 		</tr>\n";
		 	
		 	$inizia_vista=false;
		 }
		 
		 
		 
		echo "\t\t<tr class=\"$colore\">
				<td>".Common::highlight_yes_no($tab['visibile'])."</td>
				
				<td><a $title_href $color_href href=\"".$_SERVER['PHP_SELF']."?det=".$tab['id_table']."&amp;gid=$gid\">".$tab['table_name']."</a></td>
				<td>".$data_mod."</td>
				<td>".$n_sottomaschere."</td>
				<td>".htmlentities($tab['commento'],ENT_QUOTES, FRONT_ENCODING)."</td>
				<td>".Common::highlight_yes_no($tab['in_insert'])."</td>
				<td>".Common::highlight_yes_no($tab['in_update'])."</td>
				<td>".Common::highlight_yes_no($tab['in_delete'])."</td>
			</tr>\n";
	}
	
	echo "</table>\n";

	echo closeLayout1();

}








/**
 * Mostra la linguetta di dettaglio dei campi per l'amministrazione delle tabelle
 * 
 * @desc Dettaglio per le tabelle
 * @param int $oid
 * @param int $gid
 */
function tab_dett($oid,$gid=0){
	
	global  $vmsql, $vmreg, $db1;
	
	
	$oid= (int) $oid;
	
	$gid= (int) $gid;
	
	
	# Is table or view?
	$q_tipo=$vmreg->query("SELECT table_type FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE id_table=$oid");
	list($tipo_tab) = $vmreg->fetch_row($q_tipo);
	
	
	# Info table
	$sql_tab = "SELECT rb.*
				FROM {$db1['frontend']}{$db1['sep']}registro_tab rb 
				WHERE rb.id_table=$oid";
	
	$q_tab=$vmreg->query($sql_tab);
	$info=$vmreg->fetch_assoc($q_tab);
	
	
	# PK
	$array_pk= RegTools::prendi_all_PK(RegTools::oid2name($oid));

	
	# Prende le FK
	# Individua i campi coinvolti in una relazione esterna e tabella.campo a cui � legato


	list($array_fk,$colref_fk) = RegTools::prendi_FK($oid);
	
	
	# Inizia a prendere i campi
	
	
	$sql_cols = "SELECT  id_reg,
						id_table,
						column_name,
						column_default,
						is_nullable,
						data_type,
						character_maximum_length,
						column_type,
						extra,
						in_tipo,
						in_default,
						in_visibile,
						in_richiesto,
						in_suggest,
						in_table,
						jstest,
						commento,
						alias_frontend
						
						
						FROM {$db1['frontend']}{$db1['sep']}registro_col 
						WHERE id_table=$oid 
						ORDER BY ordinal_position";
	
	$q_cols=$vmreg->query($sql_cols);
	
	$matrice_col=$vmreg->fetch_assoc_all($q_cols);
	
	$matrice_rev=$vmreg->reverse_matrix($matrice_col);


	// INFO GENERALI PER LA TABELLA
	$ks=array('visibile','in_insert','in_duplica','in_update','in_delete','in_export',
	'in_import','permetti_allegati','permetti_link',
	'permetti_allegati_ins','permetti_allegati_del',
	'permetti_link_ins','permetti_link_del', 'allow_filters');

	$def=array();
	for($i=0;$i<count($ks);$i++){

		$iii = (string) $info[$ks[$i]];
		$def[$ks[$i]]= (in_array($iii, array("1","t"))) ? "checked=\"checked\" " : "";
	}

	$def['permetti_allegati_display']= (in_array((string) $info['permetti_allegati'], array("1","t"))) ? "" : "none";
	$def['permetti_link_display']= (in_array((string) $info['permetti_link'], array("1","t"))) ? "" : "none";

	
	
	###############################################
	#
	#	Inizia a stampare
	#
	
	$files= array("sty/admin.css","js/mostra_hid.js","js/mostra_nascondi_id.js", 
					"js/test_query.js", "sty/linguette.css",
                    "js/open_window.js");
	
	$files[]="js/scriptaculous/lib/prototype.js";
	$files[]="js/scriptaculous/src/scriptaculous.js";
	$files[]="js/jscolor/jscolor.js";
	$files[]="js/admin_misc.js";
	
	echo openLayout1(_("Table Administration"),$files);
	

	
	echo breadcrumbs(array("HOME","ADMIN",
					"menu_registri.php"=>_("menu groups/registries"),
					"gestione_tabelle_gruppi.php?gid=$gid" => _("settings registry id")." $gid",
					''));


	$see_now=($_SESSION['user']['livello']==3) ? " - <a href=\"../scheda.php?oid=$oid\" title=\"See now\">"._('See')."</a>\n" : "\n";
	
	if($info['table_type']=='VIEW'){
		echo "\n<h1>"._("View settings")." <span class=\"verde\">".$info['table_name']."</span> "
                ._("for")." <span class=\"verde\">".$gid."</span><span class=\"grigio\">::</span><span class=\"verde\">". Common::gid2group_name($gid)."</span> $see_now</h1>\n";
	}
	else{
		echo "\n<h1>"._("Table settings")." <span class=\"var\">".$info['table_name']."</span> ".
                _("for")." <span class=\"var\">".$gid."</span><span class=\"grigio\">::</span><span class=\"var\">". Common::gid2group_name($gid)."</span> $see_now</h1>\n";
	}
	
	
	// Feedback
	if(isset($_GET['feed'])){
		switch($_GET['feed']){
			
			case 'ok_gen': $feed_str="<p class=\"feed-mod-ok\">"._("General settings corrctly modified")."</p>\n";
			break;
			
			case 'ok_gen_trasversale': $feed_str="<p class=\"feed-mod-ok\">"._("General settings for all groups set successfully")."</p>\n";
			break;
			
			case 'ko_gen': $feed_str="<p class=\"feed-mod-ko\">"._("Error editing general settings")."</p>\n";
			break;			
			
			case 'ok_campi': $feed_str="<p class=\"feed-mod-ok\">"._("Fields settings updated successfully")."</p>\n";
			break;
			
			case 'ko_campi': $feed_str="<p class=\"feed-mod-ko\">"._("No change to the settings for the fields")."</p>\n";
			break;	
					
			case 'ok_sub_upd': $feed_str="<p class=\"feed-mod-ok\">"._("Subforms settings updated successfully")."</p>\n";
			break;
			
			case 'ko_sub_upd': $feed_str="<p class=\"feed-mod-ko\">"._("No changes in the submask settings")."</p>\n";
			break;
			
			default: $feed_str="";
		}
		
		echo $feed_str;
	}
		
	$classe_eti['gen'] =
    $classe_eti['campi'] =
    $classe_eti['mask'] =
    $classe_eti['campisort'] =
    $classe_eti['pulsanti'] =
    $classe_eti['widget'] = 
    $classe_eti['default-filters'] ='disattiva';
	
	if(!isset($_GET['a']) || $_GET['a']=='1'){
		
		$attiva = 'tabella-gen';
		$classe_eti['gen']='attiva';
	}
	else if($_GET['a']=='2'){
		
		$attiva = 'tabella-campi';
		$classe_eti['campi']='attiva';
		
	}
    else if($_GET['a']=='3'){
		
		$attiva = 'tabella-sottomaschere';
		$classe_eti['mask']='attiva';
	}
	else if($_GET['a']=='4'){
		
		$attiva = 'campi-sort';
		$classe_eti['campisort']='attiva';
	}
	else if($_GET['a']=='5'){
		
		$attiva = 'tabella-pulsanti';
		$classe_eti['pulsanti']='attiva';
	}
    
	else if($_GET['a']=='7'){
		
		$attiva = 'default-filters';
		$classe_eti['default-filters']='attiva';
	}
	
	else if($_GET['a']=='6'){
		
		$attiva = 'widget';
		$classe_eti['widget']='attiva';
	}
	
	
	// Apre il box etichette
	
	// attiva disattiva
	
	
	echo 
	"<div id=\"box-etichette\">
		
		<ul class=\"eti-var-gr\">
			<li onclick=\"eti('tabella-gen');\" id=\"li-tabella-gen\" class=\"{$classe_eti['gen']} gestione-tabelle\" >"._("General settings")."</li>
			<li onclick=\"eti('tabella-campi');\" id=\"li-tabella-campi\" class=\"{$classe_eti['campi']} gestione-tabelle\" >"._("Fields settings")."</li>
			<li onclick=\"eti('campi-sort');\" id=\"li-campi-sort\" class=\"{$classe_eti['campisort']} gestione-tabelle\" >"._("Fields sort order")."</li>
			<li onclick=\"eti('tabella-sottomaschere');\" id=\"li-tabella-sottomaschere\" class=\"{$classe_eti['mask']} gestione-tabelle\" >"._("Subforms settings")."</li>
			<li onclick=\"eti('tabella-pulsanti');\" id=\"li-tabella-pulsanti\" class=\"{$classe_eti['pulsanti']} gestione-tabelle\" >"._("Custom buttoms")."</li>
			<li onclick=\"eti('widget');\" id=\"li-widget\" class=\"{$classe_eti['widget']} gestione-tabelle\" >"._("Widgets")."</li>
			<li onclick=\"eti('default-filters');\" id=\"li-default-filters\" class=\"{$classe_eti['default-filters']} gestione-tabelle\" >"._("Default filters")."</li>
		</ul>
	</div>
		";
	
	
	
	// CAMPI TABELLA:
	list($campi_tabella,$in_tipo01) = RegTools::prendi_colonne_frontend($oid,"column_name,in_tipo",false);
	
	
	##############################################################
	#
	#	 FORM IMPOSTAZIONI GENERALI
	#
	#
	#
	
	
	
	$st_gen = (!isset($_GET['a']) || $_GET['a']==1) ? "" : "display:none;"; 
	
	echo "
	
	<div class=\"tabella-gen\" id=\"tabella-gen\" style=\"$st_gen\" >
	
	<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
    
    
    // DEFAULT VIEW (new in 0.99)
    if(isset($info['default_view']) && $info['default_view']=='table') {
        $default_view_form = '';
        $default_view_table = "checked=\"checked\"";
    }
    else{
        $default_view_table = '';
        $default_view_form = "checked=\"checked\"";
    }
    
    echo "<div id=\"default_view_cont\">
        <div>"._('Default view').": 
                <input name=\"default_view\" id=\"default_view_0\" type=\"radio\" value=\"form\" $default_view_form />
                <label for=\"default_view_0\">"._('Form')."</label>
                <input name=\"default_view\" id=\"default_view_1\" type=\"radio\" value=\"table\" $default_view_table />
                <label for=\"default_view_1\">"._('Grid')."</label>
                
        <div class=\"info-campo\">"._("Set the default view when the page load.")."</div>
     </div>
     </div>\n";
	
	
	
	
	// TABLE ALIAS
	$table_alias= (isset($info['table_alias'])) ? $vmreg->escape($info['table_alias']) : '';
    
	
	echo "<div id=\"table_alias_cont\">\n";
	echo "<label for=\"table_alias\">"._('Table alias').": </label>";
	echo "<input name=\"table_alias\" id=\"table_alias\" type=\"text\" maxlength=\"100\" size=\"40\" value=\"$table_alias\" />\n";
	echo "<div class=\"info-campo\">"._("Show a different name for the table. If blank shows the original name.")."</div>\n";
	echo "</div>\n";
    
    
    
	
	
	
	##########################################
	#
	#	ORDERBY BLOCK
	
	
	
	
	echo "
		<div id=\"orderby_tn\">\n";
	
	$array_orderby=explode(",",$info['orderby']);
	$array_orderby_sort=explode(",",$info['orderby_sort']);
	
	$c=0;
	
	do{
		
		$label_text = ($c==0) ? _("Sort by:") : _("and order by:");
		
		echo"
			<div id=\"orderby_tn{$c}\" >
			
				<label for=\"orderby{$c}\" id=\"lab_orderby{$c}\">".$label_text."</label>
				<select name=\"orderby[]\" id=\"orderby{$c}\" >
				";
			
				for($x=0;$x<count($matrice_rev['column_name']);$x++){
					
					$sel = ($matrice_rev['column_name'][$x]==$array_orderby[$c]) ? "selected=\"selected\"" : "";
					
					// se anche chiave primaria, scrivilo
					//$pk = ($matrice_rev['column_name'][$x]==$info['campo_pk']) ? " (". _("primary key") .")" : "";
					$pk='';
                                        
					echo "\t\t<option value=\"".$matrice_rev['column_name'][$x]."\" $sel >".$matrice_rev['column_name'][$x]." $pk</option>\n";		
				}
				
			echo "
				</select>
				";
			
			$sel_sort['ASC'] = ($array_orderby_sort[$c]=='ASC') ? "selected=\"selected\"" : "";
			$sel_sort['DESC'] = ($array_orderby_sort[$c]=='DESC') ? "selected=\"selected\"" : "";
			
			$display_remove = ($c==0) ? "style=\"display:none\"" : "";
			
			echo "
			
				<select name=\"orderby_sort[]\" id=\"orderby_sort{$c}\" >
					<option value=\"ASC\" ".$sel_sort['ASC'].">"._("Ascending")."</option>
					<option value=\"DESC\" ".$sel_sort['DESC'].">"._("Descending")."</option>
				</select>
				<span class=\"fakelink\" onclick=\"$(this).up().remove()\" $display_remove >"._('Remove')."</span>
			</div>"; 
			
		$c++;
			
	} while ($c<count($array_orderby));
	
	
	
	echo "</div>\n";	

	echo "<span class=\"fakelink\" onclick=\"add_orderby()\" >"._('Add order by')."</span>\n";
	// fine blocco ORDERBY
			
			
		echo "
		
		<div class=\"info-campo\">"._("Field will be sorted in the default table order")."</div>
		
		<input type=\"hidden\" name=\"visibile\" id=\"visibile-hid\" value=\"0\" />
		<input type=\"checkbox\" name=\"visibile\" id=\"visibile\" value=\"1\" ".$def['visibile']."/>
		<label for=\"visibile\">"._("Table visible")."</label>
		<div class=\"info-campo\">"._("Set whether this table should be visible for this group")."</div>
		
		";
        
        	
		
		if($info['table_type']=='VIEW'){
			
			echo "
			<input type=\"hidden\" name=\"in_insert\" id=\"in_insert-hid\" value=\"0\" />
			<input type=\"checkbox\" name=\"in_insert\" id=\"in_insert\" value=\"1\"  ".$def['in_insert']." />
			<label for=\"in_insert\">"._("Insert records (INSERT)")."</label>
			<div class=\"info-campo\">"._("Allow users from this group to insert new records")."</div>
			";
			
			
			// PRENDE LA DEFINIZIONE VISTA E CERACA JOIN PER IMPOSTARE IL DELETE SI|NO
			$IS=new iSchema();
			$VIEW_UP=$IS->view_is_updatable($info['table_name']);
			
			// IMPOSTAZIONI PER LA MODIFICABILTA' DEI DATI NELLA VISTA (prese dalla definizione della vista stessa)
			
			if($VIEW_UP){
				
				echo "
			
			<input type=\"hidden\" name=\"in_update\" id=\"in_update-hid\" value=\"0\" />
			<input type=\"checkbox\" name=\"in_update\" id=\"in_update\"  value=\"1\" ".$def['in_update']." />
			<label for=\"in_update\">"._("Modify records (UPDATE)")."</label>
			<div class=\"info-campo\">"._("Allow users this group to update existing records")."</div>
			
				";
			}
			else{
				echo "
				
				<input type=\"hidden\" name=\"in_update\" id=\"in_update-hid\" value=\"0\" />
				<input type=\"checkbox\" name=\"in_update\" id=\"in_update\" value=\"0\"  disabled=\"disabled\" />
				<label for=\"in_update\">"._("Modify records (UPDATE)")."</label>
				<div class=\"info-campo\">"._("Disabled in views")."</div>
				
				";
			}
			
			
			
				
				
			if($VIEW_UP){
				
				echo "
				
				<input type=\"hidden\" name=\"in_delete\" id=\"in_delete-hid\" value=\"0\" />
				<input type=\"checkbox\" name=\"in_delete\" id=\"in_delete\" value=\"1\" ".$def['in_delete']." />
				<label for=\"in_delete\">"._("Delete records (DELETE)")."</label>
				<div class=\"info-campo\">"._("Allow users in this group to delete records")."</div>
				";
			}
			else{
				
				echo "
				
				<input type=\"hidden\" name=\"in_delete\" id=\"in_delete-hid\" value=\"0\" />
				<input type=\"checkbox\" name=\"in_delete\" id=\"in_delete\" value=\"0\"  disabled=\"disabled\" />
				<label for=\"in_delete\">"._("Delete records (DELETE)")."</label>
				<div class=\"info-campo\">"._("Disabled in views with JOIN")."</div>
				";
			}
		
		
		}
		// Case BASE TABLE
		else{
			
				echo "
			<input type=\"hidden\" name=\"in_insert\" id=\"in_insert-hid\" value=\"0\" />
			<input type=\"checkbox\" name=\"in_insert\" id=\"in_insert\" value=\"1\" ".$def['in_insert']." onclick=\"if(this.checked){\$('cont-duplica').style.display='';} else {\$('cont-duplica').style.display='none';}\" />
			<label for=\"in_insert\">"._("Insert records (INSERT)")."</label>
			<div class=\"info-campo\">"._("Allow users from this group to insert new records")."</div>
			";
				
			$style_duplica = ($def['in_insert']=='') ? "display:none;" : '';
				
			echo "
			<div id=\"cont-duplica\" style=\"margin-left:40px;$style_duplica\">
				<input type=\"hidden\" name=\"in_duplica\" id=\"in_duplica-hid\" value=\"0\" />
				<input type=\"checkbox\" name=\"in_duplica\" id=\"in_duplica\" value=\"1\" ".$def['in_duplica']."/>
				<label for=\"in_duplica\">"._("Duplicate record (depending on INSERT permission)")."</label>
				<div class=\"info-campo\">"._("Allow users in this group to duplicate existing records")."</div>
			</div>
			";
			
			echo "
			<input type=\"hidden\" name=\"in_update\" id=\"in_update-hid\" value=\"0\" />
			<input type=\"checkbox\" name=\"in_update\" id=\"in_update\" value=\"1\" ".$def['in_update']."/>
			<label for=\"in_update\">"._("Modify records (UPDATE)")."</label>
			<div class=\"info-campo\">"._("Allow users this group to update existing records")."</div>
			
			<input type=\"hidden\" name=\"in_delete\" id=\"in_delete-hid\" value=\"0\" />
			<input type=\"checkbox\" name=\"in_delete\" id=\"in_delete\" value=\"1\" ".$def['in_delete']."/>
			<label for=\"in_delete\">"._("Delete records (DELETE)")."</label>
			<div class=\"info-campo\">"._("Allow this group to delete records")."</div>
			
			
			";
		}
		
		echo "
		
		<input type=\"hidden\" name=\"in_export\" id=\"in_export-hid\" value=\"0\" />
		<input type=\"checkbox\" name=\"in_export\" id=\"in_export\" value=\"1\" ".$def['in_export']."/>
		<label for=\"in_export\">"._("Allow data export")."</label>
		<div class=\"info-campo\">"._("Allow this group to export data")."</div>
		
		";
		
		
			
		$import_check= ($info['table_type']=='VIEW') ? "disabled=\"disabled\"" : '';
		
		echo "
		
		<input type=\"hidden\" name=\"in_import\" id=\"in_import-hid\" value=\"0\" />
		<input type=\"checkbox\" name=\"in_import\" id=\"in_import\" value=\"1\" ".$def['in_import']." $import_check />
		<label for=\"in_import\">"._("Allow data import")."</label>
		<div class=\"info-campo\">"._("Disabled in views")."</div>
		
		";
		
       
        // Allow Filters
		echo "
		
		<input type=\"hidden\" name=\"allow_filters\" id=\"allow_filters-hid\" value=\"0\" />
		<input type=\"checkbox\" name=\"allow_filters\" id=\"allow_filters\" value=\"1\" ".$def['allow_filters']."\" />
		<label for=\"allow_filters\">"._("Allow filters")."</label>
		<div class=\"info-campo\">"._("Allows you to filter the records by clicking on the icon next to a single field")."</div>
		
		";
		
		
		
		
		// IN CASO DI VISTA IMPOSTA ESPLICITAMENTE LA CHIAVE PRIMARIA DELLA TABELLA
		
		if($info['table_type']=='VIEW'){
			
			echo "<label for=\"view_pk\">"._("Set primary key")."</label> ";
			echo "<select id=\"view_pk\" name=\"view_pk\">\n";
	
	
			foreach($campi_tabella as $k=>$campo){
				
				$view_pk_sel = ($info['view_pk']==$campo)? "selected=\"selected\"": "";
				
				echo "<option value=\"$campo\" $view_pk_sel>".$campo."</option>\n";
				
			}
			
			echo "</select>
			<div class=\"info-campo\">"._("In this view you must explicitly define the primary key")."</div>\n";
		}
		
		
		// TABLE COMMENT
		$table_comment= (isset($info['commento'])) ? $info['commento'] : '';

		echo "<div id=\"table_comment_cont\">\n";
		echo "<label for=\"table_comment\">"._('Table comment').": </label>";
		echo "<input name=\"table_comment\" id=\"table_comment\" type=\"text\" maxlength=\"140\" size=\"100\"  value=\"$table_comment\" />\n";
		echo "<div class=\"info-campo\">"._("Show a comment for the table. If blank shows the original comment.")."</div>\n";
		echo "</div>\n";

		
		
		echo "
		
		<fieldset style=\"width:60%;margin-bottom:20px; padding:15px;\">
			<legend>"._("Attachments and link")."</legend>
		";
		
		
		// IN CASO DI VISTA IMPOSTA ESPLICITAMENTE LA FONTE PER ALLEGATI E LINK
		
		if($info['table_type']=='VIEW'){
			
			$tabelle_db=RegTools::prendi_tabelle($_GET['gid']);
			echo "<select id=\"fonte_al\" name=\"fonte_al\">\n";
	
	
			foreach($tabelle_db as $k=>$tab){
				
				$view_fonte_sel = ($info['fonte_al']==$tab['table_name'])? "selected=\"selected\"": "";
				
				echo "<option value=\"{$tab['table_name']}\" $view_fonte_sel>".$tab['table_name']."</option>\n";
				
			}
			
			echo "</select>
			<div class=\"info-campo\">"._("In this view you must define which table should be read or used to add attachments and links")."</div>\n";
		
		}
		
			
		echo "
			<input type=\"hidden\" name=\"permetti_allegati\" id=\"permetti_allegati-hid\" value=\"0\" />
			<input type=\"checkbox\" name=\"permetti_allegati\" id=\"permetti_allegati\" value=\"1\" ".$def['permetti_allegati']." onclick=\";if(this.checked){document.getElementById('opzioni_allegati').style.display='';}else{document.getElementById('opzioni_allegati').style.display='none';}\" />
			<label for=\"permetti_allegati\">"._("Table with attachments")."</label>
			<div class=\"info-campo\">"._("Allows you to attach files (documents or images) to records in this table")."</div>
			
			
				<div id=\"opzioni_allegati\" style=\"display:{$def['permetti_allegati_display']}\">
					<input type=\"hidden\" name=\"permetti_allegati_ins\" id=\"permetti_allegati_ins-hid\" value=\"0\" />
					<input type=\"checkbox\" name=\"permetti_allegati_ins\" id=\"permetti_allegati_ins\" value=\"1\" ".$def['permetti_allegati_ins']."/>
					<label for=\"permetti_allegati_ins\">"._("Allow insertion of attachments")."</label>
					
					<br />
					
					
					<input type=\"hidden\" name=\"permetti_allegati_del\" id=\"permetti_allegati_del-hid\" value=\"0\" />
					<input type=\"checkbox\" name=\"permetti_allegati_del\" id=\"permetti_allegati_del\" value=\"1\" ".$def['permetti_allegati_del']."/>
					<label for=\"permetti_allegati_del\">"._("Allow deletions of attachments")."</label>
					
				</div>
			
			
			
			<input type=\"hidden\" name=\"permetti_link\" id=\"permetti_link-hid\" value=\"0\" />
			<input type=\"checkbox\" name=\"permetti_link\" id=\"permetti_link\" value=\"1\" ".$def['permetti_link']." onclick=\";if(this.checked){document.getElementById('opzioni_link').style.display='';}else{document.getElementById('opzioni_link').style.display='none';}\"  />
			<label for=\"permetti_link\">"._("Table with link")."</label>
			<div class=\"info-campo\">"._("Allow links and attachments for this table")."</div>
			
				<div id=\"opzioni_link\" style=\"display:{$def['permetti_link_display']}\">
					<input type=\"hidden\" name=\"permetti_link_ins\" id=\"permetti_link_ins-hid\" value=\"0\" />
					<input type=\"checkbox\" name=\"permetti_link_ins\" id=\"permetti_link_ins\" value=\"1\" ".$def['permetti_link_ins']."/>
					<label for=\"permetti_link_ins\">"._("Allow link insertion")."</label>
					
					<br />
					
					
					<input type=\"hidden\" name=\"permetti_link_del\" id=\"permetti_link_del-hid\" value=\"0\" />
					<input type=\"checkbox\" name=\"permetti_link_del\" id=\"permetti_link_del\" value=\"1\" ".$def['permetti_link_del']."/>
					<label for=\"permetti_link_del\">"._("Allow link deletion")."</label>
					
				</div>
			
		</fieldset>
		<br /><br />
		<input type=\"hidden\" name=\"oid\" value=\"$oid\" />
		<input type=\"hidden\" name=\"gid\" value=\"$gid\" />
		<input id=\"trasversale\" type=\"hidden\" name=\"trasversale_gen\" value=\"0\" />
		
		<input type=\"submit\" name=\"invia_gen\" value=\""._("Save general setting")."\" />
		
		&nbsp;&nbsp;&nbsp;
		
		<input type=\"button\" onclick=\"if(confirm('"._("Warning! Do you really want to change the settings in this way for all groups?")."')){ document.getElementById('trasversale').value='1';submit();}\" name=\"invia_gen_trasversale\" value=\""._("Save general settings for all groups")."\" />
	
	</form>
	
	</div>\n";
		
	//-- fine impostazioni generali
	
	
	
	
	######################################################################################
	#
	#	IMPOSTAZIONI CAMPI SORT
	#
        require_once("./admin.sort.inc.php");
	

        
        
        
	##########################################
	#
	#	 FORM SETTINGS SUBMASKS
	#
	 require_once("./admin.submask.inc.php");
	
	
	
	
	
	##############################################
	#
	#	 FORM SETTINGS SPECIAL BUTTONS
	#
	require_once("./admin.buttons.inc.php");
	
	
	
	
    ##############################################################
	#
	#	 FORM WIDGET SETTINGS
	#
        require_once("./admin.widget.inc.php");
	
	
	
	
    ##############################################################
	#
	#	 FORM DEFAULT FILTERS
	#
        require_once("./admin.default-filters.inc.php");
	
	
        
        
	###############################################################################
	#
	#   FORM campi
	#
	
	$st_campi = (isset($_GET['a']) && $_GET['a']==2) ? "" : "display:none;"; 
	
	echo "<div id=\"tabella-campi\" class=\"tabella-campi\" style=\"$st_campi\">\n";
	
	
	if(isset($_GET['feed']) && $_GET['feed']=='copia_ok'){
		
		echo "<p class=\"feed-mod-ok\">"._("Field settings copied successfully")."</p>\n";
		
	}
	
	// COPIA IMPOSTAZIONI DA ALTRO GRUPPO
	
	$mat_gruppi = RegTools::prendi_gruppi($_GET['gid']);
	
		if(count($mat_gruppi)>0){
		
		echo "\t<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" id=\"form_copia_campi\">\n"; 
		
		
		
		$sel_gruppi="\t<select name=\"copia_campi_gid\" id=\"copia_campi_gid\" >\n";
		
		
		foreach ($mat_gruppi as $k=>$ar){
			
			$sel_gruppi.="\t\t<option value=\"".$ar['gid']."\">".$ar['gid']. " - ". $ar['nome_gruppo']."</option>\n";
		}
		
		$sel_gruppi.="</select>";
		
		$confirm_cp ="if(confirm('"._("If you copy the subforms settings from another group the currents will be completely overwritten.\\n The operation is not recoverable.\\nWant to proceed?")."')){submit();}";
		echo "<input type=\"hidden\" name=\"copia_campi\" value=\"1\" />\n";
		echo "<input type=\"hidden\" name=\"det\" value=\"".$_GET['det']."\" />\n";
		echo "<input type=\"hidden\" name=\"gid\" value=\"".$_GET['gid']."\" />\n";
		echo _("Copy field setting from group:")." $sel_gruppi  <input type=\"button\" onclick=\"$confirm_cp\" name=\"copia\" value=\" "._('Apply')." \" />\n";
		
		echo "</form>\n";
		
		unset($mat_gruppi);
	}
	
	//------------------------------------
	
	
	echo "\t<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n";
	
	
					   
	while(list($k,$array_val)=each($matrice_col)){
		
		echo tratta_campo($array_val, $array_pk, $array_fk, $colref_fk);
	}
	
	echo "\t\t<input type=\"hidden\" name=\"oid\" value=\"$oid\" />\n";
	
	echo "\t\t<input type=\"hidden\" name=\"gid\" value=\"$gid\" />\n";
	
	echo "\t\t<input id=\"trasversale_campi\" type=\"hidden\" name=\"trasversale_campi\" value=\"0\" />\n";
	
	
	echo "\t\t<input type=\"hidden\" name=\"risposta_sql_all\" id=\"risposta_sql_all\" value=\"\" />\n";

	
	
	echo "\t\t<input type=\"button\" name=\"invia_campi\" value=\""._("Save field settings")."\" onclick=\"submit();\" />\n "; 
		
	echo "\t\t<input type=\"button\" onclick=\"if(confirm('"
	     ._("Warning! Do you really want to change the settings in this way for all groups?") 
	     ."')){ document.getElementById('trasversale').value='1';submit();}\" name=\"invia_gen_trasversale\" "
	     ."value=\"". _("Save field settings for all groups") ."\" />\n";
	
	
	
	echo "\t</form>\n";
	
	echo "</div>\n";
	
	
	echo closeLayout1();
}




// ----------------------------------  FINE FUNZIONI  -----------------------------------------




















// --------------------------------- AZIONI ---------------------------------------------------












###################################################################################################
#
## INSERIMENTO INFO GENERALI DI TABELLA
#
if(isset($_POST['invia_gen']) || isset($_POST['trasversale_gen'])){
	
	
	$_dati_gen=$vmreg->recursive_escape($_POST);

	// case VIEW
	if(!isset($_dati_gen['in_duplica'])) $_dati_gen['in_duplica']=0;
	
	$clausola_view_pk = (isset($_dati_gen['view_pk'])) ? "view_pk='".$_dati_gen['view_pk']."'," : "";
	$clausola_view_fonte_al = (isset($_dati_gen['fonte_al'])) ? "fonte_al='".$_dati_gen['fonte_al']."'," : "";
	
    $_dati_gen['default_view'] = (in_array($_POST['default_view'], array('form','table'))) ? $_POST['default_view'] : 'form';
	
	$sql_gen="UPDATE {$db1['frontend']}{$db1['sep']}registro_tab 
				SET orderby='".implode(",",$_dati_gen['orderby'])."', 
					orderby_sort='".implode(",",$_dati_gen['orderby_sort'])."', 
					visibile=".intval($_dati_gen['visibile']).",
					in_insert=".intval($_dati_gen['in_insert']).",
					in_duplica=".intval($_dati_gen['in_duplica']).",
					in_update=".intval($_dati_gen['in_update']).",
					in_delete=".intval($_dati_gen['in_delete']).",
					in_export=".intval($_dati_gen['in_export']).",
					in_import=".intval($_dati_gen['in_import']).",
					permetti_allegati=".intval($_dati_gen['permetti_allegati']).",
					permetti_allegati_ins=".intval($_dati_gen['permetti_allegati_ins']).",
					permetti_allegati_del=".intval($_dati_gen['permetti_allegati_del']).",
					permetti_link=".intval($_dati_gen['permetti_link']).",
					permetti_link_ins=".intval($_dati_gen['permetti_link_ins']).",
					permetti_link_del=".intval($_dati_gen['permetti_link_del']).",
					table_alias='".$_dati_gen['table_alias']."',
					commento='".$_dati_gen['table_comment']."',
					allow_filters='".intval($_dati_gen['allow_filters'])."',
					$clausola_view_pk
					$clausola_view_fonte_al
					data_modifica=".time().",
                    default_view='".$_dati_gen['default_view']."'
				WHERE id_table=".intval($_dati_gen['oid']);
	
	if($_dati_gen['trasversale_gen']=='1'){
		
		$q_sub_trasv=$vmreg->query("SELECT table_name FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE id_table=".intval($_dati_gen['oid']));
		
		list($table_name)=$vmreg->fetch_row($q_sub_trasv);
		
		$sql_gen.=" OR table_name='$table_name'";
	}
	

	
	$q_gen=$vmreg->query($sql_gen);
	
	if($vmreg->affected_rows($q_gen)>0){
		if($_dati_gen['trasversale_gen']=='1'){
			
			header("Location: ".$_SERVER['PHP_SELF']."?gid=".$_dati_gen['gid']."&feed=ok_gen_trasversale&det=".$_dati_gen['oid']."&a=1");
		}
		else{
			
			header("Location: ".$_SERVER['PHP_SELF']."?gid=".$_dati_gen['gid']."&feed=ok_gen&det=".$_dati_gen['oid']."&a=1");
		}
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?gid=".$_dati_gen['gid']."&feed=ko_gen&det=".$_dati_gen['oid']."&a=1");
	}
	
	exit;
}



###################################################################################################
#
## COPIA IMPOSTAZIONI DEI CAMPI DA UN GUPPO AD UN ALTRO
#
else if(isset($_POST['copia_campi'])){
	
	// prendi id_tabella di destinazione
	$nome_tabella_fonte = RegTools::oid2name($_POST['det']);
	$oid_tabella_fonte= RegTools::name2oid($nome_tabella_fonte,$_POST['copia_campi_gid']);
	
	$esito_copia= Admin_Registry::copia_impostazione_campi($oid_tabella_fonte,$_POST['det']);
	
	if($esito_copia)
		header("Location: ".$_SERVER['PHP_SELF']."?det=".$_POST['det']."&gid=".$_POST['gid']."&a=2&feed=copia_ok");
	exit;
	
}




###################################################################################################
#
## COPIA IMPOSTAZIONI DELLE SOTTOMASCHERE DA UN GUPPO AD UN ALTRO
#
else if(isset($_POST['copia_sottomaschere'])){
	
	// prendi id_tabella di destinazione
	$nome_tabella_fonte = RegTools::oid2name($_POST['det']);
	$oid_tabella_fonte= RegTools::name2oid($nome_tabella_fonte,$_POST['copia_sub_gid']);
	
	Admin_Registry::copia_impostazione_sottomaschere($_POST['gid'],$_POST['copia_sub_gid'],$oid_tabella_fonte,$_POST['det']);
	
	header("Location: ".$_SERVER['PHP_SELF']."?det=".$_POST['det']."&gid=".$_POST['gid']."&a=3&feed=copia_ok");
	exit;
	
}





###################################################################################################
#
## CLONAZIONE IMPOSTAZIONI SOTTOMASCHERE DA TABELLA A VISTA AD ESSA RELATIVA
#
else if(isset($_POST['clona_sottomaschere_vista'])){
	
	$ID_TABELLA_ORIGINE= (int) $_POST['id_tabella'];
	$ID_VISTA_DESTINAZIONE= (int) $_POST['id_vista'];
	
	Admin_Registry::copia_sottomaschere_viste($ID_VISTA_DESTINAZIONE,$ID_TABELLA_ORIGINE);
	
	header("Location: ".$_SERVER['PHP_SELF']."?det=".$ID_VISTA_DESTINAZIONE."&gid=".$_GET['gid']."&a=3");
	exit;
	
}



###################################################################################################
#
## INSERIMENTO INFO SOTTOMASCHERE DI TABELLA
#
else if(isset($_POST['sottomaschere'])){
	
	$_dati_gen=$vmreg->recursive_escape($_POST);
	
	$aff=0;
	
	for($i=0;$i<count($_dati_gen['new_submask']);$i++){
		
		$esito_inizializzazione_sub = Admin_Registry::inizializza_sottomaschera($_dati_gen['oid'], RegTools::oid2name($_dati_gen['new_submask'][$i]));
	}
	
	
	
	if($esito_inizializzazione_sub){
		header("Location: ".$_SERVER['PHP_SELF']."?gid=".$_dati_gen['gid']."&feed=ok_new_sub&det=".$_dati_gen['oid']."&a=3");
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?gid=".$_dati_gen['gid']."&feed=ko_new_sub&det=".$_dati_gen['oid']."&a=3");
	}
	
	
	exit;
}


###################################################################################################
#
## INSERIMENTO INFO CAMPI DELLA  TABELLA
#

else if(isset($_POST['campo'])){
	
	$gid=(int) $_POST['gid'];
	
	$campo=$vmreg->recursive_escape($_POST['campo']);
	
	# contatore per le righe coinvolte
	$affected=0;
	
	foreach($campo as $k=>$val){

		if(!isset($val['in_tipo'])) $val['in_tipo']='';

		$sql="UPDATE {$db1['frontend']}{$db1['sep']}registro_col 
			  SET in_visibile='".$val['in_visibile']."',
			  in_richiesto='".$val['in_richiesto']."',
			  in_suggest='".$val['in_suggest']."',
			  in_tipo='".$val['in_tipo']."',
			  in_table='".$val['in_table']."',
			  alias_frontend='".$val['alias_frontend']."'
				";
				
			if(isset($val['tipo_altro']) || isset($val['hid_enum'])){
				
				// Caso selezione tra valori indicati
				if($val['in_tipo']=="select"){
					$val['tipo_altro']=str_replace(array("\\r","\\n"),array("","[|]"),$val['tipo_altro']);
				}
				elseif ($val['in_tipo']=="select_enum" ){
					$val['tipo_altro']=str_replace(array("\\r","\\n",","),array("","","[|]"),$val['hid_enum']);
				}

				$altro=$val['tipo_altro'];
				
			}
			else{
				$altro=null;
			}
			
			if($altro!==null){
				$sql.=", in_default='".$altro."'
				";
			}
			
			if(isset($val['extra']) && $val['in_tipo']=="hidden"){
				
				$sql.=", extra='".$val['extra']."' ";
			}
			
			
			$sql.=" WHERE id_reg=".intval($k);
		
//			echo $sql;
			
			$q_campi=$vmreg->query($sql,true);
			
			$affected+= $vmreg->affected_rows($q_campi);
			
		}
		
		
	
	
	if($affected>0){
		header("Location: ".$_SERVER['PHP_SELF']."?gid=$gid&feed=ok_campi&det=".intval($_POST['oid'])."&a=2");
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?gid=$gid&feed=ko_campi&det=".intval($_POST['oid'])."&a=2");
	}
		
	
	exit;
}

###############################################################################################
#
#		MODIFICA IMPOSTAZIONI DEFAULT-FILTERS
#
#

else if(isset($_POST['df'])){
    
    $_df = $vmreg->recursive_escape($_POST['df']);
    $_df_ops = $vmreg->recursive_escape($_POST['op']);
    $det = intval($_POST['det']);
    $gid = intval($_POST['gid']);
    $scheda_n = 7;
    
    $op_keys = array_keys(Admin_Registry::get_default_filters_ops());
    
    $new_df=array();
    
    foreach($_df as $campo=>$val){
        if(trim($val) == '' &&  $_df_ops[$campo]!='is_null' && $_df_ops[$campo]!='is_not_null') {
            continue;
        }
        else{
            $op = (in_array($_df_ops[$campo],$op_keys)) ? $_df_ops[$campo] : 'equal';
            $new_df[$campo]=array('value'=>$val, 'op'=>$op);
        }
    }
    
    $json_new_df=  json_encode($new_df);
    $sql = "UPDATE ".$db1['frontend'].$db1['sep']."registro_tab 
        SET default_filters='".$vmreg->escape($json_new_df)."' 
        WHERE id_table=".intval($_POST['det']);
    
    $q=$vmreg->query($sql);
    
    if($vmreg->affected_rows($q)>0){
        $feed='ok';
    }
    else{
        $feed='ok';
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."?det=".$det."&gid=".$gid."&a=".$scheda_n."&feed=".$feed);
    exit;
}


###############################################################################################
#
#		MODIFICA IMPOSTAZIONI GENERALI DELLA SOTTOMASCHERA
#
#

else if(isset($_GET['mod_sub_gen']) && isset($_POST['sub_gen'])){
	
	
	$dati_gen_sub =	$vmreg->recursive_escape($_POST['sub_gen']);
	
	$sql_update_sub = sprintf("UPDATE ".$db1['frontend'].$db1['sep']."registro_submask 
					   SET sub_select=%d,
					    sub_insert=%d,
					    sub_update=%d,
					    sub_delete=%d,
					    nome_frontend='%s',
					    campo_pk_parent='%s',
					    campo_fk_sub='%s',
					    orderby_sub='%s',
					    orderby_sub_sort='%s',
					    max_records=%d,
					    data_modifica='%d',
					    tipo_vista='%s'
					   
					   WHERE id_submask=%d
										   
					   ",
						$dati_gen_sub['sub_select'],
						$dati_gen_sub['sub_insert'],
						$dati_gen_sub['sub_update'],
						$dati_gen_sub['sub_delete'],
						$dati_gen_sub['nome_frontend'],
						$dati_gen_sub['sub_pk_parent'],
						$dati_gen_sub['sub_fk_this'],
						$dati_gen_sub['orderby_sub'],
						$dati_gen_sub['orderby_sub_sort'],
						$dati_gen_sub['max_records'],
						time(),
						$dati_gen_sub['tipo_vista'],
						$dati_gen_sub['id_submask']);
						
	$q_sub_up = $vmreg->query($sql_update_sub);
	
	$aff_sub_upd = $vmreg->affected_rows($q_sub_up);

	$gid= (int) $dati_gen_sub['gid'];

	if($aff_sub_upd>0){
		header("Location: ".$_SERVER['PHP_SELF']."?gid=$gid&feed=ok_sub_upd&det=".intval($dati_gen_sub['oid'])."&conf_sub=".intval($dati_gen_sub['id_submask'])."&a=3");
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?gid=$gid&feed=ko_sub_upd&det=".intval($dati_gen_sub['oid'])."&conf_sub=".intval($dati_gen_sub['id_submask'])."&a=3");
	}
	
	
	
	exit;
}


#############################################################
#
#	MODIFICA CAMPI DELLA SOTTOMASCHERA
#
#

else if(isset($_GET['mod_sub_campi']) && isset($_POST['campo_sub'])){
	
	
	$campo_sub=$vmreg->recursive_escape($_POST['campo_sub']);
	
	
	$gid=(int) $_GET['gid'];
	
	$ID_SUBMASK = (int) $_POST['id_submask'];
	
	
	
	# contatore per le righe coinvolte
	$affected=0;
	
	$vmreg->begin();
	
	foreach($campo_sub as $k=>$val){

		if(!isset($val['in_tipo'])) $val['in_tipo']='';

		$sql="UPDATE {$db1['frontend']}{$db1['sep']}registro_submask_col
			  SET in_visibile='".$val['in_visibile']."',
			  in_richiesto='".$val['in_richiesto']."',
			  in_tipo='".$val['in_tipo']."',
			  alias_frontend='".$val['alias_frontend']."'
				";
				
			if(isset($val['tipo_altro'])){
				
				// Caso selezione tra valori indicati
				if($val['in_tipo']=="select"){
					$altro=str_replace(array("\\r","\\n"),array("","[|]"),$val['tipo_altro']);
				}
				else $altro=$val['tipo_altro'];
				
			}
			elseif(isset($val['in_default'])){
				
				$altro=$val['in_default'];
			}
			else{
				$altro=null;
			}
			
			if($altro!==null)
			$sql.=", in_default='".$altro."'
			";
			
			$sql.=" WHERE id_reg_sub=".intval($k);
		
			$q_campi=$vmreg->query($sql,true);
			
			$affected+= $vmreg->affected_rows($q_campi);
		}
		
		
		
		
		if($affected>0){
			$vmreg->commit();
			header("Location: ".$_SERVER['PHP_SELF']."?gid=$gid&feed=ok_sub_upd&det=".intval($_POST['oid'])."&conf_sub=".intval($_POST['id_submask'])."&a=3");
		}
		else{
			
			$vmreg->rollback();
			header("Location: ".$_SERVER['PHP_SELF']."?gid=$gid&feed=ko_sub_upd&det=".intval($_POST['oid'])."&conf_sub=".intval($_POST['id_submask'])."&a=3");
		}
		
	
	exit;
	
	
}



# Funzioni di modifica di massa
else if(isset($_GET['mass']) && is_numeric($_GET['gid'])){
	
	
	
	switch($_GET['mass']){
	
		case 'visibile_all': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET visibile=1 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
		case 'visibile_none': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET visibile=0 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
		case 'insert_all': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET in_insert=1 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
		case 'insert_none': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET in_insert=0 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
		case 'update_all': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET in_update=1 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
		case 'update_none': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET in_update=0 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
		case 'delete_all': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET in_delete=1 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
		case 'delete_none': 
			$q_mass=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_tab SET in_delete=0 WHERE gid=".intval($_GET['gid']));
			$esito=$vmreg->affected_rows($q_mass);
		break;
	
	}
	
	if(isset($esito) && $esito>0){
		
		header("Location: ".$_SERVER['PHP_SELF']."?gid=".intval($_GET['gid'])."&feed=ok_mass");
		exit;
	}
	
}

// -- Fine funzioni modifica di massa

else if(isset($_POST['id_del_submask'])){
	
	// elimina la sottomaschera
	
	$q=$vmreg->query("DELETE FROM ".$db1['frontend'].$db1['sep']."registro_submask WHERE id_submask=".intval($_POST['id_del_submask']));
	
	header("Location: ".$_SERVER['PHP_SELF']."?det=".intval($_POST['oid'])."&gid=".intval($_POST['gid'])."&a=3");
	exit;
}



###################################################
#
#	ACTIONS NEW BUTTON
#
else if(isset($_POST['newbutton'])){
	

	$button_data=$vmreg->recursive_escape($_POST['button']);
	
	
	$button_data['button_name'] = ($button_data['button_name']=='') ? "CustomButton" : $button_data['button_name'];
	
	// opt
	if(intval($_POST['button_opt']['height'])>0 && intval($_POST['button_opt']['width'])>0){
		
		$settings="height=".intval($_POST['button_opt']['height'])."&width=".intval($_POST['button_opt']['width']);
	}
	else{
		$settings='';
	}
	
	$sql_ins_b=sprintf("INSERT INTO {$db1['frontend']}{$db1['sep']}button (button_type, button_name, color, background, definition, id_table, id_utente, settings)
						VALUES ('%s','%s','%s','%s','%s',%d, %d,'%s') ",
						$button_data['button_type'],
						$button_data['button_name'],
						$button_data['color'],
						$button_data['background'],
						$button_data['definition'],
						$button_data['id_table'],
						$_SESSION['user']['uid'],
						$settings
						);
						
	$q_ins_but=$vmreg->query($sql_ins_b);
	
	if($vmreg->affected_rows($q_ins_but)==1){
		
		header("Location: ".$_SERVER['PHP_SELF']."?det=".$button_data['id_table']."&gid=".$_POST['gid']."&a=5&feedins=ok");
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?det=".$button_data['id_table']."&gid=".$_POST['gid']."&a=5&feedins=ko");
	}
	
	exit;
}


else if(isset($_POST['modbutton'])){
	
	$button_data=$vmreg->recursive_escape($_POST['button']);
	
	$button_data['button_name'] = ($button_data['button_name']=='') ? "CustomButton" : $button_data['button_name'];
	
	// opt
	if(intval($_POST['button_opt']['height'])>0 && intval($_POST['button_opt']['width'])>0){
		
		$settings="height=".intval($_POST['button_opt']['height'])."&width=".intval($_POST['button_opt']['width']);
	}
	else{
		$settings='';
	}
	
	
	$sql_ins_b=sprintf("UPDATE {$db1['frontend']}{$db1['sep']}button SET button_type='%s', button_name='%s', color='%s', 
						background='%s', definition='%s', id_table=%d, id_utente=%d, settings='%s' 
						WHERE id_button=%d ",
						$button_data['button_type'],
						$button_data['button_name'],
						$button_data['color'],
						$button_data['background'],
						$button_data['definition'],
						$button_data['id_table'],
						$_SESSION['user']['uid'],
						$settings,
						$_POST['modbutton']
						);
						
	$q_ins_but=$vmreg->query($sql_ins_b);
	
	if($vmreg->affected_rows($q_ins_but)==1){
		
		header("Location: ".$_SERVER['PHP_SELF']."?det=".$button_data['id_table']."&gid=".$_POST['gid']."&a=5&feedmod=ok");
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?det=".$button_data['id_table']."&gid=".$_POST['gid']."&a=5&feedmod=ko");
	}
	
	exit;
}

else if(isset($_GET['delbutton'])){
	
	$sql_add= (!Common::is_admin()) ? " AND id_utente=".$_SESSION['user']['uid'] : '';
	
	$q_del_b=$vmreg->query("DELETE FROM {$db1['frontend']}{$db1['sep']}button WHERE id_button=".intval($_GET['delbutton'])." $sql_add");
	
	if($vmreg->affected_rows($q_del_b)==1){
		
		header("Location: ".$_SERVER['PHP_SELF']."?det=".$_GET['det']."&gid=".$_GET['gid']."&a=5&feeddel=ok");
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?det=".$_GET['det']."&gid=".$_GET['gid']."&a=5&feeddel=ko");
	}
}









if(isset($_GET['det']) && (int) $_GET['det']>0){
	
	// mostra i dettagli della tabella
	$oid= (int) $_GET['det'];
	tab_dett($oid,$_GET['gid']);
}
else{
	
	$gid= (int) $_GET['gid'];
	
	// se � selezionato l'ordine
	if(isset($_GET['ord'])) {
		
		tab_menu($gid,intval($_GET['ord']));  
	}
	else{
		
		tab_menu($gid);  
	}
	
	// mostra la lista tabelle
	
}

