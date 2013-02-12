<?php
namespace Chief;

class Image extends Plugin
{   
    public $resource = false;
    public $filetype;
    public $quality = 90;
    
    public $resize_crop = true;
    public $resize_trim = false;
    public $resize_fill = false;
    public $resize_aspect = true;
    
    private $width;
    private $height;
    
    public function __construct($path = null) 
    {
        if(!is_null($path) && is_string($path)) {
            $this->load($path);
        }
    }
   
    public function __toString() 
    {
        return $this->filename.'.'.$this->filetype.' ('.$this->width.'x'.$this->height.'px)';
    }
   
    public function __get($variable) 
    {
        $return = isset($this->$variable) ? $this->$variable : null;
        return $return;
    }
   
    public function load($path) 
    {
        if(file_exists($path)) {
            $size = getimagesize($path);
            $this->width = $size[0];
            $this->height = $size[1];
            $this->filename = pathinfo($path, PATHINFO_FILENAME);
            $this->filetype = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $this->resource = imagecreatefromstring(file_get_contents($path));
            $this->fill_color = $this->color('#ffffffff');
        }
        return $this;
    }
    
    public function close() 
    {
        $this->resource = null;
    }
   
    public function fill($x, $y, $color, $resource = null) 
    {
        return imagefill(is_resource($resource) ? $resource : $this->resource, $x, $y, is_int($color) ? $color : $this->color($color));
    }
    
    public function crop($x, $y, $w, $h) 
    {        
        $resource = imagecreatetruecolor($w, $h);        
        imagecopy($resource, $this->resource, 0, 0, $x, $y, $w, $h);
        $this->resource = $resource;
        $this->width = $w;
        $this->height = $h;
        return $this;
    }
   
    public function resize($width = null, $height = null, $aspect = true, $crop = false, $trim = true, $upscale = true) 
    {        
        $width = is_null($width) ? $this->width : $width;
        $height = is_null($height) ? $this->height : $height;
        
        $this->resize_aspect = $aspect !== null ? $aspect : $this->resize_aspect;
        $this->resize_crop   = $crop !== null   ? $crop   : $this->resize_crop;
        $this->resize_trim   = $trim !== null   ? $trim   : $this->resize_trim;
        
        if(!$this->resize_aspect) {
            $resource = imagecreatetruecolor($width, $height);
            if(imagecopyresampled($resource, $this->resource, 0, 0, 0, 0, $width, $height, $this->width, $this->height)) {
                $this->width = imagesx($resource);
                $this->height = imagesy($resource);
                $this->resource = $resource;
                return true;
            } else {
                return false;
            }
        } else {
            $old_aspect = $this->width / $this->height;
            $new_aspect = $width / $height;
            
            if(!$this->resize_crop) {
                if($new_aspect > $old_aspect) {
                    $new_height = $height;
                    $new_width = round($height * $old_aspect);
                } elseif($new_aspect <= $old_aspect) {
                    $new_width = $width;
                    $new_height = round($width / $old_aspect);
                } else {
                    $new_width = $width;
                    $new_height = $height;
                }
            } else {
                if($new_aspect > $old_aspect) {
                    $new_width = $width;
                    $new_height = round($width / $old_aspect);
                } elseif($new_aspect <= $old_aspect) {
                    $new_height = $height;
                    $new_width = round($height * $old_aspect);
                } else {
                    $new_width = $width;
                    $new_height = $height;
                }
            }
            
            if(!$upscale && ($new_width > $this->width || $new_height > $this->height)) {
                $new_width = $this->width;
                $new_height = $this->height;
            }
        
            if($this->resize_trim) {
                $width = $new_width;
                $height = $new_height;
            }
            
            $resource = imagecreatetruecolor($width, $height);
            
            imagealphablending($resource, false);
            imagesavealpha($resource, true);
            
            $this->fill(0, 0, $this->fill_color, $resource);
            
            $x = ($width - $new_width) / 2;
            $y = ($height - $new_height) / 2;
            
            if(is_array($crop)) {
                $min_crop_x = $width / 2 / $new_width;
                $max_crop_x = 1 - $min_crop_x;
                $crop_x = min($max_crop_x, max($min_crop_x, $crop['x'] / 100));
                $x = 0 - ($crop_x * $new_width - $width / 2);
                
                $min_crop_y = $height / 2 / $new_height;
                $max_crop_y = 1 - $min_crop_y;
                $crop_y = min($max_crop_y, max($min_crop_y, $crop['y'] / 100));
                $y = 0 - ($crop_y * $new_height - $height / 2);
            }
            
            if(imagecopyresampled($resource, $this->resource, $x, $y, 0, 0, $new_width, $new_height, $this->width, $this->height)) {
                $this->width = imagesx($resource);
                $this->height = imagesy($resource);
                $this->resource = $resource;
                return $this;
            } else {
                return false;
            }
        }
    }
    
    public function getWidth() 
    {
        return $this->width;
    }
    
    public function getHeight() 
    {
        return $this->height;
    }
   
    public function blur($amount = 1) 
    {       
        return $this->convolution('1 2 1 2 4 2 1 2 1', $amount);
    }
   
    public function sharpen($amount = 1) 
    {
        return $this->convolution('-1 -1 -1 -1 20 -1 -1 -1 -1', $amount);
    }
   
    public function convolution($matrix, $amount = 1) 
    {
        $matrix = trim($matrix);
        $cells = preg_split('/\s+?/', $matrix);
        $result = array();
        $div = 0;
        for($y = 0; $y < 3; $y++) {
            for($x = 0; $x < 3; $x++) {
                $result[$y][$x] = array_shift($cells);
                $div += $result[$y][$x];
            }
        }
        for($i = 1; $i <= $amount; $i++) {
            imageconvolution($this->resource, $result, $div, 0);
        }
        return $this;
    }
    
    public function text($text, $size, $angle, $x, $y, $font, $color) 
    {
        $font = strtolower($font);
        imagealphablending($this->resource, true);
        imagettftext($this->resource, $size, $angle, $x, $y, $color, '/../../fonts/'.$font, $text);
        imagealphablending($this->resource, false);
        return $this;        
    }
    
    public function stream($quality = 80) 
    {
        switch($this->filetype) {
            case 'png':
                header('Content-type: image/png');
                imagepng($this->resource);
                break;
               
            case 'jpg':
            case 'jpeg':
                header('Content-type: image/jpeg');
                imagejpeg($this->resource, null, $quality);
                break;
               
            case 'gif':
                header('Content-type: image/gif');
                imagegif($this->resource);
                break;
        }
        return $this;
    }
   
    public function save($path, $quality = 80) 
    {
        $quality  = $quality ? $quality : $this->quality;
        $filetype = strtolower(pathinfo($path, PATHINFO_EXTENSION));
       
        if($this->filetype != $filetype) {
            $this->filetype = $filetype;
        }
       
        switch($this->filetype) {
            case 'png':
                imagepng($this->resource, $path);
                break;
               
            case 'jpg':
            case 'jpeg':
                imagejpeg($this->resource, $path, $quality);
                break;
               
            case 'gif':
                imagegif($this->resource, $path);
                break;
        }
        return $this;
    }
   
    public function color() 
    {
        $color = func_num_args() > 1 ? func_get_args() : func_get_arg(0);
        
        if(is_string($color) && mb_strlen($color) != mb_strlen($color = ltrim($color, '#'))) {
             
            if(mb_strlen($color) == 3 || mb_strlen($color) == 4) {
                $new_color = '';
                for($i = 0; $i < mb_strlen($color); $i++) {
                    $new_color .= str_repeat($color[$i], 2);
                }
                $color = $new_color;
            }
            if(mb_strlen($color) >= 6) {                
                $red   = hexdec(substr($color, 0, 2));
                $green = hexdec(substr($color, 2, 2));
                $blue  = hexdec(substr($color, 4, 2));
                if(mb_strlen($color) == 8) {
                    $alpha = floor(hexdec(substr($color, 6, 2)) / 2);
                } else {
                    $alpha = 127;
                }
            }
        } elseif(is_array($color) && count($color) == 3) {
            list($red, $green, $blue) = $color;
            $alpha = 127;
        } elseif(is_array($color) && count($color) == 4) {
            list($red, $green, $blue, $alpha) = $color;
        }
        return imagecolorallocatealpha($this->resource, $red, $green, $blue, $alpha);
    }
}
