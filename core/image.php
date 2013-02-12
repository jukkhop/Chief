<?php
namespace Chief;

list($path, $params) = explode('?', trim($_SERVER['REQUEST_URI'], '/'));

if(file_exists('../plugins/image/image.php')) {
    require_once('../plugins/image/image.php');
} else {
    readfile('../'.$path);
    die();
}

preg_match('~(\d+)x(\d+)~', $params, $matches);

$width  = isset($matches[1]) ? $matches[1] : false;
$height = isset($matches[2]) ? $matches[2] : false;

if(!is_dir('../uploads/cache/')) {
    mkdir('../uploads/cache/');
}

class Plugin { }

$cache_path = '../uploads/cache/'.sha1($path.$params.filemtime('../'.$path)).'.'.pathinfo($path, PATHINFO_EXTENSION);

if(false && file_exists($cache_path)) {
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        header('HTTP/1.1 304 Not Modified');
        die();
    }
    $extension = strtolower(pathinfo($cache_path, PATHINFO_EXTENSION));
    switch($extension) {
        case 'png':
            header('Content-type: image/png');
            break;        
        case 'jpg':
        case 'jpeg':
            header('Content-type: image/jpeg');
            break;        
        case 'gif':
            header('Content-type: image/gif');
            break;
    }
    readfile($cache_path);
} else {
    $image = new Image('../'.$path);
    if($width && $height) {
        $image->resize($width, $height, true, false, true);
    }
    $image->save($cache_path);
    
    header('Last-Modified: '.gmdate(DATE_RFC1123, filemtime('../'.$path)));
    header('Cache-Control: max-age=3600');
    $image->stream();
}
