<?php

ClassLoader::import('library.smarty.libs.Smarty');

/**
 *  Extends Smarty with LiveCart-specific logic
 *
 *  @package application
 *  @author Integry Systems
 */
class LiveCartSmarty extends Smarty
{
    private $application;
    
    public function __construct(LiveCart $application)
    {
        $this->application = $application;
        $this->register_modifier('config', array($this, 'config'));
    }
    
    /**
     * Get livecart application instance
     *
     * @return LiveCart
     */
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
        return self::getApplication()->getConfig()->get($key);
    }
}

?>