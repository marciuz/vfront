<?php

    ##############################################################
	#
	#	 FORM IMPOSTAZIONI SOTTOMASCHERE
	#
	#
	#



function tipo_scheda_name($name){

	switch($name){

		case 'tabella':
            $conv=_('Table');
		break;

		case 'embed':
            $conv=_('Embedded');
		break;

		case 'schedash':
			$conv=_('Form (in a shadowbox)');
		break;

		case 'scheda':
		default:
            $conv=_('Form (in new window)');
	}

	return $conv;
}

/**
 * Genera il menu delle sottomaschere
 *
 * @param int $oid
 * @param int $order
 */
function submask_menu($oid=0,$order='nome_tabella'){

	global  $vmsql, $vmreg, $db1;

	$gid= (int) $_GET['gid'];

	switch($order){
		case 0: $order = "nome_tabella"; break;
		case 1: $order = "data_modifica"; break;
		case 2: $order = "sub_select"; break;
		case 3: $order = "sub_insert"; break;
		case 4: $order = "sub_update"; break;
		case 5: $order = "sub_delete"; break;
		default: $order = 'nome_tabella'; 
	}

	if(isset($_GET['sort'])){

		$sort = ($_GET['sort']=="d") ?  "DESC" : "ASC";
	}
	else{
		$sort = "ASC";
	}

	// Richiama tutte le tabelle dati
	$sql_tab = "SELECT id_submask,sub_select,sub_insert,sub_update,sub_delete,nome_tabella, nome_frontend, 
					campo_pk_parent,campo_fk_sub,orderby_sub,orderby_sub_sort, data_modifica, tipo_vista
				FROM {$db1['frontend']}{$db1['sep']}registro_submask
				WHERE id_table=$oid
				ORDER BY $order $sort ";
	$q_tab= $vmreg->query($sql_tab);
	$matrice_tab = $vmreg->fetch_assoc_all($q_tab);



	echo "<h2>"._("Table subforms rules")." <span class=\"var\">".RegTools::oid2name($oid)."</span></h2>\n";


	echo "<table summary=\"sottomaschere tabelle\" id=\"tab-sub-tabelle\" >\n";

	$c=0;


	echo "\t<tr>\n";

	echo "\t\t<th height=\"20\"><span class=\"help\" title=\""._("Table visibility setting")."\">"._("visible")."</span></th>\n";
	echo "\t\t<th><span class=\"help\" title=\""._("Table reference")."\">"._("table")."</span></th>\n";
	echo "\t\t<th><span class=\"help\" title=\""._("Last date for this subform settings")."\">"._("Last settings")."</span></th>\n";
	echo "\t\t<th><span class=\"help\" title=\""._("Subform name")."\">"._("Subform name")."</span></th>\n";
	echo "\t\t<th><span class=\"help\" title=\""._("Display type")."\">"._("Display type")."</span></th>\n";
	echo "\t\t<th><span class=\"help\" title=\""._("Permission to insert data from this subform by this group")."\">"."insert"."</span></th>\n";
	echo "\t\t<th><span class=\"help\" title=\""._("Permission to edit data from this subform by this group")."\">"."update"."</span></th>\n";
	echo "\t\t<th><span class=\"help\" title=\""._("Permission to delete data from this subform by this group")."\">"."delete"."</span></th>\n";
	echo "\t\t<th>&nbsp;</th>\n";

	echo "\t</tr>\n";


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
			$title_href= htmlentities(_("This subform has not been configured yet"),ENT_QUOTES,FRONT_ENCODING);
		}

		echo "\t\t<tr class=\"$colore\">
				<td>".Common::highlight_yes_no($tab['sub_select'])."</td>
				<td><a title=\"$title_href\" $color_href href=\"".Common::phpself()."?det=$oid&amp;conf_sub=".$tab['id_submask']."&amp;a=3&amp;gid=".$_GET['gid']."\">".$tab['nome_tabella']."</a></td>
				<td>".$data_mod."</td>
				<td>".$tab['nome_frontend']."</td>
				<td>".tipo_scheda_name($tab['tipo_vista'])."</td>
				<td>".Common::highlight_yes_no($tab['sub_insert'])."</td>
				<td>".Common::highlight_yes_no($tab['sub_update'])."</td>
				<td>".Common::highlight_yes_no($tab['sub_delete'])."</td>
				<td><a href=\"javascript:;\" onclick=\"if(confirm('".addslashes(_("Are you sure you delete this subform and its settings?\\n The operation can not be undone"))."')){document.getElementById('id_submask').value='".$tab['id_submask']."';document.forms.elimina_submask.submit();}\">"._("Delete")."</a></td>
			</tr>\n";
	}

	echo "</table>\n";

	echo "<form method=\"post\" action=\"" . Common::phpself() . "?elimina_submask\" id=\"elimina_submask\" name=\"elimina_submask\" style=\"display:none;\">

		<input type=\"hidden\" name=\"id_del_submask\" id=\"id_submask\" value=\"0\" />
		<input type=\"hidden\" name=\"oid\" value=\"$oid\" />
		<input type=\"hidden\" name=\"gid\" value=\"$gid\" />

		</form>
		";


}



	$sottomaschere = RegTools::prendi_sottomaschere($oid,true);


	$st_sub = (isset($_GET['a']) && $_GET['a']==3) ? "" : "display:none;"; 

	echo "<div class=\"tabella-sottomaschere\" id=\"tabella-sottomaschere\" style=\"$st_sub\">\n";

	echo "<p><a href=\"javascript:;\" onclick=\"mostra_nascondi('nuova-sottomaschera');\">"._("New subforms")."</a></p>\n";

		echo "<div id=\"nuova-sottomaschera\" style=\"display:none;\">\n";

			echo "<form action=\"" . Common::phpself() . "\" method=\"post\">


				 <select size=\"13\" multiple=\"multiple\" name=\"new_submask[]\">
				 ";

			$tabelle = RegTools::prendi_tabelle($gid);

			for($i=0;$i<count($tabelle);$i++){

				// test sull'esuistanza delle sottomaschere
				//$SUB_esiste = (isset($sottomaschere['nome_tabella']) && in_array($tabelle[$i]['table_name'],$sottomaschere['nome_tabella'])) ? true:false;

				$SUB_esiste=false;


				if($tabelle[$i]['id_table']!=$oid && !$SUB_esiste)
					echo "\t\t\t<option value=\"".$tabelle[$i]['id_table']."\">".$tabelle[$i]['table_name']."</option>\n";

			}

			echo "
				 </select>

				 <input type=\"hidden\" name=\"oid\" value=\"$oid\" />
				 <input type=\"hidden\" name=\"gid\" value=\"$gid\" />

				<br /><br />
				<input type=\"submit\" name=\"sottomaschere\" value=\""._("Create new subform")."\" />
			</form>\n";



		echo "</div>\n";


		## COPIA LE SOTTOMASCHERE

		$mat_gruppi_sub = RegTools::prendi_gruppi($_GET['gid']);

		if(count($mat_gruppi_sub)>0){

			echo "<form name=\"copia_sottomaschere\" action=\"" . Common::phpself() . "\" method=\"post\">\n";

			$sel_gruppi_sub="\t<select name=\"copia_sub_gid\" id=\"copia_sub_gid\" >\n";


			foreach ($mat_gruppi_sub as $k=>$ar){

				$sel_gruppi_sub.="\t\t<option value=\"".$ar['gid']."\">".$ar['gid']. " - ". $ar['nome_gruppo']."</option>\n";
			}

			$sel_gruppi_sub.="</select>";

			$confirm_cp_sub ="if(confirm('"._("If you copy the subforms settings from another group the currents will be completely overwritten.\\n The operation is not recoverable.\\nWant to proceed?")."')){submit();}";
			echo "<input type=\"hidden\" name=\"copia_sottomaschere\" value=\"1\" />\n";
			echo "<input type=\"hidden\" name=\"det\" value=\"".$_GET['det']."\" />\n";
			echo "<input type=\"hidden\" name=\"gid\" value=\"".$_GET['gid']."\" />\n";
			echo _("Apply the subform settings of group:")." $sel_gruppi_sub  <input type=\"button\" onclick=\"$confirm_cp_sub\" name=\"copia\" value=\" "._("Apply")." \" />\n";

			echo "</form>\n";

			unset($mat_gruppi_sub);
			unset($confirm_cp_sub);
		}


	// sottomaschere esistenti:

	if(count($sottomaschere)==0){

		echo "<p><strong>"._("There are no subforms available for this table/group")."</strong></p>\n";



		if($info['table_type']=='VIEW'){


				$tabelle_elenco=RegTools::prendi_tabelle(intval($_GET['gid']));



				echo "<form action=\"" . Common::phpself() . "?det=".$_GET['det']."&amp;gid=".$_GET['gid']."\" method=\"post\" >\n";

				echo _("Clone from table:")." <select name=\"id_tabella\">\n";

				for($i=0;$i<count($tabelle_elenco);$i++){

					echo "<option value=\"".$tabelle_elenco[$i]['id_table']."\">".$tabelle_elenco[$i]['table_name']."</option>\n";
				}

				echo "</select>\n";
				echo " <input type=\"hidden\" name=\"clona_sottomaschere_vista\" value=\"1\" />\n";
				echo " <input type=\"hidden\" name=\"id_vista\" value=\"".$_GET['det']."\" />\n";
				echo " <input type=\"button\" onclick=\"submit();\" name=\"clona_impostazioni\" value=\" "._("clone settings")." \" />\n";

				echo "</form>\n";
		}


	}
	else{

		submask_menu($oid);

	}


	// Dettagli sottomaschera

	if(isset($_GET['conf_sub'])){

		// creca l'id della submaschera nei risultati
		$k_sub= array_search($_GET['conf_sub'],$sottomaschere['id_submask']);



		echo "<h3 style=\"margin-top:60px;border-bottom:1px solid #CCC;width:75%;\">"._("Subform general settings")." <span class=\"var\">".$sottomaschere['nome_tabella'][$k_sub]."</span></h3>\n";

		echo "<strong>"._("Link between tables")."</strong>: \n";




		echo "<form name=\"sub_gen\" action=\"" . Common::phpself() . "?mod_sub_gen\" method=\"post\">\n";


		#######################################################################
		#
		#
		#	 riferimenti e FK

		// prendo le FK
		$rif_sub = RegTools::prendi_FK($sottomaschere['nome_tabella'][$k_sub],intval($_GET['gid']));

		$rif_sub_k=null;

		// ce ne sono riferite alla tabella in oggetto?
		for($i=0;$i<count($rif_sub[1]);$i++){

			if(preg_match("/^{$info['table_name']}\.[\w]+/",$rif_sub[1][$i])){

				$rif_sub_k = $i;
			}
		}

		if(!is_null($rif_sub_k)){

			$parent_consigliata = substr( $rif_sub[1][$rif_sub_k] ,  strpos($rif_sub[1][$rif_sub_k],".")+1  );
			$this_consigliata = $rif_sub[0][$rif_sub_k];
		}
        else{
            $parent_consigliata = $this_consigliata = '';
        }



		echo $info['table_name'].".";

		echo "<select name=\"sub_gen[sub_pk_parent]\">\n";

		// PRENDI LE COLONNE

		list($colonne_parent) = RegTools::prendi_colonne_frontend($oid,'column_name',false);

		list($colonne_this) = RegTools::prendi_colonne_frontend($sottomaschere['nome_tabella'][$k_sub],'column_name',false,0);




		// TESTA LA VISIBILITA' DEL CAMPO

		$campo_attuale_fk= $sottomaschere['campo_pk_parent'][$k_sub];

		$PK_fk= RegTools::prendi_PK_oid($oid);

		/*var_dump($PK_fk);
		var_dump($sottomaschere);*/

		if($campo_attuale_fk!=$PK_fk[0] && $campo_attuale_fk!=''){

			$sql_vis_fk="SELECT in_visibile FROM {$db1['frontend']}{$db1['sep']}registro_col 
						WHERE id_table='$oid' AND column_name='$campo_attuale_fk'";
			$q_vis_fk=$vmreg->query($sql_vis_fk);

			list($in_visibile_fk)=$vmreg->fetch_row($q_vis_fk);

			$campo_fk_visibile = ($in_visibile_fk) ? true:false;

		}
		else{
			$campo_fk_visibile=true;
		}




		for($i=0;$i<count($colonne_parent);$i++){

			$aggiunta_sub_parent = ($parent_consigliata==$colonne_parent[$i]) ?	 " (consigliata)" : "";

			$sel_sub_parent = ($colonne_parent[$i]==$sottomaschere['campo_pk_parent'][$k_sub]) ? "selected=\"selected\"" :"";

			echo "\t\t<option value=\"".$colonne_parent[$i]."\" $sel_sub_parent>".$colonne_parent[$i].$aggiunta_sub_parent."</option>\n";
		}

		echo "</select>\n";



		echo " ---<strong>&gt;</strong> ".$sottomaschere['nome_tabella'][$k_sub].".";

		echo "<select name=\"sub_gen[sub_fk_this]\">\n";

		$OPT_SUB_THIS="";
		$OPT_SUB_THIS_ORDER="";

		for($j=0;$j<count($colonne_this);$j++){

			$aggiunta_sub_this = ($this_consigliata==$colonne_this[$j]) ?	 " (consigliata)" : "";

			$sel_sub_this = ($colonne_this[$j]==$sottomaschere['campo_fk_sub'][$k_sub]) ? "selected=\"selected\"" :"";

			$sel_sub_this_ord = ($colonne_this[$j]==$sottomaschere['orderby_sub'][$k_sub]) ? "selected=\"selected\"" :"";


			$OPT_SUB_THIS.= "\t\t<option value=\"".$colonne_this[$j]."\" $sel_sub_this>".$colonne_this[$j].$aggiunta_sub_this."</option>\n";
			$OPT_SUB_THIS_ORDER.= "\t\t<option value=\"".$colonne_this[$j]."\" $sel_sub_this_ord>".$colonne_this[$j]."</option>\n";
		}

		echo $OPT_SUB_THIS;

		echo "</select><div class=\"info-campo\">"._("Set the connection between the table records and the subform records")."</div>\n";





		// MOSTRA AVVISO IN CASO DI CAMPO NON VISIBILE

		if(!$campo_fk_visibile){

			echo "<span style=\"color:red;font-weight:bold;\">
				"._("WARNING! The selected field in the parent table is not the primary key and is not set as visible!")." "._('When you set a field that is not the primary key, it must be visible. In this case the connection to the table will not work <br /> Set the field as visible (can also be a hidden field) from the Field Settings')."</span><br /><br />\n";
		}
		//---------------------------------------------






		$sub_select_check= ($sottomaschere['sub_select'][$k_sub]=='1') ? "checked=\"checked\"" : "";
		$sub_insert_check= ($sottomaschere['sub_insert'][$k_sub]=='1') ? "checked=\"checked\"" : "";
		$sub_update_check= ($sottomaschere['sub_update'][$k_sub]=='1') ? "checked=\"checked\"" : "";
		$sub_delete_check= ($sottomaschere['sub_delete'][$k_sub]=='1') ? "checked=\"checked\"" : "";
		$tipo_vista_check1= ($sottomaschere['tipo_vista'][$k_sub]=='tabella') ? "checked=\"checked\"" : "";
		$tipo_vista_check2= ($sottomaschere['tipo_vista'][$k_sub]=='scheda') ? "checked=\"checked\"" : "";
		$tipo_vista_check3= ($sottomaschere['tipo_vista'][$k_sub]=='embed') ? "checked=\"checked\"" : "";
		$tipo_vista_check4= ($sottomaschere['tipo_vista'][$k_sub]=='schedash') ? "checked=\"checked\"" : "";

		$sel_orderby_sub_sort_ASC = ($sottomaschere['orderby_sub_sort'][$k_sub]=="ASC") ? "selected=\"selected\"" : "";
		$sel_orderby_sub_sort_DESC = ($sottomaschere['orderby_sub_sort'][$k_sub]=="DESC") ? "selected=\"selected\"" : "";

		// imposta un default su SCHEDA
		if($tipo_vista_check1=='' && $tipo_vista_check2=='' && $tipo_vista_check3=='') 
			$tipo_vista_check2="checked=\"checked\"";

		if($sottomaschere['tipo_vista'][$k_sub]=='embed'){

			$in_disabled='disabled="disabled"';
			$up_disabled='disabled="disabled"';
			$del_disabled='disabled="disabled"';

		}
		else{
			$in_disabled='';
			$up_disabled='';
			$del_disabled='';

		}


		echo "

		<label for=\"nome_frontend\">"._("Subform name")."</label><br/> 
		<input type=\"text\" name=\"sub_gen[nome_frontend]\"  id=\"nome_frontend\" value=\"".$sottomaschere['nome_frontend'][$k_sub]."\" maxlength=\"240\" size=\"35\" />
		<div class=\"info-campo\">"._("Subform name shown to users")."</div>
		";


		echo "<div>
		<label for=\"tipo_vista_1\">"._('Display style').": </label><br />
			<div>
			<input type=\"radio\" name=\"sub_gen[tipo_vista]\" class=\"sub_gen_tv\" id=\"tipo_vista_1\"  value=\"scheda\" $tipo_vista_check2 />
                <label for=\"tipo_vista_1\">"._("Form (in new window)")."</label><br />"
			."<input type=\"radio\" name=\"sub_gen[tipo_vista]\" class=\"sub_gen_tv\" id=\"tipo_vista_4\"  value=\"schedash\" $tipo_vista_check4 />
                <label for=\"tipo_vista_4\">"._("Form (in a shadowbox)")."</label><br />"
			// ."<input type=\"radio\" name=\"sub_gen[tipo_vista]\"  id=\"tipo_vista_2\" value=\"table\" $tipo_vista_check1 /> "._("Table")." <br />"
			."<input type=\"radio\" name=\"sub_gen[tipo_vista]\" class=\"sub_gen_tv\" id=\"tipo_vista_3\" value=\"embed\" $tipo_vista_check3 />
                <label for=\"tipo_vista_3\">"._("Embedded table (read only)")."</label><br />
			<br />
			</div>
		</div>";
		//."<div class=\"info-campo\">Modalit&agrave; di visualizzazione dei dati</div>*/

		// echo "<input type=\"hidden\" name=\"sub_gen[tipo_vista]\"  id=\"tipo_vista_1\"  value=\"scheda\" />\n";

		echo "
		<input type=\"hidden\" name=\"sub_gen[sub_select]\" value=\"0\" />
		<input type=\"checkbox\" name=\"sub_gen[sub_select]\" id=\"sub_select\" value=\"1\" $sub_select_check/>
		<label for=\"sub_select\">"._("Visible (SELECT)")."</label>
		<div class=\"info-campo\">"._("Sets whether submask can be visible for this group")."</div>

		<input type=\"hidden\" name=\"sub_gen[sub_insert]\" value=\"0\" />
		<input $in_disabled type=\"checkbox\" name=\"sub_gen[sub_insert]\" id=\"sub_insert\" value=\"1\" $sub_insert_check/>
		<label for=\"sub_insert\">"._("Insert privileges")."</label>
		<div class=\"info-campo\">"._("Sets whether records can be inserted via subforms")."</div>

		<input type=\"hidden\" name=\"sub_gen[sub_update]\" value=\"0\" />
		<input $up_disabled type=\"checkbox\" name=\"sub_gen[sub_update]\" id=\"sub_update\" value=\"1\" $sub_update_check/>
		<label for=\"sub_update\">"._("Update privileges")."</label>
		<div class=\"info-campo\">"._("Sets whether records can be updated via subforms")."</div>

		<input type=\"hidden\" name=\"sub_gen[sub_delete]\" value=\"0\" />
		<input $del_disabled type=\"checkbox\" name=\"sub_gen[sub_delete]\" id=\"sub_delete\" value=\"1\" $sub_delete_check/>
		<label for=\"sub_delete\">"._("Delete privileges")."</label>
		<div class=\"info-campo\">"._("Sets whether records can be deleted")."</div>

		<label for=\"orderby_sub\">"._("Order subform records by:")."</label>
		<select id=\"orderby_sub\" name=\"sub_gen[orderby_sub]\">
			$OPT_SUB_THIS_ORDER
		</select>

		<select id=\"orderby_sub_sort\" name=\"sub_gen[orderby_sub_sort]\">
			<option value=\"ASC\" $sel_orderby_sub_sort_ASC>"._("Ascending")."</option>
			<option value=\"DESC\" $sel_orderby_sub_sort_DESC>"._("Descending")."</option>
		</select>		
		<div class=\"info-campo\">"._("Set the sort criteria for the subform")."</div>



		<label for=\"max_records\">"._("Max records in the subform")."</label><br/> 
		<input type=\"text\" name=\"sub_gen[max_records]\"  id=\"max_records\" value=\"".$sottomaschere['max_records'][$k_sub]."\" maxlength=\"3\" size=\"5\" />
		<div class=\"info-campo\">"._("Maximum number of records for the subform. You should not set this number too high, as it could slow down loading of the subform.")."</div>

		<input type=\"hidden\" name=\"sub_gen[id_submask]\" value=\"".$sottomaschere['id_submask'][$k_sub]."\" />
		<input type=\"hidden\" name=\"sub_gen[oid]\" value=\"".intval($_GET['det'])."\" />
		<input type=\"hidden\" name=\"sub_gen[gid]\" value=\"".intval($_GET['gid'])."\" />

		<input type=\"submit\" name=\"invia_sub_generali\" value=\""._("Save subform general settings")."\" />

		";

		echo "</form>\n";







		###############################################################################################################
		#
		#	IMPOSTAZIONE CAMPI PER LA SOTTOMASCHERA
		#
		#
		#


		if($sottomaschere['data_modifica'][$k_sub]!=''){

			echo "<a name=\"submask_field\"></a>\n";

			echo "<h3 style=\"margin-top:60px;border-bottom:1px solid #CCC;width:75%;\">"._("Setting for subform fields")." <span class=\"var\">".$sottomaschere['nome_tabella'][$k_sub]."</span></h3>\n";


			echo "\t<form method=\"post\" action=\"" . Common::phpself() . "?gid=".$_GET['gid']."&amp;mod_sub_campi\">\n";



			# Inizia a prendere i campi

			$id_submask= (isset($_GET['conf_sub'])) ? $_GET['conf_sub']:0;


			$sql_sub_cols = "SELECT  c.*

								FROM {$db1['frontend']}{$db1['sep']}registro_submask_col c, {$db1['frontend']}{$db1['sep']}registro_submask t
								-- WHERE t.nome_tabella='".$sottomaschere['nome_tabella'][$k_sub]."'
								WHERE t.id_submask=$id_submask 
								AND t.id_table=$oid
								AND t.id_submask = c.id_submask
								ORDER BY c.ordinal_position";

			$q_sub_cols=$vmreg->query($sql_sub_cols);

			$matrice_sub_col=$vmreg->fetch_assoc_all($q_sub_cols);

			$matrice_sub_rev=$vmreg->reverse_matrix($matrice_sub_col);

			$array_sub_pk= RegTools::prendi_all_PK($sottomaschere['nome_tabella'][$k_sub],0);

			while(list($k,$array_sub_val)=each($matrice_sub_col)){


				if($array_sub_val['column_name']==$sottomaschere['campo_fk_sub'][$k_sub]){


					echo tratta_campo_submask($array_sub_val, $array_sub_pk, $rif_sub[0], $rif_sub[1],$sottomaschere['campo_pk_parent'][$k_sub],$oid);


				}
				else{
					echo tratta_campo_submask($array_sub_val, $array_sub_pk, $rif_sub[0], $rif_sub[1]);
				}
			}

			echo "\t\t<input type=\"hidden\" name=\"oid\" value=\"".intval($_GET['det'])."\" />\n";
			echo "\t\t<input type=\"hidden\" name=\"gid\" value=\"".intval($_GET['gid'])."\" />\n";

			echo "\t\t<input type=\"hidden\" name=\"id_submask\" value=\"".intval($_GET['conf_sub'])."\" />\n";

			echo "\t\t<input type=\"button\" name=\"invia_campi\" value=\""._("Save field settings")."\" onclick=\"submit()\" />\n";

			echo "\t</form>\n";

		}


	}


	echo "</div>\n";


	//-- fine impostazioni sottomaschere