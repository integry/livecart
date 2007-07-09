<?php

class LiveCartSmarty extends Smarty
{
    private $application;
    
    public function __construct(LiveCart $application)
    {
        $this->application = $application;
    }
    
    public function getApplication()
    {
        return $this->application;
    }
}

?>