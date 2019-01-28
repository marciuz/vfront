<?php
/**
 * Dictionary of field type and utilities
 *
 * @package VFront
 * @subpackage Function-Libraries
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2010 M.Marcello Verona
 * @version 0.99.5
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
 */


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
