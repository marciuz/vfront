<?php
/**
* Get information from schema
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2010 M.Marcello Verona
* @version 0.96 $Id: class.ischema.php 1092 2014-06-18 16:27:22Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


if(VFRONT_DBTYPE=='postgres'){

	class iSchema extends iSchema_postgres {}
}
else if(VFRONT_DBTYPE=='mysql'){

	class iSchema extends iSchema_mysql{}
}
else if(VFRONT_DBTYPE=='oracle'){

    class iSchema extends iSchema_oracle{}
}
else if(VFRONT_DBTYPE=='sqlite'){

    class iSchema extends iSchema_sqlite{}
}

