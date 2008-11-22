<?php

ClassLoader::import('library.swiftmailer.Swift', true);

/**
 *  Simulates email transport connection
 *
 *  Should always be used for unit testing to avoid any real messages to be sent
 *
 * @author Integry Systems
 * @package test.mock
 */
class Swift_Connection_Fake extends Swift_ConnectionBase
{
	private $response = 250;

	private static $buffer = array();

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

	public static function getHeaderValue($headerEntry)
	{
		reset(self::$buffer);
		while ($value = next(self::$buffer))
		{
			if ('DATA' == $value)
			{
				$header = next(self::$buffer);
				reset(self::$buffer);
				break;
			}
		}

		if (!isset($header))
		{
			return false;
		}

		$header = str_replace("\r", '', $header);

		$headers = preg_match_all('/([A-Za-z0-9\-]+)\: (.*)/', $header, $matches);
		$headers = array_combine($matches[1], $matches[2]);

		if (isset($headers[$headerEntry]))
		{
			return $headers[$headerEntry];
		}
	}
}

?>