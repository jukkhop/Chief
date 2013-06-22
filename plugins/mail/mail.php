<?php
namespace Chief;

class Mail extends Plugin {
    
    private $to;
    private $subject;
    private $body;
    private $headers = [];
    
    public function to($to, $name = null) {
        if(!empty($name)) {
            $this->headers[] = 'To: '.$name.' <'.$to.'>';
        }
        $this->to = $to;
        return $this;
    }
    
    public function from($from, $name = null) {
        if(!empty($name)) {
            $from = $name.' <'.$from.'>';
        }
        $this->headers[] = 'From: '.$from;
        return $this;
    }
    
    public function subject($subject) {
        $this->subject = $subject;
        return $this;
    }
    
    public function body($body) {
        $this->body = $body;
        return $this;
    }
    
    public function send() {
        return mail($this->to, $this->subject, $this->body, implode("\r\n", $this->headers));
    }
}
