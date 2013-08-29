<?php

/**
 * Cache control directives
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application/helper/smarty
 * @author Integry Systems
 */
function smarty_function_cache($params, Smarty_Internal_Template $smarty)
{
	$renderer = $smarty->getApplication()->getRenderer();
	//$renderer->getCacheInstance()->setCacheVar($params['var'], $params['value']);
	
	/*if (!empty($params['final']))
	{
		if ($renderer->getCacheInstance()->isCached())
		{
			return $renderer->getCacheInstance()->getData();
		}
	}
	*/
}

?>
