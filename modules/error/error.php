<?php
namespace Chief;

class Error extends Controller {
	
    public function main($error = null)
    {
        $data['error'] = $error;
        $this->view($data);        
    }
}
