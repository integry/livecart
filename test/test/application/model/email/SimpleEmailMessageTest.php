<?php
require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.email.SimpleEmailMessage');
ClassLoader::ignoreMissingClasses(true);
ClassLoader::import('library.swiftmailer.lib.swift_required', true);

/**
 *
 * @package test.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
class SimpleEmailMessageTest extends LiveCartTest
{
	/**
	 * @var SimpleEmailMessage
	 */
	private $simpleMessage;
	/**
	 * @var Swift_Message
	 */
	private $message;

	/**
	 * Test storing email parameters in SimpleEmailMessage, and transferring them to Swift_Message
	 */
	public function testStoreAndRestore()
	{
		$email = 'test@yahoo.com';
		$name = 'John Doe';

		$this->simpleMessage = new SimpleEmailMessage();
		$this->simpleMessage->addTo($email, $name);
		$this->simpleMessage->addCc($email, $name);
		$this->simpleMessage->addBcc($email, $name);
		$this->simpleMessage->setReplyTo($email, $name);
		$this->simpleMessage->setFrom(array($email => $name));

		$this->message = Swift_Message::newInstance();
		$this->simpleMessage->populateMessage($this->message);

		$this->assertEqual($this->message->getTo(), array($email => $name));
		$this->assertEqual($this->message->getCc(), array($email => $name));
		$this->assertEqual($this->message->getBcc(), array($email => $name));
		$this->assertEqual($this->message->getReplyTo(), array($email => $name));
		$this->assertEqual($this->message->getFrom(), array($email => $name));
	}

} 