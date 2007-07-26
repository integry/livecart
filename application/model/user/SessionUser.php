<?php

ClassLoader::import('application.model.user.User');

class SessionUser
{
	/**
	 * Get current user (from session)

	 * @return User
	 */
    public static function getUser()
    {
        $session = new Session();
        
		$id = $session->get('User');
    
        if (!$id)
        {
            return self::getAnonymousUser();
        }
        else
        {
			try
			{
                return User::getInstanceById($id);                
            }
            catch (ARNotFoundException $e)
            {
                $session->unsetValue('User');
                return User::getAnonymousUser();
            }
		}
    }
    
	public static function setUser(User $user)
	{
		$session = new Session();
		$session->set('User', $user->getID());
	}

	public static function destroy()
	{
		$session = new Session();
		$session->unsetValue('User');
		$session->unsetValue('CustomerOrder');		
	}

	/**
	 * Get anonymous user

	 * @return User
	 */
    private function getAnonymousUser()
    {
        $instance = ActiveRecordModel::getNewInstance('User'); 
		$instance->setID(User::ANONYMOUS_USER_ID);   

        return $instance;
    }    
}

?>