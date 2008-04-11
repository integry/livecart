<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.system.*');
ClassLoader::import('application.model.user.User');

/**
 * Test Email class
 *
 * @author Integry Systems
 * @package test.model.system
 */
class TestEmail extends UnitTest
{
	public function __construct()
	{
		parent::__construct('Test email class');
	}

	public function getUsedSchemas()
	{
		return array(
			'User'
		);
	}

	function testSendingAnEmail()
	{
		$email = new Email(self::getApplication());
		$email->setSubject('test');
		$email->setText('some text');
		$email->setFrom('tester@integry.com', 'Unit Test');
		$email->setTo('recipient@test.com', 'Recipient');

		$res = $email->send();

		$this->assertEqual($res, 1);
	}

	function testUser()
	{
		$user = User::getNewInstance('recipient@test.com');
		$user->firstName->set('test');
		$user->lastName->set('recipient');

		Swift_Connection_Fake::resetBuffer();
		$user->save();
		//var_dump(Swift_Connection_Fake::getBuffer());

		$email = new Email(self::getApplication());
		$email->setFrom('tester@integry.com', 'Unit Test');
		$email->setSubject('test');
		$email->setText('some text');
		$email->setUser($user);

		$res = $email->send();

		$this->assertTrue(strpos(Swift_Connection_Fake::getHeaderValue('To'), $user->email->get()) !== false);

		$this->assertEqual($res, 1);
	}
}

?>