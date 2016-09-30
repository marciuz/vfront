<?php


	
	##############################################################
	#
	#	 FORM IMPOSTAZIONI WIDGET
	#
	#
	#
	
	$st_sub = (isset($_GET['a']) && $_GET['a']==6) ? "" : "display:none;"; 
	
	echo "<div class=\"widget\" id=\"widget\" style=\"$st_sub\">\n";
	
	$q_widget=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}widget WHERE id_table=$oid");
	
	$n_widget=$vmreg->num_rows($q_widget);
	
	$qstring_widget=$_SERVER['PHP_SELF']."?det=".intval($_GET['det'])."&amp;gid=".intval($_GET['gid'])."&amp;a=6";
	
	$widget_order='';
		
		
	// Form new|modify widget
	
	if(isset($_GET['modwidget'])){
		
		$q_widget=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}widget WHERE id_widget=".intval($_GET['modwidget']));
		
		$RSb=$vmreg->fetch_assoc($q_widget);
		
		$display_form=true;
		$display_table=false;
		
		$widget_reset="<input type=\"button\" id=\"widget_reset\" value=\" "
					._('Cancel')." \" onclick=\"location.href='".
					$_SERVER['PHP_SELF']."?det=".$_GET['det']."&amp;gid=".$_GET['gid']."&amp;a=6'\" />";
					
		$sel_widget_type=array();
	
		$sel_widget_type[0]= ($RSb['widget_type']=='submask') ? "selected=\"selected\"" :'';
		$sel_widget_type[1]= ($RSb['widget_type']=='image') ? "selected=\"selected\"" :'';
		$sel_widget_type[3]= ($RSb['widget_type']=='openlayers') ? "selected=\"selected\"" :'';
		$sel_widget_type[3]= ($RSb['widget_type']=='gmaps') ? "selected=\"selected\"" :'';
		
		
	}
	else{
		
		echo "<p><span class=\"fakelink\" onclick=\"mostra_nascondi('form_new_widget_cont')\">"
		._("Create new widget")."</span></p>";
		
		$RSb=array();
		$RSb['settings']='';
		
		$display_form=false;
		$display_table=true;
		
		$widget_reset="<input type=\"button\" id=\"widget_reset\" value=\" "
					._('Cancel')." \" onclick=\"nascondi('form_new_widget_cont');\" />";
					
		$sel_widget_type=array('','','','');
		
	}
	
	
	$legend_new_widget = (isset($_GET['modwidget'])) ? _('Modify widget') : _('New widget');
	
	$widget_bg_color = (isset($_GET['modwidget'])) ? str_replace("#",'',$RSb['background']) : 'CFF2FF';
	$widget_color = (isset($_GET['modwidget'])) ? str_replace("#",'',$RSb['color']) : '000000';
	
	$widget_name = (isset($_GET['modwidget'])) ? $RSb['widget_name'] : '';
	
	$widget_action_label = (isset($_GET['modwidget'])) ? _('Modify widget') : _('Create new widget');
	$widget_action_name = (isset($_GET['modwidget'])) ? 'modwidget' : 'newwidget';
	$widget_action_value = (isset($_GET['modwidget'])) ? intval($_GET['modwidget']) : '1';
	
	
	$show_form_widget = ($display_form) ? "" : "style=\"display:none\"";
	
	
	// Adv option - only shadowbox
	if(isset($RSb['widget_type']) && $RSb['widget_type']=='link_shadow'){
		
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
	
	$widget_opt_height=(isset($_GET['modwidget']) && isset($adv_settings['height'])) ?  $adv_settings['height'] : '';
	$widget_opt_width=(isset($_GET['modwidget']) && isset($adv_settings['width'])) ? $adv_settings['width'] : '';
	
	$campi_tabella_pc=array();
	
	for($i=0;$i<count($campi_tabella);$i++){
		
		$campi_tabella_pc[]=$campi_tabella[$i];
		
		if($in_tipo01[$i]=='select_from'){
			$campi_tabella_pc[]=$campi_tabella[$i].":label";
		}
	}
	
	echo "
	<div id=\"form_new_widget_cont\" $show_form_widget>
	
		<form id=\"form_new_widget\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
	
		
			<fieldset id=\"form_new_widget_fs\" style=\"max-width:780px;\">
				
				<legend>".$legend_new_widget."</legend>
	
				
				<p>
					<label for=\"widget_name\">"._('widget label').":</label>
					<input type=\"text\" size=\"25\" name=\"widget[widget_name]\" id=\"widget_name\" value=\"$widget_name\" />	
				</p>
				
				
				
				<div id=\"advanced_widgets_opt\">
				
					<p>
						<label for=\"widget_opt_height\">height</label>
						<input type=\"text\" name=\"widget_opt[height]\" size=\"10\" id=\"widget_opt_height\" value=\"$widget_opt_height\" /> px
					</p>
					
					<p>
						<label for=\"widget_opt_width\">width</label>
						<input type=\"text\" name=\"widget_opt[width]\" size=\"10\" id=\"widget_opt_width\" value=\"$widget_opt_width\" /> px
					</p>
				
				</div>
				
				<p>
					<label for=\"widget_position\">"._('Position').":</label>
                                        <select name=\"widget[position]\" id=\"widget_position\" value=\"$widget_order\" >
                                                 
                                             <option value=\"top\" >"._('Top')."</option>
                                             <option value=\"bottom\" >"._('Bottom')."</option>
                                                        
                                        </select>
				</p>
                                
                                <p>
					<label for=\"widget_type\">"._('widget type').":</label>
					<select id=\"widget_type\" name=\"widget[widget_type]\"  >
						<option value=\"\" >"._('Select -&gt;')."</option>
						<option value=\"submask\" {$sel_widget_type[0]}>"._('Submask contents')."</option>
						<option value=\"image\" {$sel_widget_type[1]}>"._('Embedded Image')."</option>
						<option value=\"openlayers\" {$sel_widget_type[2]}>"._('Open Layers')."</option>
						<option value=\"gmaps\" {$sel_widget_type[3]}>"._('Google Map')."</option>
					</select>	
				</p>
				
                                <div class=\"widget_options\" style=\"display:none;\" id=\"submask-options\">
                                    <h4>Submask options</h4>

                                </div>
				
                                <div class=\"widget_options\" style=\"display:none;\" id=\"image-options\">
                                    <h4>Image options</h4>

                                </div>
				
                                <div class=\"widget_options\" style=\"display:none;\" id=\"gmaps-options\">
                                    <h4>Google maps options</h4>

                                </div>
				
                                <div class=\"widget_options\" style=\"display:none;\" id=\"openlayers-options\">
                                    <h4>Openalyer options</h4>

                                </div>
				
				<div>
					<div style=\"float:left; width:480px;\">
						<label for=\"widget_definition\">"._('Link definition').":</label><br />"
						//."<textarea cols=\"60\" rows=\"3\" id=\"widget_definition\" name=\"widget[definition]\">".$RSb['definition']."</textarea>"
						."<div class=\"info-campo\">"
							._('The link (with or without http://) to be opened by the widget')."<br />"
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
					<input type=\"hidden\" name=\"widget[id_table]\" value=\"".intval($_GET['det'])."\" />
					<input type=\"hidden\" name=\"$widget_action_name\" value=\"$widget_action_value\" />
					<input type=\"hidden\" name=\"gid\" value=\"".intval($_GET['gid'])."\" />
					<input type=\"submit\" id=\"widget_submit\" value=\" ".$widget_action_label." \" />	
					
					$widget_reset	
				</p>
	
			</fieldset>
	
		</form>
	
	</div>";
		
		
		
		
		
		
		
		
		
		
		
	if($n_widget==0){
		
		echo "<p><strong>"._('There are no widgets for this table')."</strong></p>\n";
	}
	else{
		
		$show_widget_table = ($display_table) ? "" : "style=\"display:none\"";
		
		// mostra riepilogo pulsanti
		
		echo "<table summary=\"widgets\" id=\"table-widgets\" $show_widget_table class=\"table-alt\" >\n";
		
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
				<td>".$RSpp['id_widget']."</td>
				<td>".$RSpp['widget_name']."</td>
				<td>".$RSpp['widget_type']."</td>
				<td><a href=\"".$qstring_widget."&amp;modwidget=".$RSpp['id_widget']."\">"._('modify')."</a></td>
				<td><a href=\"".$qstring_widget."&amp;delwidget=".$RSpp['id_widget']."\">"._('delete')."</a></td>
			</tr>\n";
			
			$c++;
		}
		
		echo "</table>\n";
	}
	
	
	echo "</div>\n";
	
	
        
        
	//-- fine impostazioni pulsanti speciali
	
	
	
	
	
	
