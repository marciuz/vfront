<?php
/**
 * SQL per MySQL 5.x sotto forma di file PHP. 
 * Ogni istruzione è un valore di un array
 * 
 * @package VFront
 * @subpackage VFront_Web_Installer
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: vfront.mysql.sql.php 1108 2014-10-20 20:25:04Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

$db1['frontend']=(isset($db1['frontend'])) ? $db1['frontend']:'';
$db1['sep']=(isset($db1['sep'])) ? $db1['sep']:'';

$SQL_DEFINITION[]="

--
-- Begin the procedure
--

BEGIN;
";

$SQL_DEFINITION[]="


--
--	create table group
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}gruppo (
  `gid` int(11) NOT NULL COMMENT 'ID del gruppo',
  `nome_gruppo` varchar(50) NOT NULL COMMENT 'Nome del gruppo',
  `descrizione_gruppo` text,
  `data_gruppo` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`gid`),
  UNIQUE KEY `gid` (`gid`),
  UNIQUE KEY `nome_gruppo` (`nome_gruppo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$SQL_DEFINITION[]="


--
--	create log table , where are stored the operations maked via forms
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}log (
  `id_log` bigint(20) unsigned NOT NULL auto_increment,
  `op` enum('insert','update','delete','select','sconosciuta','ripristino','duplicazione','import') default NULL,
  `uid` int(11) unsigned NOT NULL,
  `gid` int(11) unsigned NOT NULL,
  `data` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `tabella` varchar(100) NOT NULL,
  `id_record` varchar(100) default NULL,
  `storico_pre` text,
  `storico_post` text,
  `id_istituto` int(11) default NULL,
  `fonte` enum('m','s') NOT NULL default 'm',
  `info_browser` varchar(20) default NULL,
  PRIMARY KEY  (`id_log`),
  KEY `op` (`op`,`uid`,`data`,`tabella`,`id_record`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabella di log';
";

$SQL_DEFINITION[]="


--
--	create table recordlock for the record multiaccess
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}recordlock (
  `tabella` varchar(50) NOT NULL,
  `colonna` varchar(50) NOT NULL,
  `id` varchar(50) NOT NULL,
  `tempo` int(11) NOT NULL,
  PRIMARY KEY  (`tabella`,`colonna`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$SQL_DEFINITION[]="


--
--	Create the table registry tables, the rules registry used by vfront for store rules and settings
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}registro_tab (
  `id_table` int(10) NOT NULL auto_increment,
  `gid` int(10) default NULL,
  `visibile` tinyint(1) default '0',
  `in_insert` int(1) unsigned NOT NULL default '0',
  `in_duplica` int(1) unsigned NOT NULL default '0',
  `in_update` int(1) unsigned NOT NULL default '0',
  `in_delete` int(1) unsigned NOT NULL default '0',
  `in_export` tinyint(1) unsigned NOT NULL default '0',
  `in_import` tinyint(1) unsigned NOT NULL default '0',
  `data_modifica` int(10) default '0',
  `orderby` varchar(255) default NULL,
  `table_name` varchar(100) default NULL,
  `table_type` varchar(20) default 'BASE TABLE',
  `commento` varchar(255) default NULL,
  `orderby_sort` varchar(255) default 'ASC',
  `permetti_allegati` tinyint(1) unsigned NOT NULL default '0',
  `permetti_allegati_ins` tinyint(1) unsigned default '0',
  `permetti_allegati_del` tinyint(1) unsigned default '0',
  `permetti_link` tinyint(1) unsigned NOT NULL default '0',
  `permetti_link_ins` tinyint(1) unsigned default '0',
  `permetti_link_del` tinyint(1) unsigned default '0',
  `view_pk` varchar(60) default NULL,
  `fonte_al` varchar(100) default NULL,
  `table_alias` varchar(100) default NULL,
  `allow_filters` tinyint(1) default '0',
  `default_view` varchar(5) DEFAULT 'form',
  `default_filters` text,
  PRIMARY KEY  (`id_table`),
  KEY `i_gid_tab` (`gid`),
  KEY `table_name` (`table_name`),
  KEY `id_table` (`id_table`),
  CONSTRAINT `registro_tab_fk` FOREIGN KEY (`gid`)
    REFERENCES {$db1['frontend']}{$db1['sep']}gruppo (`gid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";


$SQL_DEFINITION[]="


--
--	Create table registry columns, the rule table used by vfront for store the columns settings 
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}registro_col (
  `id_reg` int(10) NOT NULL auto_increment,
  `id_table` int(11) default NULL,
  `gid` int(10) default NULL,
  `column_name` varchar(255) default NULL,
  `ordinal_position` int(3) default NULL,
  `column_default` varchar(255) default NULL,
  `is_nullable` char(3) default NULL,
  `column_type` varchar(255) default NULL,
  `character_maximum_length` int(10) default NULL,
  `data_type` varchar(255) default NULL,
  `extra` varchar(200) default NULL,
  `in_tipo` text,
  `in_default` text,
  `in_visibile` tinyint(1) default '1',
  `in_richiesto` tinyint(1) default '0',
  `in_suggest` tinyint(1) default '0',
  `in_table` tinyint(1) default '1',
  `in_line` tinyint(1) default NULL,
  `in_ordine` int(3) default '0',
  `jstest` mediumtext,
  `commento` varchar(255) default NULL,
  `alias_frontend` varchar(100) default NULL,
  PRIMARY KEY  (`id_reg`),
  KEY `i_registro_col_gid` (`gid`),
  KEY `id_table` (`id_table`),
  CONSTRAINT `FK_registro_col_1` FOREIGN KEY (`id_table`)
    REFERENCES {$db1['frontend']}{$db1['sep']}registro_tab (`id_table`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Registro documentazione dei campi delle tabelle dello schema';
";


$SQL_DEFINITION[]="


--
--	Create table registry for the subforms, the rule table used by vfront for store the submasks settings 
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}registro_submask (
  `id_submask` int(11) unsigned NOT NULL auto_increment,
  `id_table` int(11) NOT NULL COMMENT 'Tabella parent per la sottomaschera',
  `sub_select` tinyint(1) unsigned NOT NULL default '0',
  `sub_insert` tinyint(1) default '0',
  `sub_update` tinyint(1) default '0',
  `sub_delete` tinyint(1) default '0',
  `nome_tabella` varchar(255) default NULL COMMENT 'Tabella fonte per la sottomaschera',
  `nome_frontend` varchar(250) default NULL COMMENT 'Nome per la sottomaschera che apparirà nella maschera utente',
  `campo_pk_parent` varchar(80) default NULL COMMENT 'Campo che rappresenta la chiave primaria nella tabella parent',
  `campo_fk_sub` varchar(80) default NULL COMMENT 'Campo che rappresenta la chiave esterna rispetto alla tabella parent',
  `orderby_sub` varchar(80) default NULL COMMENT 'Campo orderby della sottomaschera',
  `orderby_sub_sort` enum('ASC','DESC') default 'ASC',
  `data_modifica` int(11) unsigned default NULL,
  `max_records` int(3) default '10',
  `tipo_vista` enum('tabella','scheda','embed','schedash') NOT NULL default 'scheda',
  PRIMARY KEY  (`id_submask`),
  -- UNIQUE KEY `u_idtable_nometabella` (`id_table`,`nome_tabella`),
  CONSTRAINT `registro_submask_fk` FOREIGN KEY (`id_table`) 
    REFERENCES {$db1['frontend']}{$db1['sep']}registro_tab (`id_table`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$SQL_DEFINITION[]="


--
--	Create table registry for the columns subforms, the rule table used by vfront for store the submasks columns
--


CREATE TABLE {$db1['frontend']}{$db1['sep']}registro_submask_col (
  `id_reg_sub` int(10) NOT NULL auto_increment,
  `id_submask` int(11) unsigned NOT NULL,
  `column_name` varchar(255) default NULL,
  `ordinal_position` int(3) default NULL,
  `column_default` varchar(255) default NULL,
  `is_nullable` char(3) default NULL,
  `column_type` varchar(255) default NULL,
  `character_maximum_length` int(10) default NULL,
  `data_type` varchar(255) default NULL,
  `extra` varchar(200) default NULL,
  `in_tipo` text,
  `in_default` text,
  `in_visibile` tinyint(1) default '1',
  `in_richiesto` tinyint(1) default '0',
  `commento` varchar(255) default NULL,
  `alias_frontend` varchar(100) default NULL,
  PRIMARY KEY  (`id_reg_sub`),
  KEY `i_id_submask` (`id_submask`),
  CONSTRAINT `registro_submask_col_fk` FOREIGN KEY (`id_submask`) 
    REFERENCES {$db1['frontend']}{$db1['sep']}registro_submask (`id_submask`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Registro documentazione dei campi delle colonne delle sottom';
";


$SQL_DEFINITION[]="


--
--	Create table user, for the permission and (optional) authentication  
--


CREATE TABLE {$db1['frontend']}{$db1['sep']}utente (
  `id_utente` int(11) unsigned NOT NULL auto_increment,
  `nick` varchar(80) default NULL,
  `passwd` char(32) default NULL,
  `nome` varchar(50) default NULL,
  `cognome` varchar(50) default NULL,
  `email` varchar(80) default NULL,
  `info` text,
  `data_ins` date default NULL,
  `gid` int(11) NOT NULL default '0',
  `livello` int(1) NOT NULL default '1',
  `recover_passwd` varchar(32) default NULL,
  PRIMARY KEY  (`id_utente`),
  UNIQUE KEY `id_utente` (`id_utente`),
  KEY `gid` (`gid`),
  CONSTRAINT `utente_fk` FOREIGN KEY (`gid`)
    REFERENCES {$db1['frontend']}{$db1['sep']}gruppo (`gid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";


$SQL_DEFINITION[]="

--
--	Create the table variables for the environment variables
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}variabili (
  `variabile` char(32) NOT NULL,
  `gid` int(11) NOT NULL default '0',
  `valore` varchar(255) default NULL,
  `descrizione` varchar(255) default NULL,
  `tipo_var` varchar(20) default NULL,
  `pubvar` tinyint(1) UNSIGNED NOT NULL default 1,
  PRIMARY KEY  (`variabile`,`gid`),
  KEY `variabile` (`variabile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";


$SQL_DEFINITION[]="


--
--	Create the attachements table, which stores information of the files associated with records
--


CREATE TABLE {$db1['frontend']}{$db1['sep']}allegato (
  `codiceallegato` int(11) NOT NULL auto_increment COMMENT 'chiave primaria identificativa del record',
  `tipoentita` varchar(100) default NULL COMMENT 'identifica l''entità del database alla quale l''utente vuole collegare il file allegato. L''entità verrà riconosciuta dall''applicazione in base alle operazioni svolte in quella fase dall''utente.',
  `codiceentita` varchar(255) default NULL COMMENT 'identifica la particolare occorrenza (record) dell''entità del database alla quale l''utente vuole collegare il file allegato',
  `descroggall` varchar(250) default NULL COMMENT 'descrizione dell''oggetto del file',
  `autoreall` varchar(250) default NULL COMMENT 'autore del file da allegare',
  `versioneall` varchar(250) default NULL COMMENT 'eventuale numero di versione del file ',
  `lastdata` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'campo automaticamente valorizzato dal DBMS al primo inserimento o quando il record viene modificato',
  `nomefileall` varchar(250) NOT NULL,
  PRIMARY KEY  (`codiceallegato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='descrive i file in upload e li collega all''entità';
";


$SQL_DEFINITION[]="

--
--	Create the link table, which stores the links associated with records
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}link (
  `codicelink` int(11) NOT NULL auto_increment COMMENT 'chiave primaria identificativa del record',
  `tipoentita` varchar(100) default NULL COMMENT 'identifica l''entità del database alla quale l''utente vuole abbinare il link ipertestuale. L''entità verrà riconosciuta dall''applicazione in base alle operazioni svolte in quella fase dall''utente.',
  `codiceentita` varchar(255) default NULL COMMENT 'identifica la particolare occorrenza (record) dell''entità del database alla quale l''utente vuole abbinare il collegamento',
  `link` varchar(250) default NULL COMMENT 'URL del link a cui si rimanda',
  `descrizione` varchar(250) default NULL,
  `lastdata` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'campo automaticamente valorizzato dal DBMS al primo inserimento o quando il record viene modificato',
  PRIMARY KEY  (`codicelink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='lega le entità agli eventuali link ipertestuali';
";


$SQL_DEFINITION[]="

--
--	Create the statistics table , which are stored the rules for custom statistics
--


CREATE TABLE {$db1['frontend']}{$db1['sep']}stat (
  `id_stat` int(11) unsigned NOT NULL auto_increment,
  `nome_stat` varchar(250) NOT NULL COMMENT 'Nome nella statistica',
  `desc_stat` text COMMENT 'Descrizione della statistica',
  `def_stat` text COMMENT 'Definizione della query SQL per la statistica',
  `auth_stat` tinyint(1) NOT NULL default '1' COMMENT 'Tipo autorizzazione per statistica: 1=pubblica, 2=del gruppo, 3=personale',
  `tipo_graph` enum('barre','torta') default 'barre',
  `data_stat` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `autore` int(11) NOT NULL,
  `settings` TEXT NULL COMMENT 'Impostazioni avanzate del grafico',
  `published` tinyint(1) NOT NULL default '0' COMMENT 'published on home page',
  PRIMARY KEY  (`id_stat`),
  UNIQUE KEY `id_stat` (`id_stat`),
  KEY `autore` (`autore`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Statistiche descrittive registrate dagli utenti';
";




$SQL_DEFINITION[]="

--
-- Table button
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}button (
  `id_button` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_table` INT(10) NOT NULL,
  `definition` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `button_type` VARCHAR(25) COLLATE utf8_unicode_ci NOT NULL,
  `background` VARCHAR(7) COLLATE utf8_unicode_ci NOT NULL,
  `color` VARCHAR(7) COLLATE utf8_unicode_ci NOT NULL,
  `button_name` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_utente` INT(11) UNSIGNED NOT NULL,
   settings TEXT,
  PRIMARY KEY  (`id_button`),
  KEY `id_table` (`id_table`),
  KEY `id_utente` (`id_utente`),
  CONSTRAINT `button_ibfk_1` FOREIGN KEY (`id_table`) REFERENCES {$db1['frontend']}{$db1['sep']}registro_tab (`id_table`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$SQL_DEFINITION[]="
  

--
-- Table widget
--

CREATE TABLE {$db1['frontend']}{$db1['sep']}`widget` (
  `id_widget` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_table` int(10) NOT NULL,
  `widget_name` varchar(255) NOT NULL DEFAULT '',
  `form_position` varchar(11) NOT NULL DEFAULT '0',
  `widget_type` varchar(100) NOT NULL DEFAULT '',
  `settings` text NOT NULL,
  PRIMARY KEY (`id_widget`),
  KEY `i_widget_id_table` (`id_table`),
  CONSTRAINT `fk_widget_id_table` FOREIGN KEY (`id_table`) REFERENCES {$db1['frontend']}{$db1['sep']}`registro_tab` (`id_table`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Widget table';

";

$SQL_DEFINITION[]="

--
--	Create the table xml_rules, where they are stored the rules for report generation XML-based
--


CREATE TABLE {$db1['frontend']}{$db1['sep']}xml_rules (
  `id_xml_rules` int(11) unsigned NOT NULL auto_increment,
  `tabella` varchar(50) NOT NULL,
  `accesso` varchar(20) NOT NULL default 'RESTRICT' COMMENT 'RESTRICT,PUBLIC,FRONTEND,GROUP',
  `accesso_gruppo` varchar(100) default NULL,
  `autore` int(11) default NULL,
  `lastData` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `xsl` varchar(80) default NULL,
  `xslfo` varchar(80) default NULL,  
  `tipo_report` char(1) default NULL,
  `def_query` text ,
  `nome_report` varchar(255) default NULL,
  PRIMARY KEY  (`id_xml_rules`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Regole per la definizione dei report XML based';
";




$SQL_DEFINITION[]="

--
--	Inserts the default data in table variable
--

INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (`variabile`, `gid`, `valore`, `descrizione`, `tipo_var`) VALUES
  ('alert_login_default',0,'1','Mostra agli utenti l\'avviso se sono presenti nel gruppo di default','bool'),
  ('alert_config',0,'1','Mostra all\'admin l\'avviso in home page se è presente qualche errore nella configurazione','bool'),
  ('altezza_iframe_tabella',0,'360','Altezza in numero di pixel del rettangolo per visualizzare i dati in tabella','int'),
  ('crea_nuovo_valore_ref',0,'0','permette in caso di tabella parent scrivibilie l\'inserimento di nuovi valori nella medesima','bool'),
  ('cron_days_min', 0, '15', 'Numero di giorni minimi di anzianità dei file temporanei per essere eliminati', 'int'),
  ('formati_attach',0,'doc,xls,pdf,rtf,odt,sxw,ppt,odp,ods,gif,jpg,png,jpeg,zip,txt,csv,DOC,XLS,PDF,RTF,ODT,SXW,PPT,ODP,ODS,GIF,JPG,PNG,JPEG,ZIP,TXT,CSV','Formati permessi per i file allegati, separati da virgola','string'),
  ('layout',0,'default','Color theme','string'),
  ('lang',0,'','Overwrite the group language','string'),
  ('js_test',0,'1','Abilita i controlli javascript sui contenuti dei campi','bool'),
  ('max_char_tabella',0,'200','Numero massimo di caratteri da visualizzare nelle viste a tabella. (0 = tutti)','int'),
  ('max_tempo_edit',0,'300','Tempo di disponibilità del record','int'),
  ('n_record_tabella',0,'20','Numero di record da visualizzare per le tabelle dei dati','int'),
  ('passo_avanzamento_veloce',0,'20','Numero di record impostati per il movimento veloce tra i record nelle tabelle','int'),
  ('textarea_cols',0,'50','Colonne per il box di testo','int'),
  ('textarea_rows',0,'8','Righe per il box di testo','int'),
  ('recupero_password',0,'1','Se abilitato mostra nel login l\'opzione di modificare la password (non funziona in caso di login esterno)','bool'),
  ('search_limit_results',0,'1000','Numero massimo di risultati per la ricerca','int'),
  ('server_xslt',0,'1','Indica se utilizzare la trasformazione XSLT lato server - da disabilitare in caso di mancato supporto PHP(0=no, 1=si)','bool'),
  ('shortcut_tastiera_attivi',0,'1','Abilita le scorciatoie da tastiera nelle maschere delle tabelle e delle viste','bool'),
  ('shortcut_tastiera_popup',0,'1','Mostra una linguetta per leggere i comandi da tastiera nelle maschere','bool'),
  ('show_comment_in_table', '0', '1', 'Mostra il commento della tabella nella maschera di inserimento dati', 'bool'),
  ('show_updates', '0', '1', 'Cerca update di VFront', 'bool'),
  ('usa_calendari',0,'1','Indica se devono essere utilizzati o meno i calendari per facilitare l\'inserimento nei campi data (0=no, 1=si)','bool'),
  ('force_isodate_on_mask',0,'0','Forza il formato delle date in maschere e sottomaschere a ISO (ISO 8601) anche se specificato altro formato di date','bool'),
  ('usa_history',0,'1','Imposta se si deve utilizzare la history del browser','bool'),
  ('enable_adminer',0,'0','Enable Adminer schema administrator','bool'),
  ('default_group_ext',0,'0','The default group for external auth','int'),
  ('home_redirect',0,'','After login redirect to custom page/table','string');
";


$SQL_DEFINITION[]="

--
--	Insert a dummy group, useful for the system initialization
--

INSERT INTO {$db1['frontend']}{$db1['sep']}gruppo (`gid`, `nome_gruppo`, `descrizione_gruppo`) VALUES
  (-1,'temp','gruppo di installazione, viene eliminato nella inizializzazione');
";

$SQL_DEFINITION[]="

--
--	Insert default group
--

INSERT INTO {$db1['frontend']}{$db1['sep']}gruppo (`gid`, `nome_gruppo`, `descrizione_gruppo`) VALUES
  (0,'default','gruppo di default');
";


$SQL_DEFINITION[]="

CREATE TABLE {$db1['frontend']}{$db1['sep']}api_console (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(20) NOT NULL DEFAULT '',
  `rw` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=read only, 1=read and write',
  `api_key` varchar(100) NOT NULL DEFAULT '',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";


$SQL_DEFINITION[]="
CREATE TABLE {$db1['frontend']}{$db1['sep']}cache_reg (
  `id` int(11) unsigned NOT NULL,
  `obj` blob,
  `last_update` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;";

$SQL_DEFINITION[]="

--
--	If everything went well, confirms their query
--

COMMIT;
";

?>