<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.staticpage.StaticPage");

/**
 * Test StaticPage model
 * @author Integry Systems
 * @package test.model.staticpage
 */
class TestStaticPage extends UnitTest
{
	public function getUsedSchemas()
	{
		return array(
			'StaticPage'
		);
	}

	function testCreate()
	{
		$page = StaticPage::getNewInstance();
		$page->setValueByLang('title', 'en', 'test title');
		$page->save();

		ActiveRecord::clearPool();

		$instance = StaticPage::getInstanceById($page->getID());
		$this->assertEqual($page->getValueByLang('title', 'en'), 'test title');

		$page->delete();
	}

	function testUpdate()
	{
		$page = StaticPage::getNewInstance();
		$page->setValueByLang('title', 'en', 'test title');
		$page->save();

		$page->setValueByLang('title', 'en', 'changed');
		$page->save();

		ActiveRecord::clearPool();

		$instance = StaticPage::getInstanceById($page->getID());
		$this->assertEqual($page->getValueByLang('title', 'en'), 'changed');

		// test deleting a page
		$this->assertTrue(file_exists($page->getFileName()));
		$page->delete();
		$this->assertFalse(file_exists($page->getFileName()));
	}

}

?>