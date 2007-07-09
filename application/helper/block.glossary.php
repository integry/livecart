<?php

/**
 * Smarty block plugin, for generating help reference sections
 *
 * @param array $params
 * @param Smarty $smarty
 * @param $repeat
 *
 * <code>
 *  {glossary}SEO{/glossary}
 * </code>
 *
 * @return string HTML code
 * @package application.helper
 */
function smarty_block_glossary($params, $content, LiveCartSmarty $smarty, &$repeat) 
{		
    if (!$repeat) 
	{		
		$glossary = parse_ini_file(ClassLoader::getRealPath('application.view.help.en') . '/glossary.ini');
		$glossary = array_change_key_case($glossary, CASE_LOWER);

		$term = strtolower(strip_tags($content));
		
		if (isset($glossary[$term]))
		{
            return '<a href="" onclick="return false;" class="acronym">'.$content . '<div class="acronymFull">'.$glossary[$term].'</div></a>'; 
        }
        else
        {
            return $content;
        }
	}	
}

?>