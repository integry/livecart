<?php
/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application/helper/smarty
 * @author Integry Systems
 */
function smarty_function_img($params, Smarty_Internal_Template $smarty)
{
	if(isset($params['src']) && (substr($params['src'], 0, 6) == 'image/' || substr($params['src'], 0, 7) == 'upload/'))
	{
		if(is_file($params['src']))
		{
			$imageTimestamp = @filemtime(ClassLoader::getRealPath('public/') . str_replace('/',DIRECTORY_SEPARATOR, $params['src']));
			$params['src'] = $smarty->getApplication()->getPublicUrl($params['src']);
			$params['src'] .= '?' . $imageTimestamp;
		}
	}

	$content = "<img ";
	foreach($params as $name => $value)
	{
		$content .= $name . '="' . $value . '" ';
	}
	$content .= "/>";

	return $content;
}
?>