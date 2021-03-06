<?php
namespace Chief;

class Controller extends Core
{
    public $db;
    public $layout;
    
    function __construct($db, $layout)
    {        
        parent::__construct($db);
        
        $this->db       = $db;
        $this->layout   = $layout;
        
		if(AJAX_CALL) {
			$this->layout->disable();
        }
    }
    
    function view($data = null, $method = null)
    {
        if(is_array($data)) {
            extract($data);
        }
        
        # Get any output that may have been slipped through
        $buffer = ob_get_clean();
        
        # If header file is given, include it
        if(($header = $this->layout->getHeader()) !== false) {
            include($header);
        }
        
        # Dump the buffer contents
        if(!empty($buffer)) {
            printf('<div id="buffer">%s</div>', $buffer);
        }
        
        if(file_exists($method)) {
            # If $method is an actual path, use it
            $path = $method;
        } else {
            # If method isn't given, load a view that has the same name as the method
            $method = is_null($method) ? $this->getMethod() : $method;
            $path   = strtolower(sprintf('modules/%s/view/%s.php', $this->getModule(), $method));
        }
        
        if(file_exists($path)) {
            include($path);
        } else {
            ob_end_clean();
            throw new Exception("View $path not found.");
        }
        
        # If footer file is given, include it
        if(($footer = $this->layout->getFooter()) !== false) {
            include($footer);
        }
    }
    
    # Tool for doing redirects. Empty $location redirects to the previous page.
    function redirect($location = null)
    {
        if(!AJAX_CALL) {
            if(is_null($location)) {
                $location = $_SESSION['REDIRECT_URL'];
            } else {
                $location = $location[0] !== '/' && substr($location, 0, 4) != 'http' ? BASE_DIR.$location : $location;
            }
            header('Location: '.$location);
        }
        die();
    }
}
