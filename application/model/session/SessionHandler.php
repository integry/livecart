<?php

/**
 * @package application.model.session
 * @author Integry Systems
 */
abstract class SessionHandler
{
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
}

?>