<?php

/**
 * @package application.model.session
 * @author Integry Systems
 */
abstract class SessionHandler
{
	protected $forceUpdate;
	protected $userID;
	protected $cacheUpdated;
	protected $lastUpdated;

	public abstract function open();
	public abstract function close();
	public abstract function read($id);
	public abstract function write($id, $data);
	public abstract function destroy($id);
	public abstract function gc($max);

	public function setHandlerInstance()
	{
		//return true;
		session_set_save_handler(array($this, 'open'),
								 array($this, 'close'),
								 array($this, 'read'),
								 array($this, 'write'),
								 array($this, 'destroy'),
								 array($this, 'gc'));
	}

	public function setUser(User $user)
	{
		$id = $user->getID();

		if ($id != $this->userID)
		{
			$this->userID = $id;
			$this->forceUpdate = true;
		}
	}

	public function updateCacheTimestamp()
	{
		$this->cacheUpdated = time();
		$this->forceUpdate = true;
	}
}

?>