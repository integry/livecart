<?php

ClassLoader::import("application.controller.BaseController");
ClassLoader::import("library.json.json");

/**
 * Generic backend controller for administrative tools (actions, modules etc.)
 *
 * @package application.backend.controller.abstract
 */
abstract class BackendController extends BaseController
{
    /**
     *  Create a common language file for top navigation menu
     */
    public function rebuildMenuLangFile()
    {
		$locales = array(Locale::getInstance('en'), Store::getInstance()->getLocaleInstance());
		
		foreach ($locales as $locale)
		{
            $baseDir = $locale->translationManager()->getDefinitionFileDir();
            $files = $locale->translationManager()->getDefinitionFiles($baseDir . '/backend/menu');

            $menuTranslations = array();
    		foreach ($files as $file)
    		{
				$file = realpath($file);
                $relPath = substr($file, strlen($baseDir) + 1);
                $locale->translationManager()->updateCacheFile($locale->getLocaleCode() . '/' . $relPath);
                $defs = $locale->translationManager()->getCacheDefs($relPath, true);
				$menuTranslations = array_merge($menuTranslations, $defs);
    		}
    
    		$locale->translationManager()->saveCacheData($locale->getLocaleCode() . '/menu/menu', $menuTranslations); 
        }
    }
}

?>