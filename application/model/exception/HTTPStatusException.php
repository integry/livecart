<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Exception that indicates an attempt to execute a restricted controller/action
 *
 * @package application.model
 * @author Saulius Rupainis <saulius@integry.net>
 */
abstract class HTTPStatusException extends ApplicationException
{
    /**
     * @var BaseController
     */
	private $controller = null;
	private $statusCode = 200;

	public function __construct(Controller $controller, $statusCode, $message = false)
	{
		$this->controller = $controller;
		$this->statusCode = $statusCode;
		
		if(!$message)
		{
			$action = $this->getController()->getRequest()->getControllerName() . '/' . $this->getController()->getRequest()->getActionName();
			$code = $this->getStatusCode() . ' (' . self::getCodeMeaning($this->getStatusCode()) . ')';
			$message = "Error accessing $action.\n $code";
		}
		
		parent::__construct($message);
	}

	public function getStatusCode()
	{
	    return $this->statusCode;
	}
	
	/**
	 * @return Controller
	 */
	public function getController()
	{
	    return $this->controller;
	}
	
	public static function getCodeMeaning($code)
	{
	    $meanings = array (
		    100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing (WebDAV)',
			200 => 'OK. Standard response for HTTP successful requests.',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information (since HTTP/1.1)',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status (WebDAV)',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other (since HTTP/1.1)',
			304 => 'Not Modified',
			305 => 'Use Proxy (since HTTP/1.1)',
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect (since HTTP/1.1)',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity (WebDAV) (RFC 2518)',
			423 => 'Locked (WebDAV)(RFC 2518)',
			424 => 'Failed Dependency (WebDAV) (RFC 2518)',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required (RFC 2817)',
			449 => 'Retry With',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates (RFC 2295)',
			507 => 'Insufficient Storage (WebDAV)',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended (RFC 2774)',
	    );
	    
	    return isset($meanings[$code]) ? $meanings[$code] : 'Unknown code';
	}
}

?>