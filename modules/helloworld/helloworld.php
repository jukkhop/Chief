<?php
namespace Chief;

class Helloworld extends Controller
{
    public function main()
    {
        Notifications::success('You have succesfully installed Chief!');
        $this->view();
    }
}
