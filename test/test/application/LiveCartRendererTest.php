<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/Initialize.php';

ClassLoader::import('application.LiveCartRenderer');

/**
 *
 * @package test.application
 * @author Integry Systems
 */
class LiveCartRendererTest extends UnitTest
{
	/**
	 * @var LiveCartRenderer
	 */
	private $renderer;

	/**
	 * @var Controller
	 */
	private $controller;

	public function setUp()
	{
		parent::setUp();

		$this->renderer = new LiveCartRenderer(self::getApplication());
		$this->controller = new IndexController(self::getApplication());
	}

	public function testCatchAllAndAppendToBlock()
	{
		$this->setConfig
		(
'[CATEGORY]
* = menu.tpl'
		);

		$config = $this->renderer->getBlockConfiguration('CATEGORY');
		$this->assertEquals(count($config), 1);
		$this->assertTrue($this->renderer->isBlock('CATEGORY'));
		$this->assertEquals($config[0]['action']['command'], 'append');
	}

	public function testRemoveBlock()
	{
		$this->controller->addBlock('CATEGORY', 'getGeneric');

		$this->setConfig
		(
'[CATEGORY]
* = remove'
		);

		$config = $this->renderer->getBlockConfiguration('CATEGORY');
		$this->assertEquals($config[0]['action']['command'], 'remove');
	}

	private function setConfig($iniString)
	{
		$file = ClassLoader::getRealPath('cache.block') . '.ini';
		file_put_contents($file, $iniString);
		$this->renderer->getBlockConfiguration(null, $file);
	}
}

?>