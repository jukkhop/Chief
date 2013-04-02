<?php
namespace Chief;

class Core
{    
    protected $db;
    protected $module;
    protected $method;
    
    public static function init($module, $method, $arguments, $db, $layout)
    {
        $fully_qualified_name = 'Chief\\'.ucwords($module);
        $controller = new $fully_qualified_name($db, $layout);
        $controller->setModule($module);
        $controller->setMethod($method);
        $function = new \ReflectionMethod($fully_qualified_name, $method);
        $function->invokeArgs($controller, empty($arguments) ? [] : $arguments);
    }
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    function model($model)
    {
        return Model::initialize($model, $this->db, $this->getModule());
    }
    
    function plugin($plugin)
    {
        return Plugin::initialize($plugin, $this->db);
    }
    
    function setModule($module)
    {
        $this->module = $module;
    }
    
    function setMethod($method, $foo = false)
    {
        $this->method = $method;
    }
    
    function getModule()
    {
        return $this->module;
    }
    
    function getMethod()
    {
        return $this->method;
    }
}
