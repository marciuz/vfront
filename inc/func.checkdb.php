<?php
/**
* Check compatibility with VFront and the DB
*
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.checkdb.php 1076 2014-06-13 13:03:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/



/**
 *
 * @global array $db1
 * @param string $return_type
 * @return mixed
 */
function check_db($return_type='php'){

	global $db1;

	$IS= new iSchema();

	$tables=$IS->get_tables('BASE TABLE');
	$errors['tables']=array();

	$views=$IS->get_tables('VIEW');
	$errors['views']=array();

	$errors['pk']=array();

	$errors['cols']=array();
	$errors['colsv']=array();

	switch($db1['dbtype']){

		case 'mysql' : $regexp="|[\W]+|"; break;
		case 'sqlite' : $regexp="|[\W]+|"; break;
		case 'postgres' : $regexp="|[^a-z0-9_]+|"; break;
		case 'oracle' : $regexp="|[^A-Z0-9_]+|"; break;
	}


	// TABLES
	for($i=0;$i<count($tables);$i++){


		// Find strange names for tables
		if(preg_match($regexp,$tables[$i]['table_name'])){

			$errors['tables'][]=$tables[$i]['table_name'];
		}


		// no primary keys
		$PK=$IS->get_primary_keys($tables[$i]['table_name']);

		if((is_array($PK) && count($PK)==0) || (is_string($PK) && $PK=='') || $PK===null){

			$errors['pk'][]=$tables[$i]['table_name'];
		}

		// Find fields witd strange chars
		$cols=$IS->get_columns($tables[$i]['table_name']);

		for($j=0;$j<count($cols);$j++){

			if(preg_match($regexp,$cols[$j]['column_name'])){

				$errors['cols'][]=$tables[$i]['table_name'].".".$cols[$j]['column_name'];
			}
		}


	}

	$colsv_err=array();

	// VIEWS
	for($i=0;$i<count($views);$i++){


		// Find strange names for views
		if(preg_match($regexp,$views[$i]['table_name'])){

			$errors['views'][]=$views[$i]['table_name'];
		}


		$colsv=$IS->get_columns($views[$i]['table_name']);

		// Find fields witd strange chars
		for($j=0;$j<count($colsv);$j++){

			if(preg_match($regexp,$colsv[$j]['column_name'])){

				$errors['colsv'][]=$views[$i]['table_name'].".".$colsv[$j]['column_name'];
			}
		}
	}

	$errors['n']=count($errors['tables']) +
			 count($errors['views']) +
			 count($errors['pk']) +
			 count($errors['cols']) +
			 count($errors['colsv']);


	if($return_type=='php'){

		return $errors;
	}
	else if($return_type=='json'){

		return json_encode($errors);
	}

}

?>