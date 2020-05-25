<?php

/**
 * Description of class
 *
 * @author marcello
 */
class Log {


    /**
     * Qualora il record sia stato creato e gestito dalle maschere, 
     * viene riportata tutta la sua storia: inserimento, modifiche, etc.
     * 
     * @desc Show the history of a record
     */
    public function show_history($id_record, $table_name) {

        global $vmreg, $db1;

        $files = array("sty/admin.css", "sty/tabelle.css", "js/mostra_nascondi_id.js", "sty/log.css");

        $ID_RECORD = (int) $id_record;

        echo openLayout1(_("Database records history"), $files);

        echo breadcrumbs(array("HOME", "ADMIN", "log.php" => _("database log"), _("record history")));

        echo "<h1>" . _("Record history") . "</h1>\n";

        // Query Storico
        $sql_log = "SELECT log.id_log,
                        log.op,
                        log.tabella,
                        log.data,
                        log.uid,
                        " . $vmreg->concat("log.gid, ' (',g.nome_gruppo,')'", 'gruppo_desc') . ",
                        log.id_record,
                        log.fonte,
                        " . $vmreg->concat("u.nome, ' ',u.cognome", 'nomecognome') . "


                    FROM {$db1['frontend']}{$db1['sep']}log log
                    INNER JOIN {$db1['frontend']}{$db1['sep']}utente u ON u.id_utente=log.uid
                    INNER JOIN {$db1['frontend']}{$db1['sep']}gruppo g ON u.gid=g.gid

            WHERE 1=1
            AND u.gid=g.gid
            AND log.id_record='$ID_RECORD'
            AND log.tabella='".$vmreg->escape($table_name)."'
            ORDER BY log.data ASC
	 			";
        $q_log = $vmreg->query($sql_log);


    #########################################################################
    #
    #	CONTINUA A STAMPARE
    #

        echo "<table class=\"tab-color\" summary=\"" . _("Log table") . "\">

	 	<tr>
			<th class=\"grigia\">" . _("date") . "</th>
			<th class=\"grigia\">" . _("operation") . "</th>
			<th class=\"grigia\">" . _("table") . "</th>
			<th class=\"grigia\">" . _("user") . "</th>
			<th class=\"grigia\">" . _("group") . "</th>
			<th class=\"grigia\">" . _("id_record") . "</th>
			<th class=\"grigia\">" . _("source") . "</th>
			<th class=\"grigia\">" . _("details") . "</th>
		</tr>

		";

        while ($RSlog = $vmreg->fetch_assoc($q_log)) {

            $data = VFDate::date_encode($RSlog['data'], true, 'string');

            echo "
	 	<tr class=\"colore-" . $RSlog['op'] . "\" >
			<td>" . $data . "</td>
			<td>" . $RSlog['op'] . "</td>
			<td>" . $RSlog['tabella'] . "</td>
			<td>" . $RSlog['nomecognome'] . "</td>
			<td>" . $RSlog['gruppo_desc'] . "</td>
			<td>" . $RSlog['id_record'] . "</td>
			<td>" . $RSlog['fonte'] . "</td>
			<td><a href=\"log.php?dettaglio=" . $RSlog['id_log'] . "\">" . _("details") . "</a></td>
		</tr>
		 ";
        }

        echo "</table>\n";

        echo closeLayout1();
    }

    /**
     * Funzione di parsing delle istruzioni UPDATE 
     * Restituisce un array con chiave tabella e un array modifiche con campo=>valore inserito
     *
     * @param string $sql SQL da analizzare
     * @return array Array con frammenti di SQL
     */
    private function OLD_parser_sql_update($sql) {

        global $vmreg;

        $sql = str_replace(array("\n", "\r"), " ", $sql);

        $sql = preg_replace("|LIMIT .*$|i", '', $sql);

        preg_match("# *UPDATE +([a-z_]+) +SET(.+?) WHERE +(.+) *#i", $sql, $sql_frag);

        $out = array();

        if (count($sql_frag) == 4) {

            //1 - tabella
            //2 - modifiche
            //3 - condizioni

            $out['tabella'] = trim($sql_frag[1]);

            // fa l'escape in base ad DB scelto
            $find_apici = $vmreg->escape("'");

            $replaced_sql = preg_replace("|(?<!=)$find_apici|", "[[[apici]]]", $sql_frag[2]);

            preg_match_all("#(?<field>[\w]+)\s*=\s*(?<value>[0-9]+|'[^']*')(?:,|)#", $replaced_sql, $ff);

            $arr_modifiche = array();

            if (count($ff['value']) > 0) {

                for ($i = 0; $i < count($ff['value']); $i++) {

                    if (!is_numeric($ff['value'][$i])) {

                        // elimino gli apici, rimetto gli apici escaped
                        $v = str_replace("[[[apici]]]", $find_apici, substr($ff['value'][$i], 1, -1));

                        $ff['value'][$i] = $vmreg->unescape($v);
                    }

                    if (VFRONT_DBTYPE == 'oracle') {
                        $arr_modifiche[strtolower(trim($ff['field'][$i]))] = $ff['value'][$i];
                    } else {
                        $arr_modifiche[trim($ff['field'][$i])] = $ff['value'][$i];
                    }
                }
            }
        }

        $out['modifiche'] = $arr_modifiche;

        return $out;
    }

    private function parser_sql_update($sql) {

        require_once(FRONT_ROOT."/plugins/php-sql-parser/src/PHPSQLParser.php");

        $Parser = new PHPSQLParser();

        $psql = $Parser->parse($sql);

        $out['tabella'] = $psql['UPDATE'][0]['no_quotes'];
        $out['modifiche'] = array();

        if(isset($psql['SET']) && count($psql['SET'])>0) {
            foreach($psql['SET'] as $up) {

                $k = $up['sub_tree'][0]['no_quotes'];
                $v = $up['sub_tree'][2]['base_expr'];

                if($v{0} == "'" && substr($v, -1, 1) == "'") {
                    $v = substr($v, 1, strlen($v)-2);
                }

                $out['modifiche'][$k] = $v;
            }
        }

        return $out;
    }

    /**
     * Funzione di parsing delle istruzioni INSERT
     * Restituisce un array con chiave tabella e un array campo=>valore
     *
     * @param string $sql SQL da analizzare
     * @return array Array con associazione di campi e valori
     */
    private function parser_sql_insert($sql) {

        $sql = str_replace(array("\n", "\r"), "", $sql);

        preg_match("| *INSERT +INTO +([a-z_]+) +\((.+?)\) VALUES +\('?(.+?)'?\)|i", $sql, $sql_frag);

        $out = array();

        if (count($sql_frag) == 4) {

            //1 - tabella
            //2 - campi
            //3 - valori

            $out['tabella'] = trim($sql_frag[1]);

            $campi = explode(",", trim($sql_frag[2]));
            $valori = explode("','", trim($sql_frag[3]));

            $out['campi'] = $campi;
            $out['valori'] = $valori;
        }

        return $out;
    }

    /**
     * Raccoglie informazioni sull'operazione 
     * presente nel log e restituisce un array con la tabella HTML generata 
     * e una variabile boolean per la reversibilit� dell'operazione
     *
     * @param resource $RS Recordset
     * @return array Array con la tabella HTML generata e una variabile boolean per la reversibilit� dell'operazione
     * @see function mostra_dettaglio_log
     */
    private function info_tabella_operazione($RS) {

        global $vmsql, $vmreg, $db1;

        if (VFRONT_DBTYPE == 'oracle') {
            $RS['uid'] = $RS['id_user'];
        }

        $presenza_id = (intval($RS['id_record']) > 0 || strlen($RS['id_record']) > 2);

        $fonte = ($RS['fonte'] == "m") ? _('form') : _('subform');

        switch ($RS['op']) {


            case 'update':

                $storico_pre = is_array(unserialize($RS['storico_pre']));
                $storico_post = count($this->parser_sql_update($RS['storico_post'])) == 2;

                if ($storico_pre && $storico_post && $presenza_id) {

                    $campo_pk = RegTools::prendi_PK($RS['tabella']);

                    if ($vmsql->test_id($campo_pk, $RS['id_record'], $RS['tabella'], '', true)) {
                        $reversibile = "<span class=\"verde\">" . _("reversible") . "</span>\n";
                        $is_reversibile = true;
                    } else {
                        $reversibile = "<span class=\"rosso\">" . _("irreversible") . "</span> (<strong>" . _("the record has been deleted") . "</strong>, " . _("Please rollback the delete operation before") . ")\n";
                        $is_reversibile = false;
                    }
                } else {
                    $reversibile = "<span class=\"rosso\">" . _("irreversible") . "</span>\n";
                    $is_reversibile = false;
                }
                break;


            case 'insert':

                $storico_post = count($this->parser_sql_insert($RS['storico_post'])) == 2;


                $reversibile = "<span class=\"verde\">" . _("reversible (delete the record)") . "</span>\n";
                $is_reversibile = true;

                break;

            case 'delete':
                $storico_pre = is_array(unserialize($RS['storico_pre']));


                if ($storico_pre) {

                    $reversibile = "<span class=\"verde\">" . _("reversible (reinsert)") . "</span>\n";
                    $is_reversibile = true;
                } else {
                    $reversibile = "<span class=\"rosso\">" . _("irreversible") . "</span>\n";
                    $is_reversibile = false;
                }
                break;
        }


        $info_operazione = "

			<table id=\"info_log\" summary=\"" . _("information about action") . "\" border=\"1\">
				<tr>
					<th colspan=\"2\" style=\"text-align:left\"><h3>" . _("Summary") . " <span class=\"var\">" . strtoupper($RS['op']) . "</span></h3></th>
				</tr>			
				<tr>
					<th>" . _("id_log") . "</th>
					<td>" . $RS['id_log'] . "</td>
				</tr>
				<tr>
					<th>" . _("date/time") . "</th>
					<td>" . $RS['data'] . "</td>
				</tr>
				<tr>
					<th>" . _("table") . "</th>
					<td>" . $RS['tabella'] . "</td>
				</tr>
				<tr>
					<th>" . _("id_record") . "</th>
					<td>" . $RS['id_record'] . "</td>
				</tr>
				<tr>
					<th>" . _("action type") . "</th>
					<td>" . $RS['op'] . "</td>
				</tr>
				<tr>
					<th>" . _("reversibility") . "</th>
					<td>" . $reversibile . "</td>
				</tr>
				<tr>
					<th>" . _("action author") . "</th>
					<td>" . $RS['uid'] . " (" . implode(" ", RegTools::uid2name($RS['uid'])) . ")</td>
				</tr>
				<tr>
					<th>" . _("author group") . "</th>
					<td>" . $RS['gid'] . "</td>
				</tr>			

				<tr>
					<th>" . _("update source") . "</th>
					<td>" . $fonte . "</td>
				</tr>		

				<tr>
					<th>" . _("browser information") . "</th>
					<td>" . htmlentities(stripslashes($RS['info_browser']), ENT_QUOTES, FRONT_ENCODING) . "</td>
				</tr>

			</table>		


			";

        return array('table' => $info_operazione, 'rev' => $is_reversibile);
    }

    /**
     * @desc Funzione che mostra la pagina di dettaglio per una operazione di log
     * @param int $id_log ID dell'operazione da mostrare
     */
    public function mostra_dettaglio_log($id_log) {

        global $vmsql, $vmreg, $db1;

        // PRENDI IL DETTAGLIO DEL LOG

        $q_log = $vmreg->query("SELECT log.*
						FROM {$db1['frontend']}{$db1['sep']}log log
						INNER JOIN {$db1['frontend']}{$db1['sep']}utente u ON log.uid=u.id_utente
						WHERE id_log=$id_log");

        $RS = $vmreg->fetch_assoc($q_log);


        $OUT = "";

        // CASO QUERY DI MODIFICA
        if ($RS['op'] == 'update') {

            $storico_pre = unserialize($RS['storico_pre']);

            $parse_sql = $this->parser_sql_update($RS['storico_post']);

            if (count($parse_sql) == 0) {

                $OUT.= _("Read error");
            } else {

                $info_op = $this->info_tabella_operazione($RS);

                $OUT.=$info_op['table'];

                // Tabella di comparazione:

                $OUT.="<br /><br />
			<h2>" . _("Record comparison table") . "</h2>

			<p>" . _("Fields that were changed in this operation are highlighted in yellow.") . "</p>
			<table border=\"1\" summary=\"tabella comparazione\" id=\"tabella-comparazione\">\n";

                $OUT.="
			<tr>
				<th>" . _("field") . "</th>
				<th>" . _("value updated") . "</th>
				<th>" . _("Current value") . "</th>
			</tr>
			";

                foreach ($storico_pre as $campo => $valore_old) {

                    $valore_old = htmlentities($valore_old, ENT_QUOTES, FRONT_ENCODING);

                    if ($valore_old == '' || $valore_old == null)
                        $valore_old = "<em class=\"null_old\">Null</em>";

                    if (isset($parse_sql['modifiche'][$campo])) {

                        $valore_new = "<span class=\"modificato_new\">" . htmlentities(stripslashes($parse_sql['modifiche'][$campo]), ENT_QUOTES, FRONT_ENCODING) . "</span>";
                        $classe_new = '';
                        $valore_old = "<span class=\"modificato_old\">" . stripslashes($valore_old) . "</span>";
                        $classe_tr = " class=\"evidenza\"";
                    } else {
                        $valore_new = $valore_old;
                        $classe_new = " class=\"intatto\"";
                        $valore_old = "<span class=\"intatto_old\">" . $valore_old . "</span>";
                        $classe_tr = "";
                    }


                    $OUT.= "<tr $classe_tr>\n";

                    $OUT.= "<td class=\"campo\">" . $campo . "</td>\n";

                    $OUT.= "<td>" . $valore_old . "</td>\n";

                    $OUT.= "<td $classe_new>" . $valore_new . "</td>\n";

                    $OUT.="</tr>\n";
                }

                $OUT.="</table>\n";


                // PROCEDURA DI RIPRISTINO IN CASO UPDATE
                if ($info_op['rev']) {

                    $OUT.= "<br /><form action=\"" . Common::phpself() . "?ripristino=1&amp;type=update\" method=\"post\">

					<input type=\"hidden\" name=\"id_log\" value=\"$id_log\" />
					<input type=\"button\" onclick=\"submit();\" name=\"ripristino_op\" value=\" " . _("Rollback this action") . " \" />

					</form>\n";
                }
            }
        } // CASO QUERY DI INSERT
        else if ($RS['op'] == 'insert') {

            $parse_sql = $this->parser_sql_insert($RS['storico_post']);

            $info_op = $this->info_tabella_operazione($RS);

            $OUT.=$info_op['table'];


            // Tabella di comparazione:

            $OUT.="<br /><br />
			<h2>Record inserito</h2>

			<table border=\"1\" summary=\"tabella comparazione\" id=\"tabella-comparazione\">\n";

            $OUT.="
			<tr>
				<th>" . _("field") . "</th>
				<th>" . _("value") . "</th>
			</tr>
			";

            for ($i = 0; $i < count($parse_sql['campi']); $i++) {

                $valore_new = htmlentities($parse_sql['valori'][$i], null, FRONT_ENCODING);

                if ($valore_new == '' || $valore_new == null)
                    $valore_new = "<em class=\"null_old\">Null</em>";

                $valore = "<span class=\"intatto_old\">" . $valore_new . "</span>";
                $classe_tr = "";


                $OUT.= "<tr $classe_tr>\n";

                $OUT.= "<td class=\"campo\">" . $parse_sql['campi'][$i] . "</td>\n";

                $OUT.= "<td>" . $valore . "</td>\n";
            }

            $OUT.="</table>\n";
        }
        // CASO QUERY DI DELETE
        else if ($RS['op'] == 'delete') {

            $storico_pre = unserialize($RS['storico_pre']);

            $info_op = $this->info_tabella_operazione($RS);

            $OUT.=$info_op['table'];

            // Tabella di comparazione:

            $OUT.="<br /><br />
			<h2>" . _("Record deleted") . "</h2>

			<table border=\"1\" summary=\"tabella comparazione\" id=\"tabella-comparazione\">\n";

            $OUT.="
			<tr>
				<th>" . _("field") . "</th>
				<th>" . _("value") . "</th>
			</tr>
			";

            foreach ($storico_pre as $campo => $valore_old) {

                $valore_old = htmlentities($valore_old, null, FRONT_ENCODING);

                if ($valore_old == '' || $valore_old == null)
                    $valore_old = "<em class=\"null_old\">Null</em>";

                if (isset($parse_sql['modifiche'][$campo])) {

                    $valore_new = "<span class=\"modificato_new\">" . htmlentities($parse_sql['modifiche'][$campo], null, FRONT_ENCODING) . "</span>";
                    $classe_new = '';
                    $valore_old = "<span class=\"modificato_old\">" . $valore_old . "</span>";
                    $classe_tr = " class=\"evidenza\"";
                } else {
                    $valore_new = $valore_old;
                    $classe_new = " class=\"intatto\"";
                    $valore_old = "<span class=\"intatto_old\">" . $valore_old . "</span>";
                    $classe_tr = "";
                }


                $OUT.= "<tr $classe_tr>\n";

                $OUT.= "<td class=\"campo\">" . $campo . "</td>\n";

                $OUT.= "<td>" . $valore_old . "</td>\n";
            }

            $OUT.="</table>\n";


            // PROCEDURA DI RIPRISTINO IN CASO DELETE
            if ($info_op['rev']) {

                $OUT.= "<br /><form action=\"" . Common::phpself() . "?ripristino=1&amp;type=delete\" method=\"post\">

					<input type=\"hidden\" name=\"id_log\" value=\"$id_log\" />
					<input type=\"button\" onclick=\"submit();\" name=\"ripristino_op\" value=\" " . _("Rollback this action") . " \" />

					</form>\n";
            }
        }
        // CASO QUERY DI DELETE
        else if ($RS['op'] == 'duplicazione') {

            $info_op = $this->info_tabella_operazione($RS);

            $OUT.=$info_op['table'];


            // Tabella di comparazione:

            $OUT.="<br /><br />
			<h2>" . _("Record duplicated") . "</h2>
			";

            // prendi le informazioni

            $info_duplicazione = str_replace("DUPLICAZIONE ", "", $RS['storico_post']);


            $OUT.="<p>" . _("Duplication of table record") . " " . str_replace(":", ", ID:", $info_duplicazione) . "</p>";
        }
        // CASO QUERY DI RIPRISTINO
        else if ($RS['op'] == 'ripristino') {

            $OUT.="";
        }

        echo openLayout1(_("Database log details"), array("sty/admin.css", "sty/tabelle.css", "sty/log.css"));

        echo breadcrumbs(array("HOME", "ADMIN", "log.php" => _("database log"), _("details")));

        echo "<h1>" . _("Log details") . "</h1>\n";


        echo $OUT;


        echo closeLayout1();
    }

    /**
     * @desc Funzione che mostra la pagina con il log
     *
     */
    public function mostra_log() {

        global $vmsql, $vmreg, $db1;

        $files = array("sty/admin.css", "sty/tabelle.css", "js/mostra_nascondi_id.js", "sty/log.css");

        $files[] = "js/jscalendar/calendar.js";
        $files[] = "js/jscalendar/lang/calendar-it.js";
        $files[] = "js/jscalendar/calendar-setup.js";
        $files[] = "sty/jscalendar/calendar-win2k-cold-1.css";

        echo openLayout1(_("Database log"), $files);

        echo breadcrumbs(array("HOME", "ADMIN", _("database log")));

        echo "<h1>" . _("Operations log for database") . "</h1>\n";

        $ORDER = isset($_GET['or']) ? (int) $_GET['or'] : "data";

        $SORT = isset($_GET['s']) ? $_GET['or'] : "DESC";


        $PASSO = 100;

        $colore_tab = " class=\"arancio\"";


        $QS = "";

        $val_op = array('insert' => 0, 'update' => 0, 'delete' => 0, 'ripristino' => 0, 'duplicazione' => 0);

        // Impostazioni per i filtraggi SQL 
        if (isset($_GET['uid']) && $_GET['uid'] != '') {
            $clausola_uid = "AND log.uid='" . intval($_GET['uid']) . "'";
            $class_uid = $colore_tab;
            $val_uid = (int) $_GET['uid'];
            $QS.="&uid=$val_uid";
        } else {
            $clausola_uid = '';
            $class_uid = '';
            $val_uid = '';
        }

        if (isset($_GET['op']) && $_GET['op'] != '') {

            $OP = $vmsql->escape(filter_var($_GET['op'], FILTER_SANITIZE_STRING));

            $clausola_op = "AND log.op='" . $OP . "'";
            $class_op = $colore_tab;
            $val_op[$OP] = 1;
            $QS.="&op=" . $OP;
        } else {
            $clausola_op = '';
            $class_op = '';
        }


        if (isset($_GET['data_dal']) && (!preg_match("![a-z]+!i", $_GET['data_dal'])) && $_GET['data_dal'] != '') {
            $clausola_data1 = "AND log.data>'" . $vmreg->escape($_GET['data_dal']) . "'";
            $class_data = $colore_tab;
            $val_data1 = $_GET['data_dal'];
            $QS.="&data_dal=" . $_GET['data_dal'];
        } else {
            $clausola_data1 = '';
            $class_data = '';
            $val_data1 = _('All');
        }


        if (isset($_GET['data_al']) && (!preg_match("![a-z]+!i", $_GET['data_al'])) && $_GET['data_al'] != '') {
            $clausola_data2 = "AND log.data<'" . $vmreg->escape($_GET['data_al']) . "'";
            $class_data = $colore_tab;
            $val_data2 = $_GET['data_al'];
            $QS.="&data_al=" . $_GET['data_al'];
        } else {
            $clausola_data2 = '';
            $class_data = '';
            $val_data2 = _('All');
        }


        if (isset($_GET['tabella']) && $_GET['tabella'] != '') {
            $clausola_tabella = "AND log.tabella='" . $vmreg->escape($_GET['tabella']) . "'";
            $class_tabella = $colore_tab;
            $val_tabella = $_GET['tabella'];
            $QS.="&tabella=" . $_GET['tabella'];
        } else {
            $clausola_tabella = '';
            $class_tabella = "";
            $val_tabella = '';
        }

        // se non ci sono filtri nascondi le opzioni filtri mostra
        $mostra_filtri = ($clausola_uid . $clausola_op . $clausola_data1 . $clausola_data2 . $clausola_tabella == '') ?
                "display:none" : "display:";


        $clausola_istituto = '';

        $LIMIT = isset($_GET['l']) ? (int) $_GET['l'] : $PASSO;
        $OFFSET = isset($_GET['of']) ? (int) $_GET['of'] : 0;

        if ($OFFSET < 0)
            $OFFSET = 0;


        // Query Log

        $q_log_count = $vmreg->query("SELECT count(*)
	 	 							FROM {$db1['frontend']}{$db1['sep']}log log
	 	 							INNER JOIN {$db1['frontend']}{$db1['sep']}utente u ON u.id_utente=log.uid
		 							INNER JOIN {$db1['frontend']}{$db1['sep']}gruppo g ON u.gid=g.gid
	 	 							WHERE 1=1
	 	 							$clausola_uid
						 			$clausola_op
						 			$clausola_data1
						 			$clausola_data2
						 			$clausola_tabella
	 								");
        list($TOT) = $vmreg->fetch_row($q_log_count);


        $LIMIT_SYNTAX = $vmreg->limit($LIMIT, $OFFSET);

        $sql_log = "SELECT log.id_log,
							log.op,
							log.tabella,
							log.data,
							log.uid,
							" . $vmreg->concat("log.gid, ' (',g.nome_gruppo,')'", 'gruppo_desc') . ",
							log.id_record,
							log.fonte,
							" . $vmreg->concat("u.nome, ' ',u.cognome", 'nomecognome') . "


		 			FROM {$db1['frontend']}{$db1['sep']}log log
		 			INNER JOIN {$db1['frontend']}{$db1['sep']}utente u ON u.id_utente=log.uid
		 			INNER JOIN {$db1['frontend']}{$db1['sep']}gruppo g ON u.gid=g.gid

	 			WHERE 1=1
	 			$clausola_uid
	 			$clausola_op
	 			$clausola_data1
	 			$clausola_data2
	 			$clausola_tabella
	 			ORDER BY $ORDER $SORT
				$LIMIT_SYNTAX
	 			";

        $q_log = $vmreg->query($sql_log);

        $inizio_set = $OFFSET + 1;
        $fine_set = (($OFFSET + $LIMIT) < $TOT) ? $OFFSET + $LIMIT : $TOT;

        if ($OFFSET > $TOT)
            $OFFSET = $TOT;

        $str_filtrato = (trim($clausola_uid .
                        $clausola_op .
                        $clausola_data1 .
                        $clausola_data2 .
                        $clausola_tabella .
                        $clausola_istituto) != '') ? "<span class=\"grigio\">(filtrati)</span>" : "";

        echo "<p>" . _("Operations") . " $inizio_set - $fine_set " . _('of') . " <strong>$TOT</strong> $str_filtrato</p>\n";


        $PAG = "<div id=\"paginazione\">\n";


        if ($OFFSET - $PASSO >= 0) {


            $PAG.= "<a href=\"" . Common::phpself() . "?of=" . ($OFFSET - $PASSO) . $QS . "\">&lt; &lt; " . _("previous") . "</a>\n | ";
        } else {

            $PAG.= "<span class=\"pag\">&lt; &lt; " . _("previous") . "</span>\n | ";
        }

        $n_pagine = ceil($TOT / $PASSO);

        if ($n_pagine > $PASSO) {
            $n_pagine = $PASSO;
        }

        if ($n_pagine > 1) {

            for ($i = 0; $i < $n_pagine; $i++) {

                if ($OFFSET == $PASSO * $i) {

                    $PAG.= " " . ($i + 1) . " \n | ";
                } else {
                    $PAG.= " <a href=\"" . Common::phpself() . "?of=" . ($PASSO * $i) . $QS . "\">" . ($i + 1) . "</a>\n | ";
                }
            }
        }

        if ($OFFSET + $PASSO >= $TOT) {

            $PAG.= "<span class=\"pag\">" . _("next") . " &gt; &gt; </span>\n | ";
        } else {
            $PAG.= "<a href=\"" . Common::phpself() . "?of=" . ($OFFSET + $PASSO) . $QS . "\">" . _("next") . " &gt; &gt; </a>\n | ";
        }


        $PAG = substr($PAG, 0, -2);

        $PAG.= "</div><br />\n";


        // FILTRI
        // PRENDI LE TABELLE ESISTENTI SUL LOG
        $q_tab_log = $vmreg->query("SELECT DISTINCT tabella FROM {$db1['frontend']}{$db1['sep']}log ORDER BY tabella");

        list($tabelle) = $vmreg->fetch_row_all($q_tab_log, true);
        // -- fine tabelle
        // PRENDI GLI UTENTI ESISTENTI SUL LOG
        $q_tab_ut = $vmreg->query("SELECT DISTINCT log.uid, " . $vmreg->concat("log.uid,' - ',u.cognome,' ',u.nome", 'uidnomecognome') . "
	 						FROM {$db1['frontend']}{$db1['sep']}log log , {$db1['frontend']}{$db1['sep']}utente u
	 						WHERE u.id_utente=log.uid 
	 						ORDER BY uidnomecognome");

        list($id_utenti, $utenti) = $vmreg->fetch_row_all($q_tab_ut, true);
        // -- fine tabelle


        $FILTRI = "<p><span class=\"fakelink\" onclick=\"mostra_nascondi('filtri_log');\"><strong>" . 
                _("Log filters") . "</strong></span></p>\n";


        $FILTRI.= "
	 	<div id=\"filtri_log\" style=\"$mostra_filtri;\">

	 	<form action=\"" . Common::phpself() . "\" method=\"get\">
		 	<fieldset style=\"margin:5px 20px 20px 0px; width:60%;\">
		 		<label for=\"op\">" . _("Action type:") . "</label>
		 		<select name=\"op\" id=\"op\">
		 			<option value=\"\">" . _("All actions") . "</option>\n";

        $ar_op = array_keys($val_op);

        for ($i = 0; $i < count($ar_op); $i++) {

            $sel_op = ($val_op[$ar_op[$i]] == 1) ? "selected=\"selected\"" : "";

            $FILTRI.= "
		 		 <option value=\"" . $ar_op[$i] . "\" $sel_op>" . $ar_op[$i] . "</option>
		 		 ";
        }

        $FILTRI.= "	
		 		</select>

		 		<br /><br />

		 		<label for=\"op\">" . _("Table:") . "</label>
		 		<select name=\"tabella\" id=\"tabella\">
		 			<option value=\"\">" . _("All tables") . "</option>
		 		";

        for ($i = 0; $i < count($tabelle); $i++) {

            $sel_tabella = ($tabelle[$i] == $val_tabella) ? " selected=\"selected\"" : "";

            $FILTRI.= "<option value=\"" . $tabelle[$i] . "\" $sel_tabella>" . $tabelle[$i] . "</option>\n";
        }

        $FILTRI.= "
		 		</select>

		 	<br /><br />




		 		<label for=\"uid\">" . _("User:") . "</label>
		 		<select name=\"uid\" id=\"uid\">
		 			<option value=\"\">" . _("All users") . "</option>
		 		";

        for ($i = 0; $i < count($utenti); $i++) {

            $sel_utenti = ($id_utenti[$i] == $val_uid) ? " selected=\"selected\"" : "";

            $FILTRI.= "<option value=\"" . $id_utenti[$i] . "\" $sel_utenti>" . $utenti[$i] . "</option>\n";
        }

        $FILTRI.= "
		 		</select>

		 	<br /><br />

		 	<label>" . _("Date:") . "</label><br />
		 	" . _("from:") . " <input type=\"text\" name=\"data_dal\" id=\"data_dal\" value=\"$val_data1\" /> " . _("to") . " <input type=\"text\" name=\"data_al\"  id=\"data_al\" value=\"$val_data2\" />

		 	 <script type=\"text/javascript\">



			   Calendar.setup({
			        inputField     :    \"data_dal\",   // id of the input field
			        firstDay	   :    1,
			        ifFormat       :    \"%Y-%m-%d %H:%M\",       // format of the input field
			        showsTime      :    true,
			        timeFormat     :    \"24\"
			    });    

			   Calendar.setup({
			        inputField     :    \"data_al\",   // id of the input field
			        firstDay	   :    1,
			        ifFormat       :    \"%Y-%m-%d %H:%M\",       // format of the input field
			        showsTime      :    true,
			        timeFormat     :    \"24\"
			    });    


			    </script>

		 	<br /><br />



		 	<input type=\"button\" onclick=\"submit();\" name=\"filtra\" value=\" " . _("Filter log") . " \" />
		 	&nbsp;&nbsp;&nbsp;&nbsp;
		 	<input type=\"button\" onclick=\"reset(); document.getElementById('tabella').options[0].selected=true; document.getElementById('op').options[0].selected=true; document.getElementById('uid').options[0].selected=true; submit();\" name=\"rimuovi\" value=\" " . _("Remove all filters") . "\" />

	 		</fieldset>
	 	</form>
	 	</div>\n";


        #########################################################################
        #
	 #	CONTINUA A STAMPARE
        #

	 echo $FILTRI;

        echo $PAG;


        echo "<table class=\"tab-color\" summary=\"Tabella Log\">

	 	<tr>
			<th$class_data>" . _("date") . "</th>
			<th$class_op>" . _("operation") . "</th>
			<th$class_tabella>" . _("table") . "</th>
			<th>" . _("user") . "</th>
			<th>" . _("group") . "</th>
			<th>" . _("id_record") . "</th>
			<th>" . _("source") . "</th>
			<th>" . _("details") . "</th>
			<th>" . _("history") . "</th>
		</tr>

		";

        while ($RSlog = $vmreg->fetch_assoc($q_log)) {

            switch ($RSlog['op']) {
                case 'insert' : $colore = "#EFFFEF";
                    break;
                case 'update' : $colore = "#FFFBEF";
                    break;
                case 'delete' : $colore = "#FFEFEF";
                    break;
            }

            $data = VFDate::date_encode($RSlog['data'], true, 'string');

            echo "
	 	<tr class=\"colore-" . $RSlog['op'] . "\" >
			<td>" . $data . "</td>
			<td>" . $RSlog['op'] . "</td>
			<td>" . $RSlog['tabella'] . "</td>
			<td>" . $RSlog['nomecognome'] . "</td>
			<td>" . $RSlog['gruppo_desc'] . "</td>
			<td>" . $RSlog['id_record'] . "</td>
			<td>" . $RSlog['fonte'] . "</td>
			<td><a href=\"log.php?dettaglio=" . $RSlog['id_log'] . "\">" . _("details") . "</a></td>
			<td><a href=\"log.php?id_record=" . $RSlog['id_record'] . "&amp;table_name=" . $RSlog['tabella'] . "\">" . _("history") . "</a></td>
		</tr>
		 ";
        }

        echo "</table>\n";

        echo closeLayout1();
    }

    /**
     * Funzione di ripristino. 
     * Mediante questa funzione � possibile eseguire rollback di operazioni di DELETE e UPDATE
     *
     * @todo Fare verifiche sul corretto funzionamento in ambito Postgres
     * @param int $id_log ID dell'operazione nel log su cui operare
     */
    public function ripristina($id_log) {

        global $vmsql, $vmreg, $db1;

        // prendi il log
        $q_log = $vmreg->query("SELECT op,storico_pre,storico_post,tabella,id_record FROM {$db1['frontend']}{$db1['sep']}log WHERE id_log=$id_log");

        if ($vmreg->num_rows($q_log) != 1) {
            openErrorGenerico(_("Missing reference in the recovery operation"));
            exit;
        }


        list($op, $storico_pre, $storico_post, $tabella, $id_record) = $vmreg->fetch_row($q_log);

        ##############################
        #	
        #	RIPRISTINO UPDATE
        #

	if ($op == 'update') {

            $array_pre = unserialize($storico_pre);


            if (is_array($array_pre)) {

                $sql_update = "UPDATE $tabella SET ";

                // PRENDI LA DOCUMENTAZIONE DELLA TABELLA (serve per esprimere i valori null in caso di int o float o double
                list($info_column_name, $info_data_type) = RegTools::prendi_colonne_frontend($tabella, "column_name,data_type", false, 0);

                $info_cols = array();

                for ($i = 0; $i < count($info_data_type); $i++) {
                    $info_cols[$info_column_name[$i]] = $info_data_type[$i];
                }


                // ciclo sui valori
                foreach ($array_pre as $campo => $val) {

                    if ($info_cols[$campo] == 'int' ||
                            $info_cols[$campo] == 'tinyint' ||
                            $info_cols[$campo] == 'mediumint' ||
                            $info_cols[$campo] == 'double' ||
                            $info_cols[$campo] == 'float'
                    ) {

                        if ($val == '' || $val == null) {
                            $valore = "NULL";
                        } else {
                            $valore = "'$val'";
                        }
                    } else {
                        $valore = "'" . str_replace("'", "\'", stripslashes($val)) . "'";
                    }


                    $sql_update.=" $campo=$valore,";
                }

                // condizione
                $campo_pk = RegTools::prendi_PK($tabella);

                if ($campo_pk == null) {
                    openErrorGenerico(_("Procedure exception: cannot complete the rollback (1)"));
                    exit;
                }

                $sql_update = substr($sql_update, 0, -1);

                $sql_update.= " WHERE $campo_pk='" . $vmsql->escape($id_record) . "'";


                $q_rip = $vmsql->query($sql_update);
                if ($vmsql->affected_rows($q_rip) == 1) {


                    // INSERISCI NEL LOG
                    $this->rpc_log('ripristino', $tabella, $_SESSION['user']['uid'], $_SESSION['gid'], $id_record, true);
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id_record=$id_record&feed=ok");
                    exit;
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id_record=$id_record&feed=ko");
                    exit;
                }
            } else {
                openErrorGenerico(_("Procedure exception: cannot complete the rollback (2)"));
                exit;
            }
        } // -- FIne ripristino UPDATE
        else if ($op == 'delete') {

            $array_pre = unserialize($storico_pre);

            // verifica se esiste un record con il codice del record.
            // In pratica il record � rispristinabile se l'ID � libero (ad es. caso autoincrement)
            // condizione
            $campo_pk = RegTools::prendi_PK($tabella);

            $q_test = $vmsql->query("SELECT * FROM $tabella WHERE $campo_pk='$id_record'");

            // se esiste esce con errore
            if ($vmsql->num_rows($q_test) > 0) {
                openErrorGenerico(_("Unable to recover the record. The primary key is is in use by another record"));
                exit;
            }


            $sql_insert = "INSERT INTO $tabella ";

            $sql_campi = "";
            $sql_valori = "";

            // PRENDI LA DOCUMENTAZIONE DELLA TABELLA (serve per esprimere i valori null in caso di int o float o double
            list($info_column_name, $info_data_type) = RegTools::prendi_colonne_frontend($tabella, "column_name,data_type", false, 0);

            $info_cols = array();

            for ($i = 0; $i < count($info_data_type); $i++) {

                if (VFRONT_DBTYPE == 'oracle')
                    $info_column_name[$i] = strtolower($info_column_name[$i]);

                $info_cols[$info_column_name[$i]] = $info_data_type[$i];
            }


            // ciclo sui valori
            foreach ($array_pre as $campo => $val) {

                if ($info_cols[$campo] == 'int' ||
                        $info_cols[$campo] == 'tinyint' ||
                        $info_cols[$campo] == 'mediumint' ||
                        $info_cols[$campo] == 'double' ||
                        $info_cols[$campo] == 'float' ||
                        $info_cols[$campo] == 'date' ||
                        $info_cols[$campo] == 'datetime' ||
                        $info_cols[$campo] == 'timestamp'
                ) {

                    if ($val == '' || $val == null) {
                        $valore = "NULL";
                    } else {
                        $valore = "'$val'";
                    }
                } else {
                    $valore = "'" . str_replace("'", "\'", stripslashes($val)) . "'";
                }


                $sql_campi.=$campo . ",";
                $sql_valori.=$valore . ",";
            }


            $sql_campi = substr($sql_campi, 0, -1);
            $sql_valori = substr($sql_valori, 0, -1);

            echo $sql_insert.= "($sql_campi) VALUES ($sql_valori)";


            // INIZIO TRANSAZIONE

            $q_rip = $vmsql->query($sql_insert);
            if ($vmsql->affected_rows($q_rip) == 1) {


                // INSERISCI NEL LOG
                $this->rpc_log('ripristino', $tabella, $_SESSION['user']['uid'], $_SESSION['gid'], $id_record, true);
                header("Location: " . $_SERVER['PHP_SELF'] . "?id_record=$id_record&feed=ok");
                exit;
            } else {
                header("Location: " . $_SERVER['PHP_SELF'] . "?id_record=$id_record&feed=ko");
                exit;
            }
        }
    }




    /**
     * Funzione di scrittura del log delle operazioni compiute mediante le maschere di VFront
     * E' dipendente dal parametro $RPC_LOG, definito nel file di configurazione (default conf/conf.vfront.php)
     *
     * @param string $op 'select'|'insert'|'delete'|'update'|'ripristino'
     * @param string $tabella
     * @param int $uid
     * @param int $gid
     * @param string $info_pk
     * @param bool $storico
     * @param string $storico_pre
     * @param string $storico_post
     * @param string $info_browser
     * @return mixed
     */
    public function rpc_log($op,$tabella,$uid,$gid,$info_pk='',$storico=true,$storico_pre='',$storico_post='',$info_browser=''){

        global $RPC_LOG, $db1,  $vmsql, $vmreg;

        $ID_LOG=null;

        if($RPC_LOG){

            if(!in_array($op,array('select','insert','delete','update','ripristino','duplicazione','import'))){

                $op='sconosciuta';			
            }

            $gid= (int) $gid;
            $uid = (int) $uid;

            switch($op){

                case 'ripristino': 


                        $id_record = $info_pk;

                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,info_browser) 
                              VALUES ('$op','$tabella',$uid,$gid,'$id_record','$info_browser')";

                        $test=$vmreg->query_try($sql,false);
                break;	

                case 'sconosciuta': 

                        $id_record = $info_pk;

                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,info_browser) 
                              VALUES ('$op','$tabella',$uid,$gid,'$id_record','$info_browser')";

                        $test=$vmreg->query_try($sql,false);
                break;	

                case 'duplicazione': 

                        $id_record = $info_pk;

                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,info_browser,storico_post) 
                              VALUES ('$op','$tabella',$uid,$gid,'$id_record','$info_browser','$storico_post')";

                        $test=$vmreg->query_try($sql,false);
                break;	


                case 'import': 
                case 'insert': 

                    $id_record = $info_pk;

                        if($storico){
                            $storico_post=$vmreg->escape($storico_post);
                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,storico_post,id_record,info_browser) 
                              VALUES ('$op','$tabella',$uid,$gid,'$storico_post','$id_record','$info_browser')";
                        }else{

                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,info_browser) 
                              VALUES ('$op','$tabella',$uid,$gid,'$id_record','$info_browser')";
                        }

                        $test=$vmreg->query_try($sql,false);
                        $ID_LOG=$vmreg->insert_id("{$db1['frontend']}{$db1['sep']}log",'id_log');
                break;


                case 'delete':

                        if($storico){

                            $condizione='';
                            $id='';

                                foreach ($info_pk as $k=>$val){
                                    $val=$vmsql->escape(Common::vf_utf8_decode($val));
                                    $condizione.=" $k='$val' AND";
                                    $id.=$val."|";
                                }

                                $condizione=substr($condizione,0,-3);
                                $id=substr($id,0,-1);


                            $q_storico_pre=$vmsql->query("SELECT * FROM $tabella WHERE $condizione");

                            $RS_pre=$vmsql->fetch_assoc($q_storico_pre);

                            $storico_pre=$vmreg->escape(serialize($RS_pre));

                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,storico_pre,info_browser) 
                                  VALUES ('$op','$tabella',$uid,$gid,'$id','$storico_pre','$info_browser')";
                        }
                        else{

                            $condizione='';
                            $id='';

                                foreach ($info_pk as $k=>$val){
                                    $val=$vmreg->escape($val);
                                    $condizione.=" $k='$val' AND";
                                    $id.=$val."|";
                                }

                                $condizione=substr($condizione,0,-3);
                                $id=substr($id,0,-1);


                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,info_browser) 
                                  VALUES ('$op','$tabella',$uid,$gid,'$id','$info_browser')";
                        }

                        $test=$vmreg->query_try($sql,false);

                        $ID_LOG=$vmreg->insert_id("{$db1['frontend']}{$db1['sep']}log",'id_log');

                break;


                case 'update':

                        if($storico){

                            $condizione='';
                            $id='';

                                foreach ($info_pk as $k=>$val){
                                    $val=$vmreg->escape($val);
                                    $condizione.=" $k='$val' AND";
                                    $id.=$val."|";
                                }

                                $condizione=substr($condizione,0,-3);
                                $id=$vmreg->escape(substr($id,0,-1));


                            $q_storico_pre=$vmsql->query("SELECT * FROM $tabella WHERE $condizione");

                            $RS_pre=$vmsql->fetch_assoc($q_storico_pre);

                            $storico_pre=$vmreg->escape(serialize($RS_pre));
                            $storico_post=$vmreg->escape($storico_post);

                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,storico_pre,storico_post,info_browser) 
                                  VALUES ('$op','$tabella',$uid,$gid,'$id','$storico_pre','$storico_post','$info_browser')";
                            $test=$vmreg->query_try($sql,false);

                            $ID_LOG=$vmreg->insert_id("{$db1['frontend']}{$db1['sep']}log",'id_log');

                        }
                        else{

                            $condizione='';
                            $id='';

                                foreach ($info_pk as $k=>$val){
                                    $val=$vmreg->escape($val);
                                    $condizione.=" $k='$val' AND";
                                    $id.=$val."|";
                                }

                                $condizione=substr($condizione,0,-3);
                                $id=substr($id,0,-1);


                            $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}log (op,tabella,uid,gid,id_record,info_browser) 
                                  VALUES ('$op','$tabella',$uid,$gid,'$id','$info_browser')";

                            $test=$vmreg->query_try($sql,false);

                            $ID_LOG=$vmreg->insert_id("{$db1['frontend']}{$db1['sep']}log",'id_log');

                        }
                break;


            }


        }
        else{
            Common::rpc_debug("Operazione $op saltata");
        }

        return $ID_LOG;
    }
}
