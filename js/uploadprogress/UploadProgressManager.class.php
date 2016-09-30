<?php
/**
 * Class UploadProgressManager
 * 	This class is dedicated to my AJAX / PHP upload progress method, look at this page
 *      to know more about upload progress: http://www.devpro.it/upload_progress/
 *
 * @author              Andrea Giammarchi
 * @site		www.devpro.it
 * @date                2005/09/22
 * @lastmod             2005/09/22 13:30
 * @version             0.1 stable 
 */
class UploadProgressManager {
	
	/**
	 * 'Private' variables
         *      __personalFile:String		User temporary file name that is uploading
	 *      __sentinell:String		A dedicated text file to save temporary files list
	 */
	var $__personalFile = '';
	var $__sentinell = '/.__php__upm_sentinell__';
	
	/**
	 * Public constructor:
	 *	Creates filename for sentinell temporary file then checks if file was found or
         *      try to find that.
         *       	new UploadProgressManager( $tmpfolder:String, $pattern:String )
	 * @Param	String		Temporary folder where file are created during upload process
         * @Param	String		Dedicated Linux / Windows pattern string, default '/[p][h][p]*'
         * 				For Windows users is better and faster '/[p][h][p]*.tmp'
	 */
	function UploadProgressManager($tmpfolder, $pattern = '/[p][h][p]*') {
		$this->__sentinell = $tmpfolder.$this->__sentinell;
		if(isset($_SESSION['tempfile']) && file_exists($_SESSION['tempfile']))
			$this->__personalFile = $_SESSION['tempfile'];
		else
			$this->__personalFile = &$this->__getPersonalFile($tmpfolder, $pattern);
			if($this->__personalFile !== false)
				$_SESSION['tempfile'] = $this->__personalFile;
	}
	
	/**
	 * Public method:
	 *	Returns size of temporary file or false
         *       	this->getTemporaryFileSize( Void ):Mixed
         * @Return	Mixed		Bollean false if was not possible to find file or its size, in bytes.
	 */
	function getTemporaryFileSize() {
		return ($this->__personalFile === false ? false : filesize($this->__personalFile));
	}
	
	// PRIVATE METHODS [ UNCOMMENTED ]
	function __checkSentinell(&$phptempfiles) {
		$found = false;
		if(file_exists($this->__sentinell)) {
			$fsize = filesize($this->__sentinell);
			if(@$fp = fopen($this->__sentinell, 'r+')) {
				@flock($fp, LOCK_EX);
				$tmpfiles = unserialize(fread($fp, $fsize));
				$tmpfound = array_diff_assoc($phptempfiles, $tmpfiles);
				if(is_array($tmpfound) && count($tmpfound) === 1) {
					foreach($tmpfound as $k => $v)
						$found = &$v;
					rewind($fp);
					fwrite($fp, serialize($phptempfiles));
				}
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		return $found;
	}
	function __getPersonalFile(&$tmpfolder, &$pattern) {
		$found = false;
		if(is_dir($tmpfolder)) {
			$phptempfiles = glob($tmpfolder.$pattern);
			if(count($phptempfiles) === 1) {
				if(@$fp = fopen($this->__sentinell, 'w')) {
					@flock($fp, LOCK_EX);
					fwrite($fp, serialize($phptempfiles));
					@flock($fp, LOCK_UN);
					fclose($fp);
				}
				$found = $phptempfiles[0];
			}
			else
				$found = $this->__checkSentinell($phptempfiles);
		}
		return $found;
	}
}
?>