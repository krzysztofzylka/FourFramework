<?php
return $this->file = new class(){ 
	public $version = '1.7'; 
	public function fileCount(string $path){ 
		core::setError();
		if(!file_exists($path))
			return core::setError(1, 'path not exists');
		$tmp = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
		return iterator_count($tmp);
	}
	public function uploadFile(string $fileFormName, string $newPath, string $newName = null, array $option = []) : bool{
		core::setError();
		if(!is_array($option))
			$option = [];
		if(!isset($option['ignoreFileExists']))
			$option['ignoreFileExists'] = false;
		if(!isset($option['maxFileSize']))
			$option['maxFileSize'] = -1;
		if(!isset($option['fileExtension']))
			$option['fileExtension'] = null;
		if(!isset($_FILES[$fileFormName]))
			return core::setError(1, 'fileFormName not exists');
		$file = $_FILES[$fileFormName];
		if($file['error'] > 0)
			return core::setError(2, 'upload file error', 'error: '.$file['error']);
		if($option['fileExtension'] <> null){
			$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
			$option['fileExtension'] = strtolower($option['fileExtension']);
			if(array_search($ext, explode(',', $option['fileExtension'])) === false)
				return core::setError(7, 'invalid file extension', 'possible extensions: '.$option['fileExtension']);
		}
		if($option['maxFileSize'] > -1)
			if($file['size'] >= $option['maxFileSize'])
				return core::setError(6, 'file size is too large', 'file size: '.core::$library->memory->formatBytes($file['size']).', max file size: '.core::$library->memory->formatBytes($option['maxFileSize']));
		$newName = $newName??$file['name'];
		if(!file_exists($file['tmp_name']))
			return core::setError(3, 'temp file not exists');
		$newPathLastChr = substr($newPath, strlen($newPath)-1);
		if($newPathLastChr <> '\\' and $newPathLastChr <> '/')
			$newPath .= '\\';
		$newPath .= $newName;
		if(file_exists($newPath))
			if($option['ignoreFileExists'])
				unlink($newPath);
			else
				return core::setError(4, 'file is already exists', $newPath);
		if(!copy($file['tmp_name'], $newPath))
			return core::setError(5, 'error copy file');
		return true;
	}
	public function dirSize(string $path){
		core::setError();
		$size = 0;
		foreach (glob(rtrim($path, '/').'/*', GLOB_NOSORT) as $each)
			$size += is_file($each) ? filesize($each) : $this->dirSize($each);
		return $size;
	}
	public function protectLongFileSize(string $path, int $maxFileSize = 102400, bool $createEmptyFile = false) : bool{
		core::setError();
		if(!file_exists($path))
			return core::setError(1, 'File not found');
		if(filesize($path) < $maxFileSize) return false;
		return false;
		$pathInfo = pathinfo($path);
		$count = 1;
		while(true){
			$newPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_'.$count.'.'.$pathInfo['extension'];
			if(!file_exists($newPath)){
				rename($path, $newPath);
				if($createEmptyFile)
					file_put_contents($path, '');
				return true;
			}
			$count++;
		}
		return false;
	}
	public function writeToLine(string $path, string $value, int $line = 0){
		core::setError();
		if(!file_exists($path)) core::setError(1, 'file not found', $path);
		$readFile = file($path);
		$readFile[$line] = $value;
		file_put_contents($path, implode('', $readFile));
		return true;
	}
	public function deleteLine(string $path, int $line = 0){
		core::setError();
		if(!file_exists($path)) core::setError(1, 'file not found', $path);
		$readFile = file($path);
		unset($readFile[$line]);
		file_put_contents($path, implode('', $readFile));
		return true;
	}
	public function repairPath(string $path){
		core::setError();
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		while(strpos($path, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) <> false)
			$path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		return $path;
	}
	public function dirToArray($dir) {
		core::setError();
		$result = array();
		$cdir = scandir($dir);
		foreach ($cdir as $key => $value){
			if(!in_array($value,array(".",".."))){
				if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
					$result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
			  	else
					 $result[] = $value;
		   	}
		}
		return $result;
	}
	public function rmdir($path){
		if (!file_exists($path)) return true;
		if (!is_dir($path)) return unlink($path);
		foreach (scandir($path) as $item) {
			if ($item == '.' || $item == '..') continue;
			if (!$this->rmdir($path . DIRECTORY_SEPARATOR . $item)) return false;
		}
		return rmdir($path);
	}
	public function recurseCopy($src, $dst, $childFolder='') { 
		$dir = opendir($src);
		mkdir($dst);
		if($childFolder!='') {
			mkdir($dst.'/'.$childFolder);
			while(false !== ( $file = readdir($dir)) ) {
				if(( $file != '.' ) && ( $file != '..' )){
					if ( is_dir($src . '/' . $file) )
						$this->recurseCopy($src . '/' . $file,$dst.'/'.$childFolder . '/' . $file);
					else
						copy($src . '/' . $file, $dst.'/'.$childFolder . '/' . $file);
				}
			}
		}else{
			while(false !== ($file = readdir($dir))){
				if (($file != '.') && ($file != '..')){
					if (is_dir($src . '/' . $file))
						$this->recurseCopy($src . '/' . $file,$dst . '/' . $file);
					else
						copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir); 
	}
}
?>