<?php

/**
 *   +----------------------------------------------------------------------+
 *   |    EasyZIP.class.php                                                 |
 *   +----------------------------------------------------------------------+
 *   |    php4 - 14 October 2004                                            |
 *   +----------------------------------------------------------------------+
 *   |  Cette classe PHP permet de créer des fichiers ZIP (+split)          |
 *   |  à la volée. Download sur www.phpclasses.org.                        |
 *   +----------------------------------------------------------------------+
 *   |  Auteur:   huda m elmatsani (justhuda at netscape dot net)           |
 *   |  Modifications:   Cédric Meier                                       |
 *   |  Modifications:   Tafel (jan. 2006                                   |
 *   |  Version:  1.0 stable                                                |
 *   +----------------------------------------------------------------------+
 */

/**
 *  Example:
 *  --------
 *  create zip file
 *	$z = new EasyZIP;
 *	$z -> addFile("map.bmp");
 *	$z -> addFile("guide.pdf");
 *  $z -> addDir("files/test");
 *  // xyz.zip est optionnel. Si un fichier n'est pas spécifié,
 *  // la méthode retourne le flux ZIP. Sinon, retourne true.
 *	$z -> zipFile("xyz.zip");
 *
 *  created splitted file
 *      $z = new EasyZIP;
 *      $z -> addFile("guide.pdf");
 *	$z -> splitFile("map.zip",1048576);
 * 
 *  pack and split
 *      $z = new EasyZIP;
 *	$z -> addFile("map.bmp");
 *	$z -> addFile("guide.pdf");
 *	$z -> splitFile("xyz.zip",1048576);
 *
 *
 */

//simple error message definition
define('FUNCTION_NOT_FOUND','Error: gzcompress() function is not found');
define('FILE_NOT_FOUND','Error: file is not found');
define('DIRECTORY_NOT_FOUND','Error: directory is not found');

class EasyZIP {
	var $filelist = array();
//	var $pathname = array();
	var $data_segments = array();
	var $data_block;
	var $file_headers  = array();
	var $filename;
	var $pathname;
	var $filedata;
	var $old_offset = 0;
	var $splitted = 0;
	var $split_signature = "";
	var $split_size = 1;
	var $split_offset = 0;
	var $disk_number = 1;

	function EasyZIP() {
		if (!@function_exists('gzcompress')) die(FUNCTION_NOT_FOUND);
	}
 
    function addFile($filename, $pathname='') {
		if(file_exists($pathname.$filename)) {

			$this -> filelist['filename'][] = str_replace('\\', '/', $filename);
			$this -> filelist['pathname'][] = str_replace('\\', '/', $pathname);

		} else {
			die(FILE_NOT_FOUND);
		}
	}

	/**
	 * Fonction pour ajouter le contenu d'un dossier
	 *
	 * Modifié le code pour qu'on puisse ajouter des dossiers en relatif. Spécifiez le dossier
	 * à ouvrir si vous voulez le comportement par défaut. Sinon, il faut spécifier le dossier
	 * parent de celui à ouvrir, et juste le dossier à ouvrir dans $path
	 * Modifié par : FTafel
	 *
	 * @access 	public
	 * @author	huda m elmatsani (justhuda at netscape dot net)  
	 * @param 	string			$dirname				Le nom du dossier contenant le dossier à ouvrir sans le '/' à la fin
	 * @param 	string			$path					Le nom du dossier à ouvrir (sans le chemin et sans slash à la fin)
	 * @return	void
	 */
    function addDir($dirname, $path = false) {
    	$dirnameOpen = ($path) ? $dirname.'/'.$path : $dirname;
    	
		if ($handle = opendir($dirnameOpen)) { 
		   while (false !== ($filename = readdir($handle))) { 
			 if ($filename != "." && $filename != ".."){
			 	if (substr($dirname, -1) == '/') {
			 		if (!$path) $this->addFile($dirname . $filename);
			 		else        $this->addFile($path.'/'.$filename, $dirname);
			 	} else {
				  	if (!$path) $this->addFile($dirname . '/' . $filename);
			 		else        $this->addFile($path.'/'.$filename, $dirname.'/');
				}
			 }
		   } 
		   closedir($handle); 
		} else {
			die(DIRECTORY_NOT_FOUND);
		}
	}


	function zipFile($zipfilename='') {
		$zip = $this -> packFiles();
		if ($zipfilename != '') {
		    if(!($fp = @fopen($zipfilename, "w"))) return false;
			fwrite($fp, $zip, strlen($zip));
			fclose($fp);
			return true;
		} else {
			return $zip;
		}
    } 

	function splitFile($splitfilename, $chunk_size) {

		$this -> chunk_size = $chunk_size;
		$this -> splitted = 1;
		$this -> split_offset = 4;
		$this -> old_offset = $this -> split_offset;
		$this -> split_signature = "\x50\x4b\x07\x08";

		$zip = $this -> packFiles();

        $out = $this -> str_split($this -> split_signature . $zip, $chunk_size);

		for ($i = 0; $i < sizeof($out); $i++){
			if($i < sizeof($out)-1) {
				$sfilename = basename ($splitfilename,".zip"); 
				$sfilename = $sfilename . ".z" . sprintf("%02d",$i+1);
			}
			else $sfilename = $splitfilename;
			$fp = fopen($sfilename, "w");
			fwrite($fp, $out[$i], strlen($out[$i]));
			fclose($fp);
		}

	}



	function packFiles() {

		$counter=0; 
		while ($counter<count($this->filelist['filename'])) {
 			$this -> filename = $this->filelist['filename'][$counter];
			$this -> pathname = $this->filelist['pathname'][$counter];
			$this -> setFileData();
			$this -> setLocalFileHeader();
			$this -> setDataDescriptor();
			$this -> setDataSegment();
			$this -> setFileHeader();
			//echo $this->filelist['filename'][$counter]; 
			//echo $this->filelist['pathname'][$counter]; 
			$counter++; 
		}

		/**
		foreach($this->filelist['filename'] as $k => $filename) {
 			$this -> filename =  $filename;
			$this -> setFileData();
			$this -> setLocalFileHeader();
			$this -> setDataDescriptor();
			$this -> setDataSegment();
			$this -> setFileHeader();
		}
* 		*/
		return  $this -> getDataSegments() . 
				$this -> getCentralDirectory();

	}

	function setFileData() {
			clearstatcache();
			$fd = fopen ($this->pathname.$this->filename, "rb");
			$this->filedata = fread ($fd, filesize ($this->pathname.$this->filename));
			fclose ($fd);
			$filetime = filectime($this->pathname.$this->filename);
			$this -> DOSFileTime($filetime);

	}
 
	function setLocalFileHeader() {

		$local_file_header_signature 		  = "\x50\x4b\x03\x04";//4 bytes  (0x04034b50)
		$this -> version_needed_to_extract	  = "\x14\x00";  //2 bytes
		$this -> general_purpose_bit_flag	  = "\x00\x00";  //2 bytes
		$this -> compression_method           = "\x08\x00";  //2 bytes
		$this -> crc_32	                	  = pack('V', crc32($this -> filedata));//  4 bytes
				//compressing data
				$c_data   = gzcompress($this -> filedata);
				$this->compressed_filedata    = substr(substr($c_data, 0, strlen($c_data) - 4), 2); // fix crc bug
		
		$this -> compressed_size          	  = pack('V', strlen($this -> compressed_filedata));// 4 bytes
		$this -> uncompressed_size        	  = pack('V', strlen($this -> filedata));//4 bytes
		$this -> filename_length              = pack('v', strlen($this -> filename));// 2 bytes
		$this -> extra_field_length           = pack('v', 0);  //2 bytes

		$this -> local_file_header = 	$local_file_header_signature .
				$this -> version_needed_to_extract .
				$this -> general_purpose_bit_flag .
				$this -> compression_method .
				$this -> last_mod_file_time .
				$this -> last_mod_file_date .
				$this -> crc_32 .
				$this -> compressed_size .
				$this -> uncompressed_size .
				$this -> filename_length .
				$this -> extra_field_length .
				$this -> filename;

		
	}

	function setDataDescriptor() {
	
		$this -> data_descriptor =  $this->crc_32 .   //4 bytes
				$this -> compressed_size .           //4 bytes
				$this -> uncompressed_size;          //4 bytes
	}

	function setDataSegment() {
	
			$this -> data_segments[] 	= 	$this -> local_file_header . 
									$this -> compressed_filedata . 
									$this -> data_descriptor;
			$this -> data_block = implode('', $this -> data_segments);
	}

	function getDataSegments() {
		return $this -> data_block;
	}

	function setFileHeader() {

        $new_offset        = strlen( $this -> split_signature . $this -> data_block );
		
		$central_file_header_signature  = "\x50\x4b\x01\x02";//4 bytes  (0x02014b50)
		$version_made_by                = pack('v', 0);  //2 bytes
		
		$file_comment_length            = pack('v', 0);  //2 bytes
		$disk_number_start              = pack('v', $this -> disk_number - 1); //2 bytes
		$internal_file_attributes       = pack('v', 0); //2 bytes
		$external_file_attributes       = pack('V', 32); //4 bytes
		$relative_offset_local_header   = pack('V', $this -> old_offset); //4 bytes
		
		if($this -> splitted) {
			$this -> disk_number = ceil($new_offset/$this->chunk_size);
			$this -> old_offset = $new_offset - ($this->chunk_size * ($this -> disk_number-1));
		} else $this -> old_offset = $new_offset;
		
		$this -> file_headers[] = 	$central_file_header_signature .
				$version_made_by .
				$this -> version_needed_to_extract .
				$this -> general_purpose_bit_flag .
				$this -> compression_method .
				$this -> last_mod_file_time .
				$this -> last_mod_file_date .
				$this -> crc_32 .
				$this -> compressed_size .
				$this -> uncompressed_size .
				$this -> filename_length .
				$this -> extra_field_length .
				$file_comment_length .
				$disk_number_start .
				$internal_file_attributes .
				$external_file_attributes .
				$relative_offset_local_header .
				$this -> filename;
	}

	function getCentralDirectory() {
		$this -> central_directory = implode('', $this -> file_headers);		
		return  $this -> central_directory . 
				$this -> getEndCentralDirectory();
	}

	function getEndCentralDirectory() {
					
		$zipfile_comment = "Compressed/Splitted by PHP EasyZIP";
		$zipfile_comment = "";

		if($this -> splitted) {
			$data_len = strlen($this -> split_signature . $this -> data_block . $this -> central_directory);
			$last_chunk_len = $data_len - floor($data_len / $this -> chunk_size) * $this -> chunk_size;
			$this -> old_offset = $last_chunk_len - strlen($this -> central_directory);
		}

		$end_central_dir_signature    = "\x50\x4b\x05\x06";//4 bytes  (0x06054b50)
		$number_this_disk             = pack('v', $this->disk_number - 1);//2 bytes
		$number_disk_start			  = pack('v', $this->disk_number - 1);//  2 bytes
		$total_number_entries		  = pack('v', sizeof($this -> file_headers));//2 bytes
		$total_number_entries_central = pack('v', sizeof($this -> file_headers));//2 bytes
		$size_central_directory   	  = pack('V', strlen($this -> central_directory));  //4 bytes

		$offset_start_central         = pack('V', $this -> old_offset); //4 bytes     
		$zipfile_comment_length       = pack('v', strlen($zipfile_comment));//2 bytes
		
		return $end_central_dir_signature .
			$number_this_disk .
			$number_disk_start .
			$total_number_entries .
			$total_number_entries_central .
			$size_central_directory .
			$offset_start_central .
			$zipfile_comment_length .
			$zipfile_comment;
	}

    function DOSFileTime($unixtime = 0) {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
        	$timearray['year']    = 1980;
        	$timearray['mon']     = 1;
        	$timearray['mday']    = 1;
        	$timearray['hours']   = 0;
        	$timearray['minutes'] = 0;
        	$timearray['seconds'] = 0;
        } 

        $dostime = (($timearray['year'] - 1980) << 25) | 
					($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
                	($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | 
					($timearray['seconds'] >> 1);
				
		$dtime    = dechex($dostime);
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
                  . '\x' . $dtime[4] . $dtime[5];
				  
        $hexddate = '\x' . $dtime[2] . $dtime[3]
                  . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');
		eval('$hexddate = "' . $hexddate . '";');
		
		$this->last_mod_file_time = $hexdtime;
		$this->last_mod_file_date = $hexddate;
    } 	
	function str_split($string, $length) {
	    for ($i = 0; $i < strlen($string); $i += $length) {
	        $array[] = substr($string, $i, $length);
	    }
	    return $array;
	}

} 
?>