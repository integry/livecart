<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.system.Language');

/**
 * Language model test
 *
 * @author Integry Systems
 * @package test.model.locale 
 */
class TestLanguage extends UnitTest
{	  
    public function __construct()
    {
        parent::__construct('test languages');
    }
    
    public function getUsedSchemas()
    {
        return array(
            'Language',
            'InterfaceTranslation'
        );
    }
    
	/*
    function testLanguagesExist() 
	{  
		$languages = Language::getLanguages();  
		$this->assertTrue(count($languages) > 0);
	}
	
	function testDefaultLanguageExists() 
	{  
		$def = Language::getDefaultLanguage();  
		$this->assertTrue($def instanceof Language);
	}
		
	function testSwitchDefaultLanguages()
	{
		// get current default language
		$def = Language::getDefaultLanguage();  

		// get active languages
		$languages = Language::getLanguages(1);  
		foreach ($languages as $nonDef) 
		{
		  	if (!$nonDef->isDefault())
		  	{
			    break;			    
			}
		}
		
		// switch default languages
		$def->setAsDefault(0);
		$def->save();
		
		$nonDef->setAsDefault();
		$nonDef->save();
		
		$newDef = Language::getDefaultLanguage();  
		$this->assertTrue($newDef->getID() == $nonDef->getID());
		
		// switch back using static functions
		Language::setDefault($def->getID());
		$newDef = Language::getDefaultLanguage();  
		$this->assertTrue($newDef->getID() == $def->getID());				
	}
	
	function testActivatingAndInactivating()
	{
		// inactivate all languages
		$languages = Language::getLanguages();  
		foreach ($languages as $lang) 
		{
		  	$lang->setAsEnabled(false);
		  	$lang->save();
		}
			  
		// get inactive languages
		$inactive = Language::getLanguages(2);  
		$this->assertEqual($inactive->getTotalRecordCount(), $languages->getTotalRecordCount());

		// get active languages - should be none
		$active = Language::getLanguages(1); 		 
		$this->assertEqual($active->getTotalRecordCount(), 0);
		
		// activate all languages using static function
		foreach ($languages as $lang) 
		{
		  	Language::setEnabled($lang->getID(), true);
		}

		// get active languages
		$active = Language::getLanguages(1);  
		$this->assertEqual($active->getTotalRecordCount(), $languages->getTotalRecordCount());
	}

	function testAddNewLanguage()
	{		
	  	Language::add('it');

	  	// try to read back the new object
		try
	  	{
			$it = Language::getInstanceById('it');
		}
		catch (Exception $exc)
		{
		  	$it = false;
		}
		$this->assertTrue($it instanceof Language);

	  	// try to read interface translation data object
		try
	  	{
		}
		catch (Exception $exc)
		{
		  	$interface = false;
		}
		$this->assertTrue($interface instanceof InterfaceTranslation);
		
		// try to read non-existing object
		try
	  	{
			$it = Language::getInstanceById('zzz');
		}
		catch (Exception $exc)
		{
		  	$it = false;
		}
		$this->assertFalse($it instanceof Language);
	}
	*/
}

?>