<?php

class Plugin extends Core {
	
	protected $db;
	
	static public function initialize($plugin, $db) {
		
		$path = strtolower(sprintf('plugins/%s/%s.php', $plugin, $plugin));
		
		if(file_exists($path)) {
			require_once($path);
			$plugin = ucfirst(strtolower($plugin));			
			return new $plugin($db);
		} else {
			throw new Exception('Plugin '.$path.' not found.');
		}
	}
}
