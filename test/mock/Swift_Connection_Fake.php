<?php

ClassLoader::import('library.swiftmailer.lib.classes.Swift.Transport', true);

/**
 *  Simulates email transport connection
 *
 *  Should always be used for unit testing to avoid any real messages to be sent
 *
 * @author Integry Systems
 * @package test.mock
 */
class Swift_Connection_Fake extends Swift_Transport_MailTransport
{
	private $response = 250;

	private static $buffer = array();

	/** Addtional parameters to pass to mail() */
	private $_extraParams = '-f%s';

	/** The event dispatcher from the plugin API */
	private $_eventDispatcher;

	/** An invoker that calls the mail() function */
	private $_invoker;

	/**
	* Create a new MailTransport, optionally specifying $extraParams.
	* @param string $extraParams
	*/
	public function __construct($extraParams = '-f%s')
	{
		call_user_func_array(
		  array($this, 'Swift_Transport_MailTransport::__construct'),
		  Swift_DependencyContainer::getInstance()
			->createDependenciesFor('transport.mail')
		  );

		$this->setExtraParams($extraParams);
	}

	public static function newInstance($extraParams = '-f%s')
	{
		return new self($extraParams);
	}

	public function start()
	{
		self::resetBuffer();
		return 250;
	}

	public function read()
	{
		return $this->response;
	}

	public function write($command, $end="\r\n")
	{
		self::$buffer[] = $command;

		$cmd = explode(' ', $command, 2);
		$cmd = array_shift($cmd);

		if ('DATA' == $cmd)
		{
			$this->response = 354;
		}
		else
		{
			$this->response = 250;
		}

//		echo 'WRITE - ' . $command . '<br>';
	}

	public function stop()
	{
		return 250;
	}

	public function isAlive()
	{
		return true;
	}

	public static function resetBuffer()
	{
		self::$buffer = array();
	}

	public static function getBuffer()
	{
		return self::$buffer;
	}
}

?>