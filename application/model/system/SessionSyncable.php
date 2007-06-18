<?php

/**
 * A common interface for objects that can be stored to session
 *
 * @author Integry Systems <http://integry.com>  
 * @package application.model.system
 */
interface SessionSyncable
{
    /**
     *  Determine if the object is stored in session  
     *
     *  @return bool
     */
    public function isSyncedToSession();
    
    /**
     *  Store object to session
     *
     *  @return bool
     */
    public function syncToSession();
    
    /**
     *  Reload object and synced related objects from database
     *
     *  @return bool
     */
    public function refresh();
}

?>