<?php
namespace Chief;

class Layout
{	
	protected static $header = false;
	protected static $footer = false;
	protected static $enabled = true;
	protected static $isWidget = false;

	protected static $layoutPath = 'layout/';
	protected static $assets;
	protected static $title;
	protected static $keywords;
	protected static $favicon;
	protected static $meta;
	protected static $webfonts;

	public function __construct()
	{
		self::$assets = array(
			'css' => array(),
			'js' => array()
		);
		self::$meta = array();
		self::$header = self::$layoutPath.'header.php';
		self::$footer = self::$layoutPath.'footer.php';
	}
	
	public static function setLayoutPath($path) {
		self::$layoutPath = trim($path, '/').'/';
		self::$header = self::$layoutPath.'header.php';
		self::$footer = self::$layoutPath.'footer.php';
	}

	public static function setHeader($header)
	{
		if(file_exists($header)) {
			self::$header = $header;
		} else {
			throw new Exception('Layout header file '.$header.' not found.');
		}
	}

	public static function setFooter($footer)
	{
		if(file_exists($footer)) {
			self::$footer = $footer;
		} else {
			throw new Exception('Layout footer file '.$footer.' not found.');
		}
	}

	public static function getHeader()
	{
		return self::$enabled && !self::$isWidget ? self::$header : false;
	}

	public static function getFooter()
	{
		return self::$enabled && !self::$isWidget ? self::$footer : false;
	}

	public static function enable()
	{
		self::$enabled = true;
	}

	public static function disable()
	{
		self::$enabled  = false;
	}

	public static function isEnabled()
	{
		return self::$enabled;
	}
	
	public static function isWidget() {
		return !!self::$isWidget;
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
	
	public static function webfont($font, $weights = null) {
		self::$webfonts[] = [
			'font' => $font,
			'weights' => $weights
		];
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
				$css['href'] = BASE_DIR.self::$layoutPath.'css/'.$css['href'];
			}
			$attrs = array();
			foreach($css as $key => $value) {
				$attrs[] = $key.'="'.$value.'"';
			}
			self::$assets['css'][$priority][] = sprintf('<link %s>', implode(' ', $attrs));
		} else if(is_string($priority) && (int)$priority !== $priority) {
			foreach(func_get_args() as $path) {
				self::css($path);
			}
		} else {
			if(ltrim($path, '/\\') === $path && strtolower(substr($path, 0, 4)) !== 'http') {
				$path = BASE_DIR.self::$layoutPath.'css/'.$path;
			}

			if(!isset(self::$assets['css'][$priority])) {
				self::$assets['css'][$priority] = array();
			}
			self::$assets['css'][$priority][] = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $path);
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
				$path = BASE_DIR.self::$layoutPath.'js/'.$path;
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
		$html .= '	<meta charset="UTF-8">'."\n";
		if(!empty(self::$keywords)) {
			$html .= '	<meta name="keywords" content="'.(is_array(self::$keywords) ? implode(',', self::$keywords) : self::$keywords).'">'."\n";
		}
		if(!empty(self::$meta)) {
			foreach(self::$meta as $m) {
				$html .= '	<meta '.$m['keyname'].'="'.$m['key'].'" content="'.$m['value'].'">'."\n";
			}
		}
		if(!empty(self::$favicon)) {
			$html .= '	<link rel="shortcut icon" href="'.self::$favicon.'">'."\n";
		}
		$html .= '	<title>'.$title.'</title>'."\n";
		
		if(!empty(self::$webfonts)) {
			$fonts = array();
			foreach(self::$webfonts as $webfont) {
				$weights = null;
				if(!empty($webfont['weights'])) {
					$weights = is_array($webfont['weights']) ? implode(',', $webfont['weights']) : $webfont['weights'];
				}
				$fonts[] = urlencode($webfont['font']).(empty($weights) ? null : ':'.$weights);
			}
			$html .= '	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.implode('|', $fonts).'">'."\n";
		}
		
		$html .= '	'.self::getAssetHTML()."\n";
		$html .= '	<script type="text/javascript">var BASE_DIR = \''.BASE_DIR.'\';</script>'."\n";
		$html .= '</head>'."\n";

		echo $html;
	}

	public static function getAssetHTML()
	{
		$assets = array();
		foreach(self::$assets as $type => $priorities) {
			ksort($priorities);
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
			self::$isWidget = true;
			Core::init($module, $method, $args, $db, $layout);
			self::$isWidget = false;
		} catch(Exception $e) {
			return false;
		}
	}
	
	public static function asset() {
		foreach(func_get_args() as $asset) {
			if(file_exists('assets/'.$asset.'/loader.php')) {
				require_once('assets/'.$asset.'/loader.php');
			}
		}
	}
}
