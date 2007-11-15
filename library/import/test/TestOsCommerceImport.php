<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../../test/Initialize.php';

ActiveRecord::setDSN('mysql://root@server/import');

require_once dirname(__FILE__) . '/../driver/OsCommerceImport.php';
require_once dirname(__FILE__) . '/../LiveCartImporter.php';

class TestOsCommerceImport extends UnitTest
{
	private $instance;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->instance = new OsCommerceImport('mysql://root@server/oscommerce');
		$this->importer = new LiveCartImporter($this->instance);
	}
	
	public function getUsedSchemas()
	{
		return array();
	}
	
	public function testLanguageImport()
	{
		$this->assertTrue($this->instance->getNextLanguage() instanceof Language);
	}
}

?>