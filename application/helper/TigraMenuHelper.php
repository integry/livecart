<?php

/**
 * Class for creating js array of Tigra menu (@see ). Tigra menu .js files lie in public/js catalog. Stylesheet of tigra menu- public/stylesheet/style.css file
 */
class TigraMenuHelper {
  
	/**
	 * Creates js array.
	 * @param array $structure Menu structure (@see MenuLoader::getCurrentStructure())
	 * @return string
	 */
	public static function formatJsMenuArray(&$structure) {
		
		$javascript = "<script language=\"JavaScript\">\n";
	  	$javascript .= "var MENU_ITEMS = [";		
		TigraMenuHelper::formatJsMenuPart(&$structure, &$javascript);		
		$javascript .= "];\n";		
		$javascript .= "new menu (MENU_ITEMS, MENU_POS);\n";
		$javascript .= "</script>\n";
		
		return $javascript;
	}
	
	/**	 
	 */	
	private static function formatJsMenuPart(&$structure, &$javascript) {
	  
		//$locale = Locale::getInstance();
	  
	  	foreach ($structure['items'] as $item) {
			
			if (empty($item['controller']) || empty($item['action'])) {
			  
			  	$url = "null";
			} else {
			  	
			  	$url = "'".Router::getInstance()->createUrl(array('controller' => $item['controller'], 'action' => $item['action']))."'";
			}
			
			$locale = Locale::getCurrentLocale();		
		  	$javascript .= "['".$locale->translator()->translate($item['title'])."', ".$url.", null,";
		  	//$javascript .= "['".$item['title']."', ".$url.", null,";
			 
			if (!empty($item['items']) && count($item['items']) > 0) {
			  			  	
			  	TigraMenuHelper::formatJsMenuPart(&$item, &$javascript);
			}
			 
			$javascript .= "],";
		}			
	}
}

?>