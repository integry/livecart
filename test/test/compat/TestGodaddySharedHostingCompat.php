<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../Initialize.php';

ClassLoader::import("test.mock.InstallCompat");
ClassLoader::import("installdata.compat.GodaddySharedHostingCompat");

/**
 *
 * @package test.model.category
 * @author Integry Systems
 */
class TestGodaddySharedHostingCompat extends UnitTest
{
	public function __construct()
	{
		parent::__construct('Godaddy Shared Hosting Compatibility');
	}

	public function getUsedSchemas()
	{
		return array(
		);
	}

	public function testDetect()
	{
		$compat = new GodaddySharedHostingCompat(ActiveRecordModel::getApplication());

		$compat->setConfig('System', 'Linux p3slh173.shr.phx3.secureserver.net 2.4.21-53.ELsmp #1 SMP Wed Nov 14 03:54:12 EST 2007 i686');
		$this->assertTrue($compat->isApplicable());

		$compat->setConfig('System', 'Linux p3slh173.decicated.phx3.secureserver.net 2.4.21-53.ELsmp #1 SMP Wed Nov 14 03:54:12 EST 2007 i686');
		$this->assertFalse($compat->isApplicable());

		$compat->setConfig('System', 'Linux localhost 2.4.21-53.ELsmp #1 SMP Wed Nov 14 03:54:12 EST 2007 i686');
		$this->assertFalse($compat->isApplicable());
	}

	public function testApply()
	{
		$livecart = ActiveRecordModel::getApplication();
		$compat = new GodaddySharedHostingCompat($livecart);
		$compat->apply();

		$this->assertEqual($livecart->getConfig()->get('PROXY_HOST'), 'proxy.shr.secureserver.net');
		$this->assertEqual($livecart->getConfig()->get('PROXY_PORT'), 3128);
	}
}

?>