<?php
/**
 * SimpleEmailMessage used to package data that could be populated into Swift_Message instance
 *
 * @package application.model.email
 * @author Shumoapp <http://shumoapp.com>
 */
class SimpleEmailMessage
{
	private $headers = array();

	public function addTo($email, $name)
	{
		$this->headers['addTo'][] = array($email, $name);
	}

	public function addCc($email, $name)
	{
		$this->headers['addCc'][] = array($email, $name);
	}

	public function addBcc($email, $name)
	{
		$this->headers['addBcc'][] = array($email, $name);
	}

	public function setReplyTo($email, $name)
	{
		$this->headers['setReplyTo'][] = array($email, $name);
	}

	public function setFrom($array)
	{
		$this->headers['setFrom'] = $array;
	}

	/**
	 * Get a Swift_Message object, and populate it with the values in this class.
	 *
	 * @param Swift_Message $message
	 */
	public function populateMessage(Swift_Message &$message)
	{
		foreach($this->headers as $function=>$parameters)
		{
			if($function=='setFrom')
			{
				$message->setFrom($parameters);
			}
			else
			{
				foreach($parameters as $email)
				{
					$message->$function($email[0], $email[1]);
				}
			}
		}
	}
}