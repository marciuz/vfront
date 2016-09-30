<?php


##############################################################
#
#	 FORM IMPOSTAZIONI PULSANTI SPECIALI
#
#
#
        
        $st_sub = (isset($_GET['a']) && $_GET['a']==5) ? "" : "display:none;"; 
	
	echo "<div class=\"tabella-pulsanti\" id=\"tabella-pulsanti\" style=\"$st_sub\">\n";
	
	$q_pulsanti=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}button WHERE id_table=$oid");
	
	$n_button=$vmreg->num_rows($q_pulsanti);
	
	$qstring_button=$_SERVER['PHP_SELF']."?det=".intval($_GET['det'])."&amp;gid=".intval($_GET['gid'])."&amp;a=5";
	
		
		
		
	// Form new|modify button
	
	if(isset($_GET['modbutton'])){
		
		$q_button=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}button WHERE id_button=".intval($_GET['modbutton']));
		
		$RSb=$vmreg->fetch_assoc($q_button);
		
		$display_form=true;
		$display_table=false;
		
		$button_reset="<input type=\"button\" id=\"button_reset\" value=\" "
					._('Cancel')." \" onclick=\"location.href='".
					$_SERVER['PHP_SELF']."?det=".$_GET['det']."&amp;gid=".$_GET['gid']."&amp;a=5'\" />";
					
		$sel_button_type=array();
	
		$sel_button_type[0]= ($RSb['button_type']=='link_self') ? "selected=\"selected\"" :'';
		$sel_button_type[1]= ($RSb['button_type']=='link_blank') ? "selected=\"selected\"" :'';
		$sel_button_type[2]= ($RSb['button_type']=='link_shadow') ? "selected=\"selected\"" :'';
		
		
	}
	else{
		
		echo "<p><span class=\"fakelink\" onclick=\"mostra_nascondi('form_new_button_cont')\">"
		._("Create new button")."</span></p>";
		
		$RSb=array();
		$RSb['definition']='';
		
		$display_form=false;
		$display_table=true;
		
		$button_reset="<input type=\"button\" id=\"button_reset\" value=\" "
					._('Cancel')." \" onclick=\"nascondi('form_new_button_cont');\" />";
					
		$sel_button_type=array('','','');
		
	}
	
	$legend_new_button = (isset($_GET['modbutton'])) ? _('Modify custom button') : _('New custom button');
	
	$button_bg_color = (isset($_GET['modbutton'])) ? str_replace("#",'',$RSb['background']) : 'CFF2FF';
	$button_color = (isset($_GET['modbutton'])) ? str_replace("#",'',$RSb['color']) : '000000';
	
	$button_name = (isset($_GET['modbutton'])) ? $RSb['button_name'] : '';
	
	$button_action_label = (isset($_GET['modbutton'])) ? _('Modify button') : _('Create new button');
	$button_action_name = (isset($_GET['modbutton'])) ? 'modbutton' : 'newbutton';
	$button_action_value = (isset($_GET['modbutton'])) ? intval($_GET['modbutton']) : '1';
	
	
	$show_form_button = ($display_form) ? "" : "style=\"display:none\"";
	
	
	// Adv option - only shadowbox
	if(isset($RSb['button_type']) && $RSb['button_type']=='link_shadow'){
		
		$adv_opt=true;
		
		$opt1=explode("&",$RSb['settings']);
		
		if(is_array($opt1)){
			
			foreach ($opt1 as $val) {

				if(strstr($val,"=")!==false){

					list($k,$v)=explode("=",$val);
					$adv_settings[$k]=$v;
				}
			}
		}
		
	}
	else{
		$adv_opt=false;
	}
	
	$button_opt_height=(isset($_GET['modbutton']) && isset($adv_settings['height'])) ?  $adv_settings['height'] : '';
	$button_opt_width=(isset($_GET['modbutton']) && isset($adv_settings['width'])) ? $adv_settings['width'] : '';
	
	$vis_adv_option=($adv_opt) ? '':'display:none';
	
	$campi_tabella_pc=array();
	
	for($i=0;$i<count($campi_tabella);$i++){
		
		$campi_tabella_pc[]=$campi_tabella[$i];
        
		if($in_tipo01[$i]=='select_from' || $in_tipo01[$i]=='autocompleter_from' ) {
			$campi_tabella_pc[]=$campi_tabella[$i].":label";
		}
	}
	
	echo "
	<div id=\"form_new_button_cont\" $show_form_button>
	
		<form id=\"form_new_button\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
	
		
			<fieldset id=\"form_new_button_fs\" style=\"max-width:780px;\">
				
				<legend>".$legend_new_button."</legend>
	
				
				<p>
					<label for=\"button_name\">"._('Button label').":</label>
					<input type=\"text\" size=\"25\" name=\"button[button_name]\" id=\"button_name\" value=\"$button_name\" />	
				</p>
				
				<p>
					<label for=\"button_type\">"._('Button type').":</label>
					<select id=\"button_type\" name=\"button[button_type]\" onchange=\"if($(this).value=='link_shadow'){ $('advanced_buttons_opt').show();} else{ $('advanced_buttons_opt').hide();} \" >
						<option value=\"link_blank\" {$sel_button_type[1]}>"._('Open dynamic link in new window')."</option>
						<option value=\"link_self\" {$sel_button_type[0]}>"._('Open dynamic link in same window')."</option>
						<option value=\"link_shadow\" {$sel_button_type[2]}>"._('Open dynamic link on shadowbox layer')."</option>
					</select>	
					
				</p>
				
				<div id=\"advanced_buttons_opt\" style=\"$vis_adv_option\">
				
					<p>
						<label for=\"button_opt_height\">height</label>
						<input type=\"text\" name=\"button_opt[height]\" size=\"10\" id=\"button_opt_height\" value=\"$button_opt_height\" /> px
					</p>
					
					<p>
						<label for=\"button_opt_width\">width</label>
						<input type=\"text\" name=\"button_opt[width]\" size=\"10\" id=\"button_opt_width\" value=\"$button_opt_width\" /> px
					</p>
				
				</div>
				
				<p>
					<label for=\"button_bg_color\">"._('Background color for the button').":</label>
					<input class=\"color\" type=\"text\" size=\"10\" name=\"button[background]\" id=\"button_bg_color\" value=\"$button_bg_color\" />	
				</p>
				
				<p>
					<label for=\"button_color\">"._('Label color for the button').":</label>
					<input class=\"color\" type=\"text\" size=\"10\" name=\"button[color]\" id=\"button_color\" value=\"$button_color\" />	
				</p>
				
				
				<div>
					<div style=\"float:left; width:480px;\">
						<label for=\"button_definition\">"._('Link definition').":</label><br />
						<textarea cols=\"60\" rows=\"3\" id=\"button_definition\" name=\"button[definition]\">".$RSb['definition']."</textarea>			
						<div class=\"info-campo\">"
							._('The link (with or without http://) to be opened by the button')."<br />"
							._('You can insert here variables referring to the table, with double braces {{  }}, ')."<br />"
							._('for example http://www.google.com/?q={{your_field_name}} , or')
							._('xml/your_table_name/id/{{ID}}/')."
						</div>
							
					</div>
					
					<div style=\"float:left; width:140px; border-left:1px solid #999;padding-left:1em;\">
					
						"._('Available fields').":<br />".
						
						"<ul><li>{{".implode("}}</li><li>{{",$campi_tabella_pc)."}}</li></ul>
							
							
					
					</div>
				
				</div>
				
				<p style=\"clear:left\">
					<input type=\"hidden\" name=\"button[id_table]\" value=\"".intval($_GET['det'])."\" />
					<input type=\"hidden\" name=\"$button_action_name\" value=\"$button_action_value\" />
					<input type=\"hidden\" name=\"gid\" value=\"".intval($_GET['gid'])."\" />
					<input type=\"submit\" id=\"button_submit\" value=\" ".$button_action_label." \" />	
					
					$button_reset	
				</p>
	
			</fieldset>
	
		</form>
	
	</div>";
		
		
		
		
		
		
		
		
		
		
		
	if($n_button==0){
		
		echo "<p><strong>"._('There are no custom buttons for this table')."</strong></p>\n";
	}
	else{
		
		$show_button_table = ($display_table) ? "" : "style=\"display:none\"";
		
		// mostra riepilogo pulsanti
		
		echo "<table summary=\"buttons\" id=\"table-buttons\" $show_button_table class=\"table-alt\" >\n";
		
		echo "
		<tr>
			<th>ID</th>
			<th>"._('name')."</th>
			<th>"._('type')."</th>
			<th>"._('modify')."</th>
			<th>"._('delete')."</th>
		</tr>\n";
		
		$c=0;
		
		while($RSpp=$vmreg->fetch_assoc($q_pulsanti)){
			
			$class_gr=($c%2==0) ? "class=\"gr\"" :'';
			
			echo "
			<tr $class_gr>
				<td>".$RSpp['id_button']."</td>
				<td>".$RSpp['button_name']."</td>
				<td>".$RSpp['button_type']."</td>
				<td><a href=\"".$qstring_button."&amp;modbutton=".$RSpp['id_button']."\">"._('modify')."</a></td>
				<td><a href=\"".$qstring_button."&amp;delbutton=".$RSpp['id_button']."\">"._('delete')."</a></td>
			</tr>\n";
			
			$c++;
		}
		
		echo "</table>\n";
	}
	
	
	echo "</div>\n";
	
	//-- fine impostazioni pulsanti speciali