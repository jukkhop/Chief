<?php

# Helper functions
class Common {
	
	# Dumps the given data neatly and exits if second parameter isn't false
	static public function dump($var, $die = true) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';
		if($die) {
			die();
		}
	}
	
	# Converts any decimal string to a float
	static public function float($val) {
		return (float)str_replace(',', '.', $val);
	}
	
	# Generates password hashes
	static public function password($pw) {
		return sha1(PASSWORD_SALT.$pw);
	}
	
	# Naively validates the given email address
	static public function validate_email($email) {
		return preg_match('/.*?@.*?\..{2,}/', $email) > 0 ? true : false;
	}
	
	# Transforms a string into a suitable format to be used in URLs
	static public function slug($title) {
		$slug = trim($title);
		$slug = htmlentities($title, ENT_COMPAT, "UTF-8");
		$slug = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/', '$1',$slug);
		$slug = strtolower(html_entity_decode($slug));
		$slug = preg_replace('/[^a-z0-9 -]/', '', $slug);
		$slug = str_replace(' ', '_', $slug);
		return $slug;
	}
	
	# Transforms the php.ini notation for numbers (like '2M') to an integer
	static public function literal_size_to_numeric($v){ 
		$l = substr($v, -1);
		$ret = substr($v, 0, -1);
		switch(strtoupper($l)){
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
			break;
		}
		return $ret;
	}
	
	# Returns the maximum filesize allowed for uploads by the server configuration, in bytes
	static public function max_upload_filesize() {
		return min(Common::literal_size_to_numeric(ini_get('upload_max_filesize')), Common::literal_size_to_numeric(ini_get('post_max_size')));
	}
	
	# Transforms an integer (bytes) to an human readable filesize
	static public function human_readable_filesize($size) {
		if($size > 1024 * 1024 * 1024) {
			$size = number_format($size / 1024 / 1024 / 1024, 0, ',', ' ').' GiB';
		} elseif($size > 1024 * 1024) {
			$size = $size / 1024 / 1024;
			$dec = $size > 10 ? 0 : 2;
			$size = number_format($size, $dec, ',', ' ').' MiB';
		} elseif($size > 1024) {
			$size = number_format($size / 1024, 2, ',', ' ').' kB';
		} else {
			$size = round($size).' t';
		}
		return $size;		
	}
	
	# Transforms seconds elapsed to more easily readable format (84 => 0:01:14)
	static public function human_readable_time($time, $format = 'H:i:s') {		
		$hours   = floor($time / 3600);
		$minutes = floor(($time - $hours * 3600) / 60);
		$seconds = $time % 60;
		return date($format, mktime($hours, $minutes, $seconds));
	}
	
	# Truncates a given value to desired length, preserving words
	function excerpt($value, $length, $preserve_linebreaks = false) {
		if($preserve_linebreaks) {
			$value = preg_replace('~<\s*br\s*/?>~', "\n", $value);
		}
		$value = strip_tags($value);
		if(mb_strlen($value) > $length) {
			$value = mb_substr($value, 0, $length);
			$value = mb_substr($value, 0, mb_strrpos($value, ' ')).'&hellip;';
		}
		if($preserve_linebreaks) {
			$value = nl2br($value);
		}
		return $value;
	}
	
	# Extends the first object's values with second object
	static function extend($defaults, $options) {
		foreach($defaults as $key => &$value) {
			if(isset($options[$key])) {
				$value = $options[$key];
			}
		} unset($value);
		foreach($options as $key => $value) {
			if(!isset($defaults[$key])) {
				$defaults[$key] = $value;
			}
		}
		return $defaults;
	}
}
