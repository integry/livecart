<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.staticpage.StaticPage");

/**
 *	Test StaticPage model
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
		$page->setTitle('test title');
		$page->save();
		
		ActiveRecord::clearPool();
		
		$instance = StaticPage::getInstanceById($page->getID());
		$this->assertEqual($instance->getTitle(), $page->getTitle());
		
		$page->delete();
    }    

    function testUpdate()
    {        
		$page = StaticPage::getNewInstance();
		$page->setTitle('test title');
		$page->save();
		
		$page->setTitle('changed');
		$page->save();
		
		ActiveRecord::clearPool();
		
		$instance = StaticPage::getInstanceById($page->getID());
		$this->assertEqual($instance->getTitle(), 'changed');

		// test deleting a page
		$this->assertTrue(file_exists($page->getFileName()));
		$page->delete();
		$this->assertFalse(file_exists($page->getFileName()));
    }    

}

?>