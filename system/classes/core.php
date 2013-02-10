<?php

class Core {
	
	protected $db;
	protected $module;
	protected $method;
	
	public static function init($module, $method, $arguments, $db, $layout) {
		$controller = new $module($db, $layout);
		$controller->set_module($module);
		$controller->set_method($method);
		$function = new ReflectionMethod($module, $method);
		$function->invokeArgs($controller, $arguments);
	}
	
	public function __construct($db) {
		$this->db = $db;
	}
	
	function model($model) {
		return Model::initialize($model, $this->db, $this->get_module());
	}
	
	function plugin($plugin) {
		return Plugin::initialize($plugin, $this->db);
	}
	
	function set_module($module) {
		$this->module = $module;
	}
	
	function set_method($method, $foo = false) {
		$this->method = $method;
	}
	
	function get_module() {
		return $this->module;
	}
	
	function get_method() {
		return $this->method;
	}
}
