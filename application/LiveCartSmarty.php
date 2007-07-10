<?php

ClassLoader::import('library.smarty.libs.Smarty');

class LiveCartSmarty extends Smarty
{
    private $application;
    
    public function __construct(LiveCart $application)
    {
        $this->application = $application;
        $this->register_modifier('config', array($this, 'config'));
    }
    
    public function getApplication()
    {
        return $this->application;
    }
    
    /**
     *  Retrieve software configuration values from Smarty templates
     *
     *  <code>
     *      {'STORE_NAME'|config}
     *  </code>
     */    
    public function config($key)
    {
        return $this->getApplication()->getConfig()->get($key);
    }
}

?>