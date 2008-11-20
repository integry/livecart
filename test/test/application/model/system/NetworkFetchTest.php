<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.system.*');

/**
 * Test Email class
 *
 * @author Integry Systems
 * @package test.model.system
 */
class NetworkFetchTest extends UnitTest
{
	public function __construct()
	{
		parent::__construct('Test network resource downloader class');
	}

	public function getUsedSchemas()
	{
		return array(
		);
	}

	function testInvalidHostFetch()
	{
		$fetch = new NetworkFetch('http://sdfskjfhsdkjhdfmsdfhjksdhjsdhjgksdjkfsdf.zz/test.jpg');
		$this->assertFalse($fetch->fetch());
	}

	function testInvalidSchemaFetch()
	{
		$fetch = new NetworkFetch('XXX://sdfskjfhsdkjhdfmsdfhjksdhjsdhjgksdjkfsdf.zz/test.jpg');
		$this->assertFalse($fetch->fetch());
	}

	function xtest404Fetch()
	{
		$fetch = new NetworkFetch('http://example.com/test_404_does_not_e-x-i-s-t.jpg');
		$this->assertFalse($fetch->fetch());
	}

	function testFetch()
	{
		$fetch = new NetworkFetch('http://example.com/');
		$this->assertTrue($fetch->fetch());

		$tmpFile = $fetch->getTmpFile();
		$this->assertTrue(file_exists($tmpFile));

		unset($fetch);
		$this->assertFalse(file_exists($tmpFile));
	}


}

?>