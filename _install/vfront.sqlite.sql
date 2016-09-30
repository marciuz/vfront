PRAGMA foreign_keys = 1;

CREATE TABLE gruppo
(
  gid integer NOT NULL, -- ID del gruppo
  nome_gruppo varchar(50) NOT NULL, -- Nome del gruppo
  descrizione_gruppo text,
  data_gruppo timestamp DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT pk_gruppo PRIMARY KEY (gid),
  CONSTRAINT u_gruppo_nome_gruppo UNIQUE (nome_gruppo)
);






CREATE TABLE log
(
  id_log integer,
  op varchar(20) NOT NULL, 
  uid integer NOT NULL,
  gid integer NOT NULL,
  data timestamp DEFAULT CURRENT_TIMESTAMP,
  tabella varchar(100) NOT NULL,
  id_record varchar(100) default NULL,
  storico_pre text,
  storico_post text,
  fonte char(1) NOT NULL default 'm',
  info_browser varchar(20) default NULL,
  CONSTRAINT pk_log PRIMARY KEY (id_log)
);

CREATE INDEX i_log_op ON log (op);
CREATE INDEX i_log_uid ON log (uid);
CREATE INDEX i_log_gid ON log (gid);
CREATE INDEX i_log_tabella ON log (tabella);
CREATE INDEX i_log_id_record ON log (id_record);




CREATE TABLE recordlock (
  tabella varchar(50) NOT NULL,
  colonna varchar(50) NOT NULL,
  id varchar(50) NOT NULL,
  tempo integer NOT NULL,
  CONSTRAINT pk_recordlock PRIMARY KEY  (tabella,colonna,id)
);

CREATE INDEX i_recordlock ON recordlock (id);




CREATE TABLE registro_tab (
  id_table integer,
  gid integer default NULL,
  visibile integer DEFAULT 0,
  in_insert integer DEFAULT 0,
  in_duplica integer DEFAULT 0,
  in_update integer DEFAULT 0,
  in_delete integer DEFAULT 0,
  in_export integer DEFAULT 0,
  in_import integer DEFAULT 0,
  data_modifica integer default 0,
  orderby varchar(255) default NULL,
  table_name varchar(100) default NULL,
  table_type varchar(20) default 'BASE TABLE',
  commento varchar(255) default NULL,
  orderby_sort varchar(255) default 'ASC',
  permetti_allegati integer DEFAULT 0,
  permetti_allegati_ins integer DEFAULT 0,
  permetti_allegati_del integer DEFAULT 0,
  permetti_link integer DEFAULT 0,
  permetti_link_ins integer DEFAULT 0,
  permetti_link_del integer DEFAULT 0,
  view_pk varchar(60) default NULL,
  fonte_al varchar(100) default NULL,
  table_alias varchar(100) default NULL,
  allow_filters integer DEFAULT 0,
  default_view varchar(5) DEFAULT 'form',
  default_filters text,
  CONSTRAINT pk_id_table PRIMARY KEY  (id_table),
  CONSTRAINT gid FOREIGN KEY (gid) REFERENCES gruppo (gid) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX i_registro_tab_table_name ON registro_tab (table_name);
CREATE INDEX i_registro_tab_gid ON registro_tab (gid);
CREATE INDEX i_registro_tab_visibile ON registro_tab (visibile);




CREATE TABLE registro_col (
  id_reg integer,
  id_table integer default NULL,
  gid integer default NULL,
  column_name varchar(255) default NULL,
  ordinal_position integer default NULL,
  column_default varchar(255) default NULL,
  is_nullable varchar(3) default NULL,
  column_type varchar(255) default NULL,
  character_maximum_length integer default NULL,
  data_type varchar(255) default NULL,
  extra varchar(200) default NULL,
  in_tipo text,
  in_default text,
  in_visibile integer DEFAULT 1,
  in_richiesto integer DEFAULT 0,
  in_suggest integer DEFAULT 0,
  in_table integer DEFAULT 1,
  in_line integer DEFAULT NULL,
  in_ordine integer default 0,
  jstest text,
  commento varchar(255) default NULL,
  alias_frontend varchar(100) default NULL,
  CONSTRAINT pk_registro_col PRIMARY KEY  (id_reg),
  CONSTRAINT fk_registro_col_1 FOREIGN KEY (id_table) REFERENCES registro_tab (id_table) ON DELETE CASCADE
);

CREATE INDEX i_registro_col_gid ON registro_col (gid);
CREATE INDEX i_registro_col_id_table ON registro_col (id_table);
CREATE INDEX i_registro_col_column_name ON registro_col (column_name);




CREATE TABLE registro_submask (
  id_submask integer,
  id_table integer NOT NULL , -- Tabella parent per la sottomaschera
  sub_select  integer DEFAULT 0,
  sub_insert  integer DEFAULT 0,
  sub_update  integer DEFAULT 0,
  sub_delete  integer DEFAULT 0,
  nome_tabella varchar(255) default NULL ,-- Tabella fonte per la sottomaschera
  nome_frontend varchar(250) default NULL ,-- Nome per la sottomaschera che apparir? nella maschera utente
  campo_pk_parent varchar(80) default NULL ,-- Campo che rappresenta la chiave primaria nella tabella parent
  campo_fk_sub varchar(80) default NULL ,-- Campo che rappresenta la chiave esterna rispetto alla tabella parent
  orderby_sub varchar(80) default NULL, -- Campo orderby della sottomaschera
  orderby_sub_sort char(4) default 'ASC',
  data_modifica integer default NULL,
  max_records integer default '10',
  tipo_vista varchar(8) NOT NULL default 'scheda',
   CONSTRAINT pk_registro_submask PRIMARY KEY  (id_submask),
  -- CONSTRAINT u_registro_submask_nome_gruppo UNIQUE (id_table,nome_tabella),
  CONSTRAINT fk_registro_submask FOREIGN KEY (id_table) REFERENCES registro_tab (id_table) ON DELETE CASCADE
);

CREATE INDEX i_registro_submask_id_table ON registro_submask (id_table);




CREATE TABLE registro_submask_col (
  id_reg_sub integer,
  id_submask integer NOT NULL,
  column_name varchar(255) default NULL,
  ordinal_position integer default NULL,
  column_default varchar(255) default NULL,
  is_nullable varchar(3) default NULL,
  column_type varchar(255) default NULL,
  character_maximum_length integer default NULL,
  data_type varchar(255) default NULL,
  extra varchar(200) default NULL,
  in_tipo text,
  in_default text,
  in_visibile integer DEFAULT 1,
  in_richiesto integer DEFAULT 0,
  commento varchar(255) default NULL,
  alias_frontend varchar(100) default NULL,
  CONSTRAINT pk_registro_submask_col PRIMARY KEY  (id_reg_sub),
  CONSTRAINT fk_registro_submask_col FOREIGN KEY (id_submask) REFERENCES registro_submask (id_submask) ON DELETE CASCADE
);

CREATE INDEX i_id_submask ON registro_submask_col (id_submask);





CREATE TABLE utente (
  id_utente integer,
  nick varchar(80) default NULL,
  passwd char(32) default NULL,
  nome varchar(50) default NULL,
  cognome varchar(50) default NULL,
  email varchar(80) default NULL,
  info text,
  data_ins date default CURRENT_TIMESTAMP,
  gid integer NOT NULL default '0',
  livello integer NOT NULL default '1',
  recover_passwd varchar(32) default NULL,
  CONSTRAINT pk_utente  PRIMARY KEY  (id_utente),
  CONSTRAINT u_id_utente UNIQUE (id_utente),
  CONSTRAINT fk_utente FOREIGN KEY (gid) REFERENCES gruppo (gid) ON UPDATE CASCADE
);

CREATE INDEX i_utente_gid ON utente (gid);



CREATE TABLE variabili (
  variabile varchar(32) NOT NULL,
  gid integer NOT NULL default 0,
  valore varchar(255) default NULL,
  descrizione varchar(255) default NULL,
  tipo_var varchar(20) default NULL,
  pubvar smallint NOT NULL DEFAULT 1,
  CONSTRAINT pk_variabili PRIMARY KEY  (variabile,gid)
);

CREATE INDEX i_variabile ON variabili (variabile);





CREATE TABLE allegato (
  codiceallegato integer , -- chiave primaria identificativa del record
  tipoentita varchar(100) default NULL , -- 'identifica l''entita del database alla quale l''utente vuole collegare il file allegato. L''entit? verr? riconosciuta dall''applicazione in base alle operazioni svolte in quella fase dall''utente.',
  codiceentita text default NULL , -- 'identifica la particolare occorrenza (record) dell''entità del database alla quale l''utente vuole collegare il file allegato',
  descroggall varchar(250) default NULL, -- 'descrizione dell''oggetto del file',
  autoreall varchar(250) default NULL , -- 'autore del file da allegare',
  versioneall varchar(250) default NULL , -- 'eventuale numero di versione del file ',
  lastdata timestamp NOT NULL default CURRENT_TIMESTAMP , --  'campo automaticamente valorizzato dal DBMS al primo inserimento o quando il record viene modificato',
  nomefileall varchar(250) NOT NULL,
  CONSTRAINT pk_allegato PRIMARY KEY (codiceallegato)
);

CREATE INDEX allegato_index_codiceentita ON allegato (codiceentita);
CREATE INDEX allegato_index_tipoentita ON allegato (tipoentita);


CREATE TABLE link (
  codicelink integer , --  'chiave primaria identificativa del record',
  tipoentita varchar(100) default NULL , --  'identifica l''entit? del database alla quale l''utente vuole abbinare il link ipertestuale. L''entit? verr? riconosciuta dall''applicazione in base alle operazioni svolte in quella fase dall''utente.',
  codiceentita integer default NULL , --  'identifica la particolare occorrenza (record) dell''entit? del database alla quale l''utente vuole abbinare il collegamento',
  link varchar(250) default NULL , --  'URL del link a cui si rimanda',
  descrizione varchar(250) default NULL,
  lastdata timestamp NOT NULL default CURRENT_TIMESTAMP , --  'campo automaticamente valorizzato dal DBMS al primo inserimento o quando il record viene modificato',
  CONSTRAINT pk_link PRIMARY KEY  (codicelink)
);

CREATE INDEX link_index_codiceentita ON link (codiceentita);
CREATE INDEX link_index_tipoentita ON link (tipoentita);





CREATE TABLE stat (
  id_stat integer,
  nome_stat varchar(250) NOT NULL , --  'Nome nella statistica',
  desc_stat text , --  'Descrizione della statistica',
  def_stat text , --  'Definizione della query SQL per la statistica',
  auth_stat integer DEFAULT 3 , --  'Tipo autorizzazione per statistica: 1=pubblica, 2=del gruppo, 3=personale',
  tipo_graph char(8) default 'barre',
  data_stat timestamp NOT NULL default CURRENT_TIMESTAMP,
  autore integer NOT NULL,
  settings text, --  'Impostazioni avanzate del grafico',
  published smallint NOT NULL DEFAULT 0, -- 'published on home page'
  CONSTRAINT pk_stat PRIMARY KEY  (id_stat)
  
);

CREATE INDEX i_autore_stat ON stat (autore);



CREATE TABLE xml_rules
(
  id_xml_rules integer,
  tabella varchar(50) NOT NULL,
  accesso varchar(20) DEFAULT 'RESTRICT',
  accesso_gruppo varchar(100),
  autore integer,
  lastdata timestamp DEFAULT CURRENT_TIMESTAMP,
  xsl varchar(80),
  xslfo varchar(80),
  tipo_report char(1) DEFAULT 't',
  def_query text, 
  nome_report varchar(255),
  CONSTRAINT pk_xml_rules PRIMARY KEY (id_xml_rules)
);








CREATE TABLE button
(
  id_button integer NOT NULL,
  id_table integer NOT NULL,
  definition text NOT NULL,
  button_type varchar(25) NOT NULL,
  background varchar(7),
  color varchar(7),
  button_name varchar(50) NOT NULL,
  last_data timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_utente integer NOT NULL,
  settings text,
  CONSTRAINT button_pkey PRIMARY KEY (id_button),
  CONSTRAINT fk_button_id_table FOREIGN KEY (id_table)
      REFERENCES registro_tab (id_table) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX i_button_id_table ON button (id_table);



CREATE TABLE widget (
  id_widget integer NOT NULL,
  id_table integer  NOT NULL,
  widget_name varchar(255) NOT NULL DEFAULT '',
  form_position varchar(11) NOT NULL DEFAULT '0',
  widget_type varchar(100) NOT NULL DEFAULT '',
  settings text NOT NULL,
  CONSTRAINT widget_pkey PRIMARY KEY (id_widget),
  CONSTRAINT fk_widget_id_table FOREIGN KEY (id_table) 
        REFERENCES registro_tab (id_table) MATCH SIMPLE
        ON UPDATE CASCADE ON DELETE CASCADE 
);

CREATE INDEX i_widget_id_table ON widget (id_table);


-- api_console

CREATE TABLE api_console (
  id integer NOT NULL,
  ip_address varchar(20) NOT NULL DEFAULT '',
  rw integer NOT NULL DEFAULT 0 ,
  api_key varchar(100) NOT NULL DEFAULT '',
  last_update timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT api_console_pkey PRIMARY KEY (id),
  CONSTRAINT u_apy_key UNIQUE (api_key)
);

CREATE TABLE cache_reg (
    id INTEGER PRIMARY KEY NOT NULL ,
    obj BLOB, 
    last_update DATETIME
);

-- registro_tab

CREATE TRIGGER fkdc_registro_col_registro_tab
BEFORE DELETE ON registro_tab
FOR EACH ROW BEGIN
    DELETE FROM registro_col WHERE registro_col.id_table = OLD.id_table;
    DELETE FROM registro_submask WHERE registro_submask.id_table = OLD.id_table;
    DELETE FROM button WHERE button.id_table = OLD.id_table;
END;

CREATE TRIGGER fki_registro_tab_gruppo
BEFORE INSERT ON registro_tab
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'insert on table "registro_tab" violates foreign key constraint "fki_registro_tab_gruppo"')
   WHERE (SELECT gid FROM gruppo WHERE gid = NEW.gid) IS NULL;
END;

CREATE TRIGGER fku_registro_tab_gruppo
BEFORE UPDATE ON registro_tab
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'update on table "registro_tab" violates foreign key constraint "fku_registro_tab_gruppo"')
   WHERE (SELECT  gid FROM gruppo WHERE gid = NEW.gid) IS NULL;
END;




-- registro_col

CREATE TRIGGER fki_registro_col_registro_tab
BEFORE INSERT ON registro_col
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'insert on table "registro_col" violates foreign key constraint "fki_registro_col_registro_tab"')
   WHERE (SELECT id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;

CREATE TRIGGER fku_registro_col_registro_tab
BEFORE UPDATE ON registro_col
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'update on table "registro_col" violates foreign key constraint "fku_registro_col_registro_tab"')
   WHERE (SELECT  id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;



-- registro_submask_col


CREATE TRIGGER fki_registro_submask_col_registro_submask
BEFORE INSERT ON registro_submask_col
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'insert on table "registro_submask_col" violates foreign key constraint "fki_registro_submask_col_registro_submask"')
   WHERE (SELECT id_submask FROM registro_submask WHERE id_submask = NEW.id_submask) IS NULL;
END;

CREATE TRIGGER fku_registro_submask_col_registro_submask
BEFORE UPDATE ON registro_submask_col
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'update on table "registro_submask_col" violates foreign key constraint "fku_registro_submask_col_registro_submask"')
   WHERE (SELECT  id_submask FROM registro_submask WHERE id_submask = NEW.id_submask) IS NULL;
END;


-- registro_submask


CREATE TRIGGER fkdc_registro_submask_col_registro_submask
BEFORE DELETE ON registro_submask
FOR EACH ROW BEGIN
    DELETE FROM registro_submask_col WHERE registro_submask_col.id_submask = OLD.id_submask;
END;

CREATE TRIGGER fki_registro_submask_registro_tab
BEFORE INSERT ON registro_submask
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'insert on table "registro_submask" violates foreign key constraint "fki_registro_submask_registro_tab"')
   WHERE (SELECT id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;

CREATE TRIGGER fku_registro_submask_registro_tab
BEFORE UPDATE ON registro_submask
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'update on table "registro_submask" violates foreign key constraint "fku_registro_submask_registro_tab"')
   WHERE (SELECT  id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;


-- button

CREATE TRIGGER fki_button_registro_tab
BEFORE INSERT ON button
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'insert on table "button" violates foreign key constraint "fki_button_registro_tab"')
   WHERE (SELECT id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;

CREATE TRIGGER fku_button_registro_tab
BEFORE UPDATE ON button
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'update on table "button" violates foreign key constraint "fku_button_registro_tab"')
   WHERE (SELECT  id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;



-- widget

CREATE TRIGGER fki_widget_registro_tab
BEFORE INSERT ON widget
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'insert on table "widget" violates foreign key constraint "fki_widget_registro_tab"')
   WHERE (SELECT id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;

CREATE TRIGGER fku_widget_registro_tab
BEFORE UPDATE ON widget
FOR EACH ROW BEGIN
   SELECT RAISE(ROLLBACK, 'update on table "widget" violates foreign key constraint "fku_widget_registro_tab"')
   WHERE (SELECT  id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
END;



-- registro_gruppo

CREATE TRIGGER fkdc_registro_tab_gruppo
BEFORE DELETE ON gruppo
FOR EACH ROW BEGIN
    DELETE FROM registro_tab WHERE registro_tab.gid = OLD.gid;
    UPDATE utente SET gid=0 WHERE utente.gid=OLD.gid;
END;






INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('alert_login_default',0,'1','Mostra agli utenti l''avviso se sono presenti nel gruppo di default','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('alert_config',0,'1','Mostra all''admin l''avviso in home page se è presente qualche errore nella configurazione','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('altezza_iframe_tabella',0,'360','Altezza in numero di pixel del rettangolo per visualizzare i dati in tabella','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('crea_nuovo_valore_ref',0,'0','permette in caso di tabella parent scrivibilie l''inserimento di nuovi valori nella medesima','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('cron_days_min', 0, '15', 'Numero di giorni minimi di anzianita dei file temporanei per essere eliminati', 'int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('formati_attach',0,'doc,xls,pdf,rtf,odt,sxw,ppt,odp,ods,gif,jpg,png,jpeg,zip,txt,csv,DOC,XLS,PDF,RTF,ODT,SXW,PPT,ODP,ODS,GIF,JPG,PNG,JPEG,ZIP,TXT,CSV','Formati permessi per i file allegati, separati da virgola','string');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('js_test',0,'1','Abilita i controlli javascript sui contenuti dei campi','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('layout',0,'default','Color theme','string');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('lang',0,'','Overwrite the group language','string');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('max_char_tabella',0,'200','Numero massimo di caratteri da visualizzare nelle viste a tabella. (0 = tutti)','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('max_tempo_edit',0,'300','Tempo di disponibilità del record','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('n_record_tabella',0,'20','Numero di record da visualizzare per le tabelle dei dati','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('passo_avanzamento_veloce',0,'20','Numero di record impostati per il movimento veloce tra i record nelle tabelle','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('textarea_cols',0,'50','Colonne per il box di testo','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('textarea_rows',0,'8','Righe per il box di testo','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('usa_calendari',0,'1','Indica se devono essere utilizzati o meno i calendari per facilitare l''inserimento nei campi data (0=no, 1=si)','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('usa_history',0,'1','Imposta se si deve utilizzare la history del browser','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('search_limit_results',0,'1000','Limite massimo di risultati per la ricerca','int');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('server_xslt',0,'1','Indica se utilizzare la trasformazione XSLT lato server - da disabilitare in caso di mancato supporto PHP(0=no, 1=si)','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('shortcut_tastiera_attivi',0,'1','Abilita le scorciatoie da tastiera nelle maschere delle tabelle e delle viste','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('shortcut_tastiera_popup',0,'1','Mostra una linguetta per leggere i comandi da tastiera nelle maschere','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('show_comment_in_table', 0, '1', 'Mostra il commento della tabella nella maschera di inserimento dati', 'bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('show_updates', 0, '1', 'Cerca update di VFront', 'bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('force_isodate_on_mask',0,'0','Forza il formato delle date in maschere e sottomaschere a ISO (ISO 8601) anche se specificato altro formato di date','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('recupero_password',0,'1','Se abilitato mostra nel login l''opzione di modificare la password (non funziona in caso di login esterno)','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('enable_adminer',0,'0','Enable Adminer schema administrator','bool');
INSERT INTO variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('home_redirect',0,'','After login redirect to custom page/table','string');


INSERT INTO gruppo (gid, nome_gruppo, descrizione_gruppo) VALUES 
  (-1,'temp','gruppo di installazione, viene eliminato nella inizializzazione');




INSERT INTO gruppo (gid, nome_gruppo, descrizione_gruppo) VALUES 
  (0,'default','gruppo di default');

