<?php
ClassLoader::import('library.locale.Locale');

class TestLocale extends UnitTest 
{
  	/*
    function testCreate()
  	{
		// attempt to create non-existing locale
		$locale = Locale::getInstance('enz');
		$this->assertFalse($locale);	    
		
		// create existing locale
		$locale = Locale::getInstance('en');
		$this->assertTrue($locale instanceof Locale);	    

		// set as current locale
		Locale::setCurrentLocale('en');
		$current = Locale::getCurrentLocale();
		$this->assertIdentical($current, $locale);
	}
*/
}
?>