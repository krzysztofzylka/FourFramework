<?php
return $this->file = new class(){ 
	public $version = "1.0"; 
	public function fileCount(string $path){ 
		$tmp = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
		return iterator_count($tmp);
	}
}
?>