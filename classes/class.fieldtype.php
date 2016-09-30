<?php

/**
 * Dictionary of field type and utilities
 *
 * @package VFront
 * @subpackage Function-Libraries
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2010 M.Marcello Verona
 * @version 0.96 $Id:$
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
 */

/**
 * Dictionary of field type 
 */
abstract class Field_Type_Dictionary {

    protected $integers;
    protected $doubles;
    protected $numerics;
    protected $chars;
    protected $small_chars;
    protected $long_chars;
    protected $booleans;
    protected $dates;

    public function __construct() {

        // Integers
        $this->integers = array();

        $this->integers[] = 'int';
        $this->integers[] = 'integer';
        $this->integers[] = 'number';
        $this->integers[] = 'mediumint';
        $this->integers[] = 'biginteger';
        $this->integers[] = 'bigint';
        $this->integers[] = 'smallint';
        $this->integers[] = 'tinyint';
        $this->integers[] = 'serial';

        $this->doubles = array();
        $this->doubles[] = 'float';
        $this->doubles[] = 'double';
        $this->doubles[] = 'double precision';
        $this->doubles[] = 'real';
        $this->doubles[] = 'numeric';
        $this->doubles[] = 'decimal';
        $this->doubles[] = 'money';

        $this->numerics = array();
        $this->numerics = array_merge($this->integers, $this->doubles);

        $this->small_chars = array();
        $this->small_chars[] = 'varchar';
        $this->small_chars[] = 'varchar2';
        $this->small_chars[] = 'char';
        $this->small_chars[] = 'character';
        $this->small_chars[] = 'character varying';
        $this->small_chars[] = 'varbinary';
        $this->small_chars[] = 'bpchar';

        $this->long_chars = array();
        $this->long_chars[] = 'varchar2';
        $this->long_chars[] = 'mediumtext';
        $this->long_chars[] = 'blob';
        $this->long_chars[] = 'longtext';
        $this->long_chars[] = 'text';

        $this->chars = array_merge($this->small_chars, $this->long_chars);

        $this->dates = array();
        $this->dates[] = 'date';
        $this->dates[] = 'datetime';
        $this->dates[] = 'timestamp without time zone';
        $this->dates[] = 'timestamp';

        $this->booleans = array();
        $this->booleans[] = 'boolean';
    }

}

class FieldType extends Field_Type_Dictionary {

    public function __construct() {
        parent::__construct();
    }

    public function is_char($type) {

        return in_array($type, $this->chars);
    }

    public function is_longchar($type) {

        return in_array($type, $this->long_chars);
    }

    public function is_shortchar($type) {

        return in_array($type, $this->small_chars);
    }

    public function is_date($type) {

        return in_array($type, $this->dates);
    }

    public function is_boolean($type) {

        return in_array($type, $this->booleans);
    }

    public function is_integer($type) {

        return in_array($type, $this->integers);
    }

    public function is_double($type) {

        return in_array($type, $this->doubles);
    }

    public function is_numeric($type) {

        return in_array($type, $this->numerics);
    }

}
