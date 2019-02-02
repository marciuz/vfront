<?php
/**
* Get information from Postgres information_schema 
*
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona
* @version 0.96 $Id: class.ischema_postgres.php 1128 2014-12-17 11:25:17Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/



class iSchema_postgres {


    /**
	 * List of allowed tables
	 *
	 * @var array
	 */
	private $table_list=array();


    /**
	 * Construct function
	 * Put the table list into an array (allowed tables)
	 *
	 */
	function __construct(){

		global  $vmsql, $vmreg, $db1;

		$q=$vmsql->query("SELECT table_name FROM information_schema.tables WHERE table_schema='{$db1['dbname']}'");

		list($this->table_list)=$vmsql->fetch_row_all($q,true);
	}


	private function table_exist($table){

		return in_array($table,$this->table_list);
	}


	/**
	 * Get the Foreign Keys for a table.
	 * Return an array with
	 * column_name => the column
	 * key_name => the name of the key
	 * references => the table.column of reference
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_foreign_keys($table){

		global  $vmsql, $vmreg, $db1;

		$sql_fk = "SELECT
				k2.column_name,
				c.constraint_name as key_name,
				k1.table_name || '.' || k1.column_name as references

				FROM information_schema.table_constraints c
				INNER JOIN information_schema.referential_constraints r
					ON (r.constraint_schema=c.constraint_schema AND c.constraint_name=r.constraint_name)
				INNER JOIN information_schema.key_column_usage k1
					ON (k1.constraint_schema=c.constraint_schema AND k1.constraint_name=r.unique_constraint_name)
				INNER JOIN information_schema.key_column_usage k2
					ON (k2.constraint_schema=c.constraint_schema AND k2.constraint_name=c.constraint_name)

				WHERE c.constraint_type='FOREIGN KEY'
				AND c.constraint_schema='{$db1['dbname']}'
				AND c.table_name='$table'
		";

		$q_fk=$vmsql->query($sql_fk);
		$n_rows=$vmsql->num_rows($q_fk);

		$FK=array();

		if($n_rows>0){
			$FK=$vmsql->fetch_assoc_all($q_fk);
		}

		return $FK;
	}


	/**
	 * Get the PK from a table
	 * If there are once a PK return a string is is multiple return an array
	 *
	 * @param string $table
	 * @return mixed
	 */
	public function get_primary_keys($table){

		global  $vmsql, $vmreg, $db1;

		$sql="SELECT i.column_name as campo_pk
			FROM information_schema.key_column_usage i
			INNER JOIN information_schema.table_constraints c ON (c.table_schema='{$db1['dbname']}' AND c.constraint_name=i.constraint_name)
			WHERE i.table_name='$table'
			AND i.table_schema='{$db1['dbname']}'
			AND c.constraint_type='PRIMARY KEY'";

		$q=$vmsql->query($sql);

		list($mat)= $vmsql->fetch_row_all($q,true);

		if($vmsql->num_rows($q)==1){

			return $mat[0];
		}
		else{
			return $mat;
		}
	}


	/**
	 * Get the tables from DB
	 * If type is specified, get only "BASE TABLE" or VIEW
	 *
	 * @param string $type 'BASE TABLE' or 'VIEW'
	 * @param string $single_table_name table_name
	 * @return array
	 */
	public function get_tables($type=false,$single_table_name=false){

		global  $vmsql, $vmreg, $db1;

		if($type!='BASE TABLE' &&  $type!='VIEW' && $type!==false)
			die('<p><strong>Error:</strong> Function get_tables accept only false, \'BASE TABLE\' or \'VIEW\' as parameter</p>');

		if($type=='BASE TABLE'){
			$sql_add= "AND table_type='BASE TABLE'";
		}
		else if($type=='VIEW'){
			$sql_add= "AND table_type='VIEW'";
		}
		else{
			$sql_add= "AND (table_type='BASE TABLE' OR table_type='VIEW')";
		}

		if($single_table_name!=false){

			$sql_tab=" AND table_name='$single_table_name' ";
		}
		else{
			$sql_tab='';
		}


		$sql_tab="SELECT table_name, table_type, obj_description(c.oid, 'pg_class'::name) AS comment
				  FROM information_schema.tables , pg_catalog.pg_class AS c
				  WHERE table_schema='{$db1['dbname']}'
				  $sql_add
				  $sql_tab
				  AND  table_name=c.relname
				  ORDER BY table_name ASC ";

		$q0=$vmsql->query($sql_tab);

		return $vmsql->fetch_assoc_all($q0);
	}




	/**
	 * Get the columns info of $table
	 * if the $column_name is specified get only the definition of $column_name
	 *
	 * @param string $table table name
	 * @param string $column_name
	 * @return array
	 */
	public function get_columns($table,$column_name='', $get_comments=true){

		global $vmsql, $db1;
        
        if(is_array($column_name)){
            $column_name = $column_name[0];
        }

		if($column_name!=''){
			$sql_add="AND cols.column_name='$column_name'";
		}
		else{
			$sql_add='';
		}
                
        if($get_comments){
            $sql_comments = "(
                        SELECT
                            pg_catalog.col_description(c.oid, cols.ordinal_position::int)
                        FROM pg_catalog.pg_class c
                        WHERE c.oid = (SELECT '{$db1['dbname']}.$table'::regclass::oid) 
                        AND c.relname = cols.table_name
                    ) as column_comment";
        }
        else{
            $sql_comments = "'' as column_comment";
        }
        
        
        $sql=" SELECT
                cols.column_name,
                cols.ordinal_position,
                cols.column_default,
                cols.is_nullable,
                cols.udt_name as column_type,
                cols.character_maximum_length,
                cols.data_type,
                CASE WHEN cols.column_default LIKE 'nextval(%' THEN 'auto_increment' ELSE '' END AS extra,
                $sql_comments    

                FROM information_schema.columns cols

                WHERE cols.table_catalog = '{$db1['postgres_dbname']}' 
                AND cols.table_name    = '$table'    
                AND cols.table_schema  = '{$db1['dbname']}'
                $sql_add
                ORDER BY table_name ASC, ordinal_position ASC ";
                        
		$q=$vmsql->query($sql);

		return $vmsql->fetch_assoc_all($q);
	}

	
	/**
	 * Get the columns type from $table
	 *
	 * @param string $table table name
	 * @return array
	 */
	public function get_column_types($table){

		global  $vmsql, $db1;


		$sql="SELECT
				column_name,
				udt_name as column_type

				FROM information_schema.columns i, pg_attribute a, pg_class c, pg_namespace ns

				WHERE table_schema='{$db1['dbname']}'

				AND table_name='$table'

				AND c.relname=i.table_name
				AND ns.oid=c.relnamespace
				AND a.attrelid = c.oid
				AND a.attname = i.column_name

				ORDER BY ordinal_position ASC ";

		$q=$vmsql->query($sql);

		$res=array();
		
		while($RS=$vmsql->fetch_row($q)){
		    
		    $res[$RS[0]]=$RS[1];
		}
		
		return $res;
	}


	/**
	 * Get the comment of the table
	 *
	 * @param string $table
	 * @return string
	 */
	public function get_table_comment($table){

		global  $vmsql, $vmreg, $db1;

		$sql="SELECT obj_description(c.oid, 'pg_class'::name) AS comment
			FROM information_schema.tables , pg_catalog.pg_class AS c
			WHERE table_schema='{$db1['dbname']}'
			AND table_name='$table'
			AND table_name=c.relname";

		$q=$vmsql->query($sql);
		list($comment)=$vmsql->fetch_row($q);

		return $comment;

	}



	/**
	 * Get tables and column referenced to $table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_referenced($table){

		global  $vmsql, $vmreg, $db1;

		$sql="SELECT k.table_name , k.column_name
			FROM information_schema.constraint_column_usage cu
			INNER JOIN information_schema.key_column_usage k
				ON k.constraint_name=cu.constraint_name
			WHERE cu.table_schema='{$db1['dbname']}'
			AND cu.table_name='$table'
			AND k.table_name!='$table'";

		$q=$vmsql->query($sql);

		return $vmsql->fetch_assoc_all($q);

	}


	public function view_is_updatable($view){

		global  $vmsql, $vmreg, $db1;

		$q_join=$vmsql->query("SELECT view_definition
									FROM information_schema.views
									WHERE table_name='".$view."'
									AND table_catalog='".$db1['postgres_dbname']."'
									AND table_schema='".$db1['dbname']."'");

		list($show_create)=$vmsql->fetch_row($q_join);

		return (preg_match('| JOIN |i',$show_create)) ? false:true;
	}


	public function is_view($table_name){

		if($this->table_exist($table_name)){

			$info=$this->get_tables(false,$table_name);
		}
		else
			return null;

		if($info[0]['table_type']=='VIEW'){
			return true;
		}
		else{
			return false;
		}

	}


	public function show_view_table_ref($view){

		global  $vmsql, $vmreg, $db1;


		if($this->is_view($view)){

			$q_join=$vmsql->query("SELECT view_definition
									FROM information_schema.views
									WHERE table_name='".$view."'
									AND table_catalog='".$db1['postgres_dbname']."'
									AND table_schema='".$db1['dbname']."'");

			list($show_create)=$vmsql->fetch_row($q_join);

			if(preg_match('| FROM [\W]*([a-z01_]+)[\W]*|i',$show_create,$finded)){

				return $finded[1];
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}

	}
}



?>