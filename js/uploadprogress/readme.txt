[ENGLISH]
How to use this demostration

	Open whileuploading.php file and change, if necessary, temporary folder.
	If you don't know where is your temporary folder, try to use foldertest.php
	uploading a small file.

	If you're using this test on your localhost, change theese php.ini file
	informations:

		post_max_size = 200M

		file_uploads = On

		upload_max_filesize = 200M

	then restart your webserver (Apache, PAMPA or your favourite one).

	These settings are necessary because in your localhost files are 
	uploaded quickly, then when you choose a file, choose a big one (80Mb or more).

	It's not necessary if you want to test this demo on-line, where upload
	speed limit should be enought to view uploaded bytes.



[ITALIANO]
Come usare questo dimostrativo

	Aprire il file whileuploading.php e cambiare, se necessario, la cartella temporanea.
	Se non sai dov'è la tua cartella temporanea, prova ad usare foldertest.php
	upoadando un file molto piccolo.

	Se stai testando questa demo nel tuo localhost, cambia queste impostazioni
	nel file php.ini:

		post_max_size = 200M

		file_uploads = On

		upload_max_filesize = 200M

	quindi riavvia il tuo webserver (Apache, PAMPA o quello che preferisci).

	Queste impostanzioni sono necessarie perchè nel tuo localhost i files
	vengono uploadati velocemente, quindi quando scegli un file, scegline uno di grande dimensioni
	(80 Mb o più).

	Questo non è necessario se vuoi testare questo demo on-line, dove la velocità
	limitata di upload dovrebbe essere abbastanza per poter vedere i bytes uploadati.
	