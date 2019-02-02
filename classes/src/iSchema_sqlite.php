<?php
/**
* Get information from Oracle schema
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona
* @version 0.96 $Id: class.ischema_sqlite.php 1128 2014-12-17 11:25:17Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/



class iSchema_sqlite {
	

	
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

		$sql_fk = "PRAGMA foreign_key_list('$table')";
		
		$q_fk=$vmsql->query($sql_fk);
        
		$n_rows=$vmsql->num_rows($q_fk);
	
		$FK=array();
		
		if($n_rows>0){

			while($RS=$vmsql->fetch_assoc($q_fk)){

				$FK[]=array('column_name'=>$RS['from'],
							"key_name"=>'',
							'references'=>$RS['table'].".".$RS['to']);
			}
		}
		
		return $FK;
	}



	/**
	 * Get the columns info of $table
	 * if the $column_name is specified get only the definition of $column_name
	 *
	 * @param string $table table name
	 * @param string $column_name
	 * @return array
     * @todo Verificare la query su tabelle varie di DB in produzione
	 */
	public function get_column_types($table){

		global  $vmsql;

		$sql="PRAGMA table_info('$table')";

		$q=$vmsql->query($sql);

		$cols=array();

		while($RS=$vmsql->fetch_assoc($q)){

			$is_nullable= ($RS['notnull']==1) ? "NO":"YES";

			$data_type=explode("(",$RS['type']);

			$data_type=$data_type[0];

			$cols[$RS['name']]=$RS['type'];

		}

		return $cols;
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
		
		$sql="PRAGMA table_info('$table')";
		
		$q=$vmsql->query($sql);
		
		$mat= $vmsql->fetch_assoc_all($q);

		$PK=array();

		for($i=0;$i<count($mat);$i++){

			if($mat[$i]['pk']==1){
				$PK[]=$mat[$i]['name'];
			}
		}

		if(count($PK)==1){

			return $PK[0];
		}
		else{
			return $PK;
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
			$sql_add= "AND type='table'";
		}
		else if($type=='VIEW'){
			$sql_add= "AND type='view'";
		}
		else{
			$sql_add= "AND (type='table' OR type='view')";
		}
		
		if($single_table_name!=false){
			
			$sql_tab=" AND name='$single_table_name' ";
		}
		else{
			$sql_tab='';
		}

		
			
		$sql_tab="SELECT name as table_name,
              upper(type) as table_type,
              '' as comment
			  FROM sqlite_master
			  WHERE 1=1
              $sql_add
              $sql_tab
              ORDER BY name ASC ";
		
		$q0=$vmsql->query($sql_tab);

        $mat=array();

        while($RS=$vmsql->fetch_assoc($q0)){

            foreach ($RS as $k=>$v){

                $RS1[$k]=($v=='TABLE') ? "BASE TABLE" : $v;
            }

            $mat[]=$RS1;
        }
		
		return $mat;
	}
	
	
	
	
	/**
	 * Get the columns info of $table
	 * if the $column_name is specified get only the definition of $column_name
	 * 
	 * @param string $table table name
	 * @param string $column_name 
	 * @param bool $get_comments Compatibility with postgresql
	 * @return array
     * @todo Verificare la query su tabelle varie di DB in produzione
	 */
	public function get_columns($table,$column_name='', $get_comments=true){
		
		global  $vmsql;
		
		//$sql_add= ($column_name=='') ? '' : "AND tc.COLUMN_NAME='".strtoupper($column_name)."'";

		$sql="PRAGMA table_info('$table')";

		

		$q=$vmsql->query($sql);

		$mat=array();

		while($RS=$vmsql->fetch_assoc($q)){

			$is_nullable= ($RS['notnull']==1) ? "NO":"YES";
			
			$data_type=explode("(",$RS['type']);

			$data_type=$data_type[0];

			$char_length=(preg_match("|[\w]+\(([0-9]+)\)|",$RS['type'],$ff)) ? $ff[1]:'';

			$extra='';
			
			if($RS['pk']==1){
				
				$q2=$vmsql->query("SELECT typeof({$RS['name']}) FROM $table LIMIT 1");

				list($typeof)=$vmsql->fetch_row($q2);

				if(strtolower($typeof)=='integer'){
					$extra='auto_increment';
				}
			}

			if($column_name!=''){
				
				if($RS['name']!=$column_name) continue;
			}

			$mat[]=array("column_name"=>$RS['name'],
						 "ordinal_position"=>$RS['cid'],
						 "column_default"=>$RS['dflt_value'],
						 "is_nullable"=>$is_nullable,
						 "column_type"=>$RS['type'],
						 "character_maximum_length"=>$char_length,
						 "data_type"=>$data_type,
						 "extra"=>$extra,
						 'column_comment'=>''
						);
			
		}

		return $mat;
	}
	
	
	
	/**
	 * Get the comment of the table
	 *
	 * @param string $table
	 * @return string
	 */
	public function get_table_comment($table){
		
		/* Not present on SQLite*/
		
		return '';
		
	}
	
	
	
	/**
	 * Get tables and column referenced to $table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_referenced($table){

		global  $vmsql, $vmreg;
		
        $REF=array();

		$tables=$this->get_tables('BASE TABLE');

		for($i=0;$i<count($tables);$i++){

			$sql_fk = "PRAGMA foreign_key_list('{$tables[$i]['table_name']}')";
			$q_fk=$vmsql->query($sql_fk);

			while($RS=$vmsql->fetch_assoc($q_fk)){

				if($RS['table']==$table){

					$REF[]=array('table_name'=>$tables[$i]['table_name'],
							"column_name"=>$RS['from']);
				}

			}
		}
		
		return $REF;
		
	}
	
	/**
	 * @param string $view
	 * @link http://www.sqlite.org/lang_createview.html
	 * @return bool
	 */
	public function view_is_updatable($view){
		
		/* View is read-only in SQlite */

		return false;
	}
	
	
	public function is_view($table_name){
		
		$info=$this->get_tables(false,$table_name);
		
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
			
			$q_join=$vmsql->query("SELECT sql
                                 FROM sqlite_master
                                 WHERE name='$view'
								 AND type='view'");
		
			list($show_create)=$vmsql->fetch_row($q_join);

			if(preg_match('| FROM [\W]*([A-Za-z0-9_]+)[\W]*|i',$show_create,$finded)){

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

