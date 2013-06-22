<?php
namespace Chief;

class Pages extends Controller {
    
    public function __call($name, $arguments) {
    	$name = trim($name, '.');
        if(file_exists('modules/pages/view/'.$name.'.php')) {
            $this->view(null, $name);
        } else {
        	throw new Exception("Page pages/".$name." does not exist.");
        }
    }
}
