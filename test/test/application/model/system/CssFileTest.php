<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.system.CssFile');

/**
 *
 * @author Integry Systems
 * @package test.model.system
 */
class CssFileTest extends UnitTest
{
	public function getUsedSchemas()
	{
		return array(

		);
	}

	function testPatching()
	{
		$file = 'stylesheet/backend/stat.css';
		$path = ClassLoader::getRealPath('public.') . $file;
		$css = new CssFile($file);
		$css->clearPatchRules();

		$css->deleteSelector('#stat table');
		$css->save();
		$this->assertTrue(strpos(file_get_contents($css->getPatchedFilePath()), '#stat table') == 0);

		$css->deleteProperty('#stat .label', 'font-weight');
		$css->save();
		$this->assertTrue(strpos(file_get_contents($css->getPatchedFilePath()), 'font-weight') == 0);

		$css->clearPatchRules();
	}

	function testEmptySelectors()
	{
		$src = '.test { font-weight: bold; }' . "\n" . '.empty { }' . "\n" . '.whatever { color: red }';

		$css = new CssFile('somefile.css');
		$css->setSource($src);

		$this->assertTrue(strpos($css->getSource(), '.empty') == 0);
		$this->assertTrue(strpos($css->getSource(), '.whatever') > 0);
	}
}

?>