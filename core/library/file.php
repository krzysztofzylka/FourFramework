<?php
return $this->file = new class(){ 
	public $version = '1.2'; 
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
}
?>