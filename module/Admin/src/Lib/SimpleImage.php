<?php
namespace Admin\Lib;

class SimpleImage {
 
   var $image;
   var $image_type;
 
   function load($filename) {
 
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {

         $this->image = imagecreatefrompng($filename);
      }
      $exif = exif_read_data($filename);
      if (!empty($exif['Orientation'])) {
          switch ($exif['Orientation']) {
          case 3:
              $angle = 180 ;
              break;

          case 6:
              $angle = -90;
              break;

          case 8:
              $angle = 90; 
              break;
          default:
              $angle = 0;
              break;
          }   
      }
      $this->image = imagerotate($this->image, $angle, 0);
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=90, $permissions=null) {
   $image_type=$this->image_type;
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
            imagealphablending($this->image, false);
         imagesavealpha($this->image,true);
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {

         imagepng($this->image);
      }
   }
   function getWidth() {
 
      return imagesx($this->image);
   }

   function getHeight() {
 
      return imagesy($this->image);
   }

   function resizeToHeight($height) {
 
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
 
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
 
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
 
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      if(($this->image_type == IMAGETYPE_GIF) || ($this->image_type==IMAGETYPE_PNG)) {
         imagealphablending($new_image, false);
         imagesavealpha($new_image,true);
         $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
         imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
      }
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }      
 
}