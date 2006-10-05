<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . "MakeTextLocales");

require_once(dirname(__FILE__)."\\Locale\\Maketext.php");

class MyLocale_Maketext extends Locale_Maketext{
  
  	/**
  	 * Overriden. Actually changed class_exists, autoload to false
  	 */
  	function &factory ($baseclass = __CLASS__, $locale = '')
    {    
      	$l10nclassname = "${baseclass}_${locale}";

	  	if (!file_exists(dirname(__FILE__)."\\MakeTextLocales\\".$l10nclassname.".php")) {

		 	$l10nclassname = $baseclass;
		}
		
        $l10nclass = new $l10nclassname;
        return $l10nclass;
    }
} 

?>