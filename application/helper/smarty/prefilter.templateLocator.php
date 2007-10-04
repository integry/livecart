<?php

/**
 * Used to display individual template names in Customization Mode while hovering over page blocks
 *
 * @param string $tplSource
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems  
 */
function smarty_prefilter_templateLocator($tplSource, $smarty)
{
	$file = $smarty->_current_file;

	$paths = array(					
				'custom:',
				ClassLoader::getRealPath('application.view.'),
				ClassLoader::getRealPath('storage.customize.view.')
			 );
	
	foreach ($paths as $path)
	{
		if ($path == substr($file, 0, strlen($path)))
		{
			$file = substr($file, strlen($path));
		}			
	}
	
	$file = str_replace('\\', '/', $file);
		
	$editUrl = $smarty->getApplication()->getRouter()->createUrl(array('controller' => 'backend.template', 'action' => 'editPopup', 'query' => array('file' => $file)));
	
	return '<div class="templateLocator"><span class="templateName"><a onclick="window.open(\'' . $editUrl . '\', \'template\', \'width=800,height=600,scrollbars=yes,resizable=yes\'); return false;" href="#">' . $file . '</a></span>' . $tplSource . '</div>';					
}

?>