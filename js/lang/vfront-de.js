// de - 53 strings - 2010-11-10T11:30:45+01:00

Translate = new Object;
Translate.de = new Array();

Translate.de[' Search ']='Suche';
Translate.de['1 record found and shown']='1 Datensatz gefunden und angezeigt';
Translate.de['Can not delete from join view']='Can not delete from join view';
Translate.de['Can not delete record']='Kann Datensatz nicht löschen';
Translate.de['Can not delete the record <br/> there are related records']='Datensatz kann nicht gelöscht werden <br/Es existieren verbundene Datensätze';
Translate.de['Can not do this <br/> (Error Code:']='Kann dieses nicht ausführen <br/> (Fehler Code:';
Translate.de['Can not update record']='Kann Datensatz nicht aktualisieren';
Translate.de['Can\'t add record']='Kann Datensatz nicht hinzufügen';
Translate.de['Can\'t add record - Duplicate key']='Kann Datensatz nicht hinzufügen - Doppelter Schlüssel';
Translate.de['Discard changes?']='Änderungen verwerfen?';
Translate.de['Do you really want to delete this file?']='Möchten sie diese Datei wirklich löschen?';
Translate.de['Do you really want to delete this record?']='Möchten sie diesen Datensatz wirklich löschen?';
Translate.de['Do you really want to delete this record? This operation cannot be undone.']='Wollen sie den Datensatz wirklich löschen? Die Operation ist irreversibel.';
Translate.de['Do you really want to duplicate the record? Data on subforms and any attachments and links will not be duplicated automatically.']='Möchten sie den Datensatz wirklich duplizieren? Daten in Unterformularen und angehängte Dateien und Verweise werden nicht automatisch mitkopiert.';
Translate.de['Error Loading RPC request. Status:']='Fehler beim Laden der RPC Anforderung. Status:';
Translate.de['Error deleting records']='Fehler beim Löschen des Datensatzes';
Translate.de['Error inserting record']='Fehler beim Einfügen des Datensatzes';
Translate.de['Error updating record']='Fehler beim Aktualisieren des Datensatzes';
Translate.de['Go to duplicated record']='Gehe zum duplizierten Datensatz';
Translate.de['Hotkey Function Error: Action should be "link" or "code"']='Hotkey Function Error: Action should be "link" or "code"';
Translate.de['Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata']='Impossibile aggiungere il record<br/>Non esiste la referenzialit&#224; alla tabella collegata';
Translate.de['No records found by this search']='Für diese Suche wurde kein Datensatz gefunden';
Translate.de['No search was attempted! <br/> Enter values in at least one field']='Der Versuch ist fehlgeschlagen <br/> Geben sie in mindestens ein Feld Werte ein';
Translate.de['Query error']='Abfrage Fehler';
Translate.de['Record']='Datensatz';
Translate.de['Record deleted correctly']='Datensatz korrekt gelöscht';
Translate.de['Record duplicated correctly']='Datensatz korrekt dupliziert';
Translate.de['Record inserted correctly']='Datensatz korrekt eingefügt';
Translate.de['Record updated correctly']='Datensatz korrekt übernommen';
Translate.de['Record(s) deleted correctly']='Datensatz korrekt gelöscht';
Translate.de['Record(s) updated correctly']='Datensatz korrekt übernommen';
Translate.de['Some records changed correctly']='Einige Datensätze wurden korrekt verändert';
Translate.de['Start search']='Suche absenden';
Translate.de['The SQL query contains unsafe words and was not performed']='Die SQL Abfrage enthält unsichere Befehle und wurde nicht ausgeführt';
Translate.de['The field']='Das Feld';
Translate.de['The maximum number of records for this subform is set by the administrator to']='Die maximale Anzahl von Datensätzen für dieses Unterformular wurde vom Administrator gesetzt auf';
Translate.de['The query was successful']='Die Abfrage war erfolgreich';
Translate.de['The record was not saved.']='Der Datensatz wurde nicht gespeichert.';
Translate.de['There are no records in this table']='Keine Datensätze in der Tabelle vorhanden';
Translate.de['This record is being edited by another user <br/> Please try again later']='Der Datensatz wird gerade von einem anderen Benutzer bearbeitet <br/>Bitte versuchen sie es später noch einmal';
Translate.de['Unable to add a record - The reference is missing and/or not connected to the reference table.']='Kann keinen Datensatz hinzuzufügen - Die Referenz fehlt und/oder ist nicht zur Referenztabelle verbunden.';
Translate.de['Unable to change the option']='Unable to change the option';
Translate.de['Updating ...']='Aktualisiere ...';
Translate.de['Values of dropdown list']='Werte aus der Dropdown Liste';
Translate.de['Warning']='Warnung!';
Translate.de['Warning!']='Warnung!';
Translate.de['Why do you want to delete the last drop down menu?']='Warum möchten sie das letzte Drop Down Menü löschen?';
Translate.de['attachments']='Dateianhänge';
Translate.de['link']='Verknüpfung';
Translate.de['must be completed']='muss abgeschlossen werden';
Translate.de['of']='von';
Translate.de['records found for this search']='Für Suche gefundene Datensätze ';
Translate.de['updated']='übernommen';

function _(str){
	if(typeof(Translate.de[str])!=='undefined'){ return Translate.de[str]; }
	else{ return str; }
	
}