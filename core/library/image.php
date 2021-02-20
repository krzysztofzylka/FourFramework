<?php
return $this->image = new class() {
    public $version = '1.1a';
    public function compress(string $imagePath, string $imageDestiantion, int $imageQuality = 90) : bool{
        core::setError();
        if (!file_exists($imagePath)) {
            return core::setError(1, 'file not found');
        }
        if ($imageQuality > 100 or $imageQuality < 0) {
            return core::setError(2, 'bad value - quality (0-100)');
        }
        $info = getimagesize($imagePath);
        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                imagejpeg($image, $imageDestiantion, $imageQuality);
                return true;
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                imagepng($image, $imageDestiantion, round($imageQuality / 11.111111));
                return true;
                break;
        }
        return false;
    }
    public function resizeImage(string $filePath, string $destinationPath, int $maxWidth, int $maxHeight, int $imageQuality = 100){
        list($imageWidth, $imageHeight) = getimagesize($filePath);
        $resizeWidth = $imageWidth;
        $resizeHeight = $imageHeight;
        if($imageWidth > $imageHeight){
            $resizeWidth = $maxWidth;
            $resizeHeight = intval($imageHeight * $resizeWidth / $imageWidth);
        }else{
            $resizeHeight = $maxHeight;
            $resizeWidth = intval($imageWidth * $resizeHeight / $imageHeight);
        }
        $newImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
        switch(pathinfo($filePath, PATHINFO_EXTENSION)){
            case 'jpg' :
            case 'jpeg':
                $oldImage = imagecreatefromjpeg($filePath);
                imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $imageWidth, $imageHeight);
                imagejpeg($newImage, $destinationPath, $imageQuality);
                return true;
                break;
            case 'gif' :
                $oldImage = imagecreatefromgif($filePath);
                imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $imageWidth, $imageHeight);
                imagegif($newImage, $destinationPath);
                return true;
                break;
            case 'png' :
                $oldImage = imagecreatefrompng($filePath);
                imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $imageWidth, $imageHeight);
                imagepng($newImage, $destinationPath, round($imageQuality / 11.111111));
                return true;
                break;
        }
        return false;
    }
};