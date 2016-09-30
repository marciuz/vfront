<?php
/**
* Update script
* Test and version upgrade
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 Mario Marcello Verona
* @version 0.96d $Id: update.php 1131 2014-12-17 12:31:14Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
* @todo Verificare con utility se se operazioni sono eseguibili dall'autore, in caso contrario segnalare le operazioni da compiere
*/

require_once("../inc/conn.php");
require_once("../inc/layouts.php");

proteggi(3);


function check_privileges($priv){
	
	global  $vmsql, $vmreg, $db1;
	
	$GRANTEE=$vmsql->escape("'".$db1['user']."'@'".$db1['host']."'");
	
	if($db1['dbtype']=='mysql'){
		$sql="SELECT PRIVILEGE_TYPE FROM information_schema.SCHEMA_PRIVILEGES 
			  WHERE TABLE_SCHEMA='".$db1['frontend']."' AND GRANTEE='$GRANTEE'";
		
		$q=@$vmsql->query($sql);
		
		if(@$vmsql->num_rows($q)>0){
			return true;
		}
		else{
			return false;
		}
	}
	else{
		
		// No check for Postgres
		return false;
	}
	
}



##################################################
#
#	VERSION 0.95
#
#


// TEST VERSION < 0.95
function update_test_090(){
	
	global  $vmsql, $vmreg, $db1;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE){
	    
	    return false;
	}
	
	
	$q_test=$vmsql->query("SELECT CHARACTER_MAXIMUM_LENGTH 
						FROM information_schema.COLUMNS
						WHERE TABLE_SCHEMA = '{$db1['frontend']}'
						AND TABLE_NAME = 'registro_tab'
						AND COLUMN_NAME = 'orderby'");
						
	if($vmsql->num_rows($q_test)==0){
		
		list($length)=$vmsql->fetch_row($q_test);
		
		if($length<255){
			return true;
		}
		else{
			return false;
		}
	}
	else return false;
	
}

// EXEC VERSION < 0.95
function update_exec_090(){
	
	global $db1;
	
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}log CHANGE op op ENUM( 'insert', 'update', 'delete', 'select', 'sconosciuta', 'ripristino', 'duplicazione', 'import' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;";
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD in_import TINYINT( 1 ) UNSIGNED NULL AFTER in_export ;";
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab CHANGE orderby orderby VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ;";
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab CHANGE orderby_sort orderby_sort VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'ASC';";
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}stat ADD settings TEXT COMMENT 'Impostazioni avanzate del grafico';";
	
	return _update_exec('0.90',$sql_up);
}



// TEST VERSION 0.95
function update_test_095(){
	
	global $vmreg, $db1;
	
	$q_test=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}variabili WHERE variabile='layout'");
	
	if($vmreg->num_rows($q_test)==0){
		return true;
	}
	else return false;
}


// EXEC VERSION 0.95
function update_exec_095(){
	
	global $db1;

	$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('layout',0,'default','Color theme','string');";

	return _update_exec('0.95',$sql_up);
}









##################################################
#
#	VERSION 0.95a
#
#


// TEST VERSION 0.95a
function update_test_095a(){
	
	global $db1, $vmsql, $vmreg;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE){
	    
	    return false;
	}
	
	$q_test=$vmsql->query("SELECT *
						FROM information_schema.COLUMNS
						WHERE TABLE_SCHEMA = '{$db1['frontend']}'
						AND TABLE_NAME = 'registro_col'
						AND COLUMN_NAME = 'in_line'");
						
	if($vmsql->num_rows($q_test)==0){
		return true;
	}
	else return false;
}


// EXEC VERSION 0.95a
function update_exec_095a(){
	
	global $db1;

	
	if($db1['dbtype']=='mysql'){
	
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}allegato  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}gruppo  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}link  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}log  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}recordlock  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_col  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_submask  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_submask_col  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}utente  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}variabili  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}xml_rules  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
	
		$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('alert_login_default',0,'1','Mostra agli utenti l\'avviso se sono presenti nel gruppo di default','bool');";
		$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('alert_config',0,'1','Mostra all\'admin l\'avviso in home page se è presente qualche errore nella configurazione','bool');";
		$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']} variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('show_comment_in_table', '0', '0', 'Mostra il commento della tabella nella maschera di inserimento dati', 'bool');";
	
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_col ADD in_line TINYINT(1) NULL AFTER in_table;";
	
		
	}
	else{
		
		$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('alert_login_default',0,'1','Mostra agli utenti l\'avviso se sono presenti nel gruppo di default','bool');";
		$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('alert_config',0,'1','Mostra all\'admin l\'avviso in home page se è presente qualche errore nella configurazione','bool');";
		$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('show_comment_in_table', 0, '0', 'Mostra il commento della tabella nella maschera di inserimento dati', 'bool');";
	
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_col ADD in_line smallint NULL ;";
	}
	
	
	return _update_exec('0.95a',$sql_up);
}




###########################################
#
#	VERSION 0.95b
#	// change in CONF
#




###########################################
#
#	VERSION 0.95c
#






// TEST VERSION 0.95c
function update_test_095c(){
	
	global $db1, $vmsql, $vmreg;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE) return false;
	
	$q_test=$vmsql->query("SELECT *
						FROM information_schema.COLUMNS
						WHERE TABLE_SCHEMA = '{$db1['frontend']}'
						AND TABLE_NAME = 'variabili'
						AND COLUMN_NAME = 'pubvar'");
						
	if($vmsql->num_rows($q_test)==0){
		return true;
	}
	else return false;
}



// EXEC VERSION 0.95a
function update_exec_095c(){
	
	global $db1;
	
	if($db1['dbtype']=='mysql'){
	
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}variabili ADD pubvar TINYINT(1) UNSIGNED NOT NULL DEFAULT '1';";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}variabili CHANGE valore valore TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
		$sql_up[]="UPDATE {$db1['frontend']}{$db1['sep']}variabili SET pubvar=0 WHERE variabile='layout';";
		
	}
	else{
		
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}variabili ADD pubvar smallint NOT NULL DEFAULT 1;";
		$sql_up[]="UPDATE {$db1['frontend']}{$db1['sep']}variabili SET pubvar=0 WHERE variabile='layout';";
	}
	
	return _update_exec('0.95c',$sql_up);
	
}







###########################################
#
#	VERSION 0.95d -- no changes in DB
#






###########################################
#
#	VERSION 0.95e
#




// TEST VERSION 0.95c
function update_test_095e(){
	
	global $db1, $vmsql, $vmreg;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE) return false;
	
	$q_test=$vmsql->query("SELECT *
						FROM information_schema.COLUMNS
						WHERE TABLE_SCHEMA = '{$db1['frontend']}'
						AND TABLE_NAME = 'utente'
						AND COLUMN_NAME = 'recover_passwd'");
						
	if($vmsql->num_rows($q_test)==0){
		return true;
	}
	else return false;
}







// EXEC VERSION 0.95e
function update_exec_095e(){
	
	global $db1;
	
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}utente ADD recover_passwd VARCHAR( 32 ) NULL ;";
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}utente ADD UNIQUE (email) ;";
	
	return _update_exec('0.95e',$sql_up);
	
}




###########################################
#
#	VERSION 0.95f
#




// TEST VERSION 0.95f
function update_test_095f(){
	
	global $db1, $vmsql, $vmreg;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE) return false;
	
	$q_test=$vmsql->query("SELECT * 
						 FROM information_schema.COLUMNS 
						 WHERE TABLE_SCHEMA='{$db1['frontend']}' AND TABLE_NAME='stat' AND COLUMN_NAME='published'");
						
						
	if($vmsql->num_rows($q_test)==0){
		return true;
	}
	else return false;
}







// EXEC VERSION 0.95f
function update_exec_095f(){
	
	global $db1;
	
	if($db1['dbtype']=='mysql'){
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}utente CHANGE nick nick VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}stat ADD published TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'published on home page';";
	}
	else if($db1['dbtype']=='postgres'){
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}utente ALTER nick TYPE character varying(80);";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}stat ADD COLUMN published smallint NOT NULL DEFAULT 0;";
		$sql_up[]="COMMENT ON COLUMN {$db1['frontend']}{$db1['sep']}stat.published IS 'published on home page';";
	}
	
	
	return _update_exec('0.95f',$sql_up);
	
}


###########################################
#
#	VERSION 0.95g
#




// TEST VERSION 0.95g
function update_test_095g(){
	
	global $db1, $vmsql, $vmreg;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE) return false;
	
	$q_test=$vmsql->query("SELECT * 
						 FROM information_schema.TABLES 
						 WHERE TABLE_SCHEMA='{$db1['frontend']}' AND TABLE_NAME='button' ");
						
						
	if($vmsql->num_rows($q_test)==0){
		return true;
	}
	else return false;
}







// EXEC VERSION 0.95g
function update_exec_095g(){
	
	global $db1;
	
	if($db1['dbtype']=='mysql'){
		
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}stat DROP INDEX `id_stat`;";
		$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}stat ADD INDEX ( `published` ) ;";
		
		$sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}button (
		  id_button INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		  id_table INT(10) NOT NULL,
		  definition TEXT COLLATE utf8_unicode_ci NOT NULL,
		  button_type VARCHAR(25) COLLATE utf8_unicode_ci NOT NULL,
		  background VARCHAR(7) COLLATE utf8_unicode_ci NOT NULL,
		  color VARCHAR(7) COLLATE utf8_unicode_ci NOT NULL,
		  button_name VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
		  last_data TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  id_utente INT(11) UNSIGNED NOT NULL,
		  PRIMARY KEY  (id_button),
		  KEY id_table (id_table),
		  KEY id_utente (id_utente),
		  CONSTRAINT button_ibfk_1 FOREIGN KEY (id_table) REFERENCES {$db1['frontend']}{$db1['sep']}registro_tab (id_table) 
		  ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		";
		
		
		
		
		
		
	}
	else if($db1['dbtype']=='postgres'){
		
		$sql_up[]="CREATE INDEX i_stat_published ON {$db1['frontend']}{$db1['sep']}stat USING btree (published);";
		
		$sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}button
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
		  CONSTRAINT button_pkey PRIMARY KEY (id_button),
		  CONSTRAINT fk_button_id_table FOREIGN KEY (id_table)
		      REFERENCES {$db1['frontend']}{$db1['sep']}registro_tab (id_table) MATCH SIMPLE
		      ON UPDATE CASCADE ON DELETE CASCADE
		)
		WITHOUT OIDS;";
		
		$sql_up[]="CREATE INDEX i_button_id_table ON {$db1['frontend']}{$db1['sep']}button USING btree (id_table);";
		
	}
	
	
	return _update_exec('0.95g',$sql_up);
	
}



###########################################
#
#	VERSION 0.95h
#




// TEST VERSION 0.95h
function update_test_095h(){
	
	global $db1, $vmsql, $vmreg;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE) return false;
	
	$q_test=$vmsql->query("SELECT * 
						 FROM information_schema.COLUMNS 
						 WHERE TABLE_SCHEMA='{$db1['frontend']}' AND TABLE_NAME='button' AND COLUMN_NAME='settings'");
						
						
	if($vmsql->num_rows($q_test)==0){
		return true;
	}
	else return false;
}




// EXEC VERSION 0.95h
function update_exec_095h(){
	
	global $db1;

	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}button ADD settings TEXT NULL ;";
	
	$sql_up[]="INSERT INTO ${db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('search_limit_results',0,'1000','Max records in search results','int');";
		
	return _update_exec('0.95h',$sql_up);
	
}



// VERSION 0.95i
function update_test_095i(){
	
	global $db1, $vmreg;
	
	$q_test=$vmreg->query("SELECT * 
				FROM {$db1['frontend']}{$db1['sep']}variabili 
				WHERE variabile='lang' ");
						
						
	if($vmreg->num_rows($q_test)==0){
		return true;
	}
	else return false;
}



// EXEC VERSION 0.95i
function update_exec_095i(){
	
	global $db1;
	
	$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('lang',0,'','Overwrite the default language','string');";
		
	return _update_exec('0.95h',$sql_up);
}





// VERSION 0.95i
function update_test_095l(){
	
	
	global $db1, $vmsql, $vmreg;
	
	$q_test=$vmreg->query("SELECT * 
						 FROM {$db1['frontend']}{$db1['sep']}variabili 
						 WHERE variabile='show_updates' ");
						
						
	if($vmreg->num_rows($q_test)==0){
		return true;
	}
	else return false;
}



// EXEC VERSION 0.95i
function update_exec_095l(){
	
	global $db1;
	
	$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile, gid, valore, descrizione, tipo_var) VALUES ('show_updates',0,1,'Cerca update per VFront','bool');";
		
	return _update_exec('0.95l',$sql_up);
}






// VERSION 0.95m
function update_test_095m(){
	
	global $db1, $vmsql, $vmreg;
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE) return false;
	
	$q_test=$vmsql->query("SELECT * 
						 FROM information_schema.COLUMNS 
						 WHERE TABLE_SCHEMA='{$db1['frontend']}' AND TABLE_NAME='registro_tab' AND COLUMN_NAME='table_alias'");
						
						
	if($vmsql->num_rows($q_test)==0){
		return true;
	}
	else return false;
}



// EXEC VERSION 0.95m
function update_exec_095m(){
	
	global $db1;
	
	$sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD table_alias varchar(100) DEFAULT NULL ;";
		
	return _update_exec('0.95m',$sql_up);
}





function update_test_096(){

	global $db1, $vmsql, $vmreg;

	$q_test=$vmreg->query("SELECT *
						 FROM {$db1['frontend']}{$db1['sep']}variabili
						 WHERE variabile='enable_adminer' ");


	if($vmreg->num_rows($q_test)==0){
		return true;
	}
	else return false;
	
}

// EXEC VERSION 0.96
function update_exec_096(){

	global $db1;
	
	$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile,gid,valore,descrizione,tipo_var,pubvar) VALUES ( 'enable_adminer','0','0','Enable Adminer schema administrator','bool','1')";

	return _update_exec('0.96',$sql_up);
}



function update_test_097(){

	global $db1, $vmsql, $vmreg;

	$q_test=$vmreg->query("SELECT *
			    FROM {$db1['frontend']}{$db1['sep']}variabili
			    WHERE variabile='home_redirect' ");


	if($vmreg->num_rows($q_test)==0){
		return true;
	}
	else return false;
	
}

// EXEC VERSION 0.97
function update_exec_097(){

	global $db1;
	
	$sql_up[]="INSERT INTO {$db1['frontend']}{$db1['sep']}variabili (variabile,gid,valore,descrizione,tipo_var) VALUES ( 'home_redirect',0,'','After login redirect to custom page/table','string')";

	return _update_exec('0.97',$sql_up);
}


function update_test_097a(){

	global $db1, $vmsql, $vmreg;
	   
	$q_test=$vmreg->query_try("SELECT 1 FROM {$db1['frontend']}{$db1['sep']}widget");

	if($q_test==0){
		return true;
	}
	else return false;
	
}

// EXEC VERSION 0.97a
function update_exec_097a(){

	global $db1;
        
        $sql_up=array();
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE){

	    $sql_up[]="CREATE TABLE IF NOT EXISTS widget (
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
		);";

	    $sql_up[]="CREATE INDEX i_widget_id_table ON widget (id_table);";

	    $sql_up[]="CREATE TRIGGER fki_widget_registro_tab
		    BEFORE INSERT ON widget
		    FOR EACH ROW BEGIN
		    SELECT RAISE(ROLLBACK, 'insert on table \"widget\" violates foreign key constraint \"fki_widget_registro_tab\"')
		    WHERE (SELECT id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
		END;";

	    $sql_up[]="CREATE TRIGGER fku_widget_registro_tab
		    BEFORE UPDATE ON widget
		    FOR EACH ROW BEGIN
		    SELECT RAISE(ROLLBACK, 'update on table \"widget\" violates foreign key constraint \"fku_widget_registro_tab\"')
		    WHERE (SELECT  id_table FROM registro_tab WHERE id_table = NEW.id_table) IS NULL;
		END;";

	     //$sql_up[]="ALTER TABLE registro_submask DROP CONSTRAINT u_registro_submask_nome_gruppo; ";
	    
	    // Modifica tabelle
	    
	    $sql_up[]="CREATE TABLE registro_submask_temp (
		  id_submask integer,
		  id_table integer NOT NULL , 
		  sub_select  integer DEFAULT 0,
		  sub_insert  integer DEFAULT 0,
		  sub_update  integer DEFAULT 0,
		  sub_delete  integer DEFAULT 0,
		  nome_tabella varchar(255) default NULL ,
		  nome_frontend varchar(250) default NULL ,
		  campo_pk_parent varchar(80) default NULL ,
		  campo_fk_sub varchar(80) default NULL ,
		  orderby_sub varchar(80) default NULL,
		  orderby_sub_sort char(4) default 'ASC',
		  data_modifica integer default NULL,
		  max_records integer default '10',
		  tipo_vista varchar(8) NOT NULL default 'scheda',
		   CONSTRAINT pk_registro_submask PRIMARY KEY  (id_submask),
		  CONSTRAINT fk_registro_submask FOREIGN KEY (id_table) REFERENCES registro_tab (id_table) ON DELETE CASCADE
		);";

	    $sql_up[]="INSERT INTO registro_submask_temp SELECT * FROM registro_submask;";
	    $sql_up[]="ALTER TABLE registro_submask RENAME TO registro_submask_old;";
	    $sql_up[]="ALTER TABLE registro_submask_temp RENAME TO registro_submask;";
	    $sql_up[]="DROP TABLE registro_submask_old;";



	    $sql_up[]="CREATE TABLE allegato_temp (
	      codiceallegato integer , 
	      tipoentita varchar(100) default NULL , 
	      codiceentita text default NULL , 
	      descroggall varchar(250) default NULL,
	      autoreall varchar(250) default NULL , 
	      versioneall varchar(250) default NULL ,
	      lastdata timestamp NOT NULL default CURRENT_TIMESTAMP , 
	      nomefileall varchar(250) NOT NULL,
	      CONSTRAINT pk_allegato PRIMARY KEY (codiceallegato)
	    );";

	    $sql_up[]="INSERT INTO allegato_temp SELECT * FROM allegato;";
	    $sql_up[]="ALTER TABLE allegato RENAME TO allegato_old;";
	    $sql_up[]="ALTER TABLE allegato_temp RENAME TO allegato;";
	    $sql_up[]="DROP TABLE allegato_old;";
	    
	}
	
	
        else if($db1['dbtype']=='mysql'){
	
            $sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}widget (
                id_widget int(10) unsigned NOT NULL AUTO_INCREMENT,
                id_table int(10) NOT NULL,
                widget_name varchar(255) NOT NULL DEFAULT '',
                form_position varchar(11) NOT NULL DEFAULT '0',
                widget_type varchar(100) NOT NULL DEFAULT '',
                settings text NOT NULL,
                PRIMARY KEY (id_widget),
                KEY i_widget_id_table (id_table),
                CONSTRAINT fk_widget_id_table FOREIGN KEY (id_table) REFERENCES registro_tab (id_table) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Widget table';";
            
            
            $sql_up[]="ALTER TABLE  {$db1['frontend']}{$db1['sep']}registro_submask CHANGE  `tipo_vista`  `tipo_vista` ENUM(  'tabella',  'scheda',  'embed', 'schedash') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'scheda'";
            $sql_up[]="ALTER TABLE  {$db1['frontend']}{$db1['sep']}allegato CHANGE  `codiceentita`  `codiceentita` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
            
            //$sql_up[]="DROP INDEX u_idtable_nometabella ON {$db1['frontend']}{$db1['sep']}registro_submask";
        }
        else if($db1['dbtype']=='postgres'){
            
             $sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}widget (
                id_widget serial,
                id_table integer NOT NULL,
                widget_name varchar(255) NOT NULL DEFAULT '',
                form_position varchar(11) NOT NULL DEFAULT '0',
                widget_type varchar(100) NOT NULL DEFAULT '',
                settings text NOT NULL,
                CONSTRAINT widget_pkey PRIMARY KEY (id_widget),
		  CONSTRAINT fk_widget_id_table FOREIGN KEY (id_table)
		      REFERENCES {$db1['frontend']}{$db1['sep']}registro_tab (id_table) MATCH SIMPLE
		      ON UPDATE CASCADE ON DELETE CASCADE
		)
		WITHOUT OIDS;";
                      
             
            $sql_up[]="CREATE INDEX i_widget_id_table ON {$db1['frontend']}{$db1['sep']}widget USING btree (id_table);";
            
            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_submask DROP CONSTRAINT u_registro_submask_nome_gruppo; ";

            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_submask ALTER COLUMN tipo_vista type varchar(8) using rtrim(tipo_vista);";
            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}allegato ALTER COLUMN codiceentita type varchar(255) using rtrim(codiceentita);";
        }
	
        
	return _update_exec('0.97a',$sql_up);
}


function update_test_097b(){
    
    
}


function update_exec_097b(){
    
    
}


// VERSION 0.98a

function update_test_098a(){

	global $db1,$vmreg;
	   
	$q_test=$vmreg->query_try("SELECT 1 FROM {$db1['frontend']}{$db1['sep']}api_console");

	if($q_test==0){
		return true;
	}
	else return false;
	
}

function update_exec_098a(){
    
	global $db1;
        
        $sql_up=array();
	
	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE){
            
            $sql_up[]="CREATE TABLE api_console (
                id integer NOT NULL,
                ip_address varchar(20) NOT NULL DEFAULT '',
                rw integer NOT NULL DEFAULT 0 ,
                api_key varchar(100) NOT NULL DEFAULT '',
                last_update timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT api_console_pkey PRIMARY KEY (id),
                CONSTRAINT u_apy_key UNIQUE (api_key)
              );
              ";
            
        }
        else if($db1['dbtype']=='mysql'){
            
            $sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}api_console (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `ip_address` varchar(20) NOT NULL DEFAULT '',
              `rw` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=read only, 1=read and write',
              `api_key` varchar(100) NOT NULL DEFAULT '',
              `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `api_key` (`api_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            
        }
        else if($db1['dbtype']=='postgres'){


            $sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}api_console (
              id serial NOT NULL,
              ip_address character varying(20) NOT NULL DEFAULT '',
              rw integer NOT NULL DEFAULT 0 ,
              api_key character varying(100) NOT NULL DEFAULT '',
              last_update timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
              CONSTRAINT api_console_pkey PRIMARY KEY (id),
              CONSTRAINT u_apy_key UNIQUE (api_key)
            )
            WITHOUT OIDS;

            ";

        }
        
        return _update_exec('0.98a',$sql_up);
}



// VERSION 0.99

function update_test_099(){

	global $db1,$vmreg;
	   
	$q_test=$vmreg->query_try("SELECT 1 FROM {$db1['frontend']}{$db1['sep']}cache_reg");
    
    $q_test2=$vmreg->query_try("SELECT default_view FROM {$db1['frontend']}{$db1['sep']}registro_tab");

	if($q_test==0 || $q_test2==0){
		return true;
	}
	else return false;
	
}

function update_exec_099(){
    
    global $db1;
    
    $sql_up=array();
    
    if(defined('USE_REG_SQLITE') && USE_REG_SQLITE){
            
            $sql_up[]="CREATE TABLE cache_reg (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,
                obj BLOB, 
                last_update DATETIME
              );
              ";
            $sql_up[]="ALTER TABLE registro_tab ADD COLUMN default_view varchar(5) DEFAULT 'form';";
            $sql_up[]="ALTER TABLE registro_tab ADD COLUMN default_filters TEXT DEFAULT NULL;";
            
        }
        else if($db1['dbtype']=='mysql'){
            
            $sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}cache_reg (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                obj blob,
                last_update timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
              ";
            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD default_view varchar(5) DEFAULT 'form';";
            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD default_filters TEXT DEFAULT NULL;";
            
        }
        else if($db1['dbtype']=='postgres'){


            $sql_up[]="CREATE TABLE {$db1['frontend']}{$db1['sep']}cache_reg (
              id serial NOT NULL,
              obj bytea,
              last_update timestamp without time zone ,
              CONSTRAINT cache_reg_pkey PRIMARY KEY (id)
            )
            WITHOUT OIDS;

            ";
            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD COLUMN default_view character varying(5) DEFAULT 'form';";
            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD COLUMN default_filters TEXT DEFAULT NULL;";

        }
    
    return _update_exec('0.99',$sql_up);
}

// VERSION 0.99a

function update_test_099a(){

	global $db1,$vmreg;
	   
	$q_test=$vmreg->query_try("SELECT allow_filters FROM {$db1['frontend']}{$db1['sep']}registro_tab");
    
	if($q_test==0){
		return true;
	}
	else return false;
	
}

function update_exec_099a(){
    
    global $db1;
    
    $sql_up=array();
    
    if(defined('USE_REG_SQLITE') && USE_REG_SQLITE){
        
            $sql_up[]="ALTER TABLE registro_tab ADD COLUMN allow_filters integer DEFAULT 0;";
            
        }
        else if($db1['dbtype']=='mysql'){
            
            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD COLUMN allow_filters tinyint(1) DEFAULT 0;";
            
        }
        else if($db1['dbtype']=='postgres'){

            $sql_up[]="ALTER TABLE {$db1['frontend']}{$db1['sep']}registro_tab ADD COLUMN allow_filters int2 DEFAULT 0;";
        }
    
    return _update_exec('0.99a',$sql_up);
}

#######################################################################################à





function _update_exec($version,$sql_array){
	
	global  $vmsql, $vmreg, $db1;
	
	$string_out="\n--\n-- <strong>Update for $version</strong>\n--\n\n";
	
	if(!is_array($sql_array)) $sql_array=(array) $sql_array;
	
	$manual_make=array();
	
	$TEST_OK=0;
	$TEST_KO=0;
	
	foreach($sql_array as $sql){
		
		// GET SQL ACTION:
		$action=array();
		preg_match("|^ *([A-Z]+) |si",$sql,$action);
                
		if(count($action)>0){
			
			$sql_keyword=$action[1];
			$check=check_privileges($sql_keyword);
			
			$test=$vmreg->query_try($sql,false);
                        
			if($test){
				$string_out.=$sql."\n-- <span class=\"verde\">OK</span>\n\n";
				$TEST_OK++;
			}
			else{
				$string_out.=$sql."\n-- <span class=\"rosso\">Error</span>\n\n";
				
				$manual_make[]=$sql;
				
				$TEST_KO++;
			}
		}
	}
	
	if(count($manual_make)>0){
		
		$string_out.="<h3>Please run this sql as root (or with user having the correct privileges on DB):</h3>\n";
		
		for($i=0;$i<count($manual_make);$i++){
			$string_out.=$manual_make[$i]."\n\n";
		}
	}
	
	return array($string_out,$TEST_OK,$TEST_KO);
}



if(USE_REG_SQLITE){
    
    $db1['frontend']='';
    $db1['sep']='';
}

//if(USE_REG_SQLITE || $db1['dbtype']=='oracle' || $db1['dbtype']=='sqlite'){
if($db1['dbtype']=='oracle' || $db1['dbtype']=='sqlite'){

	$TEST_UPDATE=false;

	$OUT='';

}
else{

	$TEST_UPDATE=true;

	// Find updates
	preg_match_all('|function (update_test_([\w]+))|im',join('',file(__FILE__)),$function);


	$OUT='';

	if(count($function[0])>0){

		$updates=0;

		if(isset($_GET['test'])){

			for($i=0;$i<count($function[1]);$i++){
				if($function[1][$i]()){

					$updates++;
				}
			}
		}
		else{

			$n_ok=0;
			$n_ko=0;

			// Esegui la funzione TEST
			for($i=0;$i<count($function[1]);$i++){
				if($function[1][$i]()){

					$updates++;


					$func_exec='update_exec_'.$function[2][$i];
					if(function_exists($func_exec)){

						$arr_out=$func_exec();
						$OUT.=$arr_out[0];
						$n_ok+=$arr_out[1];
						$n_ko+=$arr_out[2];
					}
				}
			}
		}
	}

}



echo openLayout1(_("VFront Update"),array("sty/admin.css"));

echo breadcrumbs(array("HOME","ADMIN",strtolower(_("VFront Update"))));

	
echo "<h1>"._("VFront Update")."</h1>";

if(!$TEST_UPDATE){

	echo "<p>"._('This test is not available for this VFront rule method')."</p>\n";
}
else if($updates==0){
	
	echo "<p>"._('No updates to be installed in DB VFront')."</p>\n";
}
else{
	
	if(isset($_GET['test'])){
		
		if($updates>0){
			
			echo "<p>".sprintf(_('There are %d updates to install.'),$updates)."</p>\n";
			echo "<p><a href=\"".$_SERVER['PHP_SELF']."\">"._("Proceed to VFront database update")."</a></p>\n";
		}
		else{
			echo "<p>"._('No updates to be installed.')."</p>\n";
		}
	}
	else{
		
		if($n_ko==0 && $n_ok>0){
			echo "<p>".sprintf(_('Found and installed %d updates.'),$updates)."</p>\n";
		}
		else if($n_ko>0){
			echo "<p>".sprintf(_('Found and installed %d updates. %d failed and should be installed manually.'),$n_ok,$n_ko)."</p>\n";
		}
		else{
			echo "<p>"._('No updates to be installed.')."</p>\n";
		}
	}
}

if($OUT!=''){
	
	echo "<div id=\"boxsql\"><code>\n";
		
	echo nl2br($OUT);
	
	echo "</code></div>\n";
}


echo closeLayout1();



