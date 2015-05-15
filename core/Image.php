<?php

Class Image{

	const imageThresX = 300;
  const imageThresY = 200;
	static function validateImage($image){

    if(!$image)  return false;

		if(imagesx($image)>self::imageThresX && imagesy($image)>self::imageThresY)
			return true;

		return false;
	}

	static function resizeAndCropImage($image, $size){

		$src_width = imagesx($image);
    $src_height = imagesy($image);
    $src_aspect = $src_width / $src_height;
    $dest_width = $size["width"];
    $dest_height = $size["height"];
    $dest_aspect = $size["width"] / $size["height"];
    if ( $src_aspect >= $dest_aspect )
    {
       // If image is wider than thumbnail (in aspect ratio sense)
       $new_height = $dest_height;
       $new_width = $src_width / ($src_height / $dest_height);
    }
    else
    {
       // If the thumbnail is wider than the image
       $new_width = $dest_width;
       $new_height = $src_height / ($src_width / $dest_width);
    }
    $destImg = imagecreatetruecolor( $dest_width, $dest_height );
    // Resize and crop
    imagecopyresampled($destImg,
                       $image,
                       0 - ($new_width - $dest_width) / 2, // Center the image horizontally
                       0 - ($new_height - $dest_height) / 2, // Center the image vertically
                       0, 0,
                       $new_width, $new_height,
                       $src_width, $src_height);
    return $destImg;

	}
  static function resizeImageByWidth($image, $width){

    $src_width = imagesx($image);
    $src_height = imagesy($image);
    $src_aspect = $src_width / $src_height;

    if($src_height==0 || $src_width==0) return NULL;

    $new_width = $width;
    $new_height = $width/$src_aspect;
    
    $destImg = imagecreatetruecolor( $new_width, $new_height );
    // Resize and crop
    imagecopyresampled($destImg,
                       $image,
                       0, 0,
                       0, 0,
                       $new_width, $new_height,
                       $src_width, $src_height);
    return $destImg;

  }


}

?>