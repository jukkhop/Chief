<?php
namespace Chief;

class Layout
{    
    private $header = false;
    private $footer = false;
    private $enabled = true;

    private static $assets;
    private static $title;
    private static $keywords;
    private static $favicon;
    private static $meta;

    public function __construct()
    {
        self::$assets = array(
            'css' => array(),
            'js' => array()
        );
        self::$meta = array();
    }

    public function setHeader($header)
    {
        if(file_exists($header)) {
            $this->header = $header;
        } else {
            throw new Exception('Layout header file '.$header.' not found.');
        }
    }

    public function setFooter($footer)
    {
        if(file_exists($footer)) {
            $this->footer = $footer;
        } else {
            throw new Exception('Layout footer file '.$footer.' not found.');
        }
    }

    public function getHeader()
    {
        return $this->enabled ? $this->header : false;
    }

    public function getFooter()
    {
        return $this->enabled ? $this->footer : false;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled  = false;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public static function title($title)
    {
        self::$title = $title;
    }

    public static function keywords($keywords)
    {
        self::$keywords = $keywords;
    }

    public static function favicon($path)
    {
        self::$favicon = $path;
    }

    public static function meta($key, $value, $keyname = 'name')
    {
        self::$meta[] = array(
            'key' => $key,
            'value' => $value,
            'keyname' => $keyname
        );
    }

    public static function css($path, $priority = 1)
    {
        if(is_array($path)) {
            $defaults = array(
                'href' => null,
                'rel' => 'stylesheet',
                'type' => 'text/css'
            );
            $css = Common::extend($defaults, $path);
            if(ltrim($css['href'], '/\\') === $css['href'] && strtolower(substr($css['href'], 0, 4)) !== 'http') {
                $css['href'] = BASE_DIR.'layout/css/'.$css['href'];
            }
            $attrs = array();
            foreach($css as $key => $value) {
                $attrs[] = $key.'="'.$value.'"';
            }
            self::$assets['css'][$priority][] = sprintf('<link %s />', implode(' ', $attrs));
        } else if(is_string($priority) && (int)$priority !== $priority) {
            foreach(func_get_args() as $path) {
                self::css($path);
            }
        } else {
            if(ltrim($path, '/\\') === $path && strtolower(substr($path, 0, 4)) !== 'http') {
                $path = BASE_DIR.'layout/css/'.$path;
            }

            if(!isset(self::$assets['css'][$priority])) {
                self::$assets['css'][$priority] = array();
            }
            self::$assets['css'][$priority][] = sprintf('<link rel="stylesheet" type="text/css" href="%s" />', $path);
        }
    }

    public static function js($path, $priority = 1)
    {
        if(is_string($priority) && (int)$priority !== $priority) {
            foreach(func_get_args() as $path) {
                self::js($path);
            }
        } else {
            if(ltrim($path, '/\\') === $path && strtolower(substr($path, 0, 4)) !== 'http') {
                $path = BASE_DIR.'layout/js/'.$path;
            }

            if(!isset(self::$assets['js'][$priority])) {
                self::$assets['js'][$priority] = array();
            }
            self::$assets['js'][$priority][] = sprintf('<script type="text/javascript" src="%s"></script>', $path);
        }
    }

    public static function head()
    {
        $title = defined('PAGE_TITLE') && strlen(PAGE_TITLE) > 0 ? PAGE_TITLE.' | '.self::$title : self::$title;
        $html  = '<!DOCTYPE html>'."\n";
        $html .= '<html>'."\n";
        $html .= '<head>'."\n";
        $html .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'."\n";
        if(!empty(self::$keywords)) {
            $html .= '    <meta name="keywords" content="'.(is_array(self::$keywords) ? implode(',', self::$keywords) : self::$keywords).'" />'."\n";
        }
        if(!empty(self::$meta)) {
            foreach(self::$meta as $m) {
                $html .= '    <meta '.$m['keyname'].'="'.$m['key'].'" content="'.$m['value'].'" />'."\n";
            }
        }
        if(!empty(self::$favicon)) {
            $html .= '    <link rel="shortcut icon" href="'.self::$favicon.'" />'."\n";
        }
        $html .= '    <title>'.$title.'</title>'."\n";
        $html .= '    '.self::getAssetHTML()."\n";
        $html .= '    <script type="text/javascript">var BASE_DIR = \''.BASE_DIR.'\';</script>'."\n";
        $html .= '</head>'."\n";

        echo $html;
    }

    public static function getAssetHTML()
    {
        $assets = array();
        foreach(self::$assets as $type => $priorities) {
            krsort($priorities);
            foreach($priorities as $priority => $fragments) {
                foreach($fragments as $fragment) {
                    $assets[] = $fragment;
                }
            }
        }
        $html = trim(implode("\n\t", $assets));
        return $html;
    }

    public static function widget()
    {
        $args   = func_get_args();
        $module = array_shift($args);
        $method = array_shift($args);
        ob_start();
        global $db;
        global $layout;
        try {
            Core::init($module, $method, null, $db, $layout);
        } catch(Exception $e) {
            return false;
        }
    }
}
