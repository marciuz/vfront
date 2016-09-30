<?php

/**
 * Structure of table registry 
 */
class RegTable {
    
    public $id_table;
	public $gid;
	public $visibile  = 0;
	public $in_insert = 0;
	public $in_duplica = 0;
	public $in_update = 0;
	public $in_delete = 0;
	public $in_export = 0;
	public $in_import = 0;
	public $data_modifica;
	public $orderby;
	public $table_name;
	public $table_type;
	public $commento;
	public $orderby_sort = 'ASC';
	public $permetti_allegati = 0;
	public $permetti_allegati_ins = 0;
	public $permetti_allegati_del = 0;
	public $permetti_link = 0;
	public $permetti_link_ins = 0;
	public $permetti_link_del = 0;
	public $view_pk;
	public $fonte_al;
	public $table_alias = '';
	public $allow_filters = 0;
	public $default_filters; 
	public $default_view = 'form'; 
    
    public $columns = array();
    public $submasks = array();
    public $buttons = array();
    
}
