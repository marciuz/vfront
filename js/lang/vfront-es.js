// es - 53 strings - 2010-11-10T11:36:58+01:00

Translate = new Object;
Translate.es = new Array();

Translate.es[' Search ']='Búsqueda';
Translate.es['1 record found and shown']='N registro encontrado y mostrado';
Translate.es['Can not delete from join view']='No se puede borrar desde la vista';
Translate.es['Can not delete record']='No se puede borrar el registro';
Translate.es['Can not delete the record <br/> there are related records']='No se puede borrar el registro <br/> existen registros relacionados';
Translate.es['Can not do this <br/> (Error Code:']='No puedo hacer eso <br/> (Error Code:';
Translate.es['Can not update record']='No se puede actualizar los datos';
Translate.es['Can\'t add record']='No se puede agregar el registro - clave duplicadas';
Translate.es['Can\'t add record - Duplicate key']='No se puede agregar el registro - clave duplicadas';
Translate.es['Discard changes?']='¿No aceptar los cambios?';
Translate.es['Do you really want to delete this file?']='¿Estás seguro que quieres eliminar este fichero?';
Translate.es['Do you really want to delete this record?']='¿Estás seguro que quieres eliminar este registro?';
Translate.es['Do you really want to delete this record? This operation cannot be undone.']='¿Relamente desea borrar este registro? La operacion es irreversible';
Translate.es['Do you really want to duplicate the record? Data on subforms and any attachments and links will not be duplicated automatically.']='Realmente dese duplicar el registro, los datos en los Subforms o los adjuntos no seran duplicados';
Translate.es['Error Loading RPC request. Status:']='Error Loading RPC request. Status:';
Translate.es['Error deleting records']='Error al eliminar el registro';
Translate.es['Error inserting record']='Error al insertar el registro';
Translate.es['Error updating record']='¡Error en la modificación del registro!';
Translate.es['Go to duplicated record']='Ir al Registro duplicado';
Translate.es['Hotkey Function Error: Action should be "link" or "code"']='Hotkey Function Error: Action should be "link" or "code"';
Translate.es['Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata']='Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata';
Translate.es['No records found by this search']='No existen registros para esta busqueda';
Translate.es['No search was attempted! <br/> Enter values in at least one field']='No search was attempted! <br/> Enter values in at least one field';
Translate.es['Query error']='Error de consulta';
Translate.es['Record']='Registro';
Translate.es['Record deleted correctly']='Registro borrado con éxito';
Translate.es['Record duplicated correctly']='Registro duplicado con éxito';
Translate.es['Record inserted correctly']='Registro insertado con éxito';
Translate.es['Record updated correctly']='Registro actualizado con éxito';
Translate.es['Record(s) deleted correctly']='Registro(s) borrado con éxito';
Translate.es['Record(s) updated correctly']='Registro(s) actualizado con éxito';
Translate.es['Some records changed correctly']='Registros actualizados con éxito';
Translate.es['Start search']='Comience la Búsqueda';
Translate.es['The SQL query contains unsafe words and was not performed']='La consulta SQL contiene sentencias no seguras y no se realizó';
Translate.es['The field']='El campo';
Translate.es['The maximum number of records for this subform is set by the administrator to']='El maximo numero de registros para este subformulario fue dado por el administrador a';
Translate.es['The query was successful']='La consulta se realizo correctamente';
Translate.es['The record was not saved.']='El registro no se ha guardado.';
Translate.es['There are no records in this table']='No existen registros en esta tabla';
Translate.es['This record is being edited by another user <br/> Please try again later']='Este registro esta siendo editado por otro usuario <br/> Intentelo de nuevo mas tarde';
Translate.es['Unable to add a record - The reference is missing and/or not connected to the reference table.']='No se puede agregar el registro - No hay ninguna referencia a la tabla vinculada';
Translate.es['Unable to change the option']='No se puede cambiar la opcion';
Translate.es['Updating ...']='Actualizando ...';
Translate.es['Values of dropdown list']='Lista de valores';
Translate.es['Warning']='¡Advertencia!';
Translate.es['Warning!']='¡Advertencia!';
Translate.es['Why do you want to delete the last drop down menu?']='Why do you want to delete the last drop down menu?';
Translate.es['attachments']='Anexos';
Translate.es['link']='vínculo';
Translate.es['must be completed']='debe ser llenado';
Translate.es['of']='de';
Translate.es['records found for this search']=' registros encontrados para esta busqueda';
Translate.es['updated']='actualizado';

function _(str){
	if(Translate.es[str]!=='undefined'){ return Translate.es[str]; }
	else{ return str; }

}