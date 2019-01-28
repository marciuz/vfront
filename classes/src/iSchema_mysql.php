<?php
/**
* Get information from MySQL schema without query on information_schema
*
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona
* @version 0.96 $Id: class.ischema_mysql.php 1163 2016-04-24 22:27:39Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/



/**
 * Get information from MySQL schema without query on information_schema
 *
 */
class iSchema_mysql {

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

		global  $vmsql;

		$q=$vmsql->query("SHOW TABLES");

		list($this->table_list)=$vmsql->fetch_row_all($q,true);
	}


	public function table_exist($table){

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

		global  $vmsql, $vmreg;

		if(!$this->table_exist($table)) return null;

		$sql="SHOW CREATE TABLE `$table`";
		$q=$vmsql->query($sql);
		list(,$create)=$vmsql->fetch_row($q);

		preg_match_all("!CONSTRAINT *`?([a-z0-9_]+)`? *FOREIGN KEY *\(`?([a-z0-9_]+)`?\) *REFERENCES `?([a-z0-9_]+)`? *\(`?([a-z0-9_]+)`?\)!si",
		$create,$FKfinded);


		$FK=array();

		for($i=0;$i<count($FKfinded[0]);$i++){

			$FK[$i]['column_name']=$FKfinded[2][$i];
			$FK[$i]['key_name']=$FKfinded[1][$i];
			$FK[$i]['references']=$FKfinded[3][$i].".".$FKfinded[4][$i];
		}
		return $FK;
	}


	/**
	 * Get the columns type from $table
	 *
	 * @param string $table table name
	 * @return array
	 */
	public function get_column_types($table){

		global  $vmsql;

		if(!$this->table_exist($table)) return null;

		$q=$vmsql->query("SHOW FULL COLUMNS FROM `$table` ");


		$cols=array();

		$i=0;

		while($RS=$vmsql->fetch_assoc($q)){

			$test_char_max=preg_match('|[a-z]+\(([0-9]+)\).*|i',$RS['Type'],$char_max);
			$data_type=preg_replace('|\(.*|','',$RS['Type']);

			$cols[$RS['Field']]=$data_type;

			$i++;
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

		global  $vmsql;

		if(!$this->table_exist($table)) return null;

		$sql="SHOW INDEX FROM `$table`";
		$q=$vmsql->query($sql);

		$PK=array();

		while($RS=$vmsql->fetch_assoc($q)){

			if($RS['Key_name']=='PRIMARY'){
				$PK[]=$RS['Column_name'];
			}
		}

		if(count($PK)==1)
                    return $PK[0];

		else if(count($PK)>1)
                    return $PK;

		else
                    return null;
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

		global  $vmsql, $db1;

		if($type!='BASE TABLE' &&  $type!='VIEW' && $type!==false)
			die('<p><strong>Error:</strong> Function get_tables accept only false, \'BASE TABLE\' or \'VIEW\' as parameter</p>');

		if($single_table_name!==false){
			$sql_add=" WHERE NAME='$single_table_name'";
		}
		else{
			$sql_add='';
		}

		$sql="SHOW TABLE STATUS FROM {$db1['dbname']} $sql_add";
		$q=$vmsql->query($sql);

		$table_info=$vmsql->fetch_assoc_all($q);

		$tables=array();

		foreach ($table_info as $k=>$arr) {

			if($type=='BASE TABLE' && $arr['Comment']=='VIEW'){

				continue;
			}
			else if($type=='VIEW' && $arr['Comment']!='VIEW'){
				continue;
			}

			if($arr['Comment']=='VIEW'){
				$table_type='VIEW';
				$comment='VIEW';
			}
			else{
				$table_type='BASE TABLE';
				$comment=preg_replace("|;? *INNODB.*|i",'',$arr['Comment']);
			}

			$tables[]=array('table_name'=>$arr['Name'],
									'table_type'=>$table_type,
									'comment'=>$comment);
		}

		return $tables;
	}

    

	/**
	 * Get the columns info of $table
	 * if the $column_name is specified get only the definition of $column_name
	 *
	 * @param string $table table name
	 * @param string $column_name
	 * @param bool $get_comments (Compatibility with postgresql)
	 * @return array
	 */
	public function get_columns($table,$column_name='', $get_comments=true){

		global  $vmsql;

		if(!$this->table_exist($table)) return null;

		$q=$vmsql->query("SHOW FULL COLUMNS FROM `$table` ");


		$cols=array();

		$i=0;
		$ord=1;

		while($RS=$vmsql->fetch_assoc($q)){

			if($column_name!='' && $RS['Field']!=$column_name){

				$ord++;
				continue;
			}

			$test_char_max=preg_match('|[a-z]+\(([0-9]+)\).*|i',$RS['Type'],$char_max);
			$data_type=preg_replace('|\(.*|','',$RS['Type']);
			$comment=preg_replace('|INNODB.*|i','',$RS['Comment']);

			$cols[$i]['column_name']=$RS['Field'];
			$cols[$i]['ordinal_position']=$ord;
			$cols[$i]['column_default']=$RS['Default'];
			$cols[$i]['is_nullable']=$RS['Null'];
			$cols[$i]['column_type']=$RS['Type'];
			$cols[$i]['character_maximum_length']= ($test_char_max) ? $char_max[1] : '';
			$cols[$i]['data_type']=$data_type;
			$cols[$i]['extra']=$RS['Extra'];
			$cols[$i]['column_comment']=$comment;

			$i++;
			$ord++;
		}

		return $cols;
	}


	/**
	 * Get the comment of the table
	 *
	 * @param string $table
	 * @return string
	 */
	public function get_table_comment($table){

		global  $vmsql, $vmreg;

		if(!$this->table_exist($table)) return null;

		$q=$vmsql->query("SHOW CREATE TABLE `$table`");
		list(,$create)=$vmsql->fetch_row($q);

		$test_comment=preg_match("!COMMENT='(.+)!su",$create,$finded);

		if($test_comment){
			$comment=substr(str_replace("''","'",$finded[1]),0,-1);
			$comment=preg_replace("|INNODB.*|i",'',$comment);
		}

		return ($test_comment) ? $comment : null;

	}

	/**
	 * Get tables and column referenced to $table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_referenced($table){

		if(!$this->table_exist($table)) return null;

		$all_tabs=$this->get_tables();

		$REF=array();

		for($i=0;$i<count($all_tabs);$i++){

			$fkk=$this->get_foreign_keys($all_tabs[$i]['table_name']);

			if(is_array($fkk)){

				foreach ($fkk as $k=>$val){

					list($tname,$cname)=explode(".",$val['references']);

					// exclude self
					if($table!=$tname){
						$REF[]=array('table_name'=>$tname,'column_name'=>$cname);
					}
				}
			}
		}

		return $REF;
	}

	public function view_is_updatable($view){

		global  $vmsql, $vmreg, $db1;

		$q_join=$vmsql->query("SHOW CREATE VIEW `$view`");

		list($trash,$show_create)=$vmsql->fetch_row($q_join);

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

			$q_join=$vmsql->query("SHOW CREATE VIEW `$view`");

			list($trash,$show_create)=$vmsql->fetch_row($q_join);

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
        
        public function list_tables(){
            
            return $this->table_list;
        }

} // end of class



