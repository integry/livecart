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
		$css->deleteProperty('#stat .label', 'font-weight');
		$css->save();

		$this->assertTrue(strpos(file_get_contents($css->getPatchFile()), '#stat table') == 0);
	}
}

?>