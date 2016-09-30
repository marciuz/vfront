// it - 53 strings - 2010-11-10T11:36:58+01:00

Translate = new Object;
Translate.it = new Array();

Translate.it[' Search ']='Ricerca';
Translate.it['1 record found and shown']='1 record trovato e mostrato';
Translate.it['Can not delete from join view']='Impossibile eliminare il record da una vista con JOIN';
Translate.it['Can not delete record']='Impossibile eliminare il record';
Translate.it['Can not delete the record <br/> there are related records']='Impossibile eliminare il record<br/>Esistono record collegati';
Translate.it['Can not do this <br/> (Error Code:']='Impossibile effettuare questa operazione<br/>(Codice errore:';
Translate.it['Can not update record']='Impossibile duplicare il record';
Translate.it['Can\'t add record']='Impossibile aggiungere il nuovo record';
Translate.it['Can\'t add record - Duplicate key']='Impossibile aggiungere il record - Chiave duplicata';
Translate.it['Discard changes?']='Eliminare le modifiche?';
Translate.it['Do you really want to delete this file?']='Vuoi veramente cancellare questo file?';
Translate.it['Do you really want to delete this record?']='Vuoi veramente cancellare questo record?';
Translate.it['Do you really want to delete this record? This operation cannot be undone.']='Si vuole veramente eliminare il record? L\'operazione e\' irreversibile.';
Translate.it['Do you really want to duplicate the record? Data on subforms and any attachments and links will not be duplicated automatically.']='Si vuole veramente duplicare il record? I dati relativi alle sottomaschere ed a eventuali allegati e link non saranno duplicati automaticamente.';
Translate.it['Error Loading RPC request. Status:']='Errore nel caricamento della richiesta RPC. Status: ';
Translate.it['Error deleting records']='Errore nella eliminazione del/dei record';
Translate.it['Error inserting record']='Errore nell\'inserimento del record';
Translate.it['Error updating record']='Errore nella modifica del record';
Translate.it['Go to duplicated record']='Vai al record duplicato';
Translate.it['Hotkey Function Error: Action should be "link" or "code"']='Hotkey Function Error: Action should be "link" or "code"';
Translate.it['Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata']='Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata';
Translate.it['No records found by this search']='Nessun record trovato per questa ricerca';
Translate.it['No search was attempted! <br/> Enter values in at least one field']='Non si \350 cercato nulla!<br/>Inserire valori in un campo almeno';
Translate.it['Query error']='Errore nella query';
Translate.it['Record']='Record';
Translate.it['Record deleted correctly']='Record eliminato correttamente';
Translate.it['Record duplicated correctly']='Record duplicato correttamente';
Translate.it['Record inserted correctly']='Record inserito correttamente';
Translate.it['Record updated correctly']='Record modificato correttamente';
Translate.it['Record(s) deleted correctly']='Record eliminato/i correttamente';
Translate.it['Record(s) updated correctly']='Record modificato/i correttamente';
Translate.it['Some records changed correctly']='Alcuni record modificati correttamente';
Translate.it['Start search']='Invia ricerca';
Translate.it['The SQL query contains unsafe words and was not performed']='La query sembra contenere parole chiave SQL potenzialmente pericolose e non \350 stata eseguita';
Translate.it['The field']='Il campo ';
Translate.it['The maximum number of records for this subform is set by the administrator to']='Il numero massimo di record per questa sottomaschera e\' stato impostato dall\'amministratore a';
Translate.it['The query was successful']='La query \350 stata eseguita correttamente';
Translate.it['The record was not saved.']='Il record non e\' stato salvato.';
Translate.it['There are no records in this table']='Nessun record in questa tabella';
Translate.it['This record is being edited by another user <br/> Please try again later']='Record in fase di modifica da parte di un altro utente<br/>Prego riprovare pi&ugrave; tardi';
Translate.it['Unable to add a record - The reference is missing and/or not connected to the reference table.']='Impossibile aggiungere il record - Non esiste la referenzialit&#224; alla tabella collegata';
Translate.it['Unable to change the option']='Impossibile modificare l\'opzione';
Translate.it['Updating ...']='Aggiornamento...';
Translate.it['Values of dropdown list']='Elenco valori della tendina';
Translate.it['Warning']='Attenzione';
Translate.it['Warning!']='Attenzione!';
Translate.it['Why do you want to delete the last drop down menu?']='Perch\351 vuoi cancellare anche l\'ultima tendina?';
Translate.it['attachments']='allegati';
Translate.it['link']='link';
Translate.it['must be completed']='deve essere compilato';
Translate.it['of']='di';
Translate.it['records found for this search']='record trovati per questa ricerca';
Translate.it['updated']='aggiornato';
Translate.it['No changes in update']='Nessuna modifica richiesta';
Translate.it['Unable to insert record']='Inpossibile inserire il record';

function _(str){
	if(typeof(Translate.it[str])!=='undefined'){ return Translate.it[str]; }
	else{ return str; }
	
}