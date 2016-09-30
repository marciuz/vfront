--
--	SQL Script Postgres VFront $Id: vfront.postgres.sql 1108 2014-10-20 20:25:04Z marciuz $
--

--
-- Inizia la procedura
--

BEGIN;


CREATE SCHEMA "frontend" AUTHORIZATION "postgres";

COMMENT ON SCHEMA "frontend"
IS 'Schema dedicato alla gestione del frontend web.';





--
--	Crea la tabella gruppo
--

CREATE TABLE frontend.gruppo
(
  gid int8 NOT NULL, -- ID del gruppo
  nome_gruppo varchar(50) NOT NULL, -- Nome del gruppo
  descrizione_gruppo text,
  data_gruppo timestamp DEFAULT now(),
  CONSTRAINT pk_gruppo PRIMARY KEY (gid),
  CONSTRAINT u_gruppo_nome_gruppo UNIQUE (nome_gruppo)
) 
WITHOUT OIDS;
COMMENT ON COLUMN frontend.gruppo.gid IS 'ID del gruppo';
COMMENT ON COLUMN frontend.gruppo.nome_gruppo IS 'Nome del gruppo';





--
--	Crea la tabella log, dove vengono registrate le operazioni effettuate mediante le maschere
--

CREATE TABLE frontend.log
(
  id_log serial, 
  op varchar(20) NOT NULL, 
  uid int8 NOT NULL,
  gid int8 NOT NULL,
  data timestamp DEFAULT now(),
  tabella varchar(100) NOT NULL,
  id_record varchar(100) default NULL,
  storico_pre text,
  storico_post text,
  fonte char(1) NOT NULL default 'm',
  info_browser varchar(20) default NULL,
  CONSTRAINT pk_log PRIMARY KEY (id_log)
) 
WITHOUT OIDS;





--
--	Crea la tabella recordlock per il multiaccesso ai record
--

CREATE TABLE frontend.recordlock (
  tabella varchar(50) NOT NULL,
  colonna varchar(50) NOT NULL,
  id varchar(50) NOT NULL,
  tempo int4 NOT NULL,
  CONSTRAINT pk_recordlock PRIMARY KEY  (tabella,colonna,id)
) WITHOUT OIDS;




--
--	Crea la tabella registro tabelle, il registro di regole che vfront utilizzer? per gestire le tabelle
--

CREATE TABLE frontend.registro_tab (
  id_table serial,
  gid int8 default NULL,
  visibile int2 DEFAULT 0,
  in_insert int2 DEFAULT 0,
  in_duplica int2 DEFAULT 0,
  in_update int2 DEFAULT 0,
  in_delete int2 DEFAULT 0,
  in_export int2 DEFAULT 0,
  in_import int2 DEFAULT 0,
  data_modifica int4 default 0,
  orderby varchar(255) default NULL,
  table_name varchar(100) default NULL,
  table_type varchar(20) default 'BASE TABLE',
  commento varchar(255) default NULL,
  orderby_sort varchar(255) default 'ASC',
  permetti_allegati int2 DEFAULT 0,
  permetti_allegati_ins int2 DEFAULT 0,
  permetti_allegati_del int2 DEFAULT 0,
  permetti_link int2 DEFAULT 0,
  permetti_link_ins int2 DEFAULT 0,
  permetti_link_del int2 DEFAULT 0,
  view_pk varchar(60) default NULL,
  fonte_al varchar(100) default NULL,
  table_alias varchar(100) default NULL,
  allow_filters int2 DEFAULT 0,
  default_view varchar(5) DEFAULT 'form',
  default_filters text,
  CONSTRAINT pk_id_table PRIMARY KEY  (id_table),
  CONSTRAINT gid FOREIGN KEY (gid) REFERENCES frontend.gruppo (gid) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE
) WITHOUT OIDS;

CREATE INDEX i_table_name ON frontend.registro_tab USING btree (table_name);
CREATE INDEX i_gid ON frontend.registro_tab USING btree (gid);



--
--	Crea la tabella registro colonne, il registro di regole che vfront utilizzer? per gestire le colonne delle tabelle
--

CREATE TABLE frontend.registro_col (
  id_reg serial,
  id_table int8 default NULL,
  gid int8 default NULL,
  column_name varchar(255) default NULL,
  ordinal_position int2 default NULL,
  column_default varchar(255) default NULL,
  is_nullable varchar(3) default NULL,
  column_type varchar(255) default NULL,
  character_maximum_length int4 default NULL,
  data_type varchar(255) default NULL,
  extra varchar(200) default NULL,
  in_tipo text,
  in_default text,
  in_visibile int2 DEFAULT 1,
  in_richiesto int2 DEFAULT 0,
  in_suggest int2 DEFAULT 0,
  in_table int2 DEFAULT 1,
  in_line int2 DEFAULT NULL,
  in_ordine int2 default 0,
  jstest text,
  commento varchar(255) default NULL,
  alias_frontend varchar(100) default NULL,
  CONSTRAINT pk_registro_col PRIMARY KEY  (id_reg),
  CONSTRAINT fk_registro_col_1 FOREIGN KEY (id_table) REFERENCES frontend.registro_tab (id_table) ON DELETE CASCADE
) WITHOUT OIDS;

CREATE INDEX i_registro_col_gid ON frontend.registro_col USING btree (gid);
CREATE INDEX i_id_table ON frontend.registro_col USING btree (id_table);



--
--	Crea la tabella registro sottomaschere, il registro di regole che vfront utilizzer? per gestire le sottomaschere
--

CREATE TABLE frontend.registro_submask (
  id_submask serial,
  id_table int8 NOT NULL , -- Tabella parent per la sottomaschera
  sub_select  int2 DEFAULT 0,
  sub_insert  int2 DEFAULT 0,
  sub_update  int2 DEFAULT 0,
  sub_delete  int2 DEFAULT 0,
  nome_tabella varchar(255) default NULL ,-- Tabella fonte per la sottomaschera
  nome_frontend varchar(250) default NULL ,-- Nome per la sottomaschera che apparir? nella maschera utente
  campo_pk_parent varchar(80) default NULL ,-- Campo che rappresenta la chiave primaria nella tabella parent
  campo_fk_sub varchar(80) default NULL ,-- Campo che rappresenta la chiave esterna rispetto alla tabella parent
  orderby_sub varchar(80) default NULL, -- Campo orderby della sottomaschera
  orderby_sub_sort char(4) default 'ASC',
  data_modifica int8 default NULL,
  max_records int2 default '10',
  tipo_vista varchar(8) NOT NULL default 'scheda',
   CONSTRAINT pk_registro_submask PRIMARY KEY  (id_submask),
  -- CONSTRAINT u_registro_submask_nome_gruppo UNIQUE (id_table,nome_tabella),
  CONSTRAINT fk_registro_submask FOREIGN KEY (id_table) REFERENCES frontend.registro_tab (id_table) ON DELETE CASCADE
) WITHOUT OIDS;




--
--	Crea la tabella registro delle colonne delle sottomaschere, il registro di regole che vfront utilizzer? per gestire le colonne nelle sottomaschere
--


CREATE TABLE frontend.registro_submask_col (
  id_reg_sub serial,
  id_submask int8 NOT NULL,
  column_name varchar(255) default NULL,
  ordinal_position int2 default NULL,
  column_default varchar(255) default NULL,
  is_nullable varchar(3) default NULL,
  column_type varchar(255) default NULL,
  character_maximum_length int4 default NULL,
  data_type varchar(255) default NULL,
  extra varchar(200) default NULL,
  in_tipo text,
  in_default text,
  in_visibile int2 DEFAULT 1,
  in_richiesto int2 DEFAULT 0,
  commento varchar(255) default NULL,
  alias_frontend varchar(100) default NULL,
  CONSTRAINT pk_registro_submask_col PRIMARY KEY  (id_reg_sub),
  
  CONSTRAINT fk_registro_submask_col FOREIGN KEY (id_submask) REFERENCES frontend.registro_submask (id_submask) ON DELETE CASCADE
) WITHOUT OIDS;

CREATE INDEX i_id_submask ON frontend.registro_submask_col USING btree (id_submask);



--
--	Crea la tabella utente, per accreditamento dei diritti degli utenti ed (eventuale) autenticazione
--


CREATE TABLE frontend.utente (
  id_utente serial,
  nick varchar(80) default NULL,
  passwd char(32) default NULL,
  nome varchar(50) default NULL,
  cognome varchar(50) default NULL,
  email varchar(80) default NULL,
  info text,
  data_ins date default now(),
  gid int8 NOT NULL default '0',
  livello int2 NOT NULL default '1',
  recover_passwd varchar(32) default NULL,
  CONSTRAINT pk_utente  PRIMARY KEY  (id_utente),
  CONSTRAINT u_id_utente UNIQUE (id_utente),
  CONSTRAINT fk_utente FOREIGN KEY (gid) REFERENCES frontend.gruppo (gid) ON UPDATE CASCADE
) WITHOUT OIDS;

CREATE INDEX i_utente_gid ON frontend.utente USING btree (gid);


--
--	Crea la tabella delle variabili, dove vengono archiviati alcuni parametri di ambiente modificabili 
--

CREATE TABLE frontend.variabili (
  variabile varchar(32) NOT NULL,
  gid int8 NOT NULL default 0,
  valore varchar(255) default NULL,
  descrizione varchar(255) default NULL,
  tipo_var varchar(20) default NULL,
  pubvar smallint NOT NULL DEFAULT 1,
  CONSTRAINT pk_variabili PRIMARY KEY  (variabile,gid)
) WITHOUT OIDS;

CREATE INDEX i_variabile ON frontend.variabili USING btree (variabile);



--
--	Crea la tabella allegato, dove vengono archiviate le informazioni dei file associati ai record
--


CREATE TABLE frontend.allegato (
  codiceallegato serial , -- chiave primaria identificativa del record
  tipoentita varchar(100) default NULL , -- 'identifica l''entita del database alla quale l''utente vuole collegare il file allegato. L''entit? verr? riconosciuta dall''applicazione in base alle operazioni svolte in quella fase dall''utente.',
  codiceentita varchar(255) default NULL , -- 'identifica la particolare occorrenza (record) dell''entità del database alla quale l''utente vuole collegare il file allegato',
  descroggall varchar(250) default NULL, -- 'descrizione dell''oggetto del file',
  autoreall varchar(250) default NULL , -- 'autore del file da allegare',
  versioneall varchar(250) default NULL , -- 'eventuale numero di versione del file ',
  lastdata timestamp NOT NULL default CURRENT_TIMESTAMP , --  'campo automaticamente valorizzato dal DBMS al primo inserimento o quando il record viene modificato',
  nomefileall varchar(250) NOT NULL,
  CONSTRAINT pk_allegato PRIMARY KEY (codiceallegato)
) WITHOUT OIDS;



--
--	Crea la tabella link, dove vengono archiviati i link associati ai record
--

CREATE TABLE frontend.link (
  codicelink serial , --  'chiave primaria identificativa del record',
  tipoentita varchar(100) default NULL , --  'identifica l''entit? del database alla quale l''utente vuole abbinare il link ipertestuale. L''entit? verr? riconosciuta dall''applicazione in base alle operazioni svolte in quella fase dall''utente.',
  codiceentita varchar(255) default NULL , --  'identifica la particolare occorrenza (record) dell''entit? del database alla quale l''utente vuole abbinare il collegamento',
  link varchar(250) default NULL , --  'URL del link a cui si rimanda',
  descrizione varchar(250) default NULL,
  lastdata timestamp NOT NULL default CURRENT_TIMESTAMP , --  'campo automaticamente valorizzato dal DBMS al primo inserimento o quando il record viene modificato',
  CONSTRAINT pk_link PRIMARY KEY  (codicelink)
) WITHOUT OIDS;



--
--	Crea la tabella statistiche, dove vengono archiviate le regole per le statistiche personalizzate
--


CREATE TABLE frontend.stat (
  id_stat serial,
  nome_stat varchar(250) NOT NULL , --  'Nome nella statistica',
  desc_stat text , --  'Descrizione della statistica',
  def_stat text , --  'Definizione della query SQL per la statistica',
  auth_stat int2 DEFAULT 3 , --  'Tipo autorizzazione per statistica: 1=pubblica, 2=del gruppo, 3=personale',
  tipo_graph varchar(8) default 'barre',
  data_stat timestamp NOT NULL default now(),
  autore int8 NOT NULL,
  settings text, --  'Impostazioni avanzate del grafico',
  published smallint NOT NULL DEFAULT 0, -- 'published on home page'
  CONSTRAINT pk_stat PRIMARY KEY  (id_stat)
  
) WITHOUT OIDS;

CREATE INDEX i_autore_stat ON frontend.stat USING btree (autore);


--
--	Crea la tabella xml_rules, dove vengono archiviate le regole per la generazione dei report XML-based
--

CREATE TABLE frontend.xml_rules
(
  id_xml_rules serial,
  tabella varchar(50) NOT NULL,
  accesso varchar(20) DEFAULT 'RESTRICT'::character varying, 
  accesso_gruppo varchar(100),
  autore int4,
  lastdata timestamp DEFAULT now(),
  xsl varchar(80),
  xslfo varchar(80),
  tipo_report char(1) DEFAULT 't'::bpchar,
  def_query text, 
  nome_report varchar(255),
  CONSTRAINT pk_xml_rules PRIMARY KEY (id_xml_rules)
) 
WITHOUT OIDS;

COMMENT ON COLUMN frontend.xml_rules.accesso IS 'RESTRICT,PUBLIC,FRONTEND,GROUP';
COMMENT ON COLUMN frontend.xml_rules.tipo_report IS 'Report basato su tabella (t) o su query (q)';
COMMENT ON COLUMN frontend.xml_rules.def_query IS 'Query di definizione per i report di tipo "query"';




--
--	Crea la tabella button
--

CREATE TABLE frontend.button
(
  id_button serial NOT NULL,
  id_table integer NOT NULL,
  definition text NOT NULL,
  button_type character varying(25) NOT NULL,
  background character varying(7),
  color character varying(7),
  button_name character varying(50) NOT NULL,
  last_data timestamp without time zone NOT NULL DEFAULT now(),
  id_utente integer NOT NULL,
  settings text,
  CONSTRAINT button_pkey PRIMARY KEY (id_button),
  CONSTRAINT fk_button_id_table FOREIGN KEY (id_table)
      REFERENCES frontend.registro_tab (id_table) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITHOUT OIDS;
CREATE INDEX i_button_id_table ON frontend.button USING btree (id_table);


--
--  Table widget
--

CREATE TABLE frontend.widget (
  id_widget serial NOT NULL,
  id_table integer  NOT NULL,
  widget_name character varying(255) NOT NULL DEFAULT '',
  form_position character varying(11) NOT NULL DEFAULT '0',
  widget_type character varying(100) NOT NULL DEFAULT '',
  settings text NOT NULL,
  CONSTRAINT widget_pkey PRIMARY KEY (id_widget),
  CONSTRAINT fk_widget_id_table FOREIGN KEY (id_table) 
        REFERENCES frontend.registro_tab (id_table) MATCH SIMPLE
        ON UPDATE CASCADE ON DELETE CASCADE 
)
WITHOUT OIDS;
CREATE INDEX i_widget_id_table ON frontend.widget USING btree (id_table);



-- api_console

CREATE TABLE frontend.api_console (
  id serial NOT NULL,
  ip_address character varying(20) NOT NULL DEFAULT '',
  rw integer NOT NULL DEFAULT 0 ,
  api_key character varying(100) NOT NULL DEFAULT '',
  last_update timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT api_console_pkey PRIMARY KEY (id),
  CONSTRAINT u_apy_key UNIQUE (api_key)
);

CREATE INDEX i_api_key ON frontend.api_console USING btree (api_key);


CREATE TABLE frontend.cache_reg (
    id serial NOT NULL,
    obj bytea,
    last_update timestamp without time zone ,
    CONSTRAINT cache_reg_pkey PRIMARY KEY (id)
);


--
--	Inserisce i dati di default nella tabella variabili
--

INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('alert_login_default',0,'1','Mostra agli utenti l''avviso se sono presenti nel gruppo di default','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('alert_config',0,'1','Mostra all''admin l''avviso in home page se è presente qualche errore nella configurazione','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('altezza_iframe_tabella',0,'360','Altezza in numero di pixel del rettangolo per visualizzare i dati in tabella','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('crea_nuovo_valore_ref',0,'0','permette in caso di tabella parent scrivibilie l''inserimento di nuovi valori nella medesima','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('cron_days_min', 0, '15', 'Numero di giorni minimi di anzianita dei file temporanei per essere eliminati', 'int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('formati_attach',0,'doc,xls,pdf,rtf,odt,sxw,ppt,odp,ods,gif,jpg,png,jpeg,zip,txt,csv,DOC,XLS,PDF,RTF,ODT,SXW,PPT,ODP,ODS,GIF,JPG,PNG,JPEG,ZIP,TXT,CSV','Formati permessi per i file allegati, separati da virgola','string');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('js_test',0,'1','Abilita i controlli javascript sui contenuti dei campi','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('layout',0,'default','Color theme','string');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('lang',0,'','Overwrite the group language','string');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('max_char_tabella',0,'200','Numero massimo di caratteri da visualizzare nelle viste a tabella. (0 = tutti)','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('max_tempo_edit',0,'300','Tempo di disponibilità del record','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('n_record_tabella',0,'20','Numero di record da visualizzare per le tabelle dei dati','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('passo_avanzamento_veloce',0,'20','Numero di record impostati per il movimento veloce tra i record nelle tabelle','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('textarea_cols',0,'50','Colonne per il box di testo','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('textarea_rows',0,'8','Righe per il box di testo','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('usa_calendari',0,'1','Indica se devono essere utilizzati o meno i calendari per facilitare l''inserimento nei campi data (0=no, 1=si)','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('usa_history',0,'1','Imposta se si deve utilizzare la history del browser','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('search_limit_results',0,'1000','Limite massimo di risultati per la ricerca','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('server_xslt',0,'1','Indica se utilizzare la trasformazione XSLT lato server - da disabilitare in caso di mancato supporto PHP(0=no, 1=si)','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('shortcut_tastiera_attivi',0,'1','Abilita le scorciatoie da tastiera nelle maschere delle tabelle e delle viste','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('shortcut_tastiera_popup',0,'1','Mostra una linguetta per leggere i comandi da tastiera nelle maschere','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('show_comment_in_table', '0', '1', 'Mostra il commento della tabella nella maschera di inserimento dati', 'bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('show_updates', '0', '1', 'Cerca update di VFront', 'bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('force_isodate_on_mask',0,'0','Forza il formato delle date in maschere e sottomaschere a ISO (ISO 8601) anche se specificato altro formato di date','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('recupero_password',0,'1','Se abilitato mostra nel login l''opzione di modificare la password (non funziona in caso di login esterno)','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('enable_adminer',0,'0','Enable Adminer schema administrator','bool');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('default_group_ext',0,'0','The default group for external auth','int');
INSERT INTO frontend.variabili (variabile, gid, valore, descrizione, tipo_var) VALUES   ('home_redirect',0,'','After login redirect to custom page/table','string');

--
--	Inserisce un gruppo fittizio, utile alla inizializzazione del sistema
--

INSERT INTO frontend.gruppo (gid, nome_gruppo, descrizione_gruppo) VALUES 
  (-1,'temp','gruppo di installazione, viene eliminato nella inizializzazione');



--
--	Inserisce il gruppo di default
--

INSERT INTO frontend.gruppo (gid, nome_gruppo, descrizione_gruppo) VALUES 
  (0,'default','gruppo di default');



--
--	Se è andato tutto bene, conferma le query eseguite
--

COMMIT;

