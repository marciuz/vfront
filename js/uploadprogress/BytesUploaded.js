/**
 * Object BytesUploaded
 * 	With a dedicated server file it should know how many bytes are uploading.
 *      Look at http://www.devpro.it/upload_progress/ to know more
 *
 * @dependencies	LoadVars JavaScritp File: [ http://www.devpro.it/javascript_id_92.html ]
 *			dedicated PHP or server file [look at the bottom of this file to view an example]
 * @author              Andrea Giammarchi
 * @site		www.devpro.it
 * @date                2005/09/21
 * @lastmod             2005/09/22 16:00
 * @version             0.1 stable 
 */
function BytesUploaded(
		phpFile, 	// Contructor needs php or server filename to read informations
		latency 	// Milliseconds for each request during upload, default 1000, min value 50
	) { 
	
	/**
	 * Public method
         * 	Starts this application, set div or generic html id to write
         *      informations while uploading.
         *
         *      this.start( htmlid:String ):Boolean
         *
         * @Param	String		valid div, span or other page unique id to show information
         * @Return	Boolean		True to submit the form
	 */
	function start(htmlid) {
		__filemonitor.htmlid = htmlid;
		__fileloaderInterval = setTimeout(__readFileSize, 10);
		return true;
	}
	
	/** LIST OF ALL PRIVATE METHODS [ uncommented ] */
	function __fSize(size, dec) {
		if(!dec || dec < 0)
			dec = 2;
		var times = 0;
		var nsize = Number(size);
		var toEval = '';
		var type = Array( 'bytes', 'Kb', 'Mb', 'Gb', 'Tb', 'Zb' );
		while( nsize > 1024 ) {
			nsize = nsize / 1024;
			toEval += ' / 1024';
			times++;
		}
		if( times > 0 )
			eval( 'size = ( size' + toEval + ' );' );
		if(dec > 0) {
			var moltdiv = '(';
			while(dec > 0) {
				moltdiv += '10*';
				dec--;
			}
			moltdiv = moltdiv.substr(0, (moltdiv.length - 1)) + ')';
			eval( 'size = Math.round(size * ' + moltdiv + ') / ' + moltdiv + ';' );
		}
		return size + ' ' + type[times];
	}
	function __readFileSize() {
		__filemonitor.load(phpFile);
	}
	
	/** DECLARATION OF ALL PUBLIC METHODS */
	this.start = start;	// function to start this application
	
	/** PRIVATE VARIABLES */
	var __fileloaderInterval = 0;
	var __maybesomethingwrong = 0;
	var __filemonitor = new LoadVars();
	__filemonitor.onLoad = function(s) {
		var whatsup = '';
		if(s && this.filesize && this.filesize != 'undefined')
			whatsup = 'Caricati ' + __fSize(this.filesize) + ' ...';
		else if(this.filesize && this.filesize == 'undefined') {
			whatsup = 'In attesa di risposta dal server...';
			if(__maybesomethingwrong++ > 10) {
				__fileloaderInterval = 0;
//				whatsup = 'Temp folder seems to be not valid.';
				whatsup = 'Sembra esserci un\'errore nelle impostazioni della cartella temporanea.';
			}
		}
		else {
//			whatsup = 'Unable to find server informations.';
			whatsup = 'Informazioni dal server non disponibili. Attendere, prego.';
			__fileloaderInterval = 0;
		}
		document.getElementById(this.htmlid).innerHTML = whatsup;
		delete this.filesize;
		if(__fileloaderInterval != 0)
			__fileloaderInterval = setTimeout(__readFileSize, latency);
	}
	if(!latency || latency < 50)
		latency = 1000;
}